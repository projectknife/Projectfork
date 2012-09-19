<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.database.tableasset');


/**
 * File Table Class
 *
 */
class PFTableFile extends JTable
{
    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        // Register dependencies
        JLoader::register('ProjectforkHelper', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');

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
        return 'com_projectfork.file.' . (int) $this->$k;
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
        elseif ($this->project_id) {
            $params   = ProjectforkHelper::getProjectParams($this->project_id);
            $repo_dir = (int) $params->get('repo_dir');
            $result   = null;

            if ($repo_dir) {
                // Try to get the asset id of the project repo
                $query->clear();
                $query->select('asset_id')
                      ->from('#__pf_repo_dirs')
                      ->where('id = ' . $repo_dir);

                $this->_db->setQuery((string) $query);
                $result = $this->_db->loadResult();
            }

            if ($result) {
                $this->dir = $repo_dir;
                $asset_id  = (int) $result;
            }
            else {
                // Build the query to get the asset id for the parent project.
                $query->select('asset_id')
                      ->from('#__pf_projects')
                      ->where('id = ' . (int) $this->project_id);

                // Get the asset id from the database.
                $this->_db->setQuery((string) $query);
                $result = $this->_db->loadResult();

                if ($result) $asset_id = (int) $result;
            }
        }

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
        if (trim(str_replace('&nbsp;', '', $this->title)) == '') {
            if ($this->file_name == '') {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_TITLE'));
                return false;
            }
            else {
                $this->title = $this->file_name;
            }
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
            // New item
            $this->created = $date->toSql();
            if (empty($this->created_by)) $this->created_by = $user->get('id');
        }

        // Store the main record
        $success = parent::store($updateNulls);

        return $success;
    }


    /**
     * Deletes all items by a reference field
     *
     * @param     mixed      $id       The parent item id(s)
     * @param     string     $field    The parent field name
     *
     * @return    boolean              True on success, False on error
     */
    public function deleteByReference($id, $field)
    {
        $db    = $this->_db;
        $query = $db->getQuery(true);

        // Generate the WHERE clause
        $where = $db->quoteName($field) . (is_array($id) ? ' IN(' . implode(', ', $id) . ')' : ' = ' . (int) $id );

        if (is_array($id) && count($id) === 1) {
            $where = $db->quoteName($field) . ' = ' . (int) $id[0];
        }

        // Delete the records. Note that the assets have already been deleted
        $query->delete($this->_db->quoteName($this->_tbl))
              ->where($where);

        $db->setQuery((string) $query);
        $db->query();

        return true;
    }


    /**
     * Updates all items by reference data and parent item
     *
     * @param     integer    $id       The parent item id
     * @param     string     $field    The parent field name
     * @param     array      $data     The parent data
     * @return    boolean              True on success, False on error
     */
    public function updateByReference($id, $field, $data)
    {
        $db        = $this->_db;
        $fields    = array_keys($data);
        $null_date = $db->getNullDate();
        $pk        = $this->_tbl_key;


        // Check if the fields exist
        foreach($fields AS $i => $tbl_field)
        {
            if (!property_exists($this, $tbl_field)) {
                unset($fields[$i]);
                unset($data[$tbl_field]);
            }
        }

        $tbl_fields = implode(', ', array_keys($data));

        // Return if no fields are left to update
        if (count($fields) == 0) {
            return true;
        }

        // Find access children if access field is in the data
        $access_children = array();
        if (in_array('access', $fields)) {
            $access_children = array_keys(ProjectforkHelper::getChildrenOfAccess($data['access']));
        }

        // Get the items we have to update
        // Get the items we have to update
        $where = $db->quoteName($field) . (is_array($id) ? ' IN(' . implode(', ', $id) . ')' : ' = ' . (int) $id );

        if (is_array($id) && count($id) === 1) {
            $where = $db->quoteName($field) . ' = ' . (int) $id[0];
        }

        $query = $db->getQuery(true);
        $query->select($this->_tbl_key . ', ' . $tbl_fields)
              ->from($db->quoteName($this->_tbl))
              ->where($where);

        $db->setQuery((string) $query);

        // Get the result
        $list = (array) $db->loadObjectList();


        // Update each item
        foreach($list AS $item)
        {
            $updates = array();

            foreach($data AS $key => $val)
            {
                switch($key)
                {
                    case 'access':
                        if ($val != $item->$key && !in_array($item->$key, $access_children)) {
                            $updates[$key] = $db->quoteName($key) . ' = ' . $db->quote($val);
                        }
                        break;

                    case 'state':
                        if ($val != $item->$key) {
                            // Do not publish/unpublish items that are currently archived or trashed
                            // Also, do not publish items that are unpublished
                            if (($item->$key == '2' || $item->$key == '-2' || $item->$key == '0') && ($val == '0' || $val == '1')) {
                                continue;
                            }

                            $updates[$key] = $db->quoteName($key) . ' = ' . $db->quote($val);
                        }
                        break;

                    default:
                        if ($item->$key != $val) $updates[$key] = $db->quoteName($key) . ' = ' . $db->quote($val);
                        break;
                }
            }

            if (count($updates)) {
                $query->clear();

                $query->update($db->quoteName($this->_tbl))
                      ->set(implode(', ', $updates))
                      ->where($db->quoteName($this->_tbl_key) . ' = ' . (int) $item->$pk);

                $db->setQuery((string) $query);
                $db->query();
            }
        }
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
