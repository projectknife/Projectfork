<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfforum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Replies Feed list view class.
 *
 */
class PFforumViewReplies extends JViewLegacy
{
    /**
     * Generates a list of RSS feed items.
     *
     * @return    void
     */
    function display()
    {
        $app    = JFactory::getApplication();
        $doc    = JFactory::getDocument();
        $params = $app->getParams();

        $doc->link  = htmlspecialchars(JFactory::getURI()->toString());
        $feed_email = (($app->getCfg('feed_email') == '') ? 'site' : $app->getCfg('feed_email'));
        $site_email = $app->getCfg('mailfrom');

        // Set the query limit to the feed setting
        JRequest::setVar('limit', (int) $app->getCfg('feed_limit', 20));

        // Get model data
        $rows = $this->get('Items');

        foreach($rows as $row)
        {
            // Load individual item creator class
            $item = new JFeedItem();

            $item->title       = html_entity_decode($this->escape($row->topic_title), ENT_COMPAT, 'UTF-8');
            $item->link        = '';
            $item->description = $row->description;
            $item->date        = ($row->created ? date('r', strtotime($row->created)) : '');
            $item->author      = $row->author_name;
            $item->authorEmail = ($feed_email == 'site') ? $site_email : $row->author_email;

            // Categorize the item
            $item->category = array();

            // Project
            if (!empty($row->project_title)) {
                $item->category[] = html_entity_decode(
                    $this->escape($row->project_title),
                    ENT_COMPAT,
                    'UTF-8'
                );
            }

            // Topic
            if (!empty($row->topic_title)) {
                $item->category[] = html_entity_decode(
                    $this->escape($row->topic_title),
                    ENT_COMPAT,
                    'UTF-8'
                );
            }

            // Loads item info into the RSS array
            $doc->addItem($item);
        }
    }
}
