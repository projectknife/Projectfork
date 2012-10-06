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

$project = (int) $this->state->get('filter.project');
$topic   = (int) $this->state->get('filter.topic');

$filter_in = ($this->state->get('filter.isset') ? 'in ' : '');

$return_page     = base64_encode(JFactory::getURI()->toString());
$link_edit_topic = ProjectforkHelperRoute::getRepliesRoute($topic, $project) . '&task=topicform.edit&id=' . $this->topic->id . '&return=' . $return_page;
$editor          = JFactory::getEditor();
?>
<script type="text/javascript">
Joomla.submitbutton = function(task)
{
	if (task == 'replyform.quicksave') {
		<?php echo $editor->save('jform_description'); ?>
		Joomla.submitform(task);
	}
    else {
        Joomla.submitform(task);
    }
}
</script>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-replies">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(ProjectforkHelperRoute::getRepliesRoute($topic, $project)); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
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
                    <?php if ($this->access->get('reply.edit.state') || $this->access->get('reply.edit')) : ?>
                        <div class="filter-published btn-group">
                            <select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <?php if (is_numeric($this->state->get('filter.project'))) : ?>
                        <div class="filter-author btn-group">
                            <select id="filter_author" name="filter_author" class="inputbox" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"> </div>
                </div>
            </div>

            <!-- Start Topic -->

            <div class="page-header">
                <h2><?php echo $this->escape($this->topic->title);?></h2>
            </div>
            <dl class="article-info dl-horizontal pull-right">
                <dt class="project-title">
                    Project:
                </dt>
                <dd class="project-data">
                    <a href="#"><?php echo $this->escape($this->topic->project_title);?></a>
                </dd>
                <dt class="start-title">
                    Start Date:
                </dt>
                <dd class="start-data">
                    <?php echo JHtml::_('date', $this->topic->created, $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))); ?>
                </dd>
                <dt class="owner-title">
                    Created By:
                </dt>
                <dd class="owner-data">
                     <?php echo $this->escape($this->topic->author_name);?>
                </dd>
            </dl>
            <div class="actions btn-toolbar">
                <div class="btn-group">
                   <?php if ($this->access->get('topic.edit')) : ?>
                       <a class="btn" href="<?php echo JRoute::_($link_edit_topic);?>"><i class="icon-edit"></i> Edit</a>
                   <?php endif; ?>
                </div>
            </div>
            <div class="item-description">
                <?php echo $this->topic->description; ?>
            </div>
            <hr />

            <!-- End Topic -->

            <!-- Start Replies -->

            <?php
            $k = 0;
            foreach($this->items AS $i => $item) :
                $access = ProjectforkHelperAccess::getActions('reply', $item->id);

                $can_create   = $access->get('reply.create');
                $can_edit     = $access->get('reply.edit');
                $can_change   = $access->get('reply.edit.state');
                $can_edit_own = ($access->get('reply.edit.own') && $item->created_by == $uid);
            ?>
                <div class="well well-<?php echo $k;?>">
                    <?php
                        $this->menu->start(array('class' => 'btn-mini', 'pull' => 'right'));
                        $this->menu->itemEdit('replyform', $item->id, ($can_edit || $can_edit_own));
                        $this->menu->itemTrash('replies', $i, $can_change);
                        $this->menu->end();

                        echo $this->menu->render(array('class' => 'btn-mini'));
                    ?>
                    <div style="display: none !important;">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </div>
                    <?php if ($item->modified != $this->nulldate) : ?>
                        <span class="label label-info pull-right"><i class="icon-calendar icon-white"></i>
                            <?php echo JHtml::_('date', $item->modified, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        </span>
                    <?php endif; ?>
                    <h4 class="reply-title">
                        <?php echo $this->escape($item->author_name);?>
                    </h4>
                    <hr />
                    <div class="small" id="topic-<?php echo $item->id;?>">
                        <span class="label access pull-right">
                            <i class="icon-user icon-white"></i> <?php echo $this->escape($item->access_level);?>
                        </span>

                        <?php echo $item->description;?>

                        <span class="list-created">
                            <?php echo JHtml::_('date', $item->created, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1')))); ?>
                        </span>
                    </div>
                    <div class="clearfix"></div>
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

            <?php if ($this->access->get('reply.create')) : ?>
                <hr />
                <h3><?php echo JText::_('COM_PROJECTFORK_QUICK_REPLY');?></h3>
                <div class="well well-reply">
                    <?php echo $editor->display('jform[description]', '', '100%', '250', 0, 0, false, 'jform_description'); ?>
                    <div class="clearfix"> </div>
                    <div class="btn-group">
                        <button class="button btn btn-primary" onclick="Joomla.submitbutton('replyform.quicksave');">
                            <?php echo JText::_('COM_PROJECTFORK_ACTION_SEND');?>
                        </button>
                    </div>
                    <input type="hidden" name="jform[project_id]" value="<?php echo $project;?>" />
                    <input type="hidden" name="jform[topic_id]" value="<?php echo $topic;?>" />
                </div>
            <?php endif; ?>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
            <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="filter_project" value="<?php echo $project;?>" />
            <input type="hidden" name="filter_topic" value="<?php echo $topic;?>" />

            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
