<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;


jimport('joomla.html.html');
jimport('joomla.form.formfield');


/**
 * Form Field class for uploading a project logo
 *
 */
class JFormFieldProjectLogo extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'ProjectLogo';

    /**
     * Method to get the field input markup.
     *
     * @return    string    The html field markup
     */
    protected function getInput()
    {
        $html = $this->getHTML();

        return implode("\n", $html);
    }


    /**
     * Method to generate the input markup.
     *
     * @return    string              The html field markup
     */
    protected function getHTML()
    {
        if (!$this->value) {
            $this->value = PFApplicationHelper::getActiveProjectId();
        }

        if (JFactory::getApplication()->isSite() || version_compare(JVERSION, '3.0.0', 'ge')) {
            return $this->getSiteHTML();
        }

        return $this->getAdminHTML();
    }


    /**
     * Method to generate the backend input markup.
     *
     * @return    array     $html     The html field markup
     */
    protected function getAdminHTML()
    {
        $html = array();

        $base_url  = JURI::root(true) . '/media/com_projectfork/repo/0/logo';
        $base_path = JPATH_ROOT . '/media/com_projectfork/repo/0/logo';
        $img       = (int) $this->value;
        $img_url   = null;

        if (JFile::exists($base_path . '/' . $img . '.jpg')) {
            $img_url = $base_url . '/' . $img . '.jpg';
        }
        elseif (JFile::exists($base_path . '/' . $img . '.jpeg')) {
            $img_url = $base_url . '/' . $img . '.jpeg';
        }
        elseif (JFile::exists($base_path . '/' . $img . '.png')) {
            $img_url = $base_url . '/' . $img . '.png';
        }
        elseif (JFile::exists($base_path . '/' . $img . '.gif')) {
            $img_url = $base_url . '/' . $img . '.gif';
        }

        if ($img_url) {
            $html[] = '<img src="' . $img_url . '" style="max-width:160px;max-height:100px"/>';
            $html[] = '<span class="faux-label"><input type="checkbox" name="' . $this->name . '[delete]" value="1"/>' . JText::_('JACTION_DELETE_IMAGE') . '</span>';
        }

        $html[] = '<input type="file" name="' . $this->name . '" id="' . $this->id . '"/>';

        return $html;
    }


    /**
     * Method to generate the frontend input markup.
     *
     * @return    array     $html     The html field markup
     */
    protected function getSiteHTML()
    {
        $html = array();

        $base_url  = JURI::root(true) . '/media/com_projectfork/repo/0/logo';
        $base_path = JPATH_ROOT . '/media/com_projectfork/repo/0/logo';
        $img       = (int) $this->value;
        $img_url   = null;

        if (JFile::exists($base_path . '/' . $img . '.jpg')) {
            $img_url = $base_url . '/' . $img . '.jpg';
        }
        elseif (JFile::exists($base_path . '/' . $img . '.jpeg')) {
            $img_url = $base_url . '/' . $img . '.jpeg';
        }
        elseif (JFile::exists($base_path . '/' . $img . '.png')) {
            $img_url = $base_url . '/' . $img . '.png';
        }
        elseif (JFile::exists($base_path . '/' . $img . '.gif')) {
            $img_url = $base_url . '/' . $img . '.gif';
        }

        if ($img_url) {
            $html[] = '<div class="well"><img src="' . $img_url . '" style="max-width:160px;max-height:100px"/></div>';
            $html[] = '<span class="faux-label"><input type="checkbox" name="' . $this->name . '[delete]" value="1"/>' . JText::_('JACTION_DELETE_IMAGE') . '</span>';
        }

        $html[] = '<input type="file" name="' . $this->name . '" id="' . $this->id . '"/>';

        return $html;
    }
}