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


jimport('joomla.application.component.controllerform');


/**
 * Projectfork Reply Form Controller
 *
 */
class PFforumControllerReplyform extends JControllerForm
{
    /**
     * The default item view
     *
     * @var    string
     */
    protected $view_item = 'replyform';

    /**
     * The default list view
     *
     * @var    string
     */
    protected $view_list = 'replies';

    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
    protected $text_prefix = "COM_PROJECTFORK_REPLY";


    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Register quick-save as "save" action
        $this->registerTask('quicksave', 'save');
    }


    /**
     * Method to cancel an edit.
     *
     * @param     string     $key    The name of the primary key of the URL variable.
     *
     * @return    boolean            True if access level checks pass, false otherwise.
     */
    public function cancel($key = 'id')
    {
        $result = parent::cancel($key);

        return $result;
    }


    /**
     * Method to edit an existing record.
     *
     * @param     string     $key        The name of the primary key of the URL variable.
     * @param     string     $url_var    The name of the URL variable if different from the primary key.
     *
     * @return    boolean                True if access level check and checkout passes, false otherwise.
     */
    public function edit($key = null, $url_var = 'id')
    {
        $result = parent::edit($key, $url_var);

        return $result;
    }


    /**
     * Method to get a model object, loading it if required.
     *
     * @param     string    $name      The model name. Optional.
     * @param     string    $prefix    The class prefix. Optional.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object               The model.
     */
    public function &getModel($name = 'ReplyForm', $prefix = 'PFforumModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Method to save a record.
     *
     * @param     string     $key        The name of the primary key of the URL variable.
     * @param     string     $url_var    The name of the URL variable if different from the primary key.
     *
     * @return    boolean                True if successful, false otherwise.
     */
    public function save($key = null, $url_var = 'id')
    {
        $result = parent::save($key, $url_var);

        return $result;
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
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $access  = true;
        $levels  = $user->getAuthorisedViewLevels();
        $project = isset($data['project_id']) ? (int) $data['project_id'] : 0;
        $topic   = (isset($data['topic_id'])  ? (int) $data['topic_id']   : JRequest::getUInt('filter_topic'));

        // Topic is required
        if (!$topic) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_TOPIC_NOT_FOUND'));
            return false;
        }

        // Check if the user has access to the topic
        if (!$user->authorise('core.admin', 'com_pfforum')) {
            if ($topic && $access) {
                $query->clear();
                $query->select('access')
                      ->from('#__pf_topics')
                      ->where('id = ' . $db->quote((int) $topic));

                $db->setQuery($query);
                $access = in_array((int) $db->loadResult(), $levels);
            }
        }

        // Check if the user has access to the project
        if (!$user->authorise('core.admin', 'com_pfprojects')) {
            if ($project && $access) {
                $query->clear();
                $query->select('access')
                      ->from('#__pf_projects')
                      ->where('id = ' . $db->quote((int) $project));

                $db->setQuery($query);
                $access = in_array((int) $db->loadResult(), $levels);
            }
        }

        return ($user->authorise('core.create', 'com_pfforum') && $access);
    }


    /**
     * Method override to check if you can edit an existing record.
     *
     * @param     array      $data    An array of input data.
     * @param     string     $key     The name of the key for the primary key.
     *
     * @return    boolean
     */
    protected function allowEdit($data = array(), $key = 'id')
    {
        // Initialise variables.
        $id     = (int) isset($data[$key]) ? $data[$key] : 0;
        $uid    = JFactory::getUser()->get('id');
        $access = PFforumHelper::getReplyActions($id);

        // Check general edit permission first.
        if ($access->get('core.edit')) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if ($access->get('core.edit.own')) {
            // Now test the owner is the user.
            $owner = (int) isset($data['created_by']) ? $data['created_by'] : 0;

            if (empty($owner) && $id) {
                // Need to do a lookup from the model.
                $record = $this->getModel()->getItem($id);

                if (empty($record)) return false;

                $owner = $record->created_by;
            }

            // If the owner matches 'me' then do the test.
            if ($owner == $uid) return true;
        }

        // Since there is no asset tracking, revert to the component permissions.
        return parent::allowEdit($data, $key);
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
        $layout  = JRequest::getCmd('layout', 'edit');
        $item_id = JRequest::getUInt('Itemid');
        $project = JRequest::getUint('filter_project', 0);
        $topic   = JRequest::getUint('filter_topic', 0);
        $append  = '';


        // Setup redirect info.
        if ($tmpl) $append .= '&tmpl=' . $tmpl;

        $append .= '&layout=edit';
        if ($project) $append .= '&filter_project=' . $project;
        if ($topic)   $append .= '&filter_topic=' . $topic;
        if ($id)      $append .= '&' . $url_var . '=' . $id;
        if ($item_id) $append .= '&Itemid=' . $item_id;

        return $append;
    }


    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return    string    The arguments to append to the redirect URL.
     */
    protected function getRedirectToListAppend()
    {
        // Need to override the parent method completely.
        $tmpl    = JRequest::getCmd('tmpl');
        $project = JRequest::getUint('filter_project', 0);
        $topic   = JRequest::getUint('filter_topic', 0);
        $append  = '';

        // Setup redirect info.
        if ($project) $append .= '&filter_project=' . $project;
        if ($topic)   $append .= '&filter_topic=' . $topic;
        if ($tmpl)    $append .= '&tmpl=' . $tmpl;

        return $append;
    }
}
