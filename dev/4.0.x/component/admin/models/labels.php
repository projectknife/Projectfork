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
 * Methods supporting a list of labels.
 *
 */
class ProjectforkModelLabels extends JModelList
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
     * @param     integer    $project_id      Optional project id
     *
     * @return    mixed                    An array of data items on success, false on failure.
     */
    public function getItems($project_id = 0)
    {
        // Make sure we have a project to select from
        if ((int) $project_id <= 0) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_LABELS_NO_PROJECT'));
            return array();
        }

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Load just the ID's
        $query->select('a.id, a.project_id, a.title, a.style, a.asset_group')
              ->from('#__pf_labels AS a')
              ->where('a.project_id = ' . $db->quote((int) $project_id))
              ->order('a.asset_group, a.title ASC');

        $db->setQuery((string) $query);
        $items = (array) $db->loadObjectList();

        if ($db->getError()) {
            $this->setError($db->getErrorMsg());
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
     * Method to store project labels
     *
     * @param     array      $data          The label data
     * @param     integer    $project_id    The project id to which the labels belong
     *
     * @return    boolean                   True on success, False on error
     */
    public function save($data = array(), $project_id = 0)
    {
        $project_id = ((int) $project_id > 0 ? (int) $project_id : $this->getState('item.project'));
        $success    = true;

        // Check if we have a project
        if (!$project_id) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_LABELS_NO_PROJECT'));
            return false;
        }

        if (!is_array($data)) {
            $data = array();
            return true;
        }

        // Load the existing labels
        $label    = $this->getInstance('Label', 'ProjectforkModel', array('ignore_request' => true));
        $existing = $this->getItems($project_id);
        $delete   = array();
        $ids      = array();

        // Get the IDs
        foreach($data AS $asset => $group)
        {
            if (isset($group['id'])) {
                foreach($group['id'] AS $id)
                {
                    if (!in_array($id, $ids) && $id > 0) {
                        $ids[] = (int) $id;
                    }
                }
            }
        }


        // Filter out items that are no longer there
        foreach ($existing AS $item)
        {
            $id = (int) $item->id;
            if (!in_array($id, $ids)) {
                $delete[] = $item->id;
            }
        }

        // Save labels
        foreach ($data AS $asset => $group)
        {
            if (isset($group['id']) && isset($group['title']) && isset($group['style'])) {
                foreach ($group['id'] AS $i => $id)
                {
                    $title = (isset($group['title'][$i]) ? trim($group['title'][$i]) : '');
                    $style = (isset($group['style'][$i]) ? $group['style'][$i] : '');

                    if ($title == '') continue;

                    $item_data = array(
                        'id'          => (int) $id,
                        'project_id'  => $project_id,
                        'title'       => $title,
                        'style'       => $style,
                        'asset_group' => $asset
                    );

                    if (!$label->save($item_data)) {
                        $this->setError($label->getError());
                        $success = false;
                    }
                }
            }
        }

        // Delete attachments
        if (count($delete)) {
            if (!$label->delete($delete)) {
                $this->setError($label->getError());
                $success = false;
            }
        }

        return $success;
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
