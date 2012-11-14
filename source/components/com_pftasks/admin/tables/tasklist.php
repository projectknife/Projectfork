<?php
/**
 * @package      Projectfork
 * @subpackage   Tasklists
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Task List table
 *
 */
class PFtableTasklist extends PFTable
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
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_task_lists', 'id', $db);
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
        return 'com_pftasklists.tasklist.' . (int) $this->$k;
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
        // Initialise variables.
        $asset_id = null;
        $db       = $this->getDbo();
        $query    = $db->getQuery(true);

        $query->select($this->_db->quoteName('id'))
              ->from($this->_db->quoteName('#__assets'))
              ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote("com_pftasks"));

        // Get the asset id from the database.
        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();

        if ($result) $asset_id = (int) $result;

        // Return the asset id.
        if ($asset_id) return $asset_id;

        return parent::_getAssetParentId($table, $id);
    }


    /**
     * Method to get the access level of the parent asset
     *
     * @return    integer
     */
    protected function _getParentAccess()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $milestone = (int) $this->milestone_id;
        $project   = (int) $this->project_id;

        if ($milestone > 0) {
            $query->select('access')
                  ->from('#__pf_milestones')
                  ->where('id = ' . $db->quote($milestone));

            $db->setQuery($query);
            $access = (int) $db->loadResult();
        }
        elseif ($project > 0) {
            $query->select('access')
                  ->from('#__pf_projects')
                  ->where('id = ' . $db->quote($project));

            $db->setQuery($query);
            $access = (int) $db->loadResult();
        }

        if (!$access) $access = 1;

        return $access;
    }


    /**
     * Method to get the children on an asset (which are not directly connected in the assets table)
     *
     * @param    string    $name    The name of the parent asset
     *
     * @return    array    The names of the child assets
     */
    public function getAssetChildren($name)
    {
        $assets = array();

        list($component, $item, $id) = explode('.', $name, 3);

        // Get the project assets
        if ($component == 'com_pfprojects' && $item == 'project') {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('c.*')
                  ->from('#__assets AS c')
                  ->join('INNER', $this->_tbl . ' AS a ON (a.asset_id = c.id)')
                  ->where('a.project_id = ' . $db->quote((int) $id))
                  ->group('c.id');

            $db->setQuery($query);
            $assets = (array) $db->loadObjectList();
        }

        // Get the milestone assets
        if ($component == 'com_pfmilestones' && $item == 'milestone') {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('c.*')
                  ->from('#__assets AS c')
                  ->join('INNER', $this->_tbl . ' AS a ON (a.asset_id = c.id)')
                  ->where('a.milestone_id = ' . $db->quote((int) $id))
                  ->group('c.id');

            $db->setQuery($query);
            $assets = (array) $db->loadObjectList();
        }

        return $assets;
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

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
        }

        if (trim(str_replace('&nbsp;', '', $this->description)) == '') {
            $this->description = '';
        }

        // Check if a project is selected
        if ((int) $this->project_id <= 0) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_PROJECT'));
            return false;
        }

        // Check for selected access level
        if ($this->access <= 0) {
            $this->access = $this->_getParentAccess();
        }

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
            $this->created = $date->toSql();
            if (empty($this->created_by)) $this->created_by = $user->get('id');
        }

        // Verify that the alias is unique
        $table = JTable::getInstance('Tasklist', 'PFtable');
        $data  = array('alias' => $this->alias, 'project_id' => $this->project_id, 'milestone_id' => $this->milestone_id);

        if ($table->load($data) && ($table->id != $this->id || $this->id==0)) {
            $this->setError(JText::_('JLIB_DATABASE_ERROR_TASKLIST_UNIQUE_ALIAS'));
            return false;
        }

        return parent::store($updateNulls);
    }


    /**
     * Converts record to XML
     *
     * @param     boolean    $mapKeysToText    Map foreign keys to text values
     * @return    string                       Record in XML format
     */
    function toXML($mapKeysToText=false)
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
