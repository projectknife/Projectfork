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


/**
 * Form Field class for Projectfork.
 * Field for assigning permissions to groups for a given asset
 * and assigning groups to a new access level
 *
 */
class JFormFieldPermissions extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	public $type = 'Permissions';


    /**
	 * The user groups.
	 *
	 * @var    array
	 */
    private $groups;


	/**
	 * Method to get the field input markup for Access Control Lists.
	 * Optionally can be associated with a specific component and section.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
	    static $count;

		JHtml::_('behavior.tooltip');


		// Initialise some field attributes.
		$section    = $this->element['section']     ? (string) $this->element['section']     : '';
		$component  = $this->element['component']   ? (string) $this->element['component']   : '';
		$assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
		$allowcb    = $this->element['cb']          ? (bool) $this->element['cb']            : false;


        // Initialize variables
        $this->groups    = $this->getUserGroups();
        $actions         = $this->getActions($component, $section);

        $isSuperAdmin    = JFactory::getUser()->authorise('core.admin');
        $html            = array();
        $checkSuperAdmin = true;
        $selected        = null;
        $curLevel        = 0;
        $prev_lvl        = 0;

		$count++;


		// Get the explicit rules for this asset.
		if($section == 'component') {
			// Need to find the asset id by the name of the component.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->quoteName('id'))
			      ->from($db->quoteName('#__assets'))
			      ->where($db->quoteName('name') . ' = ' . $db->quote($component));

            $db->setQuery($query);

			$assetId = (int) $db->loadResult();

			if($error = $db->getErrorMsg()) JError::raiseNotice(500, $error);
		}
		else {
			// Find the asset id of the item.
			$assetId = $this->form->getValue($assetField);

            if(!$assetId) {
                // This is a new item, get the asset id of the component
                $db    = JFactory::getDbo();
			    $query = $db->getQuery(true);

                $query->select($db->quoteName('id'))
		               ->from($db->quoteName('#__assets'))
			           ->where($db->quoteName('name') . ' = ' . $db->quote($component));

                $db->setQuery($query);
		        if ($result = $db->loadResult())$assetId = (int) $result;
            }
		}

		// Get the rules for just this asset (non-recursive).
		$assetRules = JAccess::getAssetRules($assetId);


        // Checkbox javascript function
        if($allowcb) {
            $head_js = array();
            $head_js[] = "function toggleUsergroupCheckbox(el, btn_id, div_id, children_string)
            {
                 if(el.checked) {
                    $(btn_id).set('disabled', '');

                    if(children_string != '') {
                        var i = 0;
                        var children = children_string.split(',');
                        for (i=0;i<children.length;i++)
                        {
                            $(children[i]).set('checked', 'checked');
                            $(children[i]).fireEvent('change');
                        }
                    }
                 }
                 else {
                    $(btn_id).set('disabled', 'disabled');
                    $(div_id).hide();

                    if(children_string != '') {
                        var i = 0;
                        var children = children_string.split(',');
                        for (i=0;i<children.length;i++)
                        {
                            $(children[i]).set('checked', '');
                            $(children[i]).fireEvent('change');
                        }
                    }
                 }
            }
            window.addEvent('domready', function() {";
        }



		// Prepare output
		$html[] = '<ul class="checklist usergroups">';

		for ($i = 0, $n = count($this->groups); $i < $n; $i++)
		{
			$item = &$this->groups[$i];
            $i2   = $i + 1;

            // Testing stuff


            $canCalculateSettings = ($item->parent_id || !empty($component));

			// If checkSuperAdmin is true, only add item if the user is superadmin or the group is not super admin
			if((!$checkSuperAdmin) || $isSuperAdmin || (!JAccess::checkGroup($item->value, 'core.admin'))) {

                // Setup the variable attributes.
				$eid      = $count . 'group_' . $item->value;
				$btn_id   = $count . 'group_' . $item->value.'_btn';
				$div_id   = $count . 'group_' . $item->value.'_rules';
                $li_class = $this->getGroupClasses($item->value, $item->level);
                $checked  = '';
                $cb_js    = '';


                // Find out if this group has children and generate onchange event
                // for the checkbox
                if($allowcb) {
                    $children  = $this->getGroupChildren($i, $item->level, $count.'group_');
                    $cb_js     = "toggleUsergroupCheckbox(this, '$btn_id', '$div_id', '".implode(',', $children)."')";
                    $head_js[] = "$('$eid').addEvent('change', function(){".$cb_js."});";
                }

                $btn_js   = '$(\''.$div_id.'\').toggle();';
                $prev_lvl = $item->level;

				// Don't call in_array unless something is selected
				if ($selected) $checked = in_array($item->value, $selected) ? ' checked="checked"' : '';

                $div_style    = ($checked == '') ? ' style="display:none"' : '';
                $btn_disabled = ($checked == '') ? ' disabled="disabled"'  : '';
				$rel          = ($item->parent_id > 0) ? ' rel="' . $count . 'group_' . $item->parent_id . '"' : '';


                // Create the checkbox
                $cb = '<input type="checkbox" name="'.$this->name.'[]" value="'.$item->value.'" id="'.$eid.'"'
				    . $checked . $rel . ' class="usergroup_cb"'
                    . '/>';
                if(!$allowcb) $cb = '';


				// Build the HTML for the item.
				$html[] = '	<li class="usergroup '.$li_class.'">';
                $html[] = '     <div style="float:left">';
                $html[] = '         <button id="'.$btn_id.'" type="button" '.$btn_disabled.' onclick="'.$btn_js.'" class="usergroup_btn">Set Permissions</button>';
                $html[] = '     </div>';
                $html[] = '     <div style="float:left">';
				$html[] = '		    '.$cb;
				$html[] = '		    <label for="' . $eid . '">';
				$html[] = '		        '.str_repeat('<span class="gi">|&mdash;</span>', $item->level).$item->text;
				$html[] = '		    </label>';
                $html[] = '     </div>';
                $html[] = '		<div class="clr"></div>';
				$html[] = '		<div id="'.$div_id.'"'.$div_style.'>';
				$html[] = '		<table class="group-rules">';
                $html[] = '         <thead>';
			    $html[] = '             <tr>';
			    $html[] = '                 <th class="actions" id="actions-th' . $item->value . '">';
			    $html[] = '                     <span class="acl-action">'.JText::_('JLIB_RULES_ACTION').'</span>';
			    $html[] = '                 </th>';
			    $html[] = '                 <th class="settings" id="settings-th' . $item->value . '">';
			    $html[] = '                     <span class="acl-action">' . JText::_('JLIB_RULES_SELECT_SETTING') . '</span>';
			    $html[] = '                 </th>';

                // The calculated setting is not shown for the root group of global configuration.
    			if ($canCalculateSettings) {
    				$html[] = '<th id="aclactionth' . $item->value . '">';
    				$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_CALCULATED_SETTING') . '</span>';
    				$html[] = '</th>';
    			}
			    $html[] = '             </tr>';
                $html[] = '         </thead>';
                $html[] = '         <tbody>';

                foreach ($actions as $action)
    			{
    				// Get the actual setting for the action for this group.
                    $inheritedRule = JAccess::checkGroup($item->value, $action->name, $assetId);
    				$assetRule     = $assetRules->allow($action->name, $item->value);

                    $html[] = '<tr>';
    				$html[] = '    <td headers="actions-th' . $item->value . '">';
    				$html[] = '        <label class="hasTip" for="'.$this->id.'_'.$action->name.'_'.$item->value.'" title="'
    					    .          htmlspecialchars(JText::_($action->title) . '::' . JText::_($action->description), ENT_COMPAT, 'UTF-8') . '">';
    				$html[] =              JText::_($action->title);
    				$html[] = '        </label>';
    				$html[] = '    </td>';
    				$html[] = '    <td headers="settings-th' . $item->value . '">';
    				$html[] = '        <select name="'.$this->name.'['.$action->name.']['.$item->value.']" id="'.$this->id.'_'.$action->name
    					    .          '_'.$item->value.'" title="'
    					    .          JText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($item->text)).'">';
    				$html[] = '            <option value=""' . ($assetRule === null ? ' selected="selected"' : '') . '>'
    					    .              JText::_(empty($item->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED').'</option>';
    				$html[] = '            <option value="1"'.($assetRule === true ? ' selected="selected"' : '').'>'.JText::_('JLIB_RULES_ALLOWED').'</option>';
    				$html[] = '            <option value="0"' . ($assetRule === false ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_DENIED').'</option>';
    				$html[] = '        </select>&#160; ';

    				// If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
    				if (($assetRule === true) && ($inheritedRule === false))$html[] = JText::_('JLIB_RULES_CONFLICT');

    				$html[] = '    </td>';

    				// Build the Calculated Settings column.
    				// The inherited settings column is not displayed for the root group in global configuration.
    				if ($canCalculateSettings) {
    					$html[] = '<td headers="aclactionth' . $item->value . '">';

    					// This is where we show the current effective settings considering currrent group, path and cascade.
    					// Check whether this is a component or global. Change the text slightly.
    					if (JAccess::checkGroup($item->value, 'core.admin') !== true) {
    						if ($inheritedRule === null) {
    							$html[] = '<span class="icon-16-unset">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
    						}
    						elseif ($inheritedRule === true) {
    							$html[] = '<span class="icon-16-allowed">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
    						}
    						elseif ($inheritedRule === false) {
    							if ($assetRule === false) {
    								$html[] = '<span class="icon-16-denied">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
    							}
    							else {
    								$html[] = '<span class="icon-16-denied"><span class="icon-16-locked">' . JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED')
    									    . '</span></span>';
    							}
    						}
    					}
    					elseif (!empty($component)) {
    						$html[] = '<span class="icon-16-allowed"><span class="icon-16-locked">' . JText::_('JLIB_RULES_ALLOWED_ADMIN')
    							    . '</span></span>';
    					}
    					else {
    						// Special handling for  groups that have global admin because they can't  be denied.
    						// The admin rights can be changed.
    						if ($action->name === 'core.admin') {
    							$html[] = '<span class="icon-16-allowed">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
    						}
    						elseif ($inheritedRule === false) {
    							// Other actions cannot be changed.
    							$html[] = '<span class="icon-16-denied"><span class="icon-16-locked">'
    								    . JText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span></span>';
    						}
    						else {
    							$html[] = '<span class="icon-16-allowed"><span class="icon-16-locked">' . JText::_('JLIB_RULES_ALLOWED_ADMIN')
    								    . '</span></span>';
    						}
    					}

    					$html[] = '</td>';
    				}

    				$html[] = '</tr>';
    			}

    			$html[] = '</tbody>';
    			$html[] = '</table></div></li>';
    		}
		}

		$html[] = '</ul>';

        // Add javascript to document head
        if($allowcb) {
            $head_js[] = "});";
            JFactory::getDocument()->addScriptDeclaration(implode("\n", $head_js));
        }

		return implode("\n", $html);
	}


	/**
	 * Get a list of the user groups.
	 *
	 * @return  array
	 */
	protected function getUserGroups()
	{
		// Initialise variables.
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

        $query->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level, a.parent_id')
		      ->from($db->quoteName('#__usergroups').' AS a')
		      ->join('LEFT', $db->quoteName('#__usergroups').' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
		      ->group('a.id, a.title, a.lft, a.rgt, a.parent_id')
		      ->order('a.lft ASC');

		$db->setQuery($query);
		$groups = $db->loadObjectList();

		return $groups;
	}


    /**
	 * Returns a list of all access levels with their assigned groups
	 *
	 * @return  array
	 */
    protected function getAccessGroups()
    {
        // Initialise variables.
        $db    = JFactory::getDBO();
		$query = $db->getQuery(true);
        $list  = array();

        $query->select('a.id, a.rules')
              ->from('#__viewlevels AS a');

        $db->setQuery($query);
		$levels = $db->loadObjectList();


        foreach($levels AS $lvl)
        {
            $key    = $lvl->id;
            $groups = json_decode($lvl->rules);

            $list[$key] = $groups;
        }

        return $list;
    }


    /**
	 * Returns a string of css classes based on the associated access levels with
     * the group ($id).
	 *
     * @param   int    $id          The group id
     * @param   int    $tree_lvl    The nested tree level of the group
	 * @return  string
	 */
    protected function getGroupClasses($id, $tree_lvl = 0)
    {
        // Initialize variables
        static $list     = NULL;
        static $classes  = array();
        static $prev_lvl = 0;

        if(is_null($list)) $list = $this->getAccessGroups();

        if($tree_lvl == 0 || $tree_lvl == 1) {
            $classes = array();
            $classes[0] = array('haslvl-1');
        }
        if(!array_key_exists($tree_lvl, $classes)) $classes[$tree_lvl] = array();

        $prev_lvl = $tree_lvl;


        // Iterate through the access levels to see if the group is in there
        foreach($list AS $lvl => $groups)
        {
            if(in_array($id, $groups) && !in_array("haslvl-".$lvl, $classes[$tree_lvl])) {
                // Add to the classes
                $classes[$tree_lvl][] = "haslvl-".$lvl;
            }
        }

        // Append access level classes from the parent groups
        $k = 0;
        while($k < $tree_lvl)
        {
            if(array_key_exists($k, $classes)) {
                foreach($classes[$k] AS $class)
                {
                    if(!in_array($class, $classes[$tree_lvl])) $classes[$tree_lvl][] = $class;
                }
            }

            $k++;
        }

        // Classes array to string
        $css = implode(' ', $classes[$tree_lvl]);

        return $css;
    }


    /**
	 * Returns the available actions of the component/ access section
	 *
     * @param   string    $component    The component name
     * @param   string    $section      The access section of the component
	 * @return  array     $actions      The available actions
	 */
    protected function getActions($component, $section)
    {
        // Get the actions for the asset.
		$actions = JAccess::getActions($component, $section);


		// Iterate over the children and add to the actions.
		foreach ($this->element->children() as $el)
		{
			if($el->getName() == 'action') {
				$actions[] = (object) array('name' => (string) $el['name'],
                                            'title' => (string) $el['title'],
					                        'description' => (string) $el['description']
                                           );
			}
		}


        return $actions;
    }


    protected function getGroupChildren($i = 0, $level = 0, $prefix = '')
    {
        $children = array();
        $count    = count($this->groups);

        $i++;

        for ($i, $n = $count; $i < $n; $i++)
		{
			$item = $this->groups[$i];

            if($item->level > $level) {
                $children[] = $prefix.$item->value;
            }
            else {
                return $children;
            }
        }

        return $children;
    }
}