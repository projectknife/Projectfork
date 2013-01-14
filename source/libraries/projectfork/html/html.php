<?php
/**
* @package      Projectfork
* @subpackage   Library.html
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

abstract class PFhtml
{
    /**
     * Method to create a checkbox for a grid row.
     *
     * @param     integer    $row_num        The row index
     * @param     integer    $rec_id         The record id
     * @param     boolean    $checked_out    True if item is checke out
     * @param     string     $name           The name of the form element
     *
     * @return    mixed                      String of html with a checkbox if item is not checked out, null if checked out.
     */
    public static function id($row_num, $rec_id, $checked_out = false, $name = 'cid')
    {
        if ($checked_out) {
            return '';
        }
        else {
            return '<input type="checkbox" id="cb' . $row_num . '" name="' . $name . '[]" value="' . $rec_id
                . '" onclick="Joomla.isChecked(this.checked); PFlist.toggleBulkButton();" title="'
                . JText::sprintf('JGRID_CHECKBOX_ROW_N', ($row_num + 1)) . '" />';
        }
    }


    /**
     * Returns a truncated text. Also strips html tags
     *
     * @param     string    $text     The text to truncate
     * @param     int       $chars    The new length of the string
     *
     * @return    string              The truncated string
     */
    public static function truncate($text = '', $chars = 40)
    {
        $truncated = strip_tags($text);
        $length    = strlen($truncated);

        if (($length + 3) < $chars || $chars <= 0) return $truncated;

        return substr($truncated, 0, ($chars - 3)) . '...';
    }
}