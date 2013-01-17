<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


abstract class JHtmlPFrepo
{
    public static function attachmentsLabel($count = 0)
    {
        if (!$count) {
            return '';
        }

        return '<span class="label"><i class="icon-flag-2 icon-white"></i> ' . intval($count) . '</span>';
    }


    public static function attachments($items = array())
    {
        if (!is_array($items)) {
            return '';
        }

        if (!count($items)) {
            return '';
        }

        $user   = JFactory::getUser();
        $levels = $user->getAuthorisedViewLevels();
        $admin  = $user->authorise('core.admin', 'com_pfrepo');
        $html[] = '<ul class="unstyled">';

        foreach($items AS $item)
        {
            if (!isset($item->repo_data)) {
                continue;
            }

            if (empty($item->repo_data)) {
                continue;
            }

            $data = &$item->repo_data;

            if (!$admin) {
                if (!in_array($data->access, $levels)) {
                    continue;
                }
            }

            list($asset, $id) = explode('.', $item->attachment, 2);

            $icon = '<i class="icon-file"></i> ';
            $link = '#';

            if ($asset == 'directory') {
                $icon = '<i class="icon-folder"></i> ';
                $link = PFrepoHelperRoute::getRepositoryRoute($data->project_id, $data->id . ':' . $data->title, $data->path);
            }

            if ($asset == 'note') {
                $icon = '<i class="icon-pencil"></i> ';
                $link = PFrepoHelperRoute::getNoteRoute($data->id . ':' . $data->title, $data->project_id, $data->dir_id);
            }

            if ($asset == 'file') {
                $link = PFrepoHelperRoute::getFileRoute($data->id . ':' . $data->title, $data->project_id, $data->dir_id);
            }

            $html[] = '<li>';
            $html[] = $icon;
            $html[] = '<a href="' . JRoute::_($link) . '">';
            $html[] = htmlspecialchars($data->title, ENT_COMPAT, 'UTF-8');
            $html[] = '</a>';
            $html[] = '</li>';
        }

        $html[] = '</ul>';

        return implode('', $html);
    }


    /**
     * Displays a batch widget for moving or copying items.
     *
     * @param     string    $project    The project id
     * @param     string    $dir        The current browsing directory
     *
     * @return    string                The necessary HTML for the widget.
     */
    public static function batchItem($project, $dir)
    {
        // Create the copy/move options.
        $options = array(
            JHtml::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
            JHtml::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE'))
        );

        $paths = self::pathOptions($project);

        // Create the batch selector to change select the category by which to move or copy.
        $lines = array(
            '<label id="batch-choose-action-lbl" for="batch-choose-action">',
            JText::_('COM_PROJECTFORK_REPO_BATCH_MENU_LABEL'),
            '</label>',
            '<fieldset id="batch-choose-action" class="combo">',
            '<select name="batch[parent_id]" class="inputbox" id="batch-parent-id">',
            '<option value="">' . JText::_('JSELECT') . '</option>',
            JHtml::_('select.options', $paths),
            '</select>',
            JHtml::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'),
            '</fieldset>'
        );

        return implode("\n", $lines);
    }


    /**
     * Build a list of directory paths
     *
     * @param     string     $project    The project id
     * @param     integer    $exclude    The directory id to exclude
     *
     * @return    array                  The path array
     */
    public static function pathOptions($project, $exclude = null)
    {
        $user  = JFactory::getUser();
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        if ((int) $project == 0) {
            return array();
        }

        // Construct the query
        $query->select('a.id AS value, a.path AS text')
              ->from('#__pf_repo_dirs AS a')
              ->where('a.project_id = ' . $project);

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        if (is_numeric($exclude)) {
            $query->where('a.id != ' . $db->quote((int) $exclude));
        }

        if (is_array($exclude)) {
            JArrayHelper::toInteger($exclude);

            $query->where('a.id NOT IN(' . implode(', ', $exclude) . ')');
        }

        $query->order('a.path');
        $db->setQuery((string) $query);

        // Get the result
        $list    = (array) $db->loadObjectList();
        $options = array();

        foreach($list AS $item)
        {
            $options[] = JHtml::_('select.option',
                (int) $item->value, htmlspecialchars($item->text, ENT_COMPAT, 'UTF-8')
            );
        }

        return $options;
    }
}