<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controlleradmin');


/**
 * Projectfork Task Lists List Controller
 *
 */
class ProjectforkControllerTasklists extends JControllerAdmin
{
    /**
     * The default list view
     *
     * @var    string    
     */
    protected $view_list = 'tasks';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'TasklistForm', $prefix = 'ProjectforkModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Override of parent method
     *
     * @see    jcontrolleradmin    
     */
    public function delete()
    {
        $cid = JRequest::getVar('cid', array(), '', 'array');
        $lid = JRequest::getVar('lid', array(), '', 'array');

        if (!count($cid)) {
            JRequest::setVar('cid', $lid);
        }

        parent::delete();
    }


    /**
     * Override of parent method
     *
     * @see    jcontrolleradmin    
     */
    public function publish()
    {
        $cid = JRequest::getVar('cid', array(), '', 'array');
        $lid = JRequest::getVar('lid', array(), '', 'array');

        if (!count($cid)) {
            JRequest::setVar('cid', $lid);
        }

        parent::publish();
    }


    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param     int       $id         The primary key id for the item.
     * @param     string    $url_var    The name of the URL variable for the id.
     *
     * @return    string                The arguments to append to the redirect URL.
     */
    protected function getRedirectToItemAppend($id = null, $url_var = 'id')
    {
        // Need to override the parent method completely.
        $tmpl    = JRequest::getCmd('tmpl');
        $layout  = JRequest::getCmd('layout');
        $item_id = JRequest::getUInt('Itemid');
        $return  = $this->getReturnPage();
        $append   = '';

        // Setup redirect info.
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;
        if ($layout)  $append .= '&layout=' . $layout;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;
        if ($return)  $append .= '&return=' . base64_encode($return);

        return $append;
    }


    /**
     * Get the return URL.
     * If a "return" variable has been passed in the request
     *
     * @return    string    The return URL.
     */
    protected function getReturnPage()
    {
        $return = JRequest::getVar('return', null, 'default', 'base64');

        if (empty($return) || !JUri::isInternal(base64_decode($return))) {
            return JURI::base();
        }

        return base64_decode($return);
    }
}
