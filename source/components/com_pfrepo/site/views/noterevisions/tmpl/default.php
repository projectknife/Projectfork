<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

$file = $this->item;
$form_action = PFrepoHelperRoute::getNoteRevisionsRoute($file->slug, $file->project_slug, $file->dir_slug, $file->path);

$user       = JFactory::getUser();
$uid        = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$project    = (int) $this->state->get('filter.project');

$txt_root    = JText::_('COM_PROJECTFORK_REV_ROOT');
$txt_head    = JText::_('COM_PROJECTFORK_REV_HEAD');
$date_format = JText::_('DATE_FORMAT_LC4');

$filter_in = ($this->state->get('filter.isset') ? 'in ' : '');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-repository">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">
        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_($form_action); ?>"
            method="post" autocomplete="off">

            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar; ?>
            </div>

            <div class="<?php echo $filter_in;?>collapse" id="filters">
                <div class="btn-toolbar clearfix">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>
                </div>
            </div>

            <table class="adminlist table table-striped">
                <thead>
                    <tr>
                        <th width="1%" class="nowrap">
                            #
                        </th>
                        <th width="25%">
                            <?php echo JText::_('JGLOBAL_TITLE'); ?>
                        </th>
                        <th>
                            <?php echo JText::_('JGRID_HEADING_DESCRIPTION'); ?>
                        </th>
                        <th width="15%" class="hidden-phone">
                            <?php echo JText::_('JAUTHOR'); ?>
                        </th>
                        <th width="10%" class="hidden-phone">
                            <?php echo JText::_('JDATE'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->items as $i => $item) :
                        $rev_id    = '';
                        $rev_class = '';
                        $icon      = 'icon-checkbox-unchecked';
                        $rev_tt    = JText::_('COM_PROJECTFORK_REV_DESC');
                        $link      = null;
                        $desc      = '';

                        if (!isset($item->ordering)) {
                            $rev_id    = $txt_head;
                            $rev_class = ' badge-success';
                            $icon      = 'icon-checkbox';
                            $rev_tt    = JText::_('COM_PROJECTFORK_REV_HEAD_DESC');
                            $link      = PFrepoHelperRoute::getNoteRoute($item->slug, $item->project_slug, $item->dir_slug, $item->path);
                            $desc      = $item->text;
                        }
                        elseif ($item->ordering == 1) {
                            $rev_id    = $txt_root;
                            $rev_class = ' badge-inverse';
                            $icon      = 'icon-checkbox-partial';
                            $rev_tt    = JText::_('COM_PROJECTFORK_REV_ROOT_DESC');
                            $desc      = $item->description;
                        }
                        else {
                            $rev_id = (int) $item->ordering;
                            $desc   = $item->description;
                        }

                        if (empty($dl_link)) {
                            $link = PFrepoHelperRoute::getNoteRoute($file->slug, $file->project_slug, $file->dir_slug, $file->path, $item->id);
                        }
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="nowrap">
                                <span class="badge hasTip hasTooltip<?php echo $rev_class; ?>" title="<?php echo $rev_tt; ?>">
                                    <i class="<?php echo $icon; ?>"></i>
                                    <?php echo $rev_id; ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo JRoute::_($link); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo JHtml::_('pf.html.truncate', $desc); ?>
                            </td>
                            <td class="hidden-phone small">
                                <?php echo $this->escape($item->author_name); ?>
                            </td>
                            <td class="hidden-phone small">
                                <?php echo JHtml::_('date', $item->created, $date_format); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                	<tr>
                		<td colspan="5"></td>
                	</tr>
                </tfoot>
            </table>

            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="filter_project" value="<?php echo (int) $this->item->project_id; ?>" />
            <input type="hidden" name="filter_parent_id" value="<?php echo (int) $this->item->dir_id; ?>" />
            <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>

</div>