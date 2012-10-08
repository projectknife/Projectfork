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
 * Attachment table
 *
 */
class PFTableAttachment extends JTable
{
    /**
     * Constructor
     *
     * @param    database    &    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_ref_attachments', 'id', $db);
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
        // Verify that the attachment connection is unique
        $table = JTable::getInstance('Attachment','PFTable');
        $data  = array('item_type' => $this->item_type, 'item_id' => $this->item_id, 'attachment' => $this->attachment);

        if ($table->load($data) && ($table->id != $this->id || $this->id == 0)) {
            return true;
        }

        return parent::store($updateNulls);
    }
}
