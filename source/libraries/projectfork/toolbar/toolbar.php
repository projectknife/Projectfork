<?php
/**
 * @package      Projectfork.Library
 * @subpackage   Toolbar
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


abstract class PFToolbar
{
    protected static $html = array();

    protected static $group_open = false;


    public static function render()
    {
         return implode("\n", self::$html);
    }


    public static function clear()
    {
        self::$html = array();
        self::$group_open = false;
    }


    public static function group()
    {
        if (self::$group_open) {
            self::$html[] = '</div>';
            self::$group_open = false;
        }
        else {
            self::$html[] = '<div class="btn-group">';
            self::$group_open = true;
        }
    }


    public static function button($text, $task = '', $list = false, $options = array())
    {
        self::$html[] = self::renderButton($text, $task, $list, $options);
    }


    public static function filterButton($isset = false, $target = '#filters')
    {
        $class = ($isset ? ' active' : '');

        $html = array();
        $html[] = '<div class="btn-group pull-right">';
        $html[] = '<a data-toggle="collapse" data-target="' . $target . '" class="btn' . $class . '">';
        $html[] = '<span aria-hidden="true" class="icon-filter"></span> ' . JText::_('JSEARCH_FILTER');
        $html[] = '</a>';
        $html[] = '</div>';

        self::$html[] = implode("", $html);
    }


    public static function listButton($items, $options = array())
    {
        $list  = array();
        $html  = array();
        $class = (isset($options['class']) ? $options['class'] : '');
        $icon  = (isset($options['icon'])  ? $options['icon']  : 'icon-pencil');

        foreach ($items AS $item)
        {
            if (isset($item['options'])) {
                if (array_key_exists('access', $item['options'])) {
                    if ($item['options']['access'] == false) {
                        continue;
                    }
                }
            }

            $list[] = $item;
        }

        $count = count($list);

        if ($count == 0) {
            return;
        }

        $html[] = '<div class="btn-group">';
        $html[] = '<a class="btn ' . $class . ' dropdown-toggle disabled" data-toggle="dropdown" id="btn-bulk">';
        $html[] = '    <i class="' . $icon . '"></i> ';
        $html[] = '</a>';
        $html[] = '    <ul class="dropdown-menu">';

        foreach($list AS $i => $item)
        {
            $text = $item['text'];
            $task = (isset($item['task']) ? $item['task'] : '');
            $lst  = (isset($item['list']) ? $item['list'] : true);
            $opts = (isset($item['options']) ? $item['options'] : array());

            $html[] = self::renderListItem($text, $task, $lst, $opts);
        }

        $html[] = '    </ul>';
        $html[] = '</div>';

        self::$html[] = implode("", $html);

    }


    public static function dropdownButton($items, $options = array())
    {
        $list  = array();
        $html  = array();
        $class = (isset($options['class']) ? $options['class'] : 'btn-info');
        $icon  = (isset($options['icon'])  ? $options['icon']  : 'icon-plus icon-white');

        foreach ($items AS $item)
        {
            if (isset($item['options'])) {
                if (array_key_exists('access', $item['options'])) {
                    if ($item['options']['access'] == false) {
                        continue;
                    }
                }
            }

            $list[] = $item;
        }

        $count = count($list);

        if ($count == 0) {
            return;
        }

        if ($count == 1) {
            $text = $list[0]['text'];
            $task = (isset($list[0]['task']) ? $list[0]['task'] : '');
            $lst  = (isset($list[0]['list']) ? $list[0]['list'] : false);
            $opts = (isset($list[0]['options']) ? $list[0]['options'] : array());

            if (!isset($opts['class']) && isset($options['class'])) {
                $opts['class'] = $options['class'];
            }

            if (!isset($opts['icon']) && isset($options['icon'])) {
                $opts['icon'] = $options['icon'];
            }

            self::button($text, $task, $lst, $opts);
        }
        else {
            $reverse = array_reverse($list);
            $first   = array_pop($reverse);

            $text = $first['text'];
            $task = (isset($first['task']) ? $first['task'] : '');
            $lst  = (isset($first['list']) ? $first['list'] : false);
            $opts = (isset($first['options']) ? $first['options'] : array());

            if (!isset($opts['class']) && isset($options['class'])) {
                $opts['class'] = $options['class'];
            }

            if (!isset($opts['icon']) && isset($options['icon'])) {
                $opts['icon'] = $options['icon'];
            }

            if (!isset($opts['id']) && isset($options['id'])) {
                $opts['id'] = $options['id'];
            }

            $html[] = '<div class="btn-group">';
            $html[] = self::renderButton($text, $task, $lst, $opts);
            $html[] = '<a class="btn ' . $class . ' dropdown-toggle" data-toggle="dropdown">';
            $html[] = '    <span class="caret"></span>';
            $html[] = '</a>';
            $html[] = '    <ul class="dropdown-menu">';

            foreach($list AS $i => $item)
            {
                if ($i == 0) continue;

                $text = $item['text'];
                $task = (isset($item['task']) ? $item['task'] : '');
                $lst  = (isset($item['list']) ? $item['list'] : false);
                $opts = (isset($item['options']) ? $item['options'] : array());

                $html[] = self::renderListItem($text, $task, $lst, $opts);
            }

            $html[] = '    </ul>';
            $html[] = '</div>';

            self::$html[] = implode("", $html);
        }
    }


    protected static function renderButton($text, $task = '', $list = false, $options = array())
    {
        $html  = array();
        $class = (isset($options['class']) ? $options['class'] : 'btn-info');
        $href  = (isset($options['href'])  ? $options['href']  : 'javascript:void(0);');
        $icon  = (isset($options['icon'])  ? $options['icon']  : 'icon-plus icon-white');
        $id    = (isset($options['id'])    ? ' id="' . $options['id'] . '"' : '');

        if (array_key_exists('access', $options)) {
            if ($options['access'] !== true) {
                return '';
            }
        }

        $html[] = '<a class="btn ' . $class . '" href="' . $href . '"';

        if ($task) {
            $html[] = 'onclick="';

            if ($list) {
                $message = addslashes(JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
                $html[]  = "if (document.adminForm.boxchecked.value==0){alert('$message');}else{Joomla.submitbutton('$task')}";
            }
            else {
                $html[] = "Joomla.submitbutton('$task');";
            }

            $html[] = '" ';
        }

        $html[] = $id;
        $html[] = '>';
        $html[] = '<i class="' . $icon . '"></i> ';
        $html[] = addslashes(JText::_($text));
        $html[] = '</a>';

        return implode("", $html);
    }


    protected static function renderListItem($text, $task = '', $list = false, $options = array())
    {
        $html  = array();
        $href  = (isset($options['href']) ? $options['href']  : 'javascript:void(0);');
        $icon  = (isset($options['icon']) ? $options['icon']  : '');

        if (isset($options['access'])) {
            if ($options['access'] == false) {
                return '';
            }
        }

        if ($text == 'divider') {
            $html[] = '<li class="divider"></li>';
            return implode("", $html);
        }

        $html[] = '<li>';
        $html[] = '<a href="' . $href . '"';

        if ($task) {
            $html[] = 'onclick="';

            if ($list) {
                $message = addslashes(JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
                $html[]  = "if (document.adminForm.boxchecked.value==0){alert('$message');}else{Joomla.submitbutton('$task')}";
            }
            else {
                $html[] = "Joomla.submitbutton('$task');";
            }

            $html[] = '" ';
        }

        $html[] = '>';

        if ($icon) {
            $html[] = '<i class="' . $icon . '"></i> ';
        }

        $html[] = addslashes(JText::_($text));
        $html[] = '</a>';
        $html[] = '</li>';

        return implode("", $html);
    }
}
