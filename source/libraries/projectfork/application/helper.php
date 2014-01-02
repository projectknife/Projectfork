<?php
/**
 * @package      pkg_projectfork
 * @subpackage   lib_projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Application Helper Class
 *
 */
abstract class PFApplicationHelper
{
    /**
     * Holds the Projectfork related components
     *
     * @var    array
     */
    protected static $components;


    /**
     * URL routing cache
     *
     * @var    array
     */
    protected static $routes;


    /**
     * Method to get all projectfork related components
     * (starting with com_pf)
     *
     * @return    array
     */
    public static function getComponents()
    {
        if (is_array(self::$components)) {
            return self::$components;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('extension_id, element, client_id, enabled, access, protected')
              ->from('#__extensions')
              ->where($db->qn('type') . ' = ' . $db->quote('component'))
              ->where('(' . $db->qn('element') . ' = '  . $db->quote('com_projectfork')
                . ' OR ' . $db->qn('element') . ' LIKE ' . $db->quote('com_pf%')
                . ')'
              )
              ->order('extension_id ASC');

        $db->setQuery($query);
        $items = (array) $db->loadObjectList();
        $com   = array();

        foreach ($items AS $item)
        {
            $el = $item->element;

            $com[$el] = $item;
        }

        self::$components = $com;

        return self::$components;
    }


    /**
     * Method to check if a component exists or not
     *
     * @param     string     $name    The name of the component
     *
     * @return    boolean
     */
    public static function exists($name)
    {
        $components = self::getComponents();

        if (!array_key_exists($name, $components)) {
            return false;
        }

        return true;
    }


    /**
     * Method to check if a component is enabled or not
     *
     * @param     string    $name    The name of the component
     *
     * @return    mixed              Returns True if enabled, False if not, and NULL if not found.
     */
    public static function enabled($name)
    {
        $components = self::getComponents();

        if (!array_key_exists($name, $components)) {
            return null;
        }

        if ($components[$name]->enabled == '0') {
            return false;
        }

        return true;
    }


    /**
     * Method to check if a component uses the project asset
     *
     * @param     string     $name    The name of the component
     *
     * @return    boolean             True if it does
     */
    public static function usesProjectAsset($name)
    {
        $name = str_replace('com_', '', $name);

        if (!class_exists($name . 'Helper')) {
            $helper_file = JPATH_ADMINISTRATOR . '/components/com_' . $name . '/helpers/' . $name . '.php';

            if (!file_exists($helper_file)) return false;

            require_once $helper_file;

            if (!class_exists($name . 'Helper')) return false;
        }

        $vars = get_class_vars($name . 'Helper');

        if (!isset($vars['project_asset'])) return false;

        return $vars['project_asset'];
    }


    /**
     * Method to get the Projectfork config settings merged into
     * the project settings
     *
     * @param     integer    $id        Optional project id. If not provided, will use the currently active project
     *
     * @return    object     $params    The config settings
     */
    public static function getProjectParams($id = 0)
    {
        static $cache = array();

        $project = ($id > 0) ? (int) $id : self::getActiveProjectId();

        if (array_key_exists($project, $cache)) {
            return $cache[$project];
        }

        $params = JComponentHelper::GetParams('com_projectfork');

        // Get the project parameters if they exist
        if ($project) {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('attribs')
                  ->from('#__pf_projects')
                  ->where('id = ' . $db->quote($project));

            $db->setQuery((string) $query);
            $attribs = $db->loadResult();

            if (!empty($attribs)) {
                $registry = new JRegistry();
                $registry->loadString($attribs);

                $params->merge($registry);
            }
        }

        $cache[$project] = $params;

        return $cache[$project];
    }


    /**
     * Sets the currently active project for the user.
     * The active project serves as a global data filter.
     *
     * @param     int        $id    The project id
     *
     * @return    boolean           True on success, False on error
     **/
    public static function setActiveProject($id = 0)
    {
        static $model = null;

        if ($id == self::getActiveProjectId()) return true;

        if (is_null($model)) {
            $name   = (JFactory::getApplication()->isSite() ? 'Form' : 'Project');
            $config = array('ignore_request' => true);
            $model  = JModelLegacy::getInstance($name, 'PFprojectsModel', $config);
        }

        $result = $model->setActive(array('id' => (int) $id));

        if (!$result) {
            JFactory::getApplication()->enqueueMessage($model->getError(), 'error');
        }
        else {
            if ($id) {
                $title = self::getActiveProjectTitle();
                $msg   = JText::sprintf('COM_PROJECTFORK_INFO_NEW_ACTIVE_PROJECT', '"' . $title . '"');
                JFactory::getApplication()->enqueueMessage($msg);
            }
        }

        return $result;
    }


    /**
     * Returns the currently active project ID of the user.
     *
     * @param     string    $request    The name of the variable passed in a request.
     *
     * @return    int                   The project id
     **/
    public static function getActiveProjectId($request = null)
    {
        $key     = 'com_projectfork.project.active.id';
        $current = JFactory::getApplication()->getUserState($key);
        $current = (is_null($current) ? '' : $current);

        if (empty($request)) return $current;

        $request = JRequest::getVar($request, null);

        if (!is_null($request) && self::setActiveProject((int) $request)) {
            $current = is_numeric($request) ? (int) $request : '';
        }

        return $current;
    }


    /**
     * Returns the currently active project title of the user.
     *
     * @param     string    $alt    Alternative value of no project is set
     *
     * @return    string            The project title
     **/
    public static function getActiveProjectTitle($alt = '')
    {
        if ($alt) $alt = JText::_($alt);

        $title = JFactory::getApplication()->getUserState('com_projectfork.project.active.title', $alt);

        return $title;
    }


    public static function itemRoute($needles = null, $com_view = null)
    {
        $app       = JFactory::getApplication();
        $menus     = $app->getMenu('site');
        $com_name  = $app->input->get('option');
        $view_name = null;

        if ($com_view) {
            $parts = explode('.', $com_view);

            if (count($parts) == 2) {
                list($com_name, $view_name) = $parts;
            }
            else {
                $view_name = $com_view;
            }
        }

        // Prepare the reverse lookup array.
        if (!is_array(self::$routes)) {
            self::$routes = array();
        }

        if (!isset(self::$routes[$com_name])) {
            self::$routes[$com_name] = array();

            $component = JComponentHelper::getComponent($com_name);
            $items     = $menus->getItems('component_id', $component->id);

            foreach ($items as $item)
            {
                if (isset($item->query) && isset($item->query['view'])) {
                    $view = $item->query['view'];

                    if (!isset(self::$routes[$com_name][$view])) {
                        self::$routes[$com_name][$view] = array();
                    }

                    if (isset($item->query['id'])) {
                        self::$routes[$com_name][$view][$item->query['id']] = $item->id;
                    }
                    else {
                        self::$routes[$com_name][$view][0] = $item->id;
                    }
                }
            }
        }

        if ($needles) {
            foreach ($needles as $view => $ids)
            {
                if (isset(self::$routes[$com_name][$view])) {
                    foreach($ids as $id)
                    {
                        if (isset(self::$routes[$com_name][$view][(int)$id])) {
                            return self::$routes[$com_name][$view][(int)$id];
                        }
                    }
                }
            }
        }
        else {
            $active = $menus->getActive();

            if ($active && $active->component == $com_name) {
                if ($com_view) {
                    if (isset(self::$routes[$com_name][$view_name][0])) {
                        return self::$routes[$com_name][$view_name][0];
                    }
                    elseif ($com_view && isset($active->query['view']) && $active->query['view'] != $com_view) {
                        return null;
                    }
                }

                return $active->id;
            }
            else {
                if ($com_view) {
                    if (isset(self::$routes[$com_name][$view_name][0])) {
                        return self::$routes[$com_name][$view_name][0];
                    }
                }
            }
        }

        return null;
    }
}
