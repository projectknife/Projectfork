<?php
/**
 * @package      Projectfork Notifications
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JLoader::register('ProjectforkHelper', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');
require_once dirname(__FILE__) . '/helper.php';


/**
 * Projectfork Notifications plugin.
 *
 */
class plgContentPfnotifications extends JPlugin
{
    /**
     * The item table before it is saved/updated
     *
     * @var    object
     */
    protected $table_before;

    /**
     * The item table after it is saved/updated
     *
     * @var    object
     */
    protected $table_after;


    public function onContentBeforeSave($context, $table, $is_new = false)
    {
        $context = str_replace('form', '', $context);

        list($component, $item) = explode('.', $context, 2);

        // List of supported contexts.
        // The context tells us which kind of item we're dealing with.
        $supported = array(
            'com_projectfork.project',
            'com_projectfork.milestone',
            'com_projectfork.task',
            'com_projectfork.topic',
            'com_projectfork.comment'
        );

        // Check if the context is supported. Return true string if its not.
        if (!in_array($context, $supported)) {
            return true;
        }

        // Check if the plugin is disabled. Return true string if it is.
        if (!JPluginHelper::isEnabled('content', 'pfnotifications')) {
            return true;
        }

        if ($is_new) {
            $this->table_before = null;
        }
        else {
            $this->table_before = JTable::getInstance(ucfirst($item), 'PFtable');
            $this->table_before->load($table->id);
        }
    }


    public function onContentAfterSave($context, $table, $is_new = false)
    {
        $context = str_replace('form', '', $context);

        list($component, $item) = explode('.', $context, 2);

        $subject_method = 'get' . ucfirst($item) . 'Subject';
        $message_method = 'get' . ucfirst($item) . 'Message';
        $methods        = get_class_methods($this);

        // List of supported contexts.
        // The context tells us which kind of item we're dealing with.
        $supported = array(
            'com_projectfork.project',
            'com_projectfork.milestone',
            'com_projectfork.task',
            'com_projectfork.topic',
            'com_projectfork.reply',
            'com_projectfork.comment',
            'com_projectfork.attachment'
        );

        // Check if the context is supported. Return true string if its not.
        if (!in_array($context, $supported)) {
            return true;
        }

        // Check if the plugin is disabled. Return true string if it is.
        if (!JPluginHelper::isEnabled('content', 'pfnotifications')) {
            return true;
        }

        // Check if the item is active or not
        if (isset($table->state)) {
            if (intval($table->state) !== 1) {
                return true;
            }
        }

        // Check if the methods are available
        if (!in_array($subject_method, $methods) || !in_array($message_method, $methods)) {
            var_dump($methods);
            die();
            return true;
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Find all watching users of the item
        $lookup_item = $item;
        $lookup_id   = $table->id;

        if ($item == 'reply') {
            $lookup_item = 'topic';
            $lookup_id   = $table->topic_id;
        }

        if ($item == 'comment') {
            list($c_com, $c_type) = explode('.', $table->context, 2);

            $lookup_item = $c_type;
            $lookup_id   = $table->item_id;
        }

        if ($item == 'attachment') {
            $lookup_item = $table->item_type;
            $lookup_id   = $table->item_id;
        }

        $query->select('a.user_id')
              ->from('#__pf_ref_observer AS a')
              ->where('a.item_type = ' . $db->quote($db->escape($lookup_item)))
              ->where('a.item_id = ' . $db->quote((int) $lookup_id));

        $db->setQuery($query);
        $users = (array) $db->loadColumn();

        if (count($users) == 0) {
            return true;
        }

        // Load user objects and perform access check
        if (isset($table->access)) {
            foreach ($users AS $i => $u)
            {
                $user = JFactory::getUser((int) $u);

                if (!$user->authorise('core.admin', 'com_projectfork')) {
                    $allowed = $user->getAuthorisedViewLevels();

                    if (!in_array($table->access, $allowed)) {
                        unset($userlist[$i]);
                        continue;
                    }
                }

                $users[$i] = $user;
            }
        }
        else {
            foreach ($users AS $i => $u)
            {
                $users[$i] = JFactory::getUser((int) $u);
            }
        }

        if (count($users) == 0) {
            return true;
        }

        $txt_prefix = strtoupper(str_replace('.', '_', $context)) . '_EMAIL_' . ($is_new ? 'NEW' : 'UPD');

        $def_lang = JComponentHelper::getParams('com_languages')->get('administrator');
		$debug    = JFactory::getConfig()->get('debug_lang');
		$mailfrom = JFactory::getConfig()->get('mailfrom');
		$fromname = JFactory::getConfig()->get('fromname');
        $user     = JFactory::getUser();

        $this->table_after = $table;

        foreach ($users as $receiver)
		{
		    if ($receiver->id == $user->id) {
		        // Don't mail own actions to self
                continue;
		    }

            $lang = JLanguage::getInstance($receiver->getParam('site_language', $def_lang), $debug);
		    $lang->load('com_projectfork');

            $subject = $this->$subject_method($lang, $receiver, $user, $txt_prefix, $is_new);
            $message = $this->$message_method($lang, $receiver, $user, $txt_prefix, $is_new);

            /*echo "$subject_method - $message_method<br/>";
            echo $subject . "<br/>";
            echo nl2br($message);
            die();*/

            if ($subject === false || $message === false) {
                // Abort if the subject or message is False
                break;
            }

            JFactory::getMailer()->sendMail($mailfrom, $fromname, $receiver->email, $subject, $message);
		}

        return true;
    }


    protected function getProjectSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $format = $lang->_($txt_prefix . '_SUBJECT');
        $txt    = sprintf($format, $user->name, $this->table_after->title);

        return $txt;
    }


