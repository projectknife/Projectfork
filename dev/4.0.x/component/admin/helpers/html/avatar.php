<?php
/**
* @package      Projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


if (!class_exists('ProjectforkAvatar'))
{
    /**
     * Abstract class for User Avatar
     *
     */
    abstract class ProjectforkAvatar
    {
        public static function image($id, $name = null)
        {
            $img_url = self::lookup($id);
            $attr    = '';

            if ($name) $attr .= ' title="' . htmlspecialchars($name, ENT_COMPAT, 'UTF-8') . '"';

            $html = array();
            $html[] = '<img src="' . $img_url . '" ' . $attr . '/>';

            return implode('', $html);
        }


        public static function path($id)
        {
            return self::lookup($id);
        }


        protected static function lookup($id)
        {
            static $cache = array();

            $key = (int) $id;

            if (isset($cache[$key])) {
                return $cache[$key];
            }

            $base_path = JPATH_ROOT . '/media/com_projectfork/repo/0/avatar';
            $base_url  = JURI::root(true) . '/media/com_projectfork/repo/0/avatar';
            $img_path  = NULL;

            if (JFile::exists($base_path . '/' . $key . '.jpg')) {
                $img_path = $base_url . '/' . $key . '.jpg';
            }
            elseif (JFile::exists($base_path . '/' . $key . '.jpeg')) {
                $img_path = $base_url . '/' . $key . '.jpeg';
            }
            elseif (JFile::exists($base_path . '/' . $key . '.png')) {
                $img_path = $base_url . '/' . $key . '.png';
            }
            elseif (JFile::exists($base_path . '/' . $key . '.gif')) {
                $img_path = $base_url . '/' . $key . '.gif';
            }
            else {
                $img_path = JURI::root(true) . '/media/com_projectfork/projectfork/images/icons/avatar.jpg';
            }

            $cache[$key] = $img_path;

            return $cache[$key];
        }
    }
}