<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined('JPATH_PLATFORM') or die;


jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');


require_once(JPATH_ADMINISTRATOR.'/components/com_projectfork/helpers/projectfork.php');


/**
 * Provides a list of access levels. Access levels control what users in specific
 * groups can see.
 *
 */
class JFormFieldPFaccesslevel extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 **/
	public $type = 'PFaccesslevel';


	/**
	 * Method to get the field input markup.
	 *
	 * @return  string   The field input markup.
	 **/
	protected function getInput()
	{
		// Initialize variables.
		$attr = '';


        // Get possible parent field values
        $project_id   = (int) $this->form->getValue('project_id');
        $milestone_id = (int) $this->form->getValue('milestone_id');
        $tasklist_id  = (int) $this->form->getValue('list_id');
        $topic_id     = (int) $this->form->getValue('topic_id');


		// Initialize some field attributes.
		$attr .= $this->element['class']                         ? ' class="'.(string) $this->element['class'].'"'       : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"'                                : '';
		$attr .= $this->element['size']                          ? ' size="'.(int) $this->element['size'].'"'            : '';
        $attr .= $this->multiple                                 ? ' multiple="multiple"'                                : '';


        // Build the javascript
        if((string) $this->element['permissions'] == 'true') {
            $script = array();
    		$script[] = 'function pfSelectAccess_'.$this->id.'(val) {';
    		if((string) $this->element['create'] == 'true') {
    		    $script[] = '    if(val == 0) {';
    		    $script[] = '        $("jform_access_exist-li").hide();';
    		    $script[] = '        $("jform_access_new-li").show();';
    		    $script[] = '        $("jform_access_groups-lbl").show();';
    		    $script[] = '        $$("li.usergroup").show();';
    		    $script[] = '        $$("input.usergroup_cb").show();';
    		    $script[] = '        $$("input.usergroup_cb").set("checked", "");';
    		    $script[] = '        $$("button.usergroup_btn").set("disabled", "disabled");';
    		    $script[] = '    } else {';
    		    $script[] = '        $("jform_access_new-li").hide();';
    		    $script[] = '        $("jform_access_groups-lbl").hide();';
    		    $script[] = '        $$("li.usergroup").hide();';
    		    $script[] = '        $$("input.usergroup_cb").hide();';
    		    $script[] = '        $("jform_access_exist-li").show();';
    		    $script[] = '        $$("li.haslvl-"+val).show();';
    		    $script[] = '        $$("button.usergroup_btn").set("disabled", "");';
    		    $script[] = '    }';
            }
            else {
                $script[] = '    $$("li.usergroup").hide();';
        		$script[] = '	 $$("input.usergroup_cb").hide();';
        		$script[] = '	 $("jform_access_exist-li").show();';
        		$script[] = '	 $$("li.haslvl-"+val).show();';
        		$script[] = '	 $$("button.usergroup_btn").set("disabled", "");';
            }
    		$script[] = '}';
    		$script[] = 'window.addEvent("domready", function(){';
    		$script[] = '    var access_idx = $("'.$this->id.'").selectedIndex;';
    		$script[] = '    var access_val = $("'.$this->id.'").options[access_idx].value;';
    		$script[] = '    pfSelectAccess_'.$this->id.'(access_val);';
    		$script[] = '});';

            JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
        }


        // Handle onchange event attribute.
        if((string) $this->element['permissions'] == 'true') {
            $attr .= ' onchange="';
            $attr .= "pfSelectAccess_".$this->id."(this.options[this.selectedIndex].value);";
            if($this->element['onchange']) $attr .= (string) $this->element['onchange'].';';

            if((string) $this->element['submit'] == 'true') {
                $view  = JRequest::getCmd('view');
                $attr .= " Joomla.submitbutton('".$view.".setAccess');";
            }

            $attr .= '"';
        }
        else {
            $attr .= ' onchange="';
            if($this->element['onchange']) $attr .= (string) $this->element['onchange'].';';

            if((string) $this->element['submit'] == 'true') {
                $view  = JRequest::getCmd('view');
                $attr .= " Joomla.submitbutton('".$view.".setAccess');";
            }

            $attr .= '"';
        }


        // Get the field options
        $options = $this->getOptions($project_id, $milestone_id, $tasklist_id, $topic_id);


		// Generate the list
		return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
	}


    /**
	 * Method to get the field list options markup.
	 *
     * @param     int    $project_id      The parent project id
     * @param     int    $milestone_id    The parent milestone id
     * @param     int    $tasklist_id     The parent tasklist id
	 * @return    array    The list options markup.
	 */
    protected function getOptions($project_id = 0, $milestone_id = 0, $tasklist_id = 0, $topic_id = 0)
	{
	    // Setup vars
        $user      = JFactory::getUser();
        $options   = array();
	    $view      = (string) JRequest::getCmd('view');
        $parent_el = 'project';
        $parent_id = $project_id;
        $is_admin  = $user->authorise('core.admin');
        $groups	   = $user->getAuthorisedViewLevels();


        // Determine current parent element
        if($milestone_id) {
            $parent_el = 'milestone';
            $parent_id = $milestone_id;
        }
        if($tasklist_id) {
            $parent_el = 'tasklist';
            $parent_id = $tasklist_id;
        }
        if($topic_id) {
            $parent_el = 'topic';
            $parent_id = $topic_id;
        }


        // Get the table of the parent element
        if($parent_id) {
            JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_projectfork'.DS.'tables');

            $table = JTable::getInstance($parent_el, 'PFTable');
            if(!$table) return $options;

            // Load the parent item
            if(!$table->load($parent_id)) return $options;

            // Load access level title
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.title');
            $query->from('#__viewlevels AS a');
            $query->where('a.id = '.(int) $table->access);

            $db->setQuery($query->__toString());
            $access_title = $db->loadResult();
        }


        // Add option to create new access level
        if((string) $this->element['create'] == 'true') {
            $options[] = JHtml::_('select.option',
                                  0,
                                  JText::alt('COM_PROJECTFORK_OPTION_CREATE_NEW_ACCESS',
                                  preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                                  'value',
                                  'text'
                                 );
        }


        // Add option to inherit access from parent element
        if($parent_id) {
            $text = 'COM_PROJECTFORK_OPTION_INHERIT_FROM_'.strtoupper($parent_el);

            $options[] = JHtml::_('select.option',
                                  (int) $table->access,
                                  sprintf(JText::alt($text,
                                  preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), $access_title),
                                  'value',
                                  'text'
                                 );


            // Find access level children
            $levels = ProjectforkHelper::getChildrenOfAccess($table->access);
        }
        else {
            // Load access levels
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.id AS value, a.title AS text');
            $query->from('#__viewlevels AS a');

            $db->setQuery($query->__toString());
            $levels = (array) $db->loadObjectList();
        }



        foreach($levels AS $level)
        {
            if(!$is_admin && !isset($groups[$level->value])) {
                continue;
            }

            // Create a new option object based on the <option /> element.
			$tmp = JHtml::_('select.option',
                            (string) $level->value,
                            JText::alt(trim((string) $level->text),
                            preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                            'value',
                            'text'
                           );

			// Add the option object to the result set.
			$options[] = $tmp;
        }

		reset($options);
		return $options;
	}
}