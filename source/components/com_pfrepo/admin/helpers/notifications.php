<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JLoader::register('PFrepoHelperRoute', JPATH_SITE . '/components/com_pfrepo/helpers/route.php');


/**
 * Email Notification Helper Class
 * This class is invoked by the Projectfork notifications plugin
 *
 */
abstract class PFrepoNotificationsHelper
{
    /**
     * Supported item contexts
     *
     * @var    array
     */
    protected static $contexts = array('com_pfrepo.attachment',
                                       'com_pfrepo.file', 'com_pfrepo.fileform',
                                       'com_pfrepo.note', 'com_pfrepo.noteform'
                                       );

    /**
     * Email string prefix
     *
     * @var    string
     */
    protected static $prefix   = 'COM_PROJECTFORK_ATTACHMENT_EMAIL';


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
        switch ($context)
        {
            case 'com_pfrepo.file':
            case 'com_pfrepo.fileform':
                self::$prefix = 'COM_PFREPO_FILE_EMAIL';
                return 'file';
                break;

            case 'com_pfrepo.note':
            case 'com_pfrepo.noteform':
                self::$prefix = 'COM_PFREPO_NOTE_EMAIL';
                return 'note';
                break;

            default:
                self::$prefix = 'COM_PROJECTFORK_ATTACHMENT_EMAIL';
                return 'attachment';
                break;
        }
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
        $db = JFactory::getDbo();

        if (in_array($context, array('com_pfrepo.file', 'com_pfrepo.fileform', 'com_pfrepo.note', 'com_pfrepo.noteform'))) {
            $parents = self::getParentDirectories($table->dir_id);
            $query   = $db->getQuery(true);

            $query->select('a.user_id')
                  ->from('#__pf_ref_observer AS a');

            $query->where(
                '('
                . 'a.item_type = ' . $db->quote('com_pfrepo.directory')
                . ' AND a.item_id IN(' . implode(', ', $parents) . ') '
                . ')'
                . ' OR ('
                . 'a.item_type = ' . $db->quote('com_pfprojects.project')
                . ' AND a.item_id = ' . (int) $table->project_id
                . ')'
            );
        }
        else {
            if (isset($table->item_type) && isset($table->item_id) && !$is_new) {
                $query = $db->getQuery(true);

                $query->select('a.user_id')
                      ->from('#__pf_ref_observer AS a');

                $query->where('a.item_type = ' . $db->quote($db->escape($table->item_type)))
                      ->where('a.item_id = ' . $db->quote((int) $table->item_id));
            }
            else {
                return array();
            }
        }

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
    public static function getFileSubject($lang, $receiver, $user, $after, $before, $is_new)
    {
        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');

        $format  = $lang->_($txt_prefix . '_SUBJECT');
        $project = PFnotificationsHelper::translateValue('project_id', $after->project_id);
        $txt     = sprintf($format, $project, $user->name, $after->title);

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
    public static function getFileMessage($lang, $receiver, $user, $after, $before, $is_new)
    {
        // Get the changed fields
        $props = array(
            'description', 'created_by', 'access'
        );

        $changes = array();

        if (is_object($before) && is_object($after)) {
            $changes = PFObjectHelper::getDiff($before, $after, $props);
        }

        if ($is_new) {
            $changes = PFObjectHelper::toArray($after, $props);
        }

        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $changes = PFnotificationsHelper::formatChanges($lang, $changes);
        $footer  = sprintf($lang->_('COM_PROJECTFORK_EMAIL_FOOTER'), JURI::root());
        $link    = JRoute::_(JURI::root() . PFrepoHelperRoute::getFileRoute($after->id, $after->project_id, $after->dir_id));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
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
    public static function getNoteSubject($lang, $receiver, $user, $after, $before, $is_new)
    {
        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');

        $format  = $lang->_($txt_prefix . '_SUBJECT');
        $project = PFnotificationsHelper::translateValue('project_id', $after->project_id);
        $txt     = sprintf($format, $project, $user->name, $after->title);

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
    public static function getNoteMessage($lang, $receiver, $user, $after, $before, $is_new)
    {
        // Get the changed fields
        $props = array(
            'description', 'created_by', 'access'
        );

        $changes = array();

        if (is_object($before) && is_object($after)) {
            $changes = PFObjectHelper::getDiff($before, $after, $props);
        }

        if ($is_new) {
            $changes = PFObjectHelper::toArray($after, $props);
        }

        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $changes = PFnotificationsHelper::formatChanges($lang, $changes);
        $footer  = sprintf($lang->_('COM_PROJECTFORK_EMAIL_FOOTER'), JURI::root());
        $link    = JRoute::_(JURI::root() . PFrepoHelperRoute::getNoteRoute($after->id, $after->project_id, $after->dir_id));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
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
    public static function getAttachmentSubject($lang, $receiver, $user, $after, $before, $is_new)
    {
        if (!$is_new) {
            return false;
        }

        list($component, $item) = explode('.', $after->item_type, 2);

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
    public static function getAttachmentMessage($lang, $receiver, $user, $after, $before, $is_new)
    {
        if (!$is_new) {
            return false;
        }

        list($component, $item) = explode('.', $after->item_type, 2);

        $txt_prefix = self::$prefix . '_' . ($is_new ? 'NEW' : 'UPD');
        $class_name = 'PF' . str_replace('com_pf', '', $component) . 'NotificationsHelper';
        $value      = null;

        if (class_exists($class_name)) {
            $methods = get_class_methods($class_name);

            if (in_array('getItemName', $methods)) {
                $item = call_user_func(array($class_name, 'getItemName'), $after->context);
            }

            if (in_array('translateItem', $methods)) {
                $value = call_user_func_array(array($class_name, 'translateItem'), array($after->item_type, $after->item_id));
            }
        }

        if (!$value) {
            $value = PFnotificationsHelper::translateValue($item . '_id', $after->item_id);
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
        $txt     = sprintf($format, $receiver->name, $user->name, $value, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }


    protected static function getParentDirectories($id)
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('lft, rgt')
              ->from('#__pf_repo_dirs')
              ->where('id = ' . (int) $id);

        $db->setQuery($query);
        $data = $db->loadObject();

        if (empty($data)) {
            return array($id);
        }

        $query->clear();
        $query->select('id')
              ->from('#__pf_repo_dirs')
              ->where('lft < ' . $data->lft)
              ->where('rgt > ' . $data->rgt);

        $db->setQuery($query);
        $parents = $db->loadColumn();

        if (!is_array($parents)) {
            $parents = array();
        }

        $parents[] = $id;

        return $parents;
    }
}
