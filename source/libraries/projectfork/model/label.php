<?php
/**
 * @package      Projectfork.Library
 * @subpackage   Model
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a project label.
 *
 */
class PFModelLabel extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_LABEL';


    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
       // Register dependencies
       JLoader::register('ProjectforkHelperAccess', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/access.php');

       parent::__construct($config);
    }


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Label', $prefix = 'PFTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


    public function getForm($data = array(), $loadData = true)
    {
        return false;
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
            // TODO?
        }

        return $item;
    }


    /**
     * Method to save a label reference.
     *
     * @param     array      $data    The form data.
     *
     * @return    boolean             True on success, False on error.
     */
    public function saveRef($data)
    {
        $dispatcher = JDispatcher::getInstance();
        $table      = $this->getTable('LabelRef');
        $key        = $table->getKeyName();
        $pk         = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $isNew      = true;

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');

        // Allow an exception to be thrown.
        try {
            // Load the row if saving an existing record.
            if ($pk > 0) {
                $table->load($pk);
                $isNew = false;
            }

            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            $dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));
        }
        catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        $pkName = $table->getKeyName();

        if (isset($table->$pkName)) {
            $this->setState($this->getName() . '.id', $table->$pkName);
        }

        $this->setState($this->getName() . '.new', $isNew);

        return true;
    }


    /**
     * Method to delete one or more label references.
     *
     * @param     array  &    $pks    An array of record primary keys.
     *
     * @return    boolean             True if successful, false if an error occurs.
     */
    public function deleteRef(&$pks)
    {
        // Initialise variables.
        $dispatcher = JDispatcher::getInstance();
        $pks = (array) $pks;
        $table = $this->getTable('LabelRef');

        // Include the content plugins for the on delete events.
        JPluginHelper::importPlugin('content');

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk)
        {
            if ($table->load($pk)) {
                if ($this->canDelete($table)) {

                    $context = $this->option . '.' . $this->name;

                    // Trigger the onContentBeforeDelete event.
                    $result = $dispatcher->trigger($this->event_before_delete, array($context, $table));

                    if (in_array(false, $result, true)) {
                        $this->setError($table->getError());
                        return false;
                    }

                    if (!$table->delete($pk)) {
                        $this->setError($table->getError());
                        return false;
                    }

                    // Trigger the onContentAfterDelete event.
                    $dispatcher->trigger($this->event_after_delete, array($context, $table));

                }
                else {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    $error = $this->getError();

                    if ($error) {
                        JError::raiseWarning(500, $error);
                        return false;
                    }
                    else {
                        JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
                        return false;
                    }
                }
            }
            else {
                $this->setError($table->getError());
                return false;
            }
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
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
     * Method to test whether a record can be deleted.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canDelete($record)
    {
        return true;
    }
}
