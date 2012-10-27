<?php
/**
* @package      Projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


jimport('joomla.filesystem.path');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


/**
 * Projectfork Image Processor class
 *
 */
class ProjectforkProcImage
{
    public static $valid_extension = array('jpg', 'jpeg', 'png', 'gif');

    public static function isImage($name, $path = NULL)
    {
        $ext = strtolower(JFile::getExt($name));

        if (!in_array($ext, self::$valid_extension)) {
            return false;
        }

        if (!empty($path)) {
            if (!JFile::exists($path)) {
                return false;
            }

            $dimensions = getimagesize($path);

            if (!is_array($dimensions)) {
                return false;
            }

            if ((int) $dimensions[0] <= 0 || (int) $dimensions[1] <= 0) {
                return false;
            }
        }

        return true;
    }


}