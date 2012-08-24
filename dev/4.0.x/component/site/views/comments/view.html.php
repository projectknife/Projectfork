<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Comment list view class.
 *
 */
class ProjectforkViewComments extends JView
{
    protected $items;
    protected $params;
    protected $state;
    protected $access;


    /**
     * Display the view
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $app    = JFactory::getApplication();
        $active = $app->getMenu()->getActive();

        $this->items  = $this->get('Items');
        $this->state  = $this->get('State');
        $this->params = $this->state->params;

        // Try to find the asset
        $asset   = null;
        $context = $this->state->get('filter.context');
        $item_id = (int) $this->state->get('filter.item_id');

        if ($context) {
            $context_parts = explode('.', $context);
            $asset = end($context_parts);

            if ($asset == 'user') {
                $asset = null;
            }
        }
        else {
            $item_id = 0;
        }

        $this->access = ProjectforkHelperAccess::getActions($asset, $item_id, true);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // Check for layout override
        if (isset($active->query['layout']) && (JRequest::getCmd('layout') == '')) {
            $this->setLayout($active->query['layout']);
        }

        // Display the view
        parent::display($tpl);

        if (JRequest::getVar('tmpl') == 'component') {
            jexit();
        }
    }
}
