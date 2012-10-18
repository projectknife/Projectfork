<?php
/**
 * @package      Projectfork Notifications
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Notifications plugin.
 *
 */
class plgContentPfnotifications extends JPlugin
{
    /**
     * The item before it is saved/updated
     *
     * @var    object
     */
    protected $before;

    /**
     * The item after it is saved/updated
     *
     * @var    object
     */
    protected $after;


    public function onContentBeforeSave($context, $table, $is_new = false)
    {
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

        $this->before = $table;
    }


    public function onContentAfterSave($context, $table, $is_new = false)
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
            'com_projectfork.reply',
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

        // Check if the item is active or not
        if (isset($table->state)) {
            if (intval($table->state) !== 1) {
                return true;
            }
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Find all watching users of the item
        $query->select('a.user_id')
              ->from('#__pf_ref_observer AS a')
              ->where('a.item_type = ' . $db->quote($db->escape($item)))
              ->where('a.item_id = ' . $db->quote((int) $table->id));

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

        $subject_method = 'get' . ucfirst($item) . 'Subject';
        $message_method = 'get' . ucfirst($item) . 'Message';

        $def_lang = JComponentHelper::getParams('com_languages')->get('administrator');
		$debug    = JFactory::getConfig()->get('debug_lang');
		$mailfrom = JFactory::getConfig()->get('mailfrom');
		$fromname = JFactory::getConfig()->get('fromname');
        $user     = JFactory::getUser();

        $this->after = $table;

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

            JFactory::getMailer()->sendMail($mailfrom, $fromname, $receiver->email, $subject, $message);
		}

        return true;
    }


    protected function getProjectSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $format = $lang->_($txt_prefix . '_SUBJECT');
        $txt    = sprintf($format, $user->name, $this->after->title);

        return $txt;
    }


    protected function getProjectMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $format = $lang->_($txt_prefix . '_MESSAGE');
        $txt    = sprintf($format, $receiver->name, $this->after->title);
        $txt    = str_replace('\n', "\n", $txt);

        return $txt;
    }


    protected function getMilestoneSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $txt = '';

        return $txt;
    }


    protected function getMilestoneMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $txt = '';

        return $txt;
    }


    protected function getTaskSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $txt = '';

        return $txt;
    }


    protected function getTaskMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $txt = '';

        return $txt;
    }


    protected function getTopicSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $txt = '';

        return $txt;
    }


    protected function getTopicMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $txt = '';

        return $txt;
    }


    protected function getReplySubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $txt = '';

        return $txt;
    }


    protected function getReplyMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $txt = '';

        return $txt;
    }


    protected function getCommentSubject($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $txt = '';

        return $txt;
    }


    protected function getCommentMessage($lang, $receiver, $user, $txt_prefix, $is_new)
    {
        $txt = '';

        return $txt;
    }
}