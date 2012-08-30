<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


class ProjectforkControllerComment extends JControllerForm
{
    /**
     * Class constructor.
     *
     * @param     array              $config    A named array of configuration variables
     * @return    jcontrollerform
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Method to check if you can add a new record.
     * Extended classes can override this if necessary.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        if (empty($data)) {
           $context = JRequest::getCmd('filter_context');
           $item_id = JRequest::getUint('filter_item_id');

           if (empty($context) || $item_id == 0) {
                return false;
           }
        }

        return parent::allowAdd($data);
    }


    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param     integer    $id         The primary key id for the item.
     * @param     string     $url_var    The name of the URL variable for the id.
     *
     * @return    string                 The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($id = null, $url_var = 'id')
    {
        $tmpl    = JRequest::getCmd('tmpl');
        $layout  = JRequest::getCmd('layout', 'edit');
        $project = JRequest::getUint('filter_project', 0);
        $context = JRequest::getCmd('filter_context');
        $item_id = JRequest::getUint('filter_item_id');
        $append  = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout) {
            $append .= '&layout=' . $layout;
        }

        if ($id) {
            $append .= '&' . $url_var . '=' . $id;
        }

        if ($project) {
            $append .= '&filter_project=' . $project;
        }

        if ($context) {
            $append .= '&filter_context=' . $context;
        }

        if ($item_id) {
            $append .= '&filter_item_id=' . $item_id;
        }

        return $append;
    }
}
