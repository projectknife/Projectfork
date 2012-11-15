<?php
/**
* @package      Projectfork Dashboard Buttons
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


/**
 * Module helper class
 *
 */
abstract class modPFdashButtonsHelper
{
    /**
     * Method to get a list of available buttons
     *
     * @return    array    $buttons    The available buttons
     */
    public static function getButtons()
    {
        $components = PFapplicationHelper::getComponents();
        $buttons    = array();

        foreach ($components AS $component)
        {
            if (!PFApplicationHelper::enabled($component->element)) {
                continue;
            }

            $helper = JPATH_ADMINISTRATOR . '/components/' . $component->element . '/helpers/dashboard.php';
            $class  = str_replace('com_pf', 'PF', $component->element) . 'HelperDashboard';

            if (!JFile::exists($helper)) {
                continue;
            }

            JLoader::register($class, $helper);

            if (class_exists($class)) {
                if (in_array('getSiteButtons', get_class_methods($class))) {
                    $com_buttons = (array) call_user_func(array($class, 'getSiteButtons'));

                    $buttons[$component->element] = array();

                    foreach ($com_buttons AS $button)
                    {
                        $buttons[$component->element][] = $button;
                    }
                }
            }
        }

        return $buttons;
    }
}
