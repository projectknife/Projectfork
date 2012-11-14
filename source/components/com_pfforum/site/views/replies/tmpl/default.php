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

$project = (int) $this->state->get('filter.project');
$topic   = (int) $this->state->get('filter.topic');

$filter_in  = ($this->state->get('filter.isset') ? 'in ' : '');
$topic_in   = ($this->pagination->get('pages.current') == 1 ? 'in ' : '');
$details_in = ($this->pagination->get('pages.current') == 1 ? ' active' : '');

$return_page     = base64_encode(JFactory::getURI()->toString());
$link_edit_topic = PFforumHelperRoute::getRepliesRoute($topic, $project) . '&task=topicform.edit&id=' . $this->topic->id . '&return=' . $return_page;
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

    <h1><?php echo $this->escape($this->topic->title);?></h1>
    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(PFforumHelperRoute::getRepliesRoute($topic, $project)); ?>" method="post">
	            <div class="btn-toolbar btn-toolbar-top">
                    <?php echo $this->toolbar; ?>
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

                    <?php if ($this->access->get('core.edit.state') || $this->access->get('core.edit')) : ?>
                        <div class="filter-published btn-group">
                            <select name="filter_published" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <?php if (is_numeric($this->state->get('filter.project'))) : ?>
                        <div class="filter-author btn-group">
                            <select id="filter_author" name="filter_author" class="inputbox input-medium" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
	                <div class="clearfix"> </div>
	            </div>
	        </div>

            <!-- Start Topic -->
            <div class="btn-group pull-right">
			    <a data-toggle="collapse" data-target="#topic-details" class="btn<?php echo $details_in;?>">
                    <?php echo JText::_('COM_PROJECTFORK_DETAILS_LABEL'); ?> <span class="caret"></span>
                </a>
			</div>

            <div class="clearfix"></div>

            <div class="<?php echo $topic_in;?>collapse" id="topic-details">
                <div class="well">
                    <div class="item-description">
                        <?php echo $this->topic->description; ?>
                        <dl class="article-info dl-horizontal pull-right">
                    		<dt class="owner-title">
                    			<?php echo JText::_('JGRID_HEADING_CREATED_BY'); ?>:
                    		</dt>
                    		<dd class="owner-data">
                    			 <?php echo $this->escape($this->topic->author_name);?>
                    		</dd>
                    		<dt class="start-title">
                                <?php echo JText::_('JGRID_HEADING_CREATED_ON'); ?>:
                            </dt>
                            <dd class="start-data">
                                <?php echo JHtml::_('date', $this->topic->created, $this->params->get('date_format', JText::_('DATE_FORMAT_LC4'))); ?>
                            </dd>
                    	</dl>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <!-- End Topic -->

            <!-- Start Replies -->
            <div class="row-striped row-replies">
            <?php
            $k = 0;
            foreach($this->items AS $i => $item) :
                $access = PFforumHelper::getReplyActions($item->id);

                $can_create   = $access->get('core.create');
                $can_edit     = $access->get('core.edit');
                $can_change   = $access->get('core.edit.state');
                $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);

                $date_opts = array('past-class' => '', 'past-icon' => 'calendar');
            ?>
                <div class="row-fluid row-<?php echo $k;?>">
                    <div style="display: none !important;">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </div>
                    <blockquote id="reply-<?php echo $item->id;?>">
                    	<?php echo $item->description;?>
                    </blockquote>
                    <hr />
                    <?php
                        $this->menu->start(array('class' => 'btn-mini', 'pull' => 'left'));
                        $this->menu->itemEdit('replyform', $item->id, ($can_edit || $can_edit_own));
                        $this->menu->itemTrash('replies', $i, $can_change);
                        $this->menu->end();

                        echo $this->menu->render(array('class' => 'btn-mini', 'pull' => 'left'));
	                ?>
                    <?php echo JHtml::_('pfhtml.label.author', $item->author_name, $item->created); ?>
                    <?php echo JHtml::_('pfhtml.label.datetime', $item->created, false, $date_opts); ?>
                    <?php echo JHtml::_('pfhtml.label.access', $item->access); ?>
                </div>
            <?php
            $k = 1 - $k;
            endforeach;
            ?>
            </div>
            <?php if ($this->access->get('core.create')) : ?>
                <hr />
                <h3><?php echo JText::_('COM_PROJECTFORK_QUICK_REPLY');?> <button class="button btn btn-small btn-primary" onclick="Joomla.submitbutton('replyform.quicksave');"><i class="icon-ok icon-white"></i> <?php echo JText::_('COM_PROJECTFORK_ACTION_SEND');?></button></h3>
                <div class="topic-reply">
                    <?php echo $editor->display('jform[description]', '', '100%', '250', 0, 0, false, 'jform_description'); ?>
                    <div class="clearfix"> </div>
                    <input type="hidden" name="jform[project_id]" value="<?php echo $project;?>" />
                    <input type="hidden" name="jform[topic_id]" value="<?php echo $topic;?>" />
                </div>

            <?php endif; ?>

            <hr />

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
            <input type="hidden" name="filter_project" value="<?php echo $project;?>" />
            <input type="hidden" name="filter_topic" value="<?php echo $topic;?>" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
