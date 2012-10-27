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
 * Field to enter the repository upload path
 *
 */
class JFormFieldRepopath extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     */
    public $type = 'Repopath';


    /**
     * Method to get the user field input markup.
     *
     * @return    string    The field input markup.
     */
    protected function getInput()
    {
        // Initialize some field attributes.
        $attribs = '';
        $attribs .= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $attribs .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

        // Get HTML
        $html = array();
        $html[] = '<span class="readonly">' . JPATH_ROOT .'/</span>';
        $html[] = '<input class="inputbox" type="text" name="' . $this->name . '" id="' . $this->id . '" size="40" value="'
                . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . '/>';

        // Return HTML
        return implode("\n", $html);
    }
}
