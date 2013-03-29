<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.database.tableasset');


/**
 * File Table Class
 *
 */
class PFtableFile extends PFTable
{
    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_repo_files', 'id', $db);
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
        return 'com_pfrepo.file.' . (int) $this->$k;
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

        if ($this->dir_id) {
            // Build the query to get the asset id for the parent topic.
            $query->select('asset_id')
                  ->from('#__pf_repo_dirs')
                  ->where('id = ' . (int) $this->dir_id);

            // Get the asset id from the database.
            $this->_db->setQuery((string) $query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }

        if (!$asset_id) {
            // Build the query to get the asset id for the parent component.
            $query->clear();
            $query->select($this->_db->quoteName('id'))
                  ->from($this->_db->quoteName('#__assets'))
                  ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote("com_pfrepo"));

            // Get the asset id from the database.
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }

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
        $query   = $this->_db->getQuery(true);
        $dir     = (int) $this->dir_id;
        $project = (int) $this->project_id;

        if ($dir > 1) {
            $query->select('access')
                  ->from('#__pf_repo_dirs')
                  ->where('id = ' . $dir);
        }
        elseif ($project > 0) {
            $query->select('access')
                  ->from('#__pf_projects')
                  ->where('id = ' . $project);
        }

        $this->_db->setQuery($query);
        $access = (int) $this->_db->loadResult();

        if (!$access) $access = (int) JFactory::getConfig()->get('access');

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
            $query = $this->_db->getQuery(true);

            $query->select('c.*')
                  ->from('#__assets AS c')
                  ->join('INNER', $this->_tbl . ' AS a ON (a.asset_id = c.id)')
                  ->where('a.project_id = ' . (int) $id)
                  ->group('c.id');

            $this->_db->setQuery($query);
            $assets = (array) $this->_db->loadObjectList();
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
        if (trim(str_replace('&nbsp;', '', $this->title)) == '') {
            if ($this->file_name == '') {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_TITLE'));
                return false;
            }
            else {
                $this->title = $this->file_name;
            }
        }

        // Check if a project is selected
        if ((int) $this->project_id <= 0) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_PROJECT'));
            return false;
        }

        // Check if a directory is selected
        if ((int) $this->dir_id <= 1) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_DIRECTORY'));
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
     * @return    boolean    True on success.
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate()->toSql();
        $user = JFactory::getUser()->get('id');

        if ($this->id) {
            // Existing item
            $this->modified    = $date;
            $this->modified_by = $user;
        }
        else {
            // New item
            $this->created = $date;
            if (empty($this->created_by)) $this->created_by = $user;
        }

        // Store the main record
        $success = parent::store($updateNulls);

        return $success;
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
