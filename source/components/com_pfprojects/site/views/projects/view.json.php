<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfprojects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


/**
 * Project JSON list view class.
 *
 */
class PFprojectsViewProjects extends JViewLegacy
{
    /**
     * Generates a list of JSON items.
     *
     * @return    void
     */
    function display($tpl = null)
    {
        $ta   = (int) JRequest::getUInt('typeahead');
        $s2   = (int) JRequest::getUInt('select2');
        $resp = array();

        // Set the query limit if requesting data for typeahead or select2
        if ($ta || $s2) JRequest::setVar('limit', 5);

        // Get model data
        $rows = $this->get('Items');

        if ($ta) {
            $tmp_rows = array();

            foreach ($rows AS &$row)
            {
                $id = (int) $row->id;

                $tmp_rows[$id] = $this->escape($row->title);
            }

            $rows = $tmp_rows;
        }
        elseif ($s2) {
            $tmp_rows = array();

            foreach ($rows AS &$row)
            {
                $id = (int) $row->id;

                $item = new stdClass();
                $item->id = $id;
                $item->text = $this->escape($row->title);

                $tmp_rows[] = $item;
            }

            $rows = $tmp_rows;
        }

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        JResponse::setHeader('Content-Disposition', 'attachment;filename="' . $this->getName() . '.json"');

        // Output the JSON data.
        echo json_encode($rows);

        jexit();
    }
}
