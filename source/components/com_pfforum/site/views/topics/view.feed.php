<?php
/**
 * @package      Projectfork
 * @subpackage   Forum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Feed list view class.
 *
 */
class PFforumViewTopics extends JViewLegacy
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
        $site_email = $app->get('mailfrom');

        // Set the query limit to the feed setting
        JRequest::setVar('limit', (int) $app->getCfg('feed_limit', 20));

        // Get model data
        $rows = $this->get('Items');

        foreach($rows as $row)
        {
            // URL link to item
            $link = JRoute::_(PFforumHelperRoute::getTopicRoute($row->slug, $row->project_slug));

            // Strip html from feed item title
            $title = $this->escape($row->title);
            $title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

            $author = $row->author_name;
            $desc   = $row->description;
            $date   = ($row->created ? date('r', strtotime($row->created)) : '');

            // Load individual item creator class
            $item = new JFeedItem();

            $item->title       = $title;
            $item->link        = $link;
            $item->description = $desc;
            $item->date        = $date;
            $item->author      = $author;
            $item->authorEmail = ($feed_email == 'site') ? $site_email : $row->author_email;

            // Categorize the item
            if ($row->project_id > 0) {
                // Strip html from feed item title
                $category = $this->escape($row->project_title);
                $category = html_entity_decode($category, ENT_COMPAT, 'UTF-8');

                $item->category = array($category);
            }

            // Loads item info into the RSS array
            $doc->addItem($item);
        }
    }
}
