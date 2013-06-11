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
    }
}
