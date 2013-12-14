<?php
/**
 * @package      Projectfork Notifications
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


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
        // Check if the plugin is disabled. Return true if it is not.
        if (!JPluginHelper::isEnabled('content', 'pfnotifications')) {
            return true;
        }

        // Component name must start with com_pf
        if (substr($context, 0 , 6) != 'com_pf') {
            return;
        }

        // Check config if sending is enabled for this type of event (new/update)
        $send_type = (int) $this->params->get('send_type');

        if (($is_new && $send_type == 2) || (!$is_new && $send_type == 1)) {
            return;
        }

        // Import PF library, just to be sure
        jimport('projectfork.library');

        // Make sure the item is supported
        if (!PFnotificationsHelper::isSupported($context)) {
            return;
        }

        list($component, $item) = explode('.', $context, 2);

        $class_name = 'PF' . str_replace('com_pf', '', $component) . 'NotificationsHelper';
        $methods    = get_class_methods($class_name);

        if (in_array('getItemName', $methods)) {
            $item = call_user_func(array($class_name, 'getItemName'), $context);
        }

        if ($is_new) {
            $this->table_before = null;
        }
        else {
            $this->table_before = JTable::getInstance(ucfirst($item), 'PFtable');

            if ($this->table_before) {
                $this->table_before->load($table->id);
            }
            else {
                $this->table_before = null;
            }
        }
    }


    public function onContentAfterSave($context, $table, $is_new = false)
    {
        // Check if the plugin is disabled. Return true if it is not.
        if (!JPluginHelper::isEnabled('content', 'pfnotifications')) {
            return true;
        }

        // Component name must start with com_pf
        if (substr($context, 0 , 6) != 'com_pf') {
            return true;
        }

        // Check config if sending is enabled for this type of event (new/update)
        $send_type = (int) $this->params->get('send_type');

        if (($is_new && $send_type == 2) || (!$is_new && $send_type == 1)) {
            return;
        }

        // Import PF library, just to be sure
        jimport('projectfork.library');

        // Make sure the item is supported
        if (!PFnotificationsHelper::isSupported($context)) {
            return true;
        }

        list($component, $item) = explode('.', $context, 2);

        $class_name = 'PF' . str_replace('com_pf', '', $component) . 'NotificationsHelper';
        $methods    = get_class_methods($class_name);

        if (in_array('getItemName', $methods)) {
            $item = call_user_func(array($class_name, 'getItemName'), $context);
        }

        $subject_method = 'get' . ucfirst($item) . 'Subject';
        $message_method = 'get' . ucfirst($item) . 'Message';
        $users_method   = 'getObservers';

        // Check if the item is active or not
        if (isset($table->state)) {
            if (intval($table->state) !== 1) {
                return true;
            }
        }

        // Check if the methods are available
        if (!in_array($subject_method, $methods) || !in_array($message_method, $methods) ||
            !in_array($users_method, $methods))
        {
            return true;
        }

        $users = call_user_func_array(array($class_name, $users_method), array($context, $table, $is_new));
        $dupe  = array();

        if (count($users) == 0) {
            return true;
        }

        // Load user objects and perform access check
        if (isset($table->access)) {
            foreach ($users AS $i => $u)
            {
                if (in_array($u, $dupe)) {
                    unset($users[$i]);
                    continue;
                }

                $dupe[] = $u;

                $user = JFactory::getUser((int) $u);

                if (!$user->authorise('core.admin', $component)) {
                    $allowed = $user->getAuthorisedViewLevels();

                    if (!in_array($table->access, $allowed)) {
                        unset($users[$i]);
                        continue;
                    }
                }

                $users[$i] = $user;
            }
        }
        else {
            foreach ($users AS $i => $u)
            {
                if (in_array($u, $dupe)) {
                    unset($users[$i]);
                    continue;
                }

                $dupe[] = $u;

                $users[$i] = JFactory::getUser((int) $u);
            }
        }

        if (count($users) == 0) {
            return true;
        }

        $def_lang = JComponentHelper::getParams('com_languages')->get('administrator');
		$debug    = JFactory::getConfig()->get('debug_lang');
		$mailfrom = JFactory::getConfig()->get('mailfrom');
		$fromname = JFactory::getConfig()->get('fromname');
        $user     = JFactory::getUser();
        $is_site  = JFactory::getApplication()->isSite();
        $date     = new JDate();
        $now      = $date->toSql();
        $store    = $this->params->get('send_method');

        $db = JFactory::getDbo();

        $this->table_after = $table;

        foreach ($users as $receiver)
		{
		    // Make sure we have a user object and an email address
            if (!is_object($receiver) || empty($receiver->email)) {
		        continue;
		    }

		    if ($receiver->id == $user->id) {
		        // Don't mail own actions to self
                continue;
		    }

            // Load the default language of the component
            $lang = JLanguage::getInstance($receiver->getParam('site_language', $def_lang), $debug);
		    $lang->load($component);

            if ($is_site) {
                $lang->load($component, JPATH_ADMINISTRATOR);
            }

            // Generate the subject and body
            $subject = call_user_func_array(array($class_name, $subject_method), array($lang, $receiver, $user, $this->table_after, $this->table_before, $is_new));
            $message = call_user_func_array(array($class_name, $message_method), array($lang, $receiver, $user, $this->table_after, $this->table_before, $is_new));

            if ($subject === false || $message === false) {
                // Abort if the subject or message is False
                break;
            }

            if (!$store) {
                // Send directly
                $mailer = JFactory::getMailer();
                $mailer->sendMail($mailfrom, $fromname, $receiver->email, $subject, $message);
            }
            else {
                // Store in db
                $data = new stdClass();

                $data->id      = null;
                $data->email   = $receiver->email;
                $data->subject = $subject;
                $data->message = $message;
                $data->created = $now;

                $db->insertObject('#__pf_emailqueue', $data);
            }
		}

        return true;
    }
}