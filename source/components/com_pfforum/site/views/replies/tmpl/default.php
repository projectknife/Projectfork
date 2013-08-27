<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfforum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
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

$return_page     = base64_encode(PFforumHelperRoute::getRepliesRoute($topic, $project));
$link_edit_topic = PFforumHelperRoute::getRepliesRoute($topic, $project) . '&task=topicform.edit&id=' . $this->topic->id . '&return=' . $return_page;
$editor          = JFactory::getEditor();

$can_edit_topic     = $user->authorise('core.edit', 'com_pfforum.topic.' . $this->topic->id);
$can_edit_own_topic = ($user->authorise('core.edit.own', 'com_pfforum.topic.' . $this->topic->id) && $uid == $this->topic->created_by);

$doc   = JFactory::getDocument();
$style = '.row-replies .well,.row-replies .btn-toolbar {'
        . 'margin-bottom: 0;'
        . '}'
        . '.img-avatar {'
        . 'max-height: 50px;'
        . 'max-width: 50px;'
        . 'margin-right: 10px;'
        . '}'
        . '.well-item {'
        . 'margin-left: 60px;'
        . '}';
$doc->addStyleDeclaration( $style );
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

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(PFforumHelperRoute::getRepliesRoute($topic, $project)); ?>" method="post" autocomplete="off">
	            <div class="btn-toolbar btn-toolbar-top">
                    <?php echo $this->toolbar; ?>
	            </div>

	            <div class="clearfix"> </div>

	            <div class="<?php echo $filter_in;?>collapse" id="filters">
	                <div class="btn-toolbar">
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
	        <div class="row-striped row-replies">
            <!-- Begin Topic -->
    			<div class="row-fluid">
    				<div class="span12">
                        <img title="<?php echo $this->escape($this->topic->author_name);?>"
                             src="<?php echo JHtml::_('projectfork.avatar.path', $this->topic->created_by);?>"
                             class="img-circle img-avatar pull-left hasTooltip"
                             rel="tooltip"
                        />
    					<div class="well well-small well-item">
    						<span class="small muted pull-right"><?php echo JHtml::_('date', $this->topic->created, $this->params->get('date_format', JText::_('DATE_FORMAT_LC2'))); ?></span>
    						<div class="well-description">
    							<?php echo $this->topic->description; ?>
                                <?php if (count($this->topic->attachment)) : ?>
                                    <fieldset>
                                        <legend class="small" style="font-weight: bold;"><?php echo JText::_('COM_PROJECTFORK_FIELDSET_ATTACHMENTS'); ?></legend>
                                        <?php echo JHtml::_('pfrepo.attachments', $this->topic->attachment); ?>
                                    </fieldset>
                                <?php endif; ?>
    						</div>
    					</div>
                        <div class="btn-toolbar margin-none">
                            <?php if ($can_edit_topic || $can_edit_own_topic) : ?>
                                <div class="btn-group">
                                    <a class="btn btn-mini" href="<?php echo JRoute::_('index.php?option=com_pfforum&task=topicform.edit&id=' . $this->topic->id);?>">
                                        <span aria-hidden="true" class="icon-pencil"></span> <?php echo JText::_('COM_PROJECTFORK_ACTION_EDIT'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
    				</div>
    			</div>
			<!-- End Topic -->

            <!-- Start Replies -->

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
            	<div class="row-fluid">
    				<div class="span12">
                        <img title="<?php echo $this->escape($item->author_name);?>"
                             src="<?php echo JHtml::_('projectfork.avatar.path', $item->created_by);?>"
                             class="img-circle img-avatar pull-left hasTooltip"
                             rel="tooltip"
                        />
    					<div class="well well-small well-item">
    						<?php if ($can_change || $uid) : ?>
		                        <label for="cb<?php echo $i; ?>" class="checkbox pull-left">
		                            <?php echo JHtml::_('pf.html.id', $i, $item->id); ?>
		                        </label>
		                    <?php endif; ?>
    						<span class="small muted pull-right"><?php echo JHtml::_('date', $item->created, $this->params->get('date_format', JText::_('DATE_FORMAT_LC2'))); ?></span>
    						<div class="well-description">
    							<?php echo $item->description;?>
                                <?php if (count($item->attachment)) : ?>
                                    <fieldset>
                                        <legend class="small" style="font-weight: bold;"><?php echo JText::_('COM_PROJECTFORK_FIELDSET_ATTACHMENTS'); ?></legend>
                                        <?php echo JHtml::_('pfrepo.attachments', $item->attachment); ?>
                                    </fieldset>
                                <?php endif; ?>
    						</div>
    					</div>
    					<div class="btn-toolbar margin-none">
                			<?php if ($can_edit || $can_edit_own) : ?>
    	    	    			<div class="btn-group">
    	    	    			    <a class="btn btn-mini" href="<?php echo JRoute::_('index.php?option=com_pfforum&task=replyform.edit&id=' . $item->id);?>">
    	    	    			        <span aria-hidden="true" class="icon-pencil"></span> <?php echo JText::_('COM_PROJECTFORK_ACTION_EDIT'); ?>
    	    	    			    </a>
    	    	    			</div>
	    	    			<?php endif; ?>
                		</div>
    				</div>
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
