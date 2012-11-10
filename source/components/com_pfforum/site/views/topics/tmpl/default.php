<?php
/**
 * @package      Projectfork
 * @subpackage   Forum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('pfhtml.script.listform');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');

$filter_in  = ($this->state->get('filter.isset') ? 'in ' : '');
$repo_enabled  = PFApplicationHelper::enabled('com_pfrepo');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-topics">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(PFforumHelperRoute::getTopicsRoute()); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('pfhtml.project.filter');?>
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
                    <?php if ($this->access->get('core.edit.state') || $this->access->get('core.edit')) : ?>
                        <div class="filter-published btn-group">
                            <select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"> </div>

                    <?php if ($this->state->get('filter.project')) : ?>
                        <hr />
                        <div class="filter-labels">
                            <?php echo JHtml::_('pfhtml.label.filter', 'com_pfforum.topic', $this->state->get('filter.project'), $this->state->get('filter.labels'));?>
                        </div>
                        <div class="clearfix"> </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row-striped row-discussions">
            <?php
            $k = 0;
            foreach($this->items AS $i => $item) :
                $access = PFforumHelper::getActions($item->id);

                $can_create   = $access->get('core.create');
                $can_edit     = $access->get('core.edit');
                $can_change   = $access->get('core.edit.state');
                $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);

                // Prepare the watch button
                $watch = '';

                if ($uid) {
                    $options = array('div-class' => 'pull-right', 'a-class' => 'btn-mini');
                    $watch = JHtml::_('pfhtml.button.watch', 'topics', $i, $item->watching, $options);
                }
            ?>
                <div class="row-fluid row-<?php echo $k;?>">
                    <?php if ($can_change || $uid) : ?>
                        <label for="cb<?php echo $i; ?>" class="checkbox pull-left">
                            <?php echo JHtml::_('pf.html.id', $i, $item->id); ?>
                        </label>
                    <?php endif; ?>
                    <?php
                        $this->menu->start(array('class' => 'btn-mini', 'pull' => 'left'));
                        $this->menu->itemEdit('topicform', $item->id, ($can_edit || $can_edit_own));
                        $this->menu->itemTrash('topics', $i, $can_change);
                        $this->menu->end();

                        echo $this->menu->render(array('class' => 'btn-mini'));
                    ?>

                    <?php echo $watch; ?>

                    <h3 class="topic-title">
                        <a href="<?php echo JRoute::_(PFforumHelperRoute::getTopicRoute($item->slug, $item->project_slug));?>">
                            <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
                            <?php echo $this->escape($item->title);?>
                        </a>
                        &nbsp;
                        <?php echo JHtml::_('pfforum.repliesLabel', $item->replies, $item->last_activity); ?>
                    </h3>

                    <blockquote class="item-description" id="topic-<?php echo $item->id;?>">
                        <?php echo JHtml::_('pf.html.truncate', $item->description, 300); ?>
                    </blockquote>
                    <hr />
                    <?php echo JHtml::_('pfhtml.label.author', $item->author_name, $item->created); ?>
                    <?php echo JHtml::_('pfhtml.label.access', $item->access); ?>
                    <?php if ($repo_enabled) : echo JHtml::_('pfrepo.attachmentsLabel', $item->attachments); endif; ?>
                    <?php if ($item->label_count) : echo JHtml::_('pfhtml.label.labels', $item->labels); endif; ?>
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
