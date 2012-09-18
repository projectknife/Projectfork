<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class ProjectforkHelperRepository
{
    public static function getBasePath($project = NULL)
    {
        $params = JComponentHelper::GetParams('com_projectfork');

        $base = JPATH_ROOT . '/';
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


    public static function getFileErrorMsg($num, $name, $size)
    {
        $size_limit = ini_get('upload_max_filesize');

        switch ($num)
        {
            case 1:
                $msg = JText::sprintf('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_' . $num, $name, $size, $size_limit);
                break;

            case 2:
                $msg = JText::sprintf('COM_PROJECTFORK_WARNING_FILE_UPLOAD_ERROR_' . $num, $name, $size);
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
