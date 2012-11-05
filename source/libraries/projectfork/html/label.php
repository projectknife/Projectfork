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


abstract class PFhtmlLabel
{
    /**
     * Returns a list of label filters
     *
     * @param     string     $asset      The asset filter group
     * @param     integer    $project    The project filter
     *
     * @return    string                 The label html
     */
    public static function filter($asset, $project = 0, $selected = array(), $filter_style = '')
    {
        if (!$project) {
            $project = ProjectforkHelper::getActiveProjectId();
        }

        if (!$project) {
            return '';
        }

        if (!is_array($selected)) {
            $selected = array();
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        if ($asset == 'com_pfrepo') {
            $asset = $db->quote('com_pfrepo.directory')
                   . 'OR a.asset_group = ' . $db->quote('com_pfrepo.file')
                   . 'OR a.asset_group = ' . $db->quote('com_pfrepo.note');
        }
        else {
            $asset = $db->quote($db->escape($asset));
        }

        $query->select('a.id, a.title, a.style')
              ->from('#__pf_labels AS a')
              ->where('a.project_id = ' . $db->quote((int) $project))
              ->where('(a.asset_group = ' . $db->quote('project') . ' OR a.asset_group = ' . $asset . ')')
              ->order('a.style, a.title ASC');

        $db->setQuery($query);
        $items = (array) $db->loadObjectList();

        $html = array();

        if (!count($items)) {
            return  '';
        }

        $html[] = '<ul class="unstyled">';

        foreach ($items AS $item)
        {
            $checked = (in_array($item->id, $selected) ? ' checked="checked"' : '');
            $class   = ($item->style != '' ? ' ' . $item->style : '');
            $cbid    = htmlspecialchars(str_replace('.', '_', $asset) . '_label_' . $item->id, ENT_COMPAT, 'UTF-8');

            $html[] = '<li class="pull-left btn-group">';
            $html[] = '<label class="checkbox" for="' . $cbid . '" style="cursor:pointer">';
            $html[] = '<input type="checkbox" id="' . $cbid . '" class="inputbox" name="filter_label[]" value="' . (int) $item->id . '"' . $checked . '/>';
            $html[] = '<span class="label' . $class . '">' . htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8') . '</span>';
            $html[] = '</label>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';
        $html[] = '<div class="clearfix clr"></div>';

        $html[] = '<div class="btn-group">';
        $html[] = '<button class="btn" onclick="this.form.submit()">' . JText::_('JSEARCH_FILTER_SUBMIT') . '</button>';
        $html[] = '</div>';

        return implode('', $html);
    }


    /**
     * Returns the labels of an item as formatted html
     *
     * @param     array     $labels    The labels
     *
     * @return    string               The label html
     */
    public static function labels($labels = null)
    {
        if (!is_array($labels)) {
            return '';
        }

        $html = array();

        foreach ($labels AS $label)
        {
            $style  = ($label->style ? ' ' . $label->style : '');
            $title  = htmlspecialchars($label->title, ENT_COMPAT, 'UTF-8');
            $html[] = '<span class="label' . $style. '"><i class="icon-bookmark"></i> ' . $title . '</span>';
        }

        return implode(' ', $html);
    }


    /**
     * Returns a date as literal label
     *
     * @param     string    $date       The date
     * @param     string    $compact    If set to true, will only show the amount of days
     *
     * @return    string                The label html
     */
    public static function datetime($date, $compact = false)
    {
        static $format = null;

        if (is_null($format)) {
            $params = JComponentHelper::getParams('com_projectfork');
            $format = $params->get('date_format');

            if (!$format) {
                $format = JText::_('DATE_FORMAT_LC1');
            }
        }

        $string = PFdate::relative($date);

        if ($string == false) {
            return '';
        }

        $timestamp = strtotime($date);
        $now       = time();
        $remaining = $timestamp - $now;
        $is_past   = ($remaining < 0) ? true : false;
        $tooltip   = $string . '::' . JHtml::_('date', $date, $format);

        if ($compact) {
            $string = ($is_past ? '' : '+') . round($remaining / 86400);
        }

        $html = array();
        $html[] = '<span class="label ' . ($is_past ? 'label-important' : 'label-success');
        $html[] = ' hasTip" title="' . $tooltip . '" style="cursor: help">';
        $html[] = '<i class="icon-' . ($is_past ? 'warning' : 'calendar') . '"></i> ';
        $html[] = $string;
        $html[] = '</span>';

        return implode('', $html);
    }


    /**
     * Returns the author of an item as label
     *
     * @param     string    $name      The user name
     * @param     string    $date      The date
     * @param     string    $format    The new date format for the tooltip
     *
     * @return    string               The label html
     */
    public static function author($name = null, $date = null, $format = null)
    {
        if (!$name || !$date) {
            return '';
        }

        $string = PFDate::relative($date);

        if ($string == false) {
            return '';
        }

        $tooltip = $string . '::' . JHtml::_('date', $date, ($format ? $format : JText::_('DATE_FORMAT_LC1')));

        $html = array();
        $html[] = '<span class="label hasTip" title="' . $tooltip . '" style="cursor: help">';
        $html[] = '<i class="icon-user"></i> ';
        $html[] = htmlspecialchars($name, ENT_COMPAT, 'UTF-8');
        $html[] = '</span>';

        return implode('', $html);
    }


    /**
     * Returns the access level(s) of an item as label
     *
     * @param     integer    $id    The access level id
     *
     * @return    string            The label html
     */
    public static function access($id = null)
    {
        static $is_admin = null;
        static $cache    = array();

        if (is_null($is_admin)) {
            $is_admin = JFactory::getUser()->authorise('core.admin');
        }

        if (!$is_admin || !$id) {
            return '';
        }

        if (!isset($cache[$id]) && $id) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $cache[$id] = array();

            $query->select('rules')
                  ->from('#__viewlevels')
                  ->where('id = ' . $db->quote((int) $id));

            $db->setQuery($query);
            $rules = $db->loadResult();

            if ($rules) {
                $ids = json_decode($rules);

                foreach ($ids AS $gid)
                {
                    $query->clear();
                    $query->select('title')
                          ->from('#__usergroups')
                          ->where('id = ' . $db->quote((int) $gid));

                    $db->setQuery($query);
                    $title = $db->loadResult();

                    if ($title) {
                        $cache[$id][] = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
                    }
                }
            }
        }

        $titles = $cache[$id];
        $count  = count($titles);
        $html   = array();

        if ($count == 1) {
            $html[] = '<span class="label access">';
            $html[] = '<i class="icon-eye icon-white"></i> ';
            $html[] = htmlspecialchars($titles[0], ENT_COMPAT, 'UTF-8');
            $html[] = '</span>';
        }
        else {
            $count = $count - 1;
            $name  = trim(array_pop(array_reverse($titles)));

            $tooltip = JText::_('JGRID_HEADING_ACCESS') . '::' . htmlspecialchars(implode('<br/>', $titles), ENT_COMPAT, 'UTF-8');

            $html[] = '<span class="label hasTip" title="' . $tooltip . '" style="cursor: help">';
            $html[] = '<i class="icon-eye icon-white"></i> ';
            $html[] = htmlspecialchars($name, ENT_COMPAT, 'UTF-8') . ' +' . $count;
            $html[] = '</span>';
        }

        return implode('', $html);
    }
}
