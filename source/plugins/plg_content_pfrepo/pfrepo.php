<?php
/**
 * @package      pkg_projectfork
 * @subpackage   plg_content_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Repository Content Plugin Class
 *
 */
class plgContentPFrepo extends JPlugin
{
    /**
     * Supported plugin contexts
     *
     * @var    array
     */
    protected $contexts = array(
        'com_pfprojects.project', 'com_pfprojects.form',
        'com_pfrepo.directory', 'com_pfforum.directoryform'
    );


    /**
     * "onContentAfterSave" event handler
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $is_new     New item indicator (True is new, False is update)
     *
     * @return    boolean                True
     */
    public function onContentAfterSave($context, $table, $is_new = false)
    {
        // Do nothing if the plugin is disabled
        if (!JPluginHelper::isEnabled('content', 'pfrepo')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Do nothing if this is a new item
        if ($is_new) return true;

        $context = $this->unalias($context);

        if ($context == 'com_pfprojects.project') {
            $this->updateParentAsset($table->id);
        }

        // Update access
        $this->updateAccess($context, $table->id, $table->access);

        return true;
    }


    /**
     * "onContentAfterDelete" event handler
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     *
     * @return    boolean                True
     */
    public function onContentAfterDelete($context, $table)
    {
        // Do nothing if the plugin is disabled
        if (!JPluginHelper::isEnabled('content', 'pfrepo')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        $context = $this->unalias($context);

        // Delete repo
        if ($context == 'com_pfprojects.project') {
            $this->deleteFromProject($table->id);
            $this->deleteProjectAsset($table->id);
        }

        return true;
    }


    /**
     * Method to unalias the context
     *
     * @param     string    $context    The context alias
     *
     * @return    string    $context    The actual context
     */
    protected function unalias($context)
    {
        switch ($context)
        {
            case 'com_pfprojects.form':
                return 'com_pfprojects.project';
                break;

            case 'com_pfrepo.directoryform':
                return 'com_pfrepo.directory';
                break;
        }

        return $context;
    }


    /**
     * Method to delete a project repo
     *
     * @param     integer    $id    The project
     *
     * @return    void
     */
    protected function deleteFromProject($id)
    {
        static $imported = false;

        if (!$imported) {
            jimport('projectfork.library');
            JLoader::register('PFtableDirectory', JPATH_ADMINISTRATOR . '/components/com_pfrepo/tables/directory.php');
            JLoader::register('PFtableNote', JPATH_ADMINISTRATOR . '/components/com_pfrepo/tables/note.php');
            JLoader::register('PFtableFile', JPATH_ADMINISTRATOR . '/components/com_pfrepo/tables/file.php');
            JLoader::register('PFrepoModelDirectory', JPATH_ADMINISTRATOR . '/components/com_pfrepo/models/directory.php');
            JLoader::register('PFrepoModelNote', JPATH_ADMINISTRATOR . '/components/com_pfrepo/models/note.php');
            JLoader::register('PFrepoModelFile', JPATH_ADMINISTRATOR . '/components/com_pfrepo/models/file.php');

            $imported = true;
        }

        $cfg   = array('ignore_request' => true);
        $model = JModelLegacy::getInstance('Directory', 'PFrepoModel', $cfg);

        if (!$model) return;

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
              ->from('#__pf_repo_dirs')
              ->where('project_id = ' . (int) $id)
              ->where('parent_id = 1');

        $db->setQuery($query, 0, 1);
        $pk = (int) $db->loadResult();

        $pks    = array($pk);
        $ignore = true;

        $model->delete($pks, $ignore);
    }


    /**
     * Method to update the access level of topics and replies
     * associated with the given context
     *
     * @param     string     $context    The context name
     * @param     integer    $id         The context id
     * @param     integer    $access     The access level
     *
     * @return    void
     */
    protected function updateAccess($context, $id, $access)
    {
        jimport('projectfork.library');

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $allowed = PFAccessHelper::getAccessTree($access);

        if ($context == 'com_pfrepo.directory') {
            $query->select('lft, rgt')
                  ->from('#__pf_repo_dirs')
                  ->where('id = ' . (int) $id);

            $db->setQuery($query);
            $dir = $db->loadObject();

            if (empty($dir)) return;

            // Get sub dirs
            $query->clear()
                  ->select('id')
                  ->from('#__pf_repo_dirs')
                  ->where('lft > ' . (int) $dir->lft)
                  ->where('rgt < ' . (int) $dir->rgt);

            $db->setQuery($query);
            $dirs   = (array) $db->loadColumn();
            $dirs[] = (int) $id;

            // Update sub-dirs
            $query->clear();
            $query->update('#__pf_repo_dirs')
                  ->set('access = ' . (int) $access)
                  ->where('lft > ' . (int) $dir->lft)
                  ->where('rgt < ' . (int) $dir->rgt);

            if (count($allowed) == 1) {
                $query->where('access <> ' . (int) $allowed[0]);
            }
            elseif (count($allowed) > 1) {
                $query->where('access NOT IN(' . implode(', ', $allowed) . ')');
            }

            $db->setQuery($query);
            $db->execute();

            // Update notes and files
            foreach ($dirs AS $pk)
            {
                // Update notes
                $query->clear();
                $query->update('#__pf_repo_notes')
                      ->set('access = ' . (int) $access)
                      ->where('dir_id = ' . (int) $pk);

                if (count($allowed) == 1) {
                    $query->where('access <> ' . (int) $allowed[0]);
                }
                elseif (count($allowed) > 1) {
                    $query->where('access NOT IN(' . implode(', ', $allowed) . ')');
                }

                $db->setQuery($query);
                $db->execute();

                // Update files
                $query->clear();
                $query->update('#__pf_repo_files')
                      ->set('access = ' . (int) $access)
                      ->where('dir_id = ' . (int) $pk);

                if (count($allowed) == 1) {
                    $query->where('access <> ' . (int) $allowed[0]);
                }
                elseif (count($allowed) > 1) {
                    $query->where('access NOT IN(' . implode(', ', $allowed) . ')');
                }

                $db->setQuery($query);
                $db->execute();
            }
        }
        else {
            // Update dirs
            $query->clear();
            $query->update('#__pf_repo_dirs')
                  ->set('access = ' . (int) $access)
                  ->where('project_id = ' . (int) $id);

            if (count($allowed) == 1) {
                $query->where('access <> ' . (int) $allowed[0]);
            }
            elseif (count($allowed) > 1) {
                $query->where('access NOT IN(' . implode(', ', $allowed) . ')');
            }

            $db->setQuery($query);
            $db->execute();

            // Update notes
            $query->clear();
            $query->update('#__pf_repo_notes')
                  ->set('access = ' . (int) $access)
                  ->where('project_id = ' . (int) $id);

            if (count($allowed) == 1) {
                $query->where('access <> ' . (int) $allowed[0]);
            }
            elseif (count($allowed) > 1) {
                $query->where('access NOT IN(' . implode(', ', $allowed) . ')');
            }

            $db->setQuery($query);
            $db->execute();

            // Update files
            $query->clear();
            $query->update('#__pf_repo_files')
                  ->set('access = ' . (int) $access)
                  ->where('project_id = ' . (int) $id);

            if (count($allowed) == 1) {
                $query->where('access <> ' . (int) $allowed[0]);
            }
            elseif (count($allowed) > 1) {
                $query->where('access NOT IN(' . implode(', ', $allowed) . ')');
            }

            $db->setQuery($query);
            $db->execute();
        }
    }


    /**
     * Method to change the hierarchy of all directory assets
     *
     * @param     integer    $project    The project id
     *
     * @return    boolean                True on success
     */
    protected function updateParentAsset($project)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Find the component asset id
        $query->select('id')
              ->from('#__assets')
              ->where('name = ' . $db->quote('com_pfrepo'));

        $db->setQuery($query);
        $com_asset = (int) $db->loadResult();

        if (!$com_asset) return false;

        // Find the project asset id
        $query->clear();
        $query->select('id')
              ->from('#__assets')
              ->where('name = ' . $db->quote('com_pfrepo.project.' . $project));

        $db->setQuery($query);
        $project_asset = (int) $db->loadResult();

        if (!$project_asset) return false;

        // Find all assets that need to be updated
        $query->clear();
        $query->select('a.asset_id')
              ->from('#__pf_repo_dirs AS a')
              ->join('INNER', '#__assets AS s ON s.id = a.asset_id')
              ->where('a.project_id = ' . (int) $project)
              ->where('a.parent_id = 1')
              ->order('a.id ASC');

        $db->setQuery($query);
        $pks = $db->loadColumn();

        if (empty($pks) || !is_array($pks)) return true;

        // Update each asset
        foreach ($pks AS $pk)
        {
            $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));

            $asset->load($pk);

            $asset->setLocation($project_asset, 'last-child');
            $asset->parent_id = $project_asset;

            if (!$asset->check() || !$asset->store(false)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Method to delete a component project asset
     *
     * @param     integer    $project    The project id
     *
     * @return    boolean                True on success
     */
    protected function deleteProjectAsset($project)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->delete('#__assets')
              ->where('name = ' . $db->quote('com_pfrepo.project.' . (int) $project));

        $db->setQuery($query);

        if (!$db->execute()) return false;

        return true;

    }
}
