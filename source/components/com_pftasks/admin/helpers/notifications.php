<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JLoader::register('PFtasksHelperRoute', JPATH_SITE . '/components/com_pftasks/helpers/route.php');


/**
 * Email Notification Helper Class
 * This class is invoked by the Projectfork notifications plugin
 *
 */
abstract class PFtasksNotificationsHelper
{
    /**
     * Supported item contexts
     *
     * @var    array
     */
    protected static $contexts = array('com_pftasks.task', 'com_pftasks.taskform');

    /**
     * Email string prefix
     *
     * @var    string
     */
    protected static $prefix   = 'COM_PROJECTFORK_TASK_EMAIL';


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
        return 'task';
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
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Get observers
        $query->select('a.user_id')
              ->from('#__pf_ref_observer AS a')
              ->where(
                '('
                . 'a.item_type = ' . $db->quote('com_pftasks.task')
                . ' AND a.item_id = ' . (int) $table->id
                . ')'
                . ($table->milestone_id > 0 ?
                ' OR ('
                . 'a.item_type = ' . $db->quote('com_pfmilestones.milestone')
                . ' AND a.item_id = ' . (int) $table->milestone_id
                . ')'
                : '')
                . ' OR ('
                . 'a.item_type = ' . $db->quote('com_pfprojects.project')
                . ' AND a.item_id = ' . (int) $table->project_id
                . ')'
            );

        $db->setQuery($query);
        $users = (array) $db->loadColumn();

        // Get assigned users
        $query->clear()
              ->select('user_id')
              ->from('#__pf_ref_users')
              ->where('item_type = ' . $db->quote('com_pftasks.task'))
              ->where('item_id = ' . (int) $table->id);

        $db->setQuery($query);
        $assigned = (array) $db->loadColumn();

        $return = array_merge($users, $assigned);

        return $return;
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
    public static function getTaskSubject($lang, $receiver, $user, $after, $before, $is_new)
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
    public static function getTaskMessage($lang, $receiver, $user, $after, $before, $is_new)
    {
        // Get the changed fields
        $props = array(
            'description', 'created_by', 'access', array('start_date', 'NE-SQLDATE'), array('end_date', 'NE-SQLDATE'),
            'milestone_id', 'list_id', 'priority', 'complete', 'rate', 'estimate'
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
        $link    = JRoute::_(JURI::root() . PFtasksHelperRoute::getTaskRoute($after->id, $after->project_id, $after->milestone_id, $after->list_id));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }
}
