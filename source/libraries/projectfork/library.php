<?php
/**
 * @package      pkg_projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Make sure the cms libraries are loaded
if (!defined('JPATH_PLATFORM')) {
    require_once dirname(__FILE__) . '/../cms.php';
}

if (!defined('PF_LIBRARY')) {
    define('PF_LIBRARY', 1);
}
else {
    // Make sure we run the code below only once
    return;
}

// Register the projectfork library
JLoader::registerPrefix('PF', JPATH_PLATFORM . '/projectfork');
JLoader::register('PFQueryHelper', JPATH_PLATFORM . '/projectfork/database/query/helper.php');


// Add include paths
JHtml::addIncludePath(JPATH_PLATFORM . '/projectfork/html');
JModelLegacy::addIncludePath(JPATH_PLATFORM . '/projectfork/model', 'PFModel');
JTable::addIncludePath(JPATH_PLATFORM . '/projectfork/table', 'PFTable');
JForm::addFieldPath(JPATH_PLATFORM . '/projectfork/form/fields');
JForm::addRulePath(JPATH_PLATFORM . '/projectfork/form/rules');


// Define version
if (!defined('PFVERSION')) {
    $pfversion = new PFVersion();

    define('PFVERSION', $pfversion->getShortVersion());
}
