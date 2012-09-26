<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Access Helper Class
 *
 */
class ProjectforkHelperAccess
{
    /**
     * Calculates and returns all available actions for the given asset
     *
     * @param     string     $asset_name        Optional asset item name
     * @param     integer    $asset_id          Optional asset id
     * @param     boolean    $active_project    If True, checks permissions of the currently active project instead of the core.
     *
     * @return    object
     */
    public static function getActions($asset_name = NULL, $asset_id = 0, $active_project = false)
    {
        static $results       = array();
        static $project_assets = array();

        if (!count($project_assets)) $project_assets = self::getAssetMap('project');

        $asset = 'com_projectfork';

        if ($asset_name) $asset .= '.' . $asset_name;
        if ($asset_id)   $asset .= '.' . $asset_id;


        if ($active_project || (in_array($asset_name, $project_assets) && $asset_id == 0)) {
            $pid = (int) JFactory::getApplication()->getUserState('com_projectfork.project.active.id', 0);

            if ($pid) return self::getActions('project', $pid);
        }


        if (array_key_exists($asset, $results)) {
            // Return cached result
            return $results[$asset];
        }
        else {
            // Actions for this asset not in cache yet
            $result  = new JObject;
            $user    = JFactory::getUser();
            $assets  = array_merge(array('core'), self::getAssetMap());
            $actions = array('create', 'edit', 'edit.own', 'edit.state', 'delete');

            $auth_admin = $user->authorise('core.admin',  $asset);

            $result->set('core.admin',  $auth_admin);
            $result->set('core.manage', $user->authorise('core.manage', $asset));

            // Check if the asset name  and ID is given and reduce the assets to check to this one
            if ($asset_name && in_array($asset_name, $assets)) {
                // Check general asset type including children
                $assets = array_merge(array($asset_name), self::getAssetMap($asset_name));
            }

            foreach($assets AS $name)
            {
                foreach($actions AS $action)
                {
                    if ($name == 'core') {
                        $result->set($name . '.' . $action, $user->authorise($name . '.' . $action, $asset));
                    }
                    else {
                        // Auth non core assets against core and admin
                        $auth_1 = $user->authorise($name . '.' . $action, $asset);
                        $auth_2 = $user->authorise('core.' . $action, $asset);
                        $result->set($name . '.' . $action, ($auth_1 || $auth_2 || $auth_admin));
                    }
                }
            }

            $results[$asset] = $result;
        }

        return $results[$asset];
    }


    /**
     * Method to get the child asset names of a parent asset
     *
     * @param     string    $asset    The parent asset name
     *
     * @return    array               The children
     */
    public static function getAssetMap($asset = 'core')
    {
        $map = array();
        $map['core']      = array('project', 'milestone', 'tasklist', 'task', 'directory', 'note', 'file', 'comment', 'topic', 'reply', 'time');
        $map['project']   = array('milestone', 'tasklist', 'task', 'directory', 'note', 'file', 'comment', 'topic', 'reply', 'time');
        $map['milestone'] = array('tasklist', 'task', 'comment');
        $map['tasklist']  = array('task', 'comment');
        $map['task']      = array('comment', 'time');
        $map['topic']     = array('reply');
        $map['directory'] = array('note', 'file');

        if (!array_key_exists($asset, $map)) {
            return array();
        }

        return $map[$asset];
    }


    /**
     * Returns all parent groups of the given group id
     *
     * @param     integer    $id    The group id
     *
     * @return    array             The parent groups
     */
    public function getGroupPath($id)
    {
        static $groups;
        static $path;

        // Preload all groups
        if (empty($groups)) {
            $db = JFactory::getDbo();

            $query = $db->getQuery(true)
                   ->select('parent.id, parent.lft, parent.rgt')
                   ->from('#__usergroups AS parent')
                   ->order('parent.lft');

            $db->setQuery((string) $query);
            $groups = (array) $db->loadObjectList('id');
        }

        if (empty($path)) $path = array();

        // Make sure group id is valid
        if (!array_key_exists($id, $groups)) return array();

        // Get parent groups and leaf group
        if (!isset($path[$id])) {
            $path[$id] = array();

            foreach ($groups as $group)
            {
                if ($group->lft <= $groups[$id]->lft && $group->rgt >= $groups[$id]->rgt) {
                    $path[$id][] = $group->id;
                }
            }
        }

        return $path[$id];
    }


