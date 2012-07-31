<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a milestone form.
 *
 */
class ProjectforkModelTasklist extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string    
     */
    protected $text_prefix = 'COM_PROJECTFORK_TASKLIST';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     type      The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Tasklist', $prefix = 'PFTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


    /**
     * Method to get a single record.
     *
     * @param     integer    The id of the primary key.
     *
     * @return    mixed      Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry = new JRegistry;
            $registry->loadString($item->attribs);

            $item->attribs = $registry->toArray();
        }

        return $item;
    }


    /**
     * Method to get the record form.
     *
     * @param     array      $data        Data for the form.
     * @param     boolean    $loadData    True if the form is to load its own data (default case), false if not.
     *
     * @return    mixed                   A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_projectfork.tasklist', 'tasklist', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;


        // Check if a project id is already selected. If not, set the currently active project as value
        $project_id = (int) $form->getValue('project_id');

        if (!$this->getState('tasklist.id') && $project_id == 0) {
            $app       = JFactory::getApplication();
            $active_id = (int) $app->getUserState('com_projectfork.project.active.id', 0);

            $form->setValue('project_id', null, $active_id);
        }

        return $form;
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_projectfork.edit.tasklist.data', array());

        if (empty($data)) $data = $this->getItem();

        return $data;
    }


    /**
     * Method to save the form data.
     *
     * @param     array      The form data
     *
     * @return    boolean    True on success
     */
    public function save($data)
    {
        // Alter the title for save as copy
        if (JRequest::getVar('task') == 'save2copy') {
            list($title, $alias) = $this->generateNewTitle($data['alias'], $data['title'], $data['project_id'], $data['milestone_id']);
            $data['title'] = $title;
            $data['alias'] = $alias;
        }
        else {
            // Always re-generate the alias unless save2copy
            $data['alias'] = '';
        }

        // Store the record
        if (parent::save($data)) return true;

        return false;
    }


    /**
     * Custom clean the cache of com_projectfork and projectfork modules
     *
     */
    protected function cleanCache()
    {
        parent::cleanCache('com_projectfork');
    }


    /**
     * Method to change the title & alias.
     * Overloaded from JModelAdmin class
     *
     * @param     string     $alias           The alias
     * @param     string     $title           The title
     * @param     integer    $project_id      The project id
     * @param     integer    $milestone_id    The milestone id
     *
     * @return    array                       Contains the modified title and alias
     */
    protected function generateNewTitle($alias, $title, $project_id, $milestone_id)
    {
        // Alter the title & alias
        $table = $this->getTable();
        $data  = array('alias' => $alias, 'project_id' => $project_id, 'milestone_id' => $milestone_id);

        while ($table->load($data))
        {
            $m = null;

            if (preg_match('#-(\d+)$#', $alias, $m)) {
                $alias = preg_replace('#-(\d+)$#', '-' . ($m[1] + 1) . '', $alias);
            }
            else {
                $alias .= '-2';
            }

            if (preg_match('#\((\d+)\)$#', $title, $m)) {
                $title = preg_replace('#\(\d+\)$#', '(' . ($m[1] + 1) . ')', $title);
            }
            else {
                $title .= ' (2)';
            }
        }

        return array($title, $alias);
    }
}
