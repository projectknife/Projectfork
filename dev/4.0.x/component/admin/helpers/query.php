<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Component Query Helper
 *
 * @static
 */
class ProjectforkHelperQuery
{
    /**
     * Method to create query filter conditions
     *
     * @param     object    $query      The query object to work with
     * @param     array     $filters    The field/value pairs to filter by
     *
     * @return    void
     */
    public static function buildFilter(&$query, $filters = array())
    {
        $db = JFactory::getDbo();

        foreach($filters AS $field => $filter)
        {
            if (count($filter) != 2) continue;

            list($type, $value) = $filter;

            switch (strtoupper($type))
            {
                case 'STR-EQUALS':
                    if (!empty($value)) $query->where($field . ' = ' . $db->Quote($db->escape($value, true)));
                    break;

                case 'STR-LIKE':
                    if (!empty($value)) $query->where($field . ' LIKE ' . $db->Quote('%' . $db->escape($value, true) . '%'));
                    break;

                case 'SEARCH':
                    if (!empty($value)) {
                        if (stripos($value, 'id:') === 0) {
                            $query->where($field . '.id = ' .(int) substr($value, 4));
                        }
                        elseif (stripos($value, 'author:') === 0) {
                            $value = $db->Quote('%' . $db->escape(trim(substr($value, 8)), true) . '%');
                            $query->where('(u.name LIKE ' . $value . ' OR u.username LIKE ' . $value . ')');
                        }
                        else {
                            $value = $db->Quote('%' . $db->escape($value, true) . '%');
                            $query->where('(' . $field . '.title LIKE ' . $value . ' OR ' . $field . '.alias LIKE ' . $value . ')');
                        }
                    }
                    break;

                case 'STATE':
                    if (is_numeric($value)) {
                        $query->where($field . ' = ' . (int) $value);
                    }
                    elseif ($value === '') {
                        $query->where('(' . $field . ' = 0 OR ' . $field . ' = 1)');
                    }
                    break;

                case 'INT-NOTZERO':
                    if (is_numeric($value) && intval($value) != 0) $query->where($field . ' = ' .(int) $value);
                    break;

                case 'INT':
                default:
                    if (is_numeric($value)) $query->where($field . ' = ' . (int) $value);
                    break;
            }
        }
    }


    public static function deleteFromTablesByField($tables, $field_value)
    {
        $db      = JFactory::getDbo();
        $query   = $db->getQuery(true);
        $success = true;

        // Loop through the tables
        foreach ($tables AS $table_name)
        {
            $table = JTable::getInstance(ucfirst($table_name), 'PFtable', array('ignore_request' => true));

            if (!$table) {
                $success = false;
                continue;
            }

            $query->clear();
            $query->delete($table->getTableName());

            if (!is_array($field_value)) {
                list($ref_field, $ref_value) = explode('.', $field_value, 2);
                $query->where($db->quoteName($ref_field) . ' = ' . $db->quote($db->escape($ref_value)));
            }
            else {
                foreach ($field_value AS $ref_field => $ref_value)
                {
                    $query->where($db->quoteName($ref_field) . ' = ' . $db->quote($db->escape($ref_value)));
                }
            }

            $db->setQuery((string) $query);
            $db->execute();

            if ($db->getError()) {
                $success = false;
            }
        }

        return $success;
    }


    /**
     * Method to update table data by field reference
     *
     * @param     array      $tables         The table name classes. Expects the class prefix to be PFTable.
     * @param     mixed      $field_value    The reference field and value by which to update connected by a "."
     * @param     array      $data           The new data
     *
     * @return    boolean                    True on success, False on error
     */
    public static function updateTablesByField($tables, $field_value, $data)
    {
        $db      = JFactory::getDbo();
        $query   = $db->getQuery(true);
        $success = true;

        // Pre-fetch access level tree if access field is set
        $access_tree = array();
        if (isset($data['access'])) {
            $access_tree   = ProjectforkHelperAccess::getAccessTree($data['access']);
            $access_tree[] = $data['access'];
        }

        // Loop through the tables
        foreach ($tables AS $table_name)
        {
            $changes        = $data;
            $changed_fields = array_keys($changes);

            $table = JTable::getInstance(ucfirst($table_name), 'PFtable', array('ignore_request' => true));

            if (!$table) {
                $success = false;
                continue;
            }

            $table_fields = $table->getFields();

            if (!is_array($table_fields)) {
                $success = false;
                continue;
            }

            // Weed out fields that dont exist
            foreach($changed_fields AS $i => $field)
            {
                if (!array_key_exists($field, $table_fields)) {
                    unset($changed_fields[$i]);
                    unset($changes[$field]);
                }
            }

            // Skip table if no fields are left
            if (count($changes) == 0) {
                continue;
            }

            // Update the table
            foreach($changes AS $field => $value)
            {
                $query->clear();

                switch ($field)
                {
                    case 'start_date':
                        if (strtotime($value) == 0) continue;
                        $query->update($table->getTableName())
                              ->set($db->quoteName($field) . ' = ' . $db->quote($value))
                              ->where($db->quoteName($field) . ' < ' . $db->quote($value))
                              ->where($db->quoteName($field) . ' != ' . $db->quote($db->getNullDate()));
                        break;

                    case 'end_date':
                        if (strtotime($value) == 0) continue;
                        $query->update($table->getTableName())
                              ->set($db->quoteName($field) . ' = ' . $db->quote($value))
                              ->where($db->quoteName($field) . ' > ' . $db->quote($value))
                              ->where($db->quoteName($field) . ' != ' . $db->quote($db->getNullDate()));
                        break;

                    case 'access':
                        $query->update($table->getTableName())
                              ->set($db->quoteName($field) . ' = ' . $db->quote($value));

                        if (count($access_tree) > 1) {
                            $query->where($db->quoteName($field) . ' NOT IN(' . implode(', ', $access_tree) . ')');
                        }
                        else {
                            $query->where($db->quoteName($field) . ' != ' . $db->quote($access_tree[0]));
                        }
                        break;

                    case 'state':
                        if ($value == '1') continue;

                        $ignore = array('-2');

                        if ($value == '0')  $ignore = array('-2', '0', '2');
                        if ($value == '-2') $ignore = array('-2');
                        if ($value == '2')  $ignore = array('-2');

                        $query->update($table->getTableName())
                              ->set($db->quoteName($field) . ' = ' . $db->quote($value));

                        if (count($ignore) > 1) {
                            $query->where($db->quoteName($field) . ' NOT IN(' . implode(', ', $ignore) . ')');
                        }
                        else {
                            $query->where($db->quoteName($field) . ' != ' . $db->quote($ignore[0]));
                        }
                        break;

                    default:
                        $query->update($table->getTableName())
                              ->set($db->quoteName($field) . ' = ' . $db->quote($value));
                        break;
                }

                if (!is_array($field_value)) {
                    list($ref_field, $ref_value) = explode('.', $field_value, 2);
                    $query->where($db->quoteName($ref_field) . ' = ' . $db->quote($db->escape($ref_value)));
                }
                else {
                    foreach ($field_value AS $ref_field => $ref_value)
                    {
                        $query->where($db->quoteName($ref_field) . ' = ' . $db->quote($db->escape($ref_value)));
                    }
                }

                $db->setQuery($query);
                $db->execute();

                if ($db->getError()) {
                    $success = false;
                }
            }
        }

        return $success;
    }
}
