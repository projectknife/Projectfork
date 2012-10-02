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
 * Utility class for Projectfork style sheets
 *
 */
abstract class ProjectforkStyle
{
    /**
     * Array containing information for loaded files
     *
     * @var    array    $loaded
     */
    protected static $loaded = array();


    /**
     * Method to load bootstrap CSS
     *
     * @return    void
     */
    public static function bootstrap()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $params = JComponentHelper::getParams('com_projectfork');

        // Load only if doc type is HTML
        if (JFactory::getDocument()->getType() == 'html' && $params->get('bootstrap_css', '1') != '-1') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerProjectforkStyleBootstrap');
        }

        self::$loaded[__METHOD__] = true;
    }


    /**
     * Method to load Projectfork CSS
     *
     * @return    void
     */
    public static function projectfork()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $params = JComponentHelper::getParams('com_projectfork');

        // Load only if doc type is HTML
        if (JFactory::getDocument()->getType() == 'html' && $params->get('projectfork_css', '1') == '1') {
            JHtml::_('stylesheet', 'com_projectfork/projectfork/icons.css', false, true, false, false, false);
            JHtml::_('stylesheet', 'com_projectfork/projectfork/layout.css', false, true, false, false, false);
            JHtml::_('stylesheet', 'com_projectfork/projectfork/theme.css', false, true, false, false, false);
        }

        self::$loaded[__METHOD__] = true;
    }
}


/**
 * Stupid but necessary way of adding bootstrap JS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the CSS is only added if not already found
 *
 */
function triggerProjectforkStyleBootstrap()
{
    $params = JComponentHelper::getParams('com_projectfork');

    $load = $params->get('bootstrap_css');

    // Auto-load
    if ($load == '') {
        $css = (array) array_keys(JFactory::getDocument()->_styleSheets);
        $string  = implode('', $css);

        $isis  = stripos($string, 'isis/css/template.css');
        $proto = stripos($string, 'protostar/css/template.css');
        $strap = stripos($string, 'bootstrap');
        $j3000 = version_compare(JVERSION, '3.0.0', 'ge');

        if ($j3000 || $isis !== false || $proto !== false || $strap !== false) {
            return;
        }

        JHtml::_('stylesheet', 'com_projectfork/bootstrap/bootstrap.min.css', false, true, false, false, false);
        JHtml::_('stylesheet', 'com_projectfork/bootstrap/bootstrap-responsive.min.css', false, true, false, false, false);
    }

    // Force load
    if ($load == '1') {
        JHtml::_('stylesheet', 'com_projectfork/bootstrap/bootstrap.min.css', false, true, false, false, false);
        JHtml::_('stylesheet', 'com_projectfork/bootstrap/bootstrap-responsive.min.css', false, true, false, false, false);
    }
}
