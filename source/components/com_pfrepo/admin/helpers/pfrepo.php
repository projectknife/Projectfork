<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.filesystem.path');


abstract class PFrepoHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_pfrepo';

    /**
     * Indicates whether this component uses a project asset or not
     *
     * @var    boolean
     */
    public static $project_asset = true;


    /**
     * Configure the Linkbar.
     *
     * @param     string    $view    The name of the active view.
     *
     * @return    void
     */
    public static function addSubmenu($view)
    {
        $is_j3 = version_compare(JVERSION, '3.0.0', 'ge');
        $forms = array('directory', 'file', 'note');

        if (in_array($view, $forms) && $is_j3) return;

        $components = PFApplicationHelper::getComponents();
        $option     = JFactory::getApplication()->input->get('option');
        $class      = ($is_j3 ? 'JHtmlSidebar' : 'JSubMenuHelper');

        foreach ($components AS $component)
        {
            if ($component->enabled == '0') continue;

            $title = JText::_($component->element);
            $parts = explode('-', $title, 2);

            if (count($parts) == 2) $title = trim($parts[1]);

            call_user_func(
                array($class, 'addEntry'),
                $title,
                'index.php?option=' . $component->element,
                ($option == $component->element)
            );
        }
    }


    /**
     * Gets a list of actions that can be performed.
     *
     * @param     string     $name    The asset name
     * @param     int        $id      The item id
     *
     * @return    jobject
     */
    public static function getActions($name = 'directory', $id = 0)
    {
        $user   = JFactory::getUser();
        $result = new JObject;

        if (empty($id) || $id == 0) {
            $asset = self::$extension;
        }
        else {
            $asset = self::$extension . '.' . $name . '.' . (int) $id;
        }

        $actions = array(
            'core.admin', 'core.manage',
            'core.create', 'core.edit',
            'core.edit.own', 'core.edit.state',
            'core.delete'
        );

        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, $asset));
        }

        return $result;
    }


    /**
     * Method to get the base upload path for a project
     *
     * @param     int       $project     Optional project id
     *
     * @return    string    $basepath    The upload directory
     */
    public static function getBasePath($project = null)
    {
        static $cache = array();

        $project = (int) $project;

        // Check the cache
        if (isset($cache[$project])) return $cache[$project];

        $params = JComponentHelper::GetParams('com_pfrepo');
        $dest   = $params->get('repo_basepath', '/media/com_projectfork/repo/');
        $base   = JPATH_SITE . '/';

        $fchar = substr($dest, 0, 1);
        $lchar = substr($dest, -1, 1);

        if ($fchar == '/' || $fchar == '\\') $dest = substr($dest, 1);
        if ($lchar == '/' || $lchar == '\\') $dest = substr($dest, 0, -1);

        if ($project) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('path')
                  ->from('#__pf_repo_dirs')
                  ->where('project_id = ' . $project)
                  ->where('parent_id = 1');

            $db->setQuery($query);
            $path = $db->loadResult();

            if (empty($path)) {
                $query->clear()
                      ->select('alias')
                      ->from('#__pf_projects')
                      ->where('id = ' . $project);

                $db->setQuery($query);
                $path = $db->loadResult();
            }

            if ($path) {
                $dest .= '/' . $path;
            }
        }

        $cache[$project] = JPath::clean($base . $dest);

        return $cache[$project];
    }


    /**
     * Method to get the pyhsical path location of a file
     *
     * @param     string     $name    The file name
     * @param     integer    $dir     The directory id in which the file is stored
     *
     * @return    string              The path
     */
    public static function getFilePath($name, $dir)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('project_id, path')
              ->from('#__pf_repo_dirs')
              ->where('id = ' . (int) $dir);

        $db->setQuery($query);
        $dir = $db->loadObject();

        if (empty($dir)) return '';

        $base = PFrepoHelper::getBasePath();
        $file = $base . '/' . $dir->path . '/' . $name;

        // Look in the directory
        if (JFile::exists($file)) {
            return $base . '/' . $dir->path;
        }

        // Look in the base dir (4.0 backwards compat)
        $file = $base . '/' . $dir->project_id . '/' . $name;

        if (JFile::exists($file)) {
            return $base . '/' . $dir->project_id;
        }

        // Look in the base dir (3.0 backwards compat)
        $file = $base . '/project_' . $dir->project_id . '/' . $name;

        if (JFile::exists($file)) {
            return $base . '/project_' . $dir->project_id;
        }

        return '';
    }


    /**
     * Method for translating an upload error code into human readable format
     *
     * @param     integer    $num     The error code
     * @param     string     $name    The name of the file
     *
     * @return    string     $msg     The error message
     */
    public static function getFileErrorMsg($num, $name = '')
    {
        $size_limit = ini_get('upload_max_filesize');
        $name = '"' . htmlspecialchars($name, ENT_COMPAT, 'UTF-8') . '"';

        switch ($num)
        {
            case 1:
                $msg = JText::sprintf('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_' . $num, $name, $size_limit);
                break;

            case 2:
                $msg = JText::sprintf('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_' . $num, $name);
                break;

            case 3:
            case 7:
            case 8:
                $msg = JText::sprintf('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_' . $num, $name);
                break;

            case 4:
            case 6:
                $msg = JText::_('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_' . $num);
                break;

            default:
                $msg = JText::sprintf('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_UNKNOWN' . $num, $name, $num);
                break;
        }

        return $msg;
    }


    /**
     * Method to get the max upload size from the php config
     *
     * @return    integer    $size    Max size in bytes
     */
    public static function getMaxUploadSize()
    {
        $val  = strtolower(trim(ini_get('upload_max_filesize')));
        $char = substr($val, -1);
        $size = (int) substr($val, 0, -1);

        switch ($char)
        {
            case 'g': $size *= 1024;
            case 'm': $size *= 1024;
            case 'k': $size *= 1024;
        }

        return $size;
    }


    /**
     * Method to get the max post size from the php config
     *
     * @return    integer    $size    Max size in bytes
     */
    public static function getMaxPostSize()
    {
        $val  = strtolower(trim(ini_get('post_max_size')));
        $char = substr($val, -1);
        $size = (int) substr($val, 0, -1);

        switch ($char)
        {
            case 'g': $size *= 1024;
            case 'm': $size *= 1024;
            case 'k': $size *= 1024;
        }

        return $size;
    }


    /**
     * Method to get the project id of a directory
     *
     * @param     integer    $id    The directory id
     *
     * @return    integer           The project id
     */
    public static function getProjectFromDir($id)
    {
        static $cache = array();

        // Check cache
        if (isset($cache[$id])) return $cache[$id];

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('project_id')
              ->from('#__pf_repo_dirs')
              ->where('id = ' . (int) $id);

        $db->setQuery($query);
        $cache[$id] = (int) $db->loadResult();

        return $cache[$id];
    }


    /**
     * Method to get the allowed file extensions for upload
     *
     * @return    array    $allowed    The allowed file extensions
     */
    public static function getAllowedFileExtensions()
    {
        $config      = JComponentHelper::getParams('com_pfrepo');
        $allowed_str = trim($config->get('filter_ext'));
        $allowed     = array();

        if (empty($allowed_str)) return $allowed;

        $extensions = explode(',', $allowed_str);

        foreach ($extensions AS $ext)
        {
            $clean_ext = strtolower(trim($ext));

            if (strpos($clean_ext, '.') === 0) {
                $clean_ext = substr($clean_ext, 1);
            }

            $allowed[] = $clean_ext;
        }

        sort($allowed);

        return $allowed;
    }
}
