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


jimport('joomla.application.component.modelitem');
jimport('projectfork.application.helper');

/**
 * Methods supporting an user group including rules.
 *
 */
class PFusersModelGroupRules extends JModelItem
{
    /**
     * Model context string.
     *
     * @var    string
     */
    protected $_context = 'com_pfusers.accessmanagergroup';


    /**
     * Method to get item data.
     *
     * @param     integer    The id of the item.
     *
     * @return    mixed      Record object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');

        if ($this->_item === null) $this->_item = array();

        if (isset($this->_item[$pk])) return $this->_item[$pk];

        try
        {
            $db    = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('id, parent_id, lft, rgt, title')
                  ->from('#__usergroups')
                  ->where('id = ' . (int) $pk);

            $db->setQuery($query);
            $item = $db->loadObject();

            if ($error = $db->getErrorMsg()) throw new Exception($error);

            $this->_item[$pk] = (empty($item) ? false : $item);

            if ($this->_item[$pk]) {
                if (!$this->getState($this->getName() . '.inherit')) {
                    $component = $this->getState($this->getName() . '.component');
                    $section   = $this->getState($this->getName() . '.section');

                    $this->_item[$pk]->actions = array();
                    $this->_item[$pk]->actions[$component] = JAccess::getActions($component, $section);

                    $components = PFApplicationHelper::getComponents();

                    foreach ($components AS $name => $item)
                    {
                        if ($name == $component) continue;

                        $use_asset = PFApplicationHelper::usesProjectAsset($name);
                        $enabled   = PFApplicationHelper::enabled($name);

                        if (!$use_asset || !$enabled) continue;

                        $this->_item[$pk]->actions[$name] = JAccess::getActions($name, $section);
                    }

                    // Get child group names
                    $this->_item[$pk]->children = $this->getChildGroups($this->_item[$pk]->lft, $this->_item[$pk]->rgt);
                }
                else {
                    $component = $this->getState($this->getName() . '.component');
                    $section   = $this->getState($this->getName() . '.section');

                    $this->_item[$pk]->actions  = JAccess::getActions($component, $section);

                    // Get child group names
                    $this->_item[$pk]->children = $this->getChildGroups($this->_item[$pk]->lft, $this->_item[$pk]->rgt);
                }
            }
        }
        catch (JException $e)
        {
            if ($e->getCode() == 404) {
                // Need to go thru the error handler to allow Redirect to work.
                JError::raiseError(404, $e->getMessage());
            }
            else {
                $this->setError($e);
                $this->_item[$pk] = false;
            }
        }

        return $this->_item[$pk];
    }


    protected function getChildGroups($lft, $rgt)
    {
        $query = $this->_db->getQuery(true);

        $query->select('title')
              ->from('#__usergroups')
              ->where('lft > ' . $lft)
              ->where('rgt < ' . $rgt)
              ->order('lft ASC');

        $this->_db->setQuery($query);
        $result = $this->_db->loadColumn();

        if (!is_array($result)) $result = array();

        return $result;
    }


    /**
     * Method to auto-populate the model state.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Load state from the request.
        $pk = JRequest::getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $inherit = JRequest::getVar('inherit', false);
        $this->setState($this->getName() . '.inherit', (boolean) $inherit);

        $component = JRequest::getVar('component');
        $this->setState($this->getName() . '.component', $component);

        $section = JRequest::getVar('section');
        $this->setState($this->getName() . '.section', $section);

        $asset_id = (int) JRequest::getVar('asset_id');
        $this->setState($this->getName() . '.asset_id', $asset_id);

        $project_id = (int) JRequest::getVar('project_id');
        $this->setState($this->getName() . '.project_id', $project_id);
    }
}
