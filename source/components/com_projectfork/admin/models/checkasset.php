<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class ProjectforkModelCheckAsset extends JModelLegacy
{
    protected $project;
    protected $components;
    protected $root_assets;
    protected $project_assets;

    public function check()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
              ->from('#__pf_projects')
              ->order('id ASC');

        $db->setQuery($query, (int) $this->getState('limitstart'), 1);
        $this->project = $db->loadObject();

        if (empty($this->project)) return false;

        $this->components = PFApplicationHelper::getComponents();

        $this->getRootAssets();

        if (!$this->checkProjectCategory()) return false;

        if (!$this->checkProjectAssets()) return false;

        foreach ($this->components AS $name => $item)
        {
            if (!isset($this->root_assets[$name])    || empty($this->root_assets[$name]))    continue;
            if (!isset($this->project_assets[$name]) || empty($this->project_assets[$name])) continue;

            if (!$this->checkComponentAssets($name)) return false;
        }

        return true;
    }

    protected function getRootAssets()
    {
        $this->root_assets = array();

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        foreach ($this->components AS $name => $item)
        {
            $query->clear()
                  ->select('*')
                  ->from('#__assets')
                  ->where('name = ' . $db->quote($name));

            $db->setQuery($query);

            $this->root_assets[$name] = $db->loadObject();
        }
    }


    protected function checkProjectCategory()
    {
        if ($this->project->catid == 0) return true;

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Find the category asset
        $cat_asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));

        if (!$cat_asset->loadbyName('com_pfprojects.category.' . (int) $this->project->catid)) {
            return true;
        }

        // Get the project asset
        $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));

        if (!$asset->load($this->project->asset_id)) {
            return true;
        }

        $asset->setLocation($cat_asset->id, 'last-child');
        $asset->parent_id = $cat_asset->id;

        if (!$asset->check() || !$asset->store(false)) {
            return true;
        }

        return true;
    }


    protected function checkProjectAssets()
    {
        $this->project_assets = array();

        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        foreach ($this->components AS $name => $item)
        {
            $use_asset = PFApplicationHelper::usesProjectAsset($name);

            if (!$use_asset || !isset($this->root_assets[$name]) || empty($this->root_assets[$name])) {
                continue;
            }

            $query->clear()
                  ->select('*')
                  ->from('#__assets')
                  ->where('name = ' . $db->quote($name . '.project.' . $this->project->id));

            $db->setQuery($query);
            $asset = $db->loadObject();

            if (!empty($asset)) {
                if ($asset->parent_id != $this->root_assets[$name]->id) {
                    $id = $asset->id;

                    $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));
                    $asset->load($id);

                    $asset->setLocation($this->root_assets[$name]->id, 'first-child');
                    $asset->parent_id = $this->root_assets[$name]->id;

                    if (!$asset->check() || !$asset->store(false)) {
                        continue;
            		}
                }

                $this->project_assets[$name] = $asset;
                continue;
            }

            $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));

            $asset->setLocation($this->root_assets[$name]->id, 'first-child');

            $asset->parent_id = $this->root_assets[$name]->id;
            $asset->name      = $name . '.project.' . $this->project->id;
		    $asset->title     = $this->project->title;
            $asset->rules     = $this->root_assets[$name]->rules;

            if (!$asset->check() || !$asset->store(false)) {
    			continue;
    		}

            $this->project_assets[$name] = $asset;
        }

        return true;
    }


    protected function checkComponentAssets($name)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));
        $skip  = $name . '.project.' . $this->project->id;

        $tables = array(
            'com_pfmilestones' => '#__pf_milestones',
            'com_pftime'       => '#__pf_timesheet',
            'com_pfforum'      => '#__pf_topics',
            'com_pfrepo'       => '#__pf_repo_dirs',
            'com_pfcomments'   => '#__pf_comments'
        );

        if ($name == 'com_pftasks') {
            $pks = array();

            $query->select('asset_id')
                  ->from('#__pf_tasks')
                  ->where('project_id = ' . $this->project->id)
                  ->where('list_id = 0')
                  ->order('id ASC');

            $db->setQuery($query);
            $pks_t = $db->loadColumn();

            if (empty($pks_t)) $pks_t = array();

            $query->clear();
            $query->select('asset_id')
                  ->from('#__pf_task_lists')
                  ->where('project_id = ' . $this->project->id)
                  ->order('id ASC');

            $db->setQuery($query);
            $pks_l = $db->loadColumn();

            if (empty($pks_l)) $pks_l = array();

            $pks = array_merge($pks_t, $pks_l);
        }
        elseif ($name == 'com_pfdesigns') {
            $pks = array();

            $query->select('asset_id')
                  ->from('#__pf_designs')
                  ->where('project_id = ' . $this->project->id)
                  ->where('album_id = 0')
                  ->order('id ASC');

            $db->setQuery($query);
            $pks_d = $db->loadColumn();

            if (empty($pks_d)) $pks_d = array();

            $query->clear();
            $query->select('asset_id')
                  ->from('#__pf_design_albums')
                  ->where('project_id = ' . $this->project->id)
                  ->order('id ASC');

            $db->setQuery($query);
            $pks_a = $db->loadColumn();

            if (empty($pks_a)) $pks_a = array();

            $pks = array_merge($pks_d, $pks_a);
        }
        else {
            if (!isset($tables[$name])) return true;

            $query->select('asset_id')
                  ->from($tables[$name])
                  ->where('project_id = ' . $this->project->id);

            if ($name == 'com_pfrepo') {
                $query->where('parent_id = 1');
            }

            $query->order('id ASC');

            $db->setQuery($query);
            $pks = $db->loadColumn();
        }

        if (empty($pks) || !count($pks)) return true;

        foreach ($pks AS $pk)
        {
            if (!$asset->load($pk)) continue;

            if (strpos($asset->name, $name . '.project') === 0) continue;

            if ($asset->parent_id == $this->project_assets[$name]->id) {
                continue;
            }

            $asset->setLocation($this->project_assets[$name]->id, 'last-child');

            $asset->parent_id = $this->project_assets[$name]->id;

            if (!$asset->check()) continue;


            $asset->store(false);
        }

        return true;
    }
}