<?php
/**
* @package      Projectfork
* @subpackage   Library.html
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


jimport('joomla.application.component.helper');


/**
 * Utility class for Projectfork javascript behaviors
 *
 */
abstract class PFhtmlScript
{
    /**
     * Array containing information for loaded files
     *
     * @var    array    $loaded
     */
    protected static $loaded = array();


    /**
     * Method to load jQuery JS
     *
     * @return    void
     */
    public static function jQuery()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $params = JComponentHelper::getParams('com_projectfork');

        if (JFactory::getApplication()->isSite()) {
            $load = $params->get('jquery_site');
        }
        else {
            $load = $params->get('jquery_admin');
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html' && $load != '0') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptjQuery');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load jQuery UI JS
     *
     * @return    void
     */
    public static function jQueryUI()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptjQueryUI');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load jQuery Sortable JS
     *
     * @return    void
     */
    public static function jQuerySortable()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQueryUI'])) {
            self::jQueryUI();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptjQuerySortable');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load bootstrap JS
     *
     * @return    void
     */
    public static function bootstrap()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        $params = JComponentHelper::getParams('com_projectfork');

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html' && $params->get('bootstrap_js') != '0') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptBootstrap');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load jQuery flot JS
     *
     * @return    void
     */
    public static function flot()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptFlot');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Projectfork comments JS
     *
     * @return    void
     */
    public static function comments()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['projectfork'])) {
            self::projectfork();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptComments');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Projectfork form JS
     *
     * @return    void
     */
    public static function form()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['projectfork'])) {
            self::projectfork();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptForm');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Projectfork list form JS
     *
     * @return    void
     */
    public static function listForm()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['projectfork'])) {
            self::projectfork();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptListForm');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Projectfork task JS
     *
     * @return    void
     */
    public static function task()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['projectfork'])) {
            self::projectfork();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptTask');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Projectfork time recording JS
     *
     * @return    void
     */
    public static function timerec()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['projectfork'])) {
            self::projectfork();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptTimerec');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Projectfork base JS
     *
     * @return    void
     */
    public static function projectfork()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkScriptCore');
        }

        self::$loaded[__METHOD__] = true;
    }
}


/**
 * Stupid but necessary way of adding jQuery to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that jQuery is not already loaded
 *
 */
function triggerProjectforkScriptjQuery()
{
    $params = JComponentHelper::getParams('com_projectfork');

    if (JFactory::getApplication()->isSite()) {
        $load = $params->get('jquery_site');
    }
    else {
        $load = $params->get('jquery_admin');
    }

    // Auto-load
    if ($load == '') {
        $scripts = (array) array_keys(JFactory::getDocument()->_scripts);
        $string  = implode('', $scripts);

        if (stripos($string, 'jquery') === false) {
            JHtml::_('script', 'com_projectfork/jquery/jquery.min.js', false, true, false, false, false);
            JHtml::_('script', 'com_projectfork/jquery/jquery.noconflict.js', false, true, false, false, false);
        }
    }

    // Force load
    if ($load == '1') {
        JHtml::_('script', 'com_projectfork/jquery/jquery.min.js', false, true, false, false, false);
        JHtml::_('script', 'com_projectfork/jquery/jquery.noconflict.js', false, true, false, false, false);
    }
}


/**
 * Stupid but necessary way of adding jQuery UI to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that flot is loaded after jQuery
 *
 */
function triggerProjectforkScriptjQueryUI()
{
    $scripts = (array) array_keys(JFactory::getDocument()->_scripts);
    $string  = implode('', $scripts);

    if (stripos($string, 'jquery.ui') === false) {
        JHtml::_('script', 'com_projectfork/jquery/jquery.ui.core.min.js', false, true, false, false, false);
    }
}


/**
 * Stupid but necessary way of adding jQuery Sortable to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that flot is loaded after jQuery
 *
 */
function triggerProjectforkScriptjQuerySortable()
{
    $scripts = (array) array_keys(JFactory::getDocument()->_scripts);
    $string  = implode('', $scripts);

    if (stripos($string, 'jquery.ui.sortable') === false) {
        JHtml::_('script', 'com_projectfork/jquery/jquery.ui.sortable.min.js', false, true, false, false, false);
    }
}


/**
 * Stupid but necessary way of adding Bootstrap JS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that Bootstrap JS is not already loaded
 *
 */
function triggerProjectforkScriptBootstrap()
{
    $params = JComponentHelper::getParams('com_projectfork');

    $load = $params->get('bootstrap_js');

    // Auto-load
    if ($load == '') {
        $scripts = (array) array_keys(JFactory::getDocument()->_scripts);
        $string  = implode('', $scripts);

        if (stripos($string, 'bootstrap') === false) {
            JHtml::_('script', 'com_projectfork/bootstrap/bootstrap.min.js', false, true, false, false, false);
        }
    }

    // Force load
    if ($load == '1') {
        JHtml::_('script', 'com_projectfork/bootstrap/bootstrap.min.js', false, true, false, false, false);
    }
}


/**
 * Stupid but necessary way of adding jQuery Flot to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that flot is loaded after jQuery
 *
 */
function triggerProjectforkScriptFlot()
{
    JHtml::_('script', 'com_projectfork/flot/jquery.flot.min.js', false, true, false, false, false);
    JHtml::_('script', 'com_projectfork/flot/jquery.flot.pie.min.js', false, true, false, false, false);
    JHtml::_('script', 'com_projectfork/flot/jquery.flot.resize.min.js', false, true, false, false, false);
}


/**
 * Stupid but necessary way of adding PF comments JS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the comments JS is loaded after jQuery
 *
 */
function triggerProjectforkScriptComments()
{
    JHtml::_('script', 'com_projectfork/projectfork/comments.js', false, true, false, false, false);
}


/**
 * Stupid but necessary way of adding PF form JS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the form JS is loaded after jQuery
 *
 */
function triggerProjectforkScriptForm()
{
    JHtml::_('script', 'com_projectfork/projectfork/form.js', false, true, false, false, false);
}


/**
 * Stupid but necessary way of adding PF list form JS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the list form JS is loaded after jQuery
 *
 */
function triggerProjectforkScriptListForm()
{
    JHtml::_('script', 'com_projectfork/projectfork/list.js', false, true, false, false, false);
}


/**
 * Stupid but necessary way of adding PF tasks JS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the tasks JS is loaded after jQuery
 *
 */
function triggerProjectforkScriptTask()
{
    JHtml::_('script', 'com_projectfork/projectfork/task.js', false, true, false, false, false);
}


/**
 * Stupid but necessary way of adding PF time rec JS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the tasks JS is loaded after jQuery
 *
 */
function triggerProjectforkScriptTimerec()
{
    JHtml::_('script', 'com_projectfork/projectfork/recorder.js', false, true, false, false, false);
}


/**
 * Stupid but necessary way of adding PF core JS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the core JS is loaded after jQuery
 *
 */
function triggerProjectforkScriptCore()
{
    JHtml::_('script', 'com_projectfork/projectfork/projectfork.js', false, true, false, false, false);
}

