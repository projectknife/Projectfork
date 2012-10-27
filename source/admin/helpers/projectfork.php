<?php
/**
 * @package      Projectfork
 * @subpackage   Dashboard
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('projectfork.framework');


class ProjectforkHelper
{
    /**
     * The component name
     *
     * @var    string
     */
    public static $extension = 'com_projectfork';


    /**
     * Configure the Linkbar.
     *
     * @param     string    $view    The name of the active view.
     *
     * @return    void
     */
    public static function addSubmenu($view)
    {
        $components = PFApplicationHelper::getComponents();
        $option     = JFactory::getApplication()->input->get('option');

        foreach ($components AS $component)
        {
            if ($component->enabled == '0') {
                continue;
            }

            JSubMenuHelper::addEntry(
                JText::_($component->element),
                'index.php?option=' . $component->element,
                ($option == $component->element)
            );
        }
    }
}
