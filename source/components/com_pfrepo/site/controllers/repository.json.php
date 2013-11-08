<?php
/**
 * @package      com_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controlleradmin');


/**
 * Projectfork Repo List Controller
 *
 */
class PFrepoControllerRepository extends PFControllerAdminJson
{
    /**
     * The default view
     *
     */
    protected $view_list = 'repository';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'DirectoryForm', $prefix = 'PFrepoModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    public function watch()
    {
        $rdata = array();
        $rdata['success']  = "true";
        $rdata['messages'] = array();
        $rdata['data']     = array();

        // Check for request forgeries
        if (!JSession::checkToken()) {
            $rdata['success']    = "false";
            $rdata['messages'][] = JText::_('JINVALID_TOKEN');

            $this->sendResponse($rdata);
        }

        // Make sure the user is logged in
        if (JFactory::getUser()->get('id') == 0) {
            $rdata['success']    = "false";
            $rdata['messages'][] = JText::_('JERROR_ALERTNOAUTHOR');

            $this->sendResponse($rdata);
        }

        $cid   = JRequest::getVar('did', array(), '', 'array');
        $task  = $this->getTask();
        $data  = array('watch' => 1, 'unwatch' => 0);
        $value = JArrayHelper::getValue($data, $task, 0, 'int');

        if (empty($cid)) {
            $rdata['success']    = "false";
            $rdata['messages'][] = JText::_($this->text_prefix . '_NO_ITEM_SELECTED');
        }
        else {
            // Get the model.
            $model = $this->getModel();

            // Make sure the item ids are integers
            JArrayHelper::toInteger($cid);

            // Publish the items.
            if (!$model->watch($cid, $value)) {
                 $rdata['success']    = "false";
                 $rdata['messages'][] = $model->getError();
            }
            else {
                if ($value == 1) {
                    $ntext = $this->text_prefix . '_N_ITEMS_WATCHED';
                }
                else {
                    $ntext = $this->text_prefix . '_N_ITEMS_UNWATCHED';
                }

                $rdata['success']    = "true";
                $rdata['messages'][] = JText::plural($ntext, count($cid));
            }
        }

        $this->sendResponse($rdata);
    }
}
