<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Milestone Feed list view class.
 *
 */
class PFmilestonesViewMilestones extends JViewLegacy
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

            $item->title       = html_entity_decode($this->escape($row->title), ENT_COMPAT, 'UTF-8');
            $item->link        = JRoute::_(PFmilestonesHelperRoute::getMilestoneRoute($row->slug, $row->project_slug));
            $item->description = $row->description;
            $item->date        = ($row->created ? date('r', strtotime($row->created)) : '');
            $item->author      = $row->author_name;
            $item->authorEmail = ($feed_email == 'site') ? $site_email : $row->author_email;

            // Categorize the item
            if (!empty($row->project_title)) {
                $item->category = array(
                    html_entity_decode(
                        $this->escape($row->project_title),
                        ENT_COMPAT,
                        'UTF-8'
                    )
                );
            }

            // Loads item info into the RSS array
            $doc->addItem($item);
        }
    }
}
