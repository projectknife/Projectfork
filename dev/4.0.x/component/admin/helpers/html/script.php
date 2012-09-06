<?php
/**
* @package      Projectfork
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
abstract class ProjectforkScript
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

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html' && $params->get('jquery', '1') == '1') {
            JHtml::_('script', 'com_projectfork/jquery/jquery.min.js', false, true, false, false, false);
            JHtml::_('script', 'com_projectfork/jquery/jquery.noconflict.js', false, true, false, false, false);
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

        // Load dependancies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        $params = JComponentHelper::getParams('com_projectfork');

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html' && $params->get('bootstrap_js', '1') == '1') {
            JHtml::_('script', 'com_projectfork/bootstrap/bootstrap.min.js', false, true, false, false, false);
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

        // Load dependancies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            JHtml::_('script', 'com_projectfork/flot/jquery.flot.min.js', false, true, false, false, false);
            JHtml::_('script', 'com_projectfork/flot/jquery.flot.pie.min.js', false, true, false, false, false);
            JHtml::_('script', 'com_projectfork/flot/jquery.flot.resize.min.js', false, true, false, false, false);
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

        // Load dependancies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            JHtml::_('script', 'com_projectfork/projectfork/comments.js', false, true, false, false, false);
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

        // Load dependancies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            JHtml::_('script', 'com_projectfork/projectfork/form.js', false, true, false, false, false);
        }

        self::$loaded[__METHOD__] = true;
    }
}
