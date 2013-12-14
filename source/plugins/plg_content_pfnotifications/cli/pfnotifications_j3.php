<?php
/**
 * @package      Projectfork Notifications
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

// Initialize Joomla framework
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php')) {
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';


/**
 * Cron job to send Projectfork notifications
 *
 * @since      4.2
 */
class PFNotificationCron extends JApplicationCli
{
    /**
     * Entry point for the script
     *
     */
    public function doExecute()
    {
        $mailfrom = JFactory::getConfig()->get('mailfrom');
		$fromname = JFactory::getConfig()->get('fromname');
        $db       = JFactory::getDbo();

        // Get plugin params
        $query = $db->getQuery(true);

        $query->select('params')
              ->from('#__extensions')
              ->where('element = ' . $db->quote('pfnotifications'))
              ->where('type = ' . $db->quote('plugin'));

        $db->setQuery($query);
        $plg_params = $db->loadResult();

        $params = new JRegistry();
        $params->loadString($plg_params);

        $limit = (int) $params->get('cron_limit');


        // Get a list of emails to send
        $query->clear();

        $query->select('id, email, subject, message, created')
              ->from('#__pf_emailqueue')
              ->order('id ASC');

        $db->setQuery($query, 0, $limit);
        $items = $db->loadObjectList();

        if (!is_array($items)) $items = array();

        // Send and delete each email
        foreach ($items AS $item)
        {
            $mailer = JFactory::getMailer();
            $mailer->sendMail($mailfrom, $fromname, $item->email, $item->subject, $item->message);

            $query->clear();
            $query->delete('#__pf_emailqueue')
                  ->where('id = ' . (int) $item->id);

            $db->setQuery($query);
            $db->execute();
        }
    }
}

JApplicationCli::getInstance('PFNotificationCron')->execute();
