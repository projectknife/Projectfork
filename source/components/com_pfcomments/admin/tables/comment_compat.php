<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


if (version_compare(JVERSION, '3.2.0', '>=')) {
    class PFtableCommentCompat extends JTableNested
    {
        protected function _getAssetParentIdCompat($table = null, $id = null)
        {
            return 1;
        }

        protected function _getAssetParentId(JTable $table = null, $id = null)
        {
            return $this->_getAssetParentIdCompat($table, $id);
        }
    }
}
else {
    class PFtableCommentCompat extends JTableNested
    {
        protected function _getAssetParentIdCompat($table = null, $id = null)
        {
            return 1;
        }

        protected function _getAssetParentId($table = null, $id = null)
        {
            return $this->_getAssetParentIdCompat($table, $id);
        }
    }
}