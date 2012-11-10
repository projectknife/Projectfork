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
 * Item Model for an attachment.
 *
 */
class PFrepoModelAttachment extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_ATTACHMENT';


    /**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 */
	public function __construct($config = array())
	{
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
    public function getTable($type = 'Attachment', $prefix = 'PFtable', $config = array())
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
            // Try to get the record to which the item is attached to
            list($component, $item_type) = explode('.', $item->item_type, 2);

            $table = $this->getTable($item_type);

            if ($table) {
                if ($table->load($item->id)) {
                    $item->item_data = $table;
                }
                else {
                    $item->item_data = null;
                }
            }
            else {
                $item->item_data = null;
            }

            // Try to get repo item record
            list($type, $id) = explode('.', $item->attachment, 2);

            $table = $this->getTable($type);

            if ($table) {
                if ($table->load($id)) {
                    $item->repo_data = $table;
                }
                else {
                    $item->repo_data = null;
                }
            }
            else {
                $item->repo_data = null;
            }
        }

        return $item;
    }


    /**
     * Custom clean the cache of com_projectfork and projectfork modules
     *
     */
    protected function cleanCache()
    {
        parent::cleanCache('com_pfrepo');
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
        $user = JFactory::getUser();

        if ($user->authorise('core.admin') || empty($record->id) || $record->id == 0) {
            return true;
        }

        /*list($asset, $id) = explode('.', $record->attachment, 2);

        $groups      = $user->getAuthorisedViewLevels();
        $delete_repo = ProjectforkHelperAccess::getActions($asset, $id);

        return ($delete_item || $delete_repo);*/

        return true;
    }
}
