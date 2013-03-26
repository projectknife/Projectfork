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


class PFrepoHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_pfrepo';


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

            $class::addEntry(
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
    public static function getBasePath($project = NULL)
    {
        jimport('joomla.filesystem.path');

        $params = JComponentHelper::GetParams('com_pfrepo');

        $base = JPATH_SITE . '/';
        $dest = $params->get('repo_basepath', '/media/com_projectfork/repo/');

        $fchar = substr($dest, 0, 1);
        $lchar = substr($dest, -1, 1);

        if ($fchar == '/' || $fchar == '\\') {
            $dest = substr($dest, 1);
        }

        if ($lchar == '/' || $lchar == '\\') {
            $dest = substr($dest, 0, -1);
        }

        if (is_numeric($project)) {
            $dest .= '/' . (int) $project;
        }

        $basepath = JPath::clean($base . $dest);

        return $basepath;
    }


    /**
     * Method for translating an upload error code into human readable format
     *
     * @param     integer    $num     The error code
     * @param     string     $name    The name of the file
     *
     * @return    string     $msg     The error message
     */
    public static function getFileErrorMsg($num, $name)
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
}
