<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modellist');


/**
 * Methods supporting a list of attachments.
 *
 */
class ProjectforkModelAttachments extends JModelList
{
    /**
     * Constructor
     *
     * @param    array          An optional associative array of configuration settings.
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Method to get a list of attachments.
     *
     * @param     string     $item_type    Optional item type
     * @param     integer    $item_id      Optional item id
     *
     * @return    mixed                    An array of data items on success, false on failure.
     */
    public function getItems($item_type = NULL, $item_id = 0)
    {
        $item_type = (!empty($item_type) ? $item_type     : $this->getState('item.type'));
        $item_id   = ((int) $item_id > 0 ? (int) $item_id : $this->getState('item.id'));

        // Make sure we have an item to select from
        if (empty($item_type) || empty($item_id)) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_ATTACHMENTS_NO_ITEM_REFERENCE'));
            return array();
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Load just the ID's
        $query->select('a.id')
              ->from('#__pf_ref_attachments AS a')
              ->where('a.item_type = ' . $db->quote($db->escape($item_type)))
              ->where('a.item_id = ' . $db->quote((int) $item_id))
              ->order('a.attachment ASC');

        $db->setQuery((string) $query);
        $list = (array) $db->loadColumn();

        if ($db->getError()) {
            $this->setError($db->getErrorMsg());
            return $list;
        }

        $items      = array();
        $attachment = $this->getInstance('Attachment', 'ProjectforkModel', array('ignore_request' => true));

        // Get the fulle object of each attachment id
        foreach($list AS $id)
        {
            $items[] = $attachment->getItem($id);
        }

        return $items;
    }


    /**
     * Method to get the connections of a repo item
     *
     * @param     string    $attachment    The repo item
     *
     * @return    array     $items         The connected items
     */
    public function getConnections($attachment)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Load just the ID's
        $query->select('a.id')
              ->from('#__pf_ref_attachments AS a')
              ->where('a.attachment = ' . $db->quote($db->escape($attachment)))
              ->order('a.item_type ASC');

        $db->setQuery((string) $query);
        $items = (array) $db->loadColumn();

        if ($db->getError()) {
            $this->setError($db->getErrorMsg());
            return $items;
        }

        $count      = count($items);
        $attachment = $this->getInstance('Attachment', 'ProjectforkModel', array('ignore_request' => true));

        // Get the full object of each attachment id
        for ($i = 0; $i > $count; $i++)
        {
            $id = $items[$i];
            $items[$i] = $attachment->getItem($id);

            if (!$items[$i]) {
                unset($items[$i]);
            }
        }

        return $items;
    }


    /**
     * Method to attach multiple items from the repo to an item
     *
     * @param     array      $data          $the repo items
     * @param     string     $item_type     The item asset name or type
     * @param     integer    $item_id       The item id
     * @param     integer    $project_id    The project id to which the item belongs
     *
     * @return    boolean                   True on success, False on error
     */
    public function save($data = array(), $item_type = NULL, $item_id = 0, $project_id = 0)
    {
        $item_type  = (!empty($item_type)    ? $item_type        : $this->getState('item.type'));
        $item_id    = ((int) $item_id > 0    ? (int) $item_id    : $this->getState('item.id'));
        $project_id = ((int) $project_id > 0 ? (int) $project_id : $this->getState('item.project'));

        // Check if an item is set
        if (empty($item_type) || !$item_id || !$project_id) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_ATTACHMENTS_NO_ITEM_REFERENCE'));
            return false;
        }

        if (!is_array($data)) {
            $data = array();
        }

        // Load the existing attachments
        $attachment = $this->getInstance('Attachment', 'ProjectforkModel', array('ignore_request' => true));
        $existing   = $this->getItems($item_type, $item_id, true);
        $delete     = array();

        // Filter out attachments that are no longer there
        foreach ($existing AS $item)
        {
            if (!in_array($item->attachment, $data)) {
                $delete[] = $item->id;
            }
        }

        // Save attachments
        foreach ($data AS $item)
        {
            if (empty($item)) continue;

            $item_data = array(
                'id'         => 0,
                'item_type'  => $item_type,
                'item_id'    => $item_id,
                'project_id' => $project_id,
                'attachment' => $item
            );

            if (!$attachment->save($item_data)) {
                $this->setError($attachment->getError());
            }
        }

        // Delete attachments
        if (count($delete)) {
            if (!$attachment->delete($delete)) {
                $this->setError($attachment->getError());
            }
        }

        return true;
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Get potential form data
        $form = JRequest::getVar('jform', array(), 'post', 'array');


        // Item type
        $value = str_replace('form', '', JRequest::getCmd('view', ''));
        $this->setState('item.type', $value);


        // Item id
        $value = JRequest::getUint('id');

        if (!$value) {
            if (isset($form['id'])) {
                $value = (int) $form['id'];
            }
        }

        $this->setState('item.id', $value);


        // Project id
        $value = (int) $this->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');

        if (!$value) {
            if (isset($form['project_id'])) {
                $value = (int) $form['project_id'];
            }
        }

        $this->setState('item.project', $value);
    }
}
