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

            $title = JText::_($component->element);
            $parts = explode('-', $title, 2);

            if (count($parts) == 2) {
                $title = trim($parts[1]);
            }

            JSubMenuHelper::addEntry(
                $title,
                'index.php?option=' . $component->element,
                ($option == $component->element)
            );
        }
    }
}
