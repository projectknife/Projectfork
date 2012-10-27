<?php
/**
 * @package      Projectfork
 * @subpackage   Projects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('projectfork.table.table');


/**
 * Project table
 *
 */
class PFtableProject extends PFTable
{
    /**
     * Should the table delete all child items too?
     *
     * @var    boolean
     */
    protected $_delete_children = true;

    /**
     * Should the table update all child items too?
     *
     * @var    boolean
     */
    protected $_update_children = true;

    /**
     * The fields of the child items to update
     *
     * @var    array
     */
    protected $_update_fields = array('access', 'state', 'start_date', 'end_date');


    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_projects', 'id', $db);
    }


    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return    string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_pfprojects.project.' . (int) $this->$k;
    }


    /**
     * Method to return the title to use for the asset table.
     *
     * @return    string
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }


    /**
     * Get the parent asset id for the record
     *
     * @param     jtable     $table    A JTable object for the asset parent.
     * @param     integer    $id       The id for the asset
     *
     * @return    integer              The id of the asset's parent
     */
    protected function _getAssetParentId($table = null, $id = null)
    {
        $query    = $this->_db->getQuery(true);
        $asset_id = null;

        $query->select($this->_db->quoteName('id'))
              ->from($this->_db->quoteName('#__assets'))
              ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote("com_pfprojects"));

        // Get the asset id from the database.
        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();

        if ($result) $asset_id = (int) $result;

        // Return the asset id.
        if ($asset_id) return $asset_id;

        return parent::_getAssetParentId($table, $id);
    }


    /**
     * Overloaded bind function
     *
     * @param     array    $array     Named array
     * @param     mixed    $ignore    An optional array or space separated list of properties to ignore while binding.
     *
     * @return    mixed               Null if operation was satisfactory, otherwise returns an error string
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['attribs']) && is_array($array['attribs'])) {
            $registry = new JRegistry;
            $registry->loadArray($array['attribs']);
            $array['attribs'] = (string) $registry;
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new JRules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }


    /**
     * Overloaded check function
     *
     * @return    boolean    True on success, false on failure
     */
    public function check()
    {
        jimport('joomla.mail.helper');

        if (trim($this->title) == '') {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_TITLE'));
            return false;
        }

        if (trim($this->alias) == '') $this->alias = $this->title;
        $this->alias = JApplication::stringURLSafe($this->alias);
        if (trim(str_replace('-','', $this->alias)) == '') $this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');

        if (trim(str_replace('&nbsp;', '', $this->description)) == '') $this->description = '';

        // Check the start date is not earlier than the end date.
        if ($this->end_date > $this->_db->getNullDate() && $this->end_date < $this->start_date) {
            // Swap the dates
            $temp = $this->start_date;
            $this->start_date = $this->end_date;
            $this->end_date   = $temp;
        }

        // Check attribs
        $registry = new JRegistry;
        $registry->loadString($this->attribs);

        $website = $registry->get('website');
        $email   = $registry->get('email');

        // Validate website
        if ((strlen($website) > 0)
            && (stripos($website, 'http://') === false)
            && (stripos($website, 'https://') === false)
            && (stripos($website, 'ftp://') === false))
        {
            $registry->set('website', 'http://' . $website);
        }

        // Validate contact email
        if (!JMailHelper::isEmailAddress($email)) $registry->set('email', '');

        $this->attribs = (string) $registry;

        return true;
    }

    /**
     * Overrides JTable::store to set modified data and user id.
     *
     * @param     boolean    True to update fields even if they are null.
     *
     * @return    boolean    True on success.
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        if ($this->id) {
            // Existing item
            $this->modified    = $date->toSql();
            $this->modified_by = $user->get('id');
        }
        else {
            // New item. A project created_by field can be set by the user,
            // so we don't touch it if set.
            $this->created = $date->toSql();
            if (empty($this->created_by)) $this->created_by = $user->get('id');
        }

        // Verify that the alias is unique
        $table = JTable::getInstance('Project', 'PFtable');

        if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0)) {
            $this->setError(JText::_('COM_PROJECTFORK_ERROR_PROJECT_UNIQUE_ALIAS'));
            return false;
        }

        return parent::store($updateNulls);
    }


    /**
     * Method to delete referenced data of an item.
     *
     * @param     mixed      $pk    An primary key value to delete.
     *
     * @return    boolean
     */
    public function deleteReferences($pk)
    {
        // Delete related attachments
        $query = $this->_db->getQuery(true);
        $query->delete('#__pf_ref_attachments')
              ->where('project_id = ' . $this->_db->quote((int) $pk));

        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete related labels
        $query->clear();
        $query->delete('#__pf_labels')
              ->where('project_id = ' . $this->_db->quote((int) $pk));

        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete related label references
        $query->clear();
        $query->delete('#__pf_ref_labels')
              ->where('project_id = ' . $this->_db->quote((int) $pk));

        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete related watching users
        $query->clear();
        $query->delete('#__pf_ref_observer')
              ->where('project_id = ' . $this->_db->quote((int) $pk));

        $this->_db->setQuery($query);
        $this->_db->execute();

        return true;
    }


    /**
     * Converts record to XML
     *
     * @param     boolean    $mapKeysToText    Map foreign keys to text values
     * @return    string                       Record in XML format
     */
    public function toXML($mapKeysToText=false)
    {
        $db = JFactory::getDbo();

        if ($mapKeysToText) {
            $query = 'SELECT name'
                   . ' FROM #__users'
                   . ' WHERE id = ' . (int) $this->created_by;

            $db->setQuery($query);
            $this->created_by = $db->loadResult();
        }

        return parent::toXML($mapKeysToText);
    }
}
