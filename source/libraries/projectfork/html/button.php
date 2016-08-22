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


abstract class PFhtmlButton
{
    public static function watch($type, $i, $state = 0, $options = array())
    {
        static $enabled = null;
        static $opt_out = null;

        if (is_null($enabled)) {
            $enabled = JPluginHelper::isEnabled('content', 'pfnotifications');

            if ($enabled) {
                // Check the plugin access level
                $user = JFactory::getUser();

                if (!$user->authorise('core.admin') && !$user->authorise('core.manage')) {
                    $db = JFactory::getDbo();
                    $query = $db->getQuery(true);

                    $query->select('access')
                          ->from('#__extensions')
                          ->where('type = ' . $db->quote('plugin'))
                          ->where('element = ' . $db->quote('pfnotifications'))
                          ->where('folder = ' . $db->quote('content'));

                    $db->setQuery($query);
                    $plg_access = (int) $db->loadResult();
                    $levels     = $user->getAuthorisedViewLevels();
                    $enabled    = in_array($plg_access, $levels);
                }
            }
        }

        if (!$enabled) return '';

        if (is_null($opt_out)) {
            $plugin  = JPluginHelper::getPlugin('content', 'pfnotifications');
            $params  = new JRegistry($plugin->params);
            $opt_out = (int) $params->get('sub_method', 0);
        }

        if ($opt_out) {
            $class = ($state == 1 ? '' : ' btn-success active');
        }
        else {
            $class = ($state == 1 ? ' btn-success active' : '');
        }

        $html      = array();
        $div_class = (isset($options['div-class']) ? ' ' . $options['div-class'] : '');
        $a_class   = (isset($options['a-class'])   ? ' ' . $options['a-class'] : '');

        $new_state = ($state == 1 ? 0 : 1);
        $aid       = 'watch-btn-' . $type . '-' . $i;
        $title     = addslashes(JText::_('COM_PROJECTFORK_ACTION_WATCH_DESC'));

        $html[] = '<div class="btn-group' . $div_class . '">';
        $html[] = '<a id="' . $aid . '" rel="tooltip" class="btn hasTooltip' . $class . $a_class . '" title="' . $title . '" href="javascript:void(0);" ';
        $html[] = 'onclick="Projectfork.watchItem(' . $i . ', \'' . $type . '\')">';
        $html[] = '<span aria-hidden="true" class="icon-envelope"></span>';
        $html[] = '</a>';
        $html[] = '</div>';
        $html[] = '<div class="btn-group' . $div_class . '">';
        $html[] = '<input type="hidden" id="watch-' . $type . '-' . $i . '" value="' . (int) $state . '"/>';
        $html[] = '</div>';

        return implode('', $html);
    }


    public static function update()
    {
        // Load translations
		$basepath = JPATH_ADMINISTRATOR . '/components/com_projectfork/liveupdate';
		$lang     = JFactory::getLanguage();

		$lang->load('liveupdate', $basepath, 'en-GB', true);
		$lang->load('liveupdate', $basepath, $lang->getDefault(), true);
		$lang->load('liveupdate', $basepath, null, true);

        $info = LiveUpdate::getUpdateInformation();
        $btn  = array();
        $html = array();

        if(!$info->supported) {
			// Unsupported
			$btn['class'] = 'btn-warning';
			$btn['icon']  = 'icon-warning';
			$btn['text']  = JText::_('LIVEUPDATE_ICON_UNSUPPORTED');
		}
        elseif($info->stuck) {
			// Stuck
			$btn['class'] = 'btn-danger';
			$btn['icon']  = 'icon-warning';
			$btn['text']  = JText::_('LIVEUPDATE_ICON_CRASHED');
		}
        elseif($info->hasUpdates) {
			// Has updates
			$btn['class']   = 'btn-primary';
			$button['icon'] = 'icon-download-alt';
			$btn['text']    = JText::_('LIVEUPDATE_ICON_UPDATES');
		}
        else {
			// Already in the latest release
			$btn['class'] = 'btn-success';
			$btn['icon']  = 'icon-ok';
			$btn['text']  = JText::_('LIVEUPDATE_ICON_CURRENT');
		}

        $html[] = '<a class="btn btn-small hasTooltip ' . $btn['class'] . '" rel="tooltip" title="Complete Task" href="index.php?option=com_projectfork&view=liveupdate">';
        $html[] = '<span aria-hidden="true" class="' . $btn['icon'] . '"></span> ';
        $html[] = $btn['text'];
        $html[] = '</a>';

        return implode('', $html);
    }
}