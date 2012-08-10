<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');

$action_count = count($this->actions);
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(ProjectforkHelperRoute::getTopicsRoute()); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <div class="btn-group">
                        <?php echo $this->toolbar;?>
                </div>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('projectfork.filterProject');?>
                </div>
                <?php if ($uid) : ?>
                    <div class="btn-group">
                        <a data-toggle="collapse" data-target="#filters" class="btn"><i class="icon-list"></i> <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?> <span class="caret"></span></a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="clearfix"> </div>
            <?php if ($uid) : ?>
                <div class="collapse" id="filters">
                    <div class="well btn-toolbar">
                        <div class="filter-search btn-group pull-left">
                            <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                        </div>
                        <div class="filter-search-buttons btn-group pull-left">
                            <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                            <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                        </div>
                        <?php if ($this->access->get('milestone.edit.state') || $this->access->get('milestone.edit')) : ?>
                            <div class="filter-published btn-group pull-left">
                                <select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
                                    <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                    <?php echo JHtml::_('select.options', $this->states,
                                                        'value', 'text', $this->state->get('filter.published'),
                                                        true
                                                       );
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <?php if (intval($this->state->get('filter.project')) != 0 && count($this->authors)) : ?>
                            <div class="filter-author btn-group pull-left">
                                <select id="filter_author" name="filter_author" class="inputbox" onchange="this.form.submit()">
                                    <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                    <?php echo JHtml::_('select.options', $this->authors,
                                                        'value', 'text', $this->state->get('filter.author'),
                                                        true
                                                       );
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $k = 0;
            foreach($this->items AS $i => $item) :
                $access = ProjectforkHelperAccess::getActions('topic', $item->id);

                $can_create   = $access->get('topic.create');
                $can_edit     = $access->get('topic.edit');
                $can_change   = $access->get('topic.edit.state');
                $can_edit_own = ($access->get('topic.edit.own') && $item->created_by == $uid);
            ?>
                <div class="well well-<?php echo $k;?>">
                    <div class="topic-edit pull-right">
                        <?php
                            $this->menu->start(array('class' => 'btn-mini'));
                            $this->menu->itemEdit('topicform', $item->id, ($can_edit || $can_edit_own));
                            $this->menu->itemTrash('topics', $i, $can_change);
                            $this->menu->end();

                            echo $this->menu->render(array('class' => 'btn-mini'));
                        ?>
                    </div>
                    <div style="display: none !important;">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </div>
                    <?php if ($item->modified != $this->nulldate) : ?>
                        <span class="label label-info pull-right"><i class="icon-calendar icon-white"></i>
                            <?php echo JHtml::_('date', $item->modified, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        </span>
                    <?php endif; ?>
                    <h4 class="milestone-title">
                        <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getTopicRoute($item->slug, $item->project_slug));?>">
                            <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
                            <?php echo $this->escape($item->title);?>
                        </a>
                        <small>
                            in <a href="<?php echo JRoute::_(ProjectforkHelperRoute::getDashboardRoute($item->project_slug));?>">
                            <?php echo $this->escape($item->project_title);?>
                            </a>
                            by <?php echo $this->escape($item->author_name);?>
                        </small>
                        <a href="#topic-<?php echo $item->id;?>" class="btn btn-mini" data-toggle="collapse">
                            <?php echo JText::_('COM_PROJECTFORK_DETAILS_LABEL');?> <span class="caret"></span>
                        </a>
                    </h4>
                    <div class="collapse" id="topic-<?php echo $item->id;?>">
                        <hr />
                        <div class="small">
                            <span class="label access pull-right">
                                <i class="icon-user icon-white"></i> <?php echo $this->escape($item->access_level);?>
                            </span>

                            <?php echo $this->escape($item->description);?>

                            <span class="list-created">
                                <?php echo JHtml::_('date', $item->created, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1')))); ?>
                            </span>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <hr />
                </div>
            <?php
            $k = 1 - $k;
            endforeach;
            ?>

            <div class="filters btn-toolbar">
                <?php if ($this->pagination->get('pages.total') > 1) : ?>
                    <div class="pagination">
                        <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
                        <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
                <?php endif; ?>

                <div class="btn-group display-limit">
                    <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
            </div>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
