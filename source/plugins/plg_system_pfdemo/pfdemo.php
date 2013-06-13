<?php
/**
 * @package      pkg_system_pfdemo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Demo System Plugin Class
 *
 */
class plgSystemPFdemo extends JPlugin
{
    public function onAfterInitialise()
	{
	    $enabled = (JPluginHelper::isEnabled('system', 'pfdemo') && $this->params->get('demo'));

	    // Do nothing if the plugin is disabled
        if (!$enabled) return true;

        // Set the demo constant
        if (!defined('PFDEMO')) define('PFDEMO', 1);

        $next_reset = $this->params->get('next_reset');
        $interval   = ((int) $this->params->get('interval')) * 60;
        $reset      = $this->params->get('reset');
        $next_time  = (empty($next_reset) ? 0 : strtotime($next_reset)) + $interval;
        $now        = JFactory::getDate()->toUnix();

        if (empty($next_reset)) {
            $this->setLastResetDate();
            return true;
        }

        if ($now >= $next_time && $reset) {
            $this->deleteAssets();
            $this->resetContent();
            $this->setLastResetDate();
        }

        return true;
    }


    /**
     * Method to set the last reset date
     *
     */
    protected function setLastResetDate()
    {
        $interval = ((int) $this->params->get('interval')) * 60;
        $now      = JFactory::getDate()->toUnix();

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);
        $date  = date('Y-m-d H:i:s', $now + $interval);

        $this->params->set('next_reset', $date);

        $query->update('#__extensions')
              ->set('params = ' . $db->quote(strval($this->params)))
              ->where('type = ' . $db->quote('plugin'))
              ->where('element = ' . $db->quote('pfdemo'));

        $db->setQuery($query);
        $db->execute();

        return true;
    }


    /**
     * Method to reset the Projectfork content
     *
     */
    protected function resetContent()
    {
        $app    = JFactory::getApplication();
        $db     = JFactory::getDbo();
        $tables = $db->getTableList();
        $query  = $db->getQuery(true);
        $prefix = $app->getCfg('dbprefix') . 'pf_';
        $pks    = array();

        // Empty Projectfork tables
        foreach ($tables AS $i => $name)
        {
            // Skip unknown tables
            if (stripos($name, $prefix) !== 0) {
				unset($tables[$i]);
				continue;
			}

            $query->clear();
            $db->setQuery('TRUNCATE TABLE ' . $db->qn($name));
            $db->execute();
        }

        // Re-Insert base data
        $sql_file = JPATH_ADMINISTRATOR . '/components/com_projectfork/_install/mysql/data.sql';

        if (!file_exists($sql_file)) return true;

        $buffer = file_get_contents($sql_file);
        if ($buffer === false) return true;

        $queries = $db->splitSql($buffer);
        if (count($queries) == 0) return true;

        foreach ($queries as $query)
		{
			$query = trim($query);

			if ($query != '' && $query{0} != '#') {
				$db->setQuery($query);
                $db->execute();
			}
		}

        return true;
    }


    /**
     * Method to delete assets
     *
     */
    protected function deleteAssets()
    {
        $app    = JFactory::getApplication();
        $db     = JFactory::getDbo();
        $tables = $db->getTableList();
        $query  = $db->getQuery(true);
        $prefix = $app->getCfg('dbprefix') . 'pf_';
        $pks    = array();

        foreach ($tables AS $i => $name)
        {
            // Skip unknown tables
            if (stripos($name, $prefix) !== 0) {
				unset($tables[$i]);
				continue;
			}

            $fields = $db->getTableColumns($name);

            // Skip tables that dont have a field for the asset id
            if (!isset($fields['asset_id'])) {
				unset($tables[$i]);
				continue;
			}
        }

        foreach ($tables AS $i => $name)
        {
            $query->clear()
                  ->select('asset_id')
                  ->from($db->qn($name));

            $db->setQuery($query);
            $assets = $db->loadColumn();

            if (empty($assets) || !count($assets)) {
                continue;
            }

            array_merge($pks, $assets);
        }

        JArrayHelper::toInteger($pks);
        if (!count($pks)) return true;

        $query->clear()
              ->delete('#__assets')
              ->where('id IN(' . implode(', ', $pks) . ')');

        $db->setQuery($query);
        $db->execute();

        return true;
    }
}
