<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
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
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }

        $uploadpath = PFrepoHelper::getBasePath($this->item->project_id);
        $filepath   = $uploadpath . '/' . $this->item->file_name;
        $filename   = $this->item->file_name;

        if (!JFile::exists($filepath)) {
            JError::raiseError(404, JText::_('COM_PROJECTFORK_ERROR_FILE_NOT_FOUND'));
            return false;
        }

        if (headers_sent($file, $line)) {
            JError::raiseError(500, JText::sprintf('COM_PROJECTFORK_WARNING_FILE_DL_ERROR_HEADERS_SENT', $file, $line));
            return false;
        }

        ob_end_clean();
        header("Content-Type: APPLICATION/OCTET-STREAM");
        header("Content-Length: " . filesize($filepath));
        header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
        header("Content-Transfer-Encoding: Binary");

        if (function_exists('readfile')) {
            readfile($filepath);
        }
        else {
            echo file_get_contents($filepath);
        }

        jexit();
    }
}
