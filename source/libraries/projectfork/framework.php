<?php
/**
 * @package      Projectfork.Framework
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Make sure the cms libraries are loaded
if (!defined('JPATH_PLATFORM')) {
    require_once dirname(__FILE__) . '/../cms.php';
}

if (!defined('PF_FRAMEWORK')) {
    define('PF_FRAMEWORK', 1);
}
else {
    // Make sure we run the code below only once
    return;
}

jimport('joomla.filesystem.folder');


// Include the library
require_once dirname(__FILE__) . '/library.php';


// Get the list of Projectfork components, as well as the currently active
$components = PFApplicationHelper::getComponents();
$current    = JFactory::getApplication()->input->get('option');
$lang       = JFactory::getLanguage();
$is_site    = JFactory::getApplication()->isSite();

// Go through each component
foreach ($components AS $component)
{
    $site_path  = JPATH_SITE . '/components/' . $component->element;
    $admin_path = JPATH_ADMINISTRATOR . '/components/' . $component->element;

    $com_name = str_replace('com_', '', $component->element);

    if (substr($com_name, 0, 2) == 'pf') {
        $com_name = 'PF' . substr($com_name, 2);
    }
    else {
        $com_name = ucfirst($com_name);
    }

    // Begin loading language files
    if ($component->element != $current) {
        $lang->load($component->element);
    }

    if ($is_site) {
        // Also load the backend language when in frontend
        $lang->load($component->element, JPATH_ADMINISTRATOR);

        // Load the language from the component frontend directory if it exists
        if (JFolder::exists($site_path . '/language')) {
            $lang->load($component->element, $site_path);
        }
    }

    // Load the language from the component backend directory if it exists
    if (JFolder::exists($admin_path . '/language')) {
        $lang->load($component->element, $admin_path);
    }

    // Register backend helper class
    if (JFile::exists($admin_path . '/helpers/' . strtolower($com_name) . '.php')) {
        JLoader::register($com_name . 'Helper', $admin_path . '/helpers/' . strtolower($com_name) . '.php');
    }

    // Register the routing helper class
    if ($is_site) {
        if (JFile::exists($site_path . '/helpers/route.php')) {
            JLoader::register($com_name . 'HelperRoute', $site_path . '/helpers/route.php');
        }
    }

    if ($component->element != $current || $is_site) {
        // Register backend table classes
        if (JFolder::exists($admin_path . '/tables')) {
            JTable::addIncludePath($admin_path . '/tables');
        }

        // Register backend model classes
        if (JFolder::exists($admin_path . '/models')) {
            if ($is_site && JFolder::exists($site_path . '/models')) {
                // Give frontend models a priority over admin models
                JModelLegacy::addIncludePath($admin_path . '/models', $com_name . 'Model');
                JModelLegacy::addIncludePath($site_path . '/models', $com_name . 'Model');
            }
            else {
                JModelLegacy::addIncludePath($admin_path . '/models', $com_name . 'Model');
            }
        }

        // Register backend html classes
        if (JFolder::exists($admin_path . '/helpers/html')) {
            JHtml::addIncludePath($admin_path . '/helpers/html');
        }

        // Register backend forms
        if (JFolder::exists($admin_path . '/models/forms')) {
            JForm::addFormPath($admin_path . '/models/forms');
        }

        // Register backend form fields
        if (JFolder::exists($admin_path . '/models/fields')) {
            JForm::addFieldPath($admin_path . '/models/fields');
        }

        // Register backend form rules
        if (JFolder::exists($admin_path . '/models/rules')) {
            JForm::addRulePath($admin_path . '/models/rules');
        }
    }

    if ($component->element != $current && $is_site) {
        // Register frontend model classes
        if (JFolder::exists($site_path . '/models')) {
            JModelLegacy::addIncludePath($site_path . '/models', $com_name . 'Model');
        }

        // Register frontend html classes
        if (JFolder::exists($site_path . '/helpers/html')) {
            JHtml::addIncludePath($site_path . '/helpers/html');
        }
    }
}

