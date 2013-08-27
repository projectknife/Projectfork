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


jimport('joomla.application.component.view');


/**
 * File Download View class for the Projectfork component
 *
 */
class PFrepoViewFile extends JViewLegacy
{
    protected $item;
    protected $state;


    function display($tpl = null)
    {
        $user = JFactory::getUser();

        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        // Check access
		if ($this->item->params->get('access-view') != true) {
		    JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

        $rev = JRequest::getUInt('rev');

        if ($rev) {
            $rev_model = JModelLegacy::getInstance('FileRevision', 'PFrepoModel', $c = array('ignore_request' => true));
            $file_rev  = $rev_model->getItem($rev);

            if (!$file_rev || empty($file_rev->id)) {
                JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_FILE_NOT_FOUND'));
                return false;
            }

            // Check access
            if ($file_rev->parent_id != $this->item->id) {
                JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
                return false;
            }

            $filepath = PFrepoHelper::getBasePath($this->item->project_id) . '/_revs/file_' . $this->item->id;
            $filename = $file_rev->file_name;
        }
        else {
            $filepath = $this->item->physical_path;
            $filename = $this->item->file_name;
        }

        // Check if the file exists
        if (empty($filepath) || !JFile::exists($filepath . '/' . $filename)) {
            JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_FILE_NOT_FOUND'));
            return false;
        }

        if (headers_sent($file, $line)) {
            JError::raiseError(500, JText::sprintf('COM_PROJECTFORK_WARNING_FILE_DL_ERROR_HEADERS_SENT', $file, $line));
            return false;
        }

        ob_end_clean();
        header("Content-Type: APPLICATION/OCTET-STREAM");
        header("Content-Length: " . filesize($filepath . '/' . $filename));
        header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
        header("Content-Transfer-Encoding: Binary");

        if (function_exists('readfile')) {
            readfile($filepath . '/' . $filename);
        }
        else {
            echo file_get_contents($filepath . '/' . $filename);
        }

        jexit();
    }
}
