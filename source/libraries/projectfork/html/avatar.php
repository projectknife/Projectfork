<?php
/**
* @package      pkg_projectfork
* @subpackage   lib_projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
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
            static $cache  = array();
            static $vendor = null;

            $key = (int) $id;

            if (isset($cache[$key])) {
                return $cache[$key];
            }

            if (is_null($vendor)) {
                $params = JComponentHelper::getParams('com_projectfork');
                $vendor = $params->get('user_profile_avatar');
            }

            $img_path = null;

            switch ($vendor)
            {
                case 'cb':
                    $img_path = self::lookupCB($id);
                    break;

                case 'js':
                    $img_path = self::lookupJS($id);
                    break;

                case 'kunena':
                    $img_path = self::lookupKunena($id);
                    break;

                case 'gravatar':
                    $img_path = self::lookupGravatar($id);
                    break;

                case 'mosets':
                    $img_path = self::lookupMosets($id);
                    break;
            }

            if (!empty($img_path)) {
                return $img_path;
            }

            // Default - Projectfork avatar
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


        /**
         * Method to lookup a Community Builder user profile image path
         *
         * @param     integer    $id    The user slug
         *
         * @return    string            The profile url
         */
        protected static function lookupCB($id)
        {
            static $db;
            static $query;

            if (!$db) {
                $db    = JFactory::getDbo();
                $query = $db->getQuery(true);
            }

            $default = JURI::root(true) . '/components/com_comprofiler/images/english/tnnophoto.jpg';

            $query->clear()
                  ->select('avatar')
                  ->from('#__comprofiler')
                  ->where('user_id = ' . (int) $id)
                  ->where('avatarapproved = 1');

            $db->setQuery($query);
            $img = $db->loadResult();

            if (empty($img)) return $default;

            $path = JPath::clean(JPATH_ROOT . '/images/comprofiler/' . $img);
            if (!file_exists($path)) return $default;

            return JURI::root(true) . '/images/comprofiler/' . $img;
        }


        /**
         * Method to lookup a JomSocial user profile image path
         *
         * @param     integer    $id    The user slug
         *
         * @return    string            The profile url
         */
        protected static function lookupJS($id)
        {
            static $exists = null;

            if (is_null($exists)) {
                $path = JPATH_ROOT . '/components/com_community/libraries/core.php';

                $exists = file_exists($path);

                if ($exists) require_once $path;
            }

            $default = JURI::root(true) . '/media/com_projectfork/projectfork/images/icons/avatar.jpg';

            //if ($exists) die('dsfsdf');
            if (!$exists) return $default;

            return CFactory::getUser($id)->getAvatar();
        }


        /**
         * Method to lookup a Kunena user profile image path
         *
         * @param     integer    $id    The user slug
         *
         * @return    string            The profile url
         */
        protected static function lookupKunena($id)
        {
            static $installed = null;

            if (is_null($installed)) {
                // Initialize Kunena (if Kunena System Plugin isn't enabled).
                $api = JPATH_ADMINISTRATOR . '/components/com_kunena/api.php';
                $installed = file_exists($api);

                if ($installed) require_once $api;
            }

            if (!$installed) return null;

            $user = KunenaFactory::getUser((int) $id);

            return $user->getAvatarURL(200, 200);
        }


        /**
         * Method to lookup a Gravatar user profile image path
         *
         * @param     integer    $id    The user slug
         *
         * @return    string            The profile url
         */
        protected static function lookupGravatar($id)
        {
            static $db;
            static $query;
            static $ssl;

            if (!$db) {
                $db    = JFactory::getDbo();
                $query = $db->getQuery(true);
                $ssl   = JFactory::getURI()->isSSL();
            }

            $query->clear()
                  ->select('email')
                  ->from('#__users')
                  ->where('id = ' . (int) $id);

            $db->setQuery($query);
            $email = $db->loadResult();

            $default = JURI::root() . 'media/com_projectfork/projectfork/images/icons/avatar.jpg';

            if (empty($email)) return $default;

            $path = 'http' . ($ssl ? 's' : '') . '://www.gravatar.com/avatar/'
                  . md5(strtolower(trim($email)))
                  . '?d=' . urlencode($default)
                  . '&s=200';

            return $path;
        }


        /**
         * Method to lookup a Mosets Profile Picture plugin image path
         *
         * @param     integer    $id    The user slug
         *
         * @return    string            The profile url
         */
        protected static function lookupMosets($id)
        {
            static $exists = null;

            if (is_null($exists)) {
                $lib    = JPATH_LIBRARIES . '/mosets/profilepicture/profilepicture.php';
                $exists = file_exists($lib);

                if ($exists) require_once $lib;
            }

            $default = JURI::root(true) . '/media/com_projectfork/projectfork/images/icons/avatar.jpg';

            if (!$exists) return $default;

            $pic = new ProfilePicture((int) $id);

            if (!$pic->exists()) return $default;

            $url = str_replace('\\', '/', $pic->getURL());

            return $url;
        }
    }
}
