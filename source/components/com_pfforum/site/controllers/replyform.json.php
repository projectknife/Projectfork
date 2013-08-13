<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfforum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JLoader::register('PFforumControllerReply', JPATH_ADMINISTRATOR . '/components/com_pfforum/controllers/reply.json.php');

/**
 * Projectfork Reply Form Controller
 *
 */
class PFforumControllerReplyForm extends PFforumControllerReply
{
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
        $topic = (isset($data['topic_id']) ? (int) $data['topic_id'] : JRequest::getUInt('filter_topic'));

        $user   = JFactory::getUser();
        $asset  = 'com_pfforum.topic.' . $topic;
        $access = true;

        // Topic is required
        if (!$topic) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_TOPIC_NOT_FOUND'));
            return false;
        }

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__pf_topics')
                  ->where('id = ' . (int) $topic);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            $access = in_array($lvl, $user->getAuthorisedViewLevels());
        }

        return ($user->authorise('core.create', $asset) && $access);
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
        // Get form input
        $id     = (int) isset($data[$key]) ? $data[$key] : 0;

        $user   = JFactory::getUser();
        $uid    = JFactory::getUser()->get('id');
        $asset  = 'com_pfforum.reply.' . $id;
        $access = true;

        // Check if the user has viewing access when not a super admin
        if (!$user->authorise('core.admin')) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('access')
                  ->from('#__pf_replies')
                  ->where('id = ' . $id);

            $db->setQuery($query);
            $lvl = $db->loadResult();

            if (!in_array($lvl, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        // Check general edit permission first.
        if ($access->get('core.edit', $asset)) {
            return true;
        }

        // Fallback on edit.own.
        // First test if the permission is available.
        if (!$user->authorise('core.edit.own', $asset)) {
            return false;
        }

        // Load the item
        $record = $this->getModel()->getItem($id);

        // Abort if not found
        if (empty($record)) return false;

        // Now test the owner is the user.
        $owner = (int) isset($data['created_by']) ? (int) $data['created_by'] : $record->created_by;

        // If the owner matches 'me' then do the test.
        return ($owner == $uid && $uid > 0);
    }
}
