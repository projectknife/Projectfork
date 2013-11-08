<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfusers
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.view');
jimport('projectfork.framework');

JFactory::getLanguage()->load('joomla', JPATH_ADMINISTRATOR);


/**
 * Groups rules view class.
 *
 */
class PFusersViewGroupRules extends JViewLegacy
{
    protected $item;

    protected $component;

    protected $section;

    protected $asset_id;

    protected $inherit;

    protected $rules;

    protected $public_groups;


    /**
     * Generates a list of JSON items.
     *
     * @return    void
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        $this->item	= $this->get('Item');

        $this->component  = $model->getState($model->getName() . '.component');
        $this->section    = $model->getState($model->getName() . '.section');
        $this->asset_id   = $model->getState($model->getName() . '.asset_id');
        $this->project_id = $model->getState($model->getName() . '.project_id');
        $this->inherit    = $model->getState($model->getName() . '.inherit');

        if (!$this->asset_id && $this->inherit) {
            $this->asset_id = $this->getComponentProjectAssetId($this->component, $this->project_id);
        }

        $this->rules         = $this->getAssetRules();
        $this->public_groups = array('1', JComponentHelper::getParams('com_users')->get('guest_usergroup', 1));

        $user = JFactory::getUser();

        if (!$user->authorise('core.admin', $this->component) && !$user->authorise('core.manage', $this->component)) {
            JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
            return false;
        }

        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

        parent::display($tpl);
    }


    public function getActionHTML($action, $component, $selected = '')
    {
        $id   = 'jform_rules_' . $action->name . '_' . $this->item->id;

        if (!$this->inherit) {
            $name = 'jform[rules][' . $component . '][' . $action->name . '][' . $this->item->id . ']';
        }
        else {
            $name = 'jform[rules][' . $action->name . '][' . $this->item->id . ']';
        }

        $html = array();

        $opt1_text = JText::_($this->item->parent_id == 0 ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED');
        $opt1_sel  = ($selected == '' ? 'selected="selected"' : '');
        $opt2_sel  = ($selected == '1' ? 'selected="selected"' : '');
        $opt3_sel  = ($selected == '0' ? 'selected="selected"' : '');

        $html[] = '<select id="' . $id . '" name="' . $name . '" class="inputbox input-small">';
        $html[] = '<option ' . $opt1_sel . ' value="">' . $opt1_text . '</option>';
        $html[] = '<option ' . $opt2_sel . ' value="1">' . JText::_('JLIB_RULES_ALLOWED') . '</option>';
        $html[] = '<option ' . $opt3_sel . ' value="0">' . JText::_('JLIB_RULES_DENIED') . '</option>';
        $html[] = '</select>';

        return implode("\n", $html);
    }


    protected function getAssetRules($component = null, $asset_id = null)
    {
        static $cache  = array();
        static $assets = array();

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        if (is_null($component)) $component = $this->component;
        if (is_null($asset_id))  $asset_id  = $this->asset_id;

        if (!$asset_id) {
            if (isset($assets[$component])) {
                $asset_id = (int) $assets[$component];
            }
            else {
                // This is a new item, get the asset id of the component
                $query->select($db->quoteName('id'))
                      ->from($db->quoteName('#__assets'))
                      ->where($db->quoteName('name') . ' = ' . $db->quote($component));

                $db->setQuery($query);
                $assets[$component] = $db->loadResult();

                $asset_id = (int) $assets[$component];
            }
        }

        if (!$asset_id) $asset_id = 1;

        if (isset($cache[$asset_id])) return $cache[$asset_id];

        $cache[$asset_id] = JAccess::getAssetRules($asset_id);

        return $cache[$asset_id];
    }


    protected function getComponentProjectAssetId($component, $project = 0)
    {
        static $cache = array();

        $cache_key = $component . '.' . $project;

        if (isset($cache[$cache_key])) return $cache[$cache_key];

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('id')
              ->from('#__assets')
              ->where('name = ' . $db->quote($component . '.project.' . (int) $project));

        $db->setQuery($query);
        $cache[$cache_key] = (int) $db->loadResult();

        return $cache[$cache_key];
    }


    protected function getCalculated($action, $rule, $calc)
    {
        $html = '';

        if (JAccess::checkGroup($this->item->id, 'core.admin') !== true) {
            if ($calc === null) {
                $html = '<span class="icon-16-unset">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
            }
            elseif ($calc === true) {
                $html = '<span class="icon-16-allowed">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
            }
            elseif ($calc === false) {
                if ($rule === false) {
                    $html = '<span class="icon-16-denied">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
                }
                else {
                    $html = '<span class="icon-16-denied"><span class="icon-16-locked">' . JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED') . '</span></span>';
                }
            }
        }
        elseif (!empty($this->component)) {
            $html = '<span class="icon-16-allowed"><span class="icon-16-locked">' . JText::_('JLIB_RULES_ALLOWED_ADMIN') . '</span></span>';
        }
        else {
            // Special handling for  groups that have global admin because they can't be denied.
            // The admin rights can be changed.
            if ($action->name === 'core.admin') {
                $html = '<span class="icon-16-allowed">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
            }
            elseif ($calc === false) {
                // Other actions cannot be changed.
                $html = '<span class="icon-16-denied"><span class="icon-16-locked">' . JText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span></span>';
            }
            else {
                $html = '<span class="icon-16-allowed"><span class="icon-16-locked">' . JText::_('JLIB_RULES_ALLOWED_ADMIN') . '</span></span>';
            }
        }

        return $html;
    }
}
