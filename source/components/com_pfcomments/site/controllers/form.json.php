<?php
/**
* @package      Projectfork
* @subpackage   Comments
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


jimport('projectfork.controller.form.json');


/**
 * Projectfork Comment Form Controller
 *
 */
class PFcommentsControllerForm extends PFControllerFormJson
{
    /**
     * The item view name.
     *
     * @var    string
     **/
    protected $view_item = 'form';

    /**
     * The list view name.
     *
     * @var    string
     **/
    protected $view_list = 'comments';


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'Form', $prefix = 'PFcommentsModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to check if you can add a new record.
     *
     * @param     array      $data    An array of input data.
     *
     * @return    boolean
     */
    protected function allowAdd($data = array())
    {
        // Get form input
        $project = isset($data['project_id']) ? (int) $data['project_id'] : PFApplicationHelper::getActiveProjectId();

        $user   = JFactory::getUser();
        $asset  = 'com_pfcomments';
        $access = true;

        // Check if the user has access to the project
        if ($project) {
            // Check if in allowed projects when not a super admin
            if (!$user->authorise('core.admin')) {
                $access = in_array($project, PFUserHelper::getAuthorisedProjects());
            }

            // Change the asset name
            $asset  .= '.project.' . $project;
        }

        return ($user->authorise('core.create', $asset) && $access);
    }
}