    protected function getProjectMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        // Get the changed fields
        $props = array(
            'description', 'catid', 'created_by', 'access',
            'start_date', 'end_date'
        );

        $changes = array();

        if (is_object($this->table_before) && is_object($this->table_after)) {
            $changes = ProjectforkHelper::getItemChanges($this->table_before, $this->table_after, $props);
        }

        if (!count($changes)) {
            return false;
        }

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $changes = PFnotificationsHelper::formatChanges($lang, $changes);
        $footer  = sprintf($lang->_('COM_PROJECTFORK_EMAIL_FOOTER'), JURI::root());
        $link    = JRoute::_(JURI::root() . ProjectforkHelperRoute::getDashboardRoute($this->table_after->id . ':' . $this->table_after->alias));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }


    protected function getMilestoneSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $format  = $lang->_($txt_prefix . '_SUBJECT');
        $project = PFnotificationsHelper::translateValue('project_id', $this->table_after->project_id);
        $txt     = sprintf($format, $project, $user->name, $this->table_after->title);

        return $txt;
    }


    protected function getMilestoneMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        // Get the changed fields
        $props = array(
            'description', 'created_by', 'access', 'start_date', 'end_date'
        );

        $changes = array();

        if (is_object($this->table_before) && is_object($this->table_after)) {
            $changes = ProjectforkHelper::getItemChanges($this->table_before, $this->table_after, $props);
        }

        if (!count($changes)) {
            return false;
        }

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $changes = PFnotificationsHelper::formatChanges($lang, $changes);
        $footer  = sprintf($lang->_('COM_PROJECTFORK_EMAIL_FOOTER'), JURI::root());
        $link    = JRoute::_(JURI::root() . ProjectforkHelperRoute::getMilestoneRoute($this->table_after->id, $this->table_after->project_id));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }


    protected function getTaskSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $format  = $lang->_($txt_prefix . '_SUBJECT');
        $project = PFnotificationsHelper::translateValue('project_id', $this->table_after->project_id);
        $txt     = sprintf($format, $project, $user->name, $this->table_after->title);

        return $txt;
    }


    protected function getTaskMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        if ($is_new) {
            return false;
        }

        // Get the changed fields
        $props = array(
            'description', 'created_by', 'access', 'start_date', 'end_date',
            'milestone_id', 'list_id', 'priority', 'complete', 'rate', 'estimate'
        );

        $changes = array();

        if (is_object($this->table_before) && is_object($this->table_after)) {
            $changes = ProjectforkHelper::getItemChanges($this->table_before, $this->table_after, $props);
        }

        if (!count($changes)) {
            return false;
        }

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $changes = PFnotificationsHelper::formatChanges($lang, $changes);
        $footer  = sprintf($lang->_('COM_PROJECTFORK_EMAIL_FOOTER'), JURI::root());
        $link    = JRoute::_(JURI::root() . ProjectforkHelperRoute::getTaskRoute($this->table_after->id, $this->table_after->project_id, $this->table_after->milestone_id, $this->table_after->list_id));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }


    protected function getTopicSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $format  = $lang->_($txt_prefix . '_SUBJECT');
        $project = PFnotificationsHelper::translateValue('project_id', $this->table_after->project_id);
        $txt     = sprintf($format, $project, $user->name, $this->table_after->title);

        return $txt;
    }


    protected function getTopicMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        // Get the changed fields
        $props = array(
            'description', 'created_by', 'access'
        );

        $changes = array();

        if (is_object($this->table_before) && is_object($this->table_after)) {
            $changes = ProjectforkHelper::getItemChanges($this->table_before, $this->table_after, $props);
        }

        if (!count($changes)) {
            return false;
        }

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $changes = PFnotificationsHelper::formatChanges($lang, $changes);
        $footer  = sprintf($lang->_('COM_PROJECTFORK_EMAIL_FOOTER'), JURI::root());
        $link    = JRoute::_(JURI::root() . ProjectforkHelperRoute::getTopicRoute($this->table_after->id, $this->table_after->project_id));
        $txt     = sprintf($format, $receiver->name, $user->name, $changes, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }


    protected function getReplySubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        if (!$is_new) {
            return false;
        }

        $format  = $lang->_($txt_prefix . '_SUBJECT');
        $project = PFnotificationsHelper::translateValue('project_id', $this->table_after->project_id);
        $topic   = PFnotificationsHelper::translateValue('topic_id', $this->table_after->topic_id);
        $txt     = sprintf($format, $project, $user->name, $topic);

        return $txt;
    }


    protected function getReplyMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        if (!$is_new) {
            return false;
        }

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $footer  = sprintf($lang->_('COM_PROJECTFORK_EMAIL_FOOTER'), JURI::root());
        $link    = JRoute::_(JURI::root() . ProjectforkHelperRoute::getTopicRoute($this->table_after->topic_id, $this->table_after->project_id));
        $txt     = sprintf($format, $receiver->name, $user->name, strip_tags($this->table_after->description), $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }


    protected function getCommentSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        if (!$is_new) {
            return false;
        }

        list($com, $type) = explode('.', $this->table_after->context);

        $format  = $lang->_($txt_prefix . '_SUBJECT_' . strtoupper($type));
        $project = PFnotificationsHelper::translateValue('project_id', $this->table_after->project_id);
        $item    = PFnotificationsHelper::translateValue($type . '_id', $this->table_after->item_id);

        if ($item != 'project') {
            $txt = sprintf($format, $project, $user->name, $item);
        }
        else {
            $txt = sprintf($format, $project, $user->name);
        }

        return $txt;
    }


    protected function getCommentMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        if (!$is_new) {
            return false;
        }

        list($com, $item) = explode('.', $this->table_after->context);

        switch ($item)
        {
            case 'project':
                $link = ProjectforkHelperRoute::getDashboardRoute($this->table_after->project_id);
                break;

            default:
                $method = 'get' . ucfirst($item) . 'Route';
                $link = ProjectforkHelperRoute::$method($this->table_after->item_id, $this->table_after->project_id);
                break;
        }

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $footer  = sprintf($lang->_('COM_PROJECTFORK_EMAIL_FOOTER'), JURI::root());
        $link    = JRoute::_(JURI::root() . $link);
        $txt     = sprintf($format, $receiver->name, $user->name, strip_tags($this->table_after->description), $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }


    protected function getAttachmentSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        if (!$is_new) {
            return false;
        }

        $format  = $lang->_($txt_prefix . '_SUBJECT_' . strtoupper($this->table_after->item_type));
        $project = PFnotificationsHelper::translateValue('project_id', $this->table_after->project_id);
        $item    = PFnotificationsHelper::translateValue($this->table_after->item_type . '_id', $this->table_after->item_id);

        if ($this->table_after->item_type != 'project') {
            $txt = sprintf($format, $project, $user->name, $item);
        }
        else {
            $txt = sprintf($format, $project, $user->name);
        }

        return $txt;
    }


    protected function getAttachmentMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        if (!$is_new) {
            return false;
        }

        $type = $this->table_after->item_type;

        switch ($type)
        {
            case 'project':
                $link = ProjectforkHelperRoute::getDashboardRoute($this->table_after->project_id);
                break;

            default:
                $method = 'get' . ucfirst($type) . 'Route';
                $link = ProjectforkHelperRoute::$method($this->table_after->item_id, $this->table_after->project_id);
                break;
        }

        list($type, $id) = explode('.', $this->table_after->attachment);

        $item = PFnotificationsHelper::translateValue($type . '_id', (int) $id);

        $format  = $lang->_($txt_prefix . '_MESSAGE');
        $footer  = sprintf($lang->_('COM_PROJECTFORK_EMAIL_FOOTER'), JURI::root());
        $link    = JRoute::_(JURI::root() . $link);
        $txt     = sprintf($format, $receiver->name, $user->name, $item, $link);
        $txt     = str_replace('\n', "\n", $txt . "\n\n" . $footer);

        return $txt;
    }
}