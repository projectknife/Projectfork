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


jimport('joomla.database.tableasset');


/**
 * Topic table
 *
 */
class PFtableTopic extends PFTable
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
    protected $_update_fields = array('access', 'state');


    /**
     * Constructor
     *
     * @param     database         $db    A database connector object
     * @return    jtableproject
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_topics', 'id', $db);
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
        return 'com_pfforum.topic.' . (int) $this->$k;
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
     * Method to get the parent asset id for the record
     *
     * @param     jtable     $table    A JTable object for the asset parent
     * @param     integer    $id
     *
     * @return    integer
     */
    protected function _getAssetParentId($table = null, $id = null)
    {
        $asset_id = null;
        $query    = $this->_db->getQuery(true);

        // No asset found, fall back to the component
        $query->clear();
        $query->select($this->_db->quoteName('id'))
              ->from($this->_db->quoteName('#__assets'))
              ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote("com_pfforum"));

        // Get the asset id from the database.
        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();

        if ($result) $asset_id = (int) $result;

        // Return the asset id.
        if ($asset_id) return $asset_id;
        return parent::_getAssetParentId($table, $id);
    }


    /**
     * Method to get the project access level id
     *
     * @return    integer
     */
    protected function _getAccessProjectId()
    {
        if ((int) $this->project_id == 0) return 1;

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('access')
              ->from('#__pf_projects')
              ->where('id = '. (int) $this->project_id);

        $db->setQuery($query);
        $access = (int) $db->loadResult();

        if (!$access) $access = 1;

        return $access;
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
        if (trim($this->title) == '') {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_TITLE'));
            return false;
        }

        if (trim($this->alias) == '') $this->alias = $this->title;

        $this->alias = JApplication::stringURLSafe($this->alias);

        if (trim(str_replace('-','', $this->alias)) == '') {
            $this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
        }

        if (trim(str_replace('&nbsp;', '', $this->description)) == '') {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_DESC'));
            return false;
        }

        // Check if a project is selected
        if ((int) $this->project_id <= 0) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_PROJECT'));
            return false;
        }

        // Check for selected access level
        if ($this->access <= 0) {
            $this->access = $this->_getAccessProjectId();
        }

        return true;
    }


    /**
     * Overrides JTable::store to set modified data and user id.
     *
     * @param     boolean    True to update fields even if they are null.
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
            // New item. A created_by field can be set by the user, so we don't touch it if set.
            $this->created = $date->toSql();
            if (empty($this->created_by)) $this->created_by = $user->get('id');
        }

        // Verify that the alias is unique
        $table = JTable::getInstance('Topic', 'PFtable');

        if ($table->load(array('alias' => $this->alias, 'project_id' => $this->project_id)) && ($table->id != $this->id || $this->id==0)) {
            $this->setError(JText::_('JLIB_DATABASE_ERROR_TOPIC_UNIQUE_ALIAS'));
            return false;
        }

        // Store the main record
        $success = parent::store($updateNulls);

        return $success;
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
              ->where('item_id = ' . $this->_db->quote((int) $pk))
              ->where('item_type = ' . $this->_db->quote('com_pfforum.topic'));

        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete related label references
        $query->clear();
        $query->delete('#__pf_ref_labels')
              ->where('item_id = ' . $this->_db->quote((int) $pk))
              ->where('item_type = ' . $this->_db->quote('com_pfforum.topic'));

        $this->_db->setQuery($query);
        $this->_db->execute();

        // Delete related watching users
        $query->clear();
        $query->delete('#__pf_ref_observer')
              ->where('item_id = ' . $this->_db->quote((int) $pk))
              ->where('item_type = ' . $this->_db->quote('com_pfforum.topic'));

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
