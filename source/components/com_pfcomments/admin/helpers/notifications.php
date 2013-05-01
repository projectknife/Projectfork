<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JLoader::register('PFprojectsHelperRoute', JPATH_SITE . '/components/com_pfprojects/helpers/route.php');


/**
 * Email Notification Helper Class
 * This class is invoked by the Projectfork notifications plugin
 *
 */
abstract class PFcommentsNotificationsHelper
{
    /**
     * Supported item contexts
     *
     * @var    array
     */
    protected static $contexts = array('com_pfcomments.comment', 'com_pfcomments.form');

    /**
     * Email string prefix
     *
     * @var    string
     */
    protected static $prefix   = 'COM_PROJECTFORK_COMMENT_EMAIL';


    /**
     * Method that checks if the given context is supported by this component
     *
     * @param     string     $context    The item context
     *
     * @return    boolean
     */
    public static function isSupported($context)
    {
        return in_array($context, self::$contexts);
    }


    /**
     * Method to get the proper context item name
     * This is helpful if the frontend context differs from the backend.
     * For example: com_pfprojects.project vs com_pfprojects.form
     *
     * @param     string    $context    The item context
     *
     * @return    string
     */
    public static function getItemName($context)
    {
        return 'comment';
    }


    /**
     * Method to get a list of user id's which are observing the item
     *
     * @param     string     $context    The item context
     * @param     object     $table      Instance of the item table
     * @param     boolean    $is_new     True if the item is new
     *
     * @return    array
     */
    public static function getObservers($context, $table, $is_new = false)
    {
        if (!$is_new) {
            return array();
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.user_id')
              ->from('#__pf_ref_observer AS a')
              ->where('a.item_type = ' . $db->quote($db->escape($table->context)))
              ->where('a.item_id = ' . $db->quote((int) $table->item_id));

        $db->setQuery($query);
        $users = (array) $db->loadColumn();

        return $users;
    }


    /**
     * Method to generate the email subject
     *
     * @param     object     $lang         Instance of the default user language
     * @param     object     $receiveer    Instance of the the receiving user
     * @param     object     $user         Instance of the user who made the change
     * @param     object     $after        Instance of the item table after it was updated
     * @param     object     $before       Instance of the item table before it was updated
     * @param     boolean    $is_new       True if the item is new ($before will be null)
     *
     * @return    string
     */
    public static function getCommentSubject($lang, $receiver, $user, $after, $before, $is_new)
    {
        if (!$is_new) {
            return false;
        }

        list($component, $item) = explode('.', $after->context, 2);

        $class_name = 'PF' . str_replace('com_pf', '', $component) . 'NotificationsHelper';
        $value      = null;

        if (class_exists($class_name)) {
            $methods = get_class_methods($class_name);

            if (in_array('getItemName', $methods)) {
                $item = call_user_func(array($class_name, 'getItemName'), $after->context);
            }

            if (in_array('translateItem', $methods)) {
                $value = call_user_func_array(array($class_name, 'translateItem'), array($after->context, $after->item_id));
            }
        }

        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');

        $format  = $lang->_($txt_prefix . '_SUBJECT_' . strtoupper($item));
        $project = PFnotificationsHelper::translateValue('project_id', $after->project_id);

        if (!$value) {
            $value = PFnotificationsHelper::translateValue($item . '_id', $after->item_id);
        }

        if ($item != 'project') {
            $txt = sprintf($format, $project, $user->name, $value);
        }
        else {
            $txt = sprintf($format, $project, $user->name);
        }

        return $txt;
    }


    /**
     * Method to generate the email message
     *
     * @param     object     $lang         Instance of the default user language
     * @param     object     $receiveer    Instance of the the receiving user
     * @param     object     $user         Instance of the user who made the change
     * @param     object     $after        Instance of the item table after it was updated
     * @param     object     $before       Instance of the item table before it was updated
     * @param     boolean    $is_new       True if the item is new ($before will be null)
     *
     * @return    string
     */
    public static function getCommentMessage($lang, $receiver, $user, $after, $before, $is_new)
    {
        if (!$is_new) {
            return false;
        }

        list($component, $item) = explode('.', $after->context, 2);

        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');
        $class_name = 'PF' . str_replace('com_pf', '', $component) . 'NotificationsHelper';
        $value      = null;

        if (class_exists($class_name)) {
            if (in_array('getItemName', get_class_methods($class_name))) {
                $item = call_user_func(array($class_name, 'getItemName'), $after->context);
            }
        }

        switch ($item)
        {
            case 'project':
                $link = PFprojectsHelperRoute::getDashboardRoute($after->project_id);
                break;

            default:
                $class_name = 'PF' . str_replace('com_pf', '', $component) . 'HelperRoute';
                $method     = 'get' . ucfirst($item) . 'Route';
                $link       = '';

                if (file_exists(JPATH_SITE . '/components/' . $component . '/helpers/route.php')) {
                    JLoader::register($class_name, JPATH_SITE . '/components/' . $component . '/helpers/route.php');
                }

                if (class_exists($class_name)) {
                    if (in_array($method, get_class_methods($class_name))) {
                        $link = call_user_func_array(array($class_name, $method), array($after->item_id, $after->project_id));
                    }
                }
                break;
        }

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $footer  = sprintf($lang->_('COM_PROJECTFORK_EMAIL_FOOTER'), JURI::root());
        $link    = JRoute::_(JURI::root() . $link);
        $txt     = sprintf($format, $receiver->name, $user->name, strip_tags($after->description), $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }
}