    /**
     * Returns all groups with the given access level
     *
     * @param     integer    $access        The access level id
     * @param     boolean    $diagnostic    If true, will only load the group id's
     *
     * @return    array                     The groups
     */
    public function getGroupsByAccessLevel($access, $diagnostic = true)
    {
        static $cache = array();
        $cache_key    = $access . '.' . strval($diagnostic);

        if (isset($cache[$cache_key])) {
            return $cache[$cache_key];
        }

        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $groups = array();
        $fields = ($diagnostic ? 'a.id' : 'a.id, a.lft, a.rgt');

        // Handle public access
        if ($access == '1') {
            $query->select($fields)
                  ->from('#__usergroups');

            $db->setQuery((string) $query);
            $groups = (array) $db->loadColumn();

            $cache[$cache_key] = $groups;

            return $cache[$cache_key];
        }


        // Get the groups of the access level
        $query->select('a.rules')
              ->from('#__viewlevels AS a')
              ->where('a.id = '. (int) $access);

        $db->setQuery((string) $query);
        $rules = $db->loadResult();
        $rules = (empty($rules) ? array() : json_decode($rules));

        if (!count($rules)) {
            $cache[$cache_key] = array();
            return $cache[$cache_key];
        }


        // Loop through the top groups to find the children
        $groups = array();
        foreach($rules AS $gid)
        {
            $gid = (int) $gid;

            // Load the group data
            $query->clear();
            $query->select('a.id, a.lft, a.rgt')
                  ->from('#__usergroups AS a')
                  ->where('a.id = ' . $db->quote($gid))
                  ->join('LEFT', $query->quoteName('#__usergroups') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
                  ->group('a.id')
                  ->order('a.lft ASC');

            $db->setQuery((string) $query);

            $group = $db->loadObject();

            // Load child groups
            if (is_object($group)) {
                $groups[] = ($diagnostic ? $group->id : $group);

                $query->clear();
                $query->select($fields)
                      ->from('#__usergroups AS a')
                      ->join('LEFT', $query->quoteName('#__usergroups'). ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
                      ->where('a.lft > ' . intval($group->lft) . ' AND a.rgt < ' . intval($group->rgt))
                      ->group('a.id')
                      ->order('a.lft ASC');

                $db->setQuery((string) $query);
                $children = (array) ($diagnostic ? $db->loadColumn() : $db->loadObjectList());

                foreach($children AS $child)
                {
                    $groups[] = $child;
                }
            }
        }

        $cache[$cache_key] = $groups;

        return $cache[$cache_key];
    }


    /**
    * Returns all children of the given access level
    *
    * @param     integer    $id    The access level id
    *
    * @return    array             The child access levels
    */
    public static function getAccessTree($id)
    {
        static $cache = array();
        static $list  = null;

        // Load all access levels if not yet set
        if (is_null($list)) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.id, a.rules')
                  ->from('#__viewlevels AS a');

            $db->setQuery((string) $query);

            $list = (array) $db->loadObjectList();
        }

        if (isset($cache[$id])) {
            return $cache[$id];
        }

        $groups = self::getGroupsByAccessLevel($id);
        $tree   = array();

        if (!count($groups)) {
            $cache[$id] = $tree;

            return $cache[$id];
        }

        foreach ($list AS $item)
        {
            if (empty($item->rules) || $item->id == $id || in_array($item->id, $tree)) {
                continue;
            }

            $rules = (array) json_decode($item->rules);
            $count = count($rules);
            $found = 0;

            if ($count == 0) {
                continue;
            }

            foreach ($rules AS $rule)
            {
                if ($rule < 0) {
                    continue;
                }

                if (in_array($rule, $groups)) {
                    $found++;
                }
            }

            if ($found == $count) {
                $tree[] = $item->id;
            }
        }

        $cache[$id] = $tree;


        return $cache[$id];
    }


    /**
     * Method to get a Viewing Access Level based on the selected groups.
     * The Access level will be created if none is found
     *
     * @param     mixed      $rules     The selected rules from the form
     * @param     integer $access       The access level from the form (optional)
     * @param     integer $inherit       If set to true, will inherit the groups from $access if no groups have been selected
     *
     * @return    integer    $access    The viewing access level on success, False on error.
     */
    public static function getViewLevelFromRules(&$rules, $access = 0, $inherit = true)
    {
        JLoader::register('UsersModelLevel', JPATH_ADMINISTRATOR . '/components/com_users/models/level.php');

        if (!is_array($rules)) {
            return false;
        }

        $db     = JFactory::getDbo();
        $query  = $db->getQuery(true);
        $groups = array();

        // Filter out the groups from the rules
        foreach ($rules as $key => $value)
        {
            if (!is_numeric($key) || !is_numeric($value)) continue;

            $groups[] = (int) $value;
            unset($rules[$key]);
        }

        if (!count($groups)) {
            if ($access && $inherit) {
                $query->clear();
                $query->select('rules')
                      ->from('#__viewlevels')
                      ->where('id = ' . $db->quote((int) $access));

                $db->setQuery($query);
                $access_rules = $db->loadResult();

                $groups = (array) json_decode($access_rules);

                if (!count($groups)) {
                    return false;
                }
            }
            else {
                return false;
            }
        }

        if ($access > 1 && !$inherit) {
            // Parent access level is given, see if the rules are the same
            $query->clear();
            $query->select('rules')
                  ->from('#__viewlevels')
                  ->where('id = ' . $db->quote((int) $access));

            $db->setQuery($query);
            $access_rules = $db->loadResult();

            if (json_encode($groups) == $access_rules) {
                return (int) $access;
            }
        }

        // Try to find the access levels that are connecting to the groups
        $query->clear();
        $query->select('id')
              ->from('#__viewlevels')
              ->where('rules = ' . $db->quote(json_encode($groups)));

        $db->setQuery($query);
        $results = (array) $db->loadColumn();
        $count   = count($results);

        // Return the 1st access level found
        if ($count >= 1) {
            return $results[0];
        }

        // Create the access level if none is found
        $query->clear();
        $query->select('title')
              ->from('#__usergroups')
              ->where('id IN(' . implode(', ', $groups) . ')');

        $db->setQuery($query);
        $group_names = (array) $db->loadColumn();

        if (!count($group_names)) {
            return false;
        }

        // Generate level title
        $level_name = implode(', ', $group_names);
        $model      = new UsersModelLevel(array('ignore_request' => true));
        $table      = $model->getTable();

        $x = 100 - strlen($level_name);
        if ($x >= 0) $x = -3;

        while (strlen($level_name) > 100 || $table->load(array('title' => $level_name)))
        {
            $level_name = substr($level_name, 0, $x) . '...';
            $x--;
        }

        $data  = array('id' => 0, 'title' => $level_name, 'rules' => $groups);

        // Store access level
        if (!$model->save($data)) {
            return false;
        }

        return $model->getState('level.id');

    }
}
