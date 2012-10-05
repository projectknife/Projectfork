<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined('_JEXEC') or die;

jimport('joomla.application.component.view');


class ProjectforkViewUser extends JViewLegacy
{
	function display($tpl = null)
	{
	    $state   = $this->get('State');
        $item    = $this->get('Item');
        $params	 = $state->params;

        $modules    = JFactory::getDocument()->loadRenderer('modules');
        $dispatcher	= JDispatcher::getInstance();

        // Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

        if (!$state->get('user.id')) {
            JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_USER_NOT_FOUND'));
			return false;
        }

        // Process the content plugins.
        if ($item) {
            $item->title = $item->username;

    		// Import comment plugin only
    		JPluginHelper::importPlugin('content', 'pfcomments');

            // Trigger events
    		$results = $dispatcher->trigger('onContentPrepare', array ('com_projectfork.user', &$item, &$params, 0));

    		$item->event = new stdClass();
    		$results = $dispatcher->trigger('onContentAfterTitle', array('com_projectfork.user', &$item, &$params, 0));
    		$item->event->afterDisplayTitle = trim(implode("\n", $results));

    		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_projectfork.user', &$item, &$params, 0));
    		$item->event->beforeDisplayContent = trim(implode("\n", $results));

    		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_projectfork.user', &$item, &$params, 0));
    		$item->event->afterDisplayContent = trim(implode("\n", $results));
        }


        // Assign references
        $this->assignRef('params', $params);
        $this->assignRef('state',  $state);
        $this->assignRef('modules', $modules);
        $this->assignRef('item', $item);


        // Prepare the document
        $this->prepareDocument();


		parent::display($tpl);
	}


    /**
	 * Prepares the document
     *
	 */
	protected function prepareDocument()
	{
		$app	 = JFactory::getApplication();
		$menus	 = $app->getMenu();
		$pathway = $app->getPathway();
		$title	 = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else {
			$this->params->def('page_heading', JText::_('COM_PROJECTFORK_USER_DASHBOARD'));
		}


        // Set the page title
		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);


        // Set crawler behavior info
		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}


        // Set page description
        if($this->params->get('menu-meta_description')) {
            $this->document->setDescription($desc);
        }


        // Set page keywords
        if($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $keywords);
        }


		// Add feed links
		if ($this->params->get('show_feed_link', 1)) {
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
