<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('projectfork.script.listform');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');

$filter_in  = ($this->state->get('filter.isset') ? 'in ' : '');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(ProjectforkHelperRoute::getTopicsRoute()); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('projectfork.filterProject');?>
                </div>
            </div>

            <div class="clearfix"> </div>

            <div class="<?php echo $filter_in;?>collapse" id="filters">
                <div class="well btn-toolbar">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>

                    <div class="clearfix"> </div>
                    <hr />

                    <?php if (is_numeric($this->state->get('filter.project'))) : ?>
                        <div class="filter-author btn-group">
                            <select id="filter_author" name="filter_author" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->access->get('topic.edit.state') || $this->access->get('topic.edit')) : ?>
                        <div class="filter-published btn-group">
                            <select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"> </div>
                </div>
            </div>
            <div class="row-striped row-discussions">
            <?php
            $k = 0;
            foreach($this->items AS $i => $item) :
                $access = ProjectforkHelperAccess::getActions('topic', $item->id);

                $can_create   = $access->get('topic.create');
                $can_edit     = $access->get('topic.edit');
                $can_change   = $access->get('topic.edit.state');
                $can_edit_own = ($access->get('topic.edit.own') && $item->created_by == $uid);
            ?>
                <div class="row-fluid row-<?php echo $k;?>">
                    <div style="display: none !important;">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </div>
                    <?php if ($item->modified != $this->nulldate) : ?>
                        <span class="list-edited small pull-right"><i class="icon-edit muted"></i>
                            <?php echo JHtml::_('date', $item->modified, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        </span>
                    <?php else: ?>
                    <span class="list-created small pull-right">
                        <?php echo JHtml::_('date', $item->last_activity, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1')))); ?>
                    </span>
                    <?php endif; ?>
                    <h3 class="milestone-title">
                    	<span class="toolbar-inline pull-left">
                        	<?php
	                        $this->menu->start(array('class' => 'btn-mini', 'pull' => 'left'));
	                        $this->menu->itemEdit('topicform', $item->id, ($can_edit || $can_edit_own));
	                        $this->menu->itemTrash('topics', $i, $can_change);
	                        $this->menu->end();

	                        echo $this->menu->render(array('class' => 'btn-mini'));
		                    ?>
                        </span>
                        <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getTopicRoute($item->slug, $item->project_slug));?>">
                            <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
                            <?php echo $this->escape($item->title);?>
                        </a>
                        <small>
                            in <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getDashboardRoute($item->project_slug));?>">
                            <?php echo $this->escape($item->project_title);?>
                            </a>
                        </small>
                        <span class="label access">
                            <i class="icon-eye icon-white"></i> <?php echo $this->escape($item->access_level);?>
                        </span>
                    </h3>
                    <blockquote class="item-description" id="topic-<?php echo $item->id;?>">
                        <?php echo $item->description;?>
                        <small>by <cite title="<?php echo $this->escape($item->author_name);?>"><?php echo $this->escape($item->author_name);?></cite></small>
                    </blockquote>
                </div>
            <?php
            $k = 1 - $k;
            endforeach;
            ?>
            </div>

            <div class="filters btn-toolbar">
                <div class="btn-group filter-order">
                    <select name="filter_order" class="inputbox input-medium" onchange="this.form.submit()">
                        <?php echo JHtml::_('select.options', $this->sort_options, 'value', 'text', $list_order, true);?>
                    </select>
                </div>
                <div class="btn-group folder-order-dir">
                    <select name="filter_order_Dir" class="inputbox input-medium" onchange="this.form.submit()">
                        <?php echo JHtml::_('select.options', $this->order_options, 'value', 'text', $list_dir, true);?>
                    </select>
                </div>
                <div class="btn-group display-limit">
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
                <?php if ($this->pagination->get('pages.total') > 1) : ?>
                    <div class="btn-group pagination">
                        <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
                        <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
