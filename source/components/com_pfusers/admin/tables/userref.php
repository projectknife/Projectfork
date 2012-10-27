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
 * User Reference table
 *
 */
class PFTableUserRef extends JTable
{
    /**
     * Constructor
     *
     * @param    database    &    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_ref_users', 'id', $db);
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
        // Verify that the reference is unique
        $table = JTable::getInstance('UserRef','PFTable');
        $data  = array('item_type' => $this->item_type, 'item_id' => $this->item_id, 'user_id' => $this->user_id);

        if ($table->load($data) && ($table->id != $this->id || $this->id==0)) {
            return true;
        }

        return parent::store($updateNulls);
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
            . ' WHERE id = ' . (int) $this->user_id;
            $db->setQuery($query);
            $this->user_name = $db->loadResult();
        }

        return parent::toXML($mapKeysToText);
    }
}
