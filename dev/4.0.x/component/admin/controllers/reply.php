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


class ProjectforkControllerReply extends JControllerForm
{
    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
    protected $text_prefix = "COM_PROJECTFORK_REPLY";


    /**
     * Class constructor.
     *
     * @param    array    $config    A named array of configuration variables
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
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
        $topic  = (isset($data['topic_id']) ? (int) $data['topic_id'] : JRequest::getUInt('filter_topic'));
        $access = ProjectforkHelperAccess::getActions('topic', $topic);

        if (!$topic) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_TOPIC_NOT_FOUND'));
            return false;
        }

        return $access->get('reply.create');
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
        $topic   = JRequest::getUint('filter_topic', 0);
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

        if ($topic) {
            $append .= '&filter_topic=' . $topic;
        }

        return $append;
    }
}
