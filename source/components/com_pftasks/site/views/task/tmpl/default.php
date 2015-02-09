<?php
/**
 * @package      Projectfork
 * @subpackage   Tasks
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Create shortcuts to some parameters.
$item    = &$this->item;
$params	 = $item->params;
$canEdit = $item->params->get('access-edit');
$user	 = JFactory::getUser();
$uid	 = $user->get('id');

$nulldate = JFactory::getDBO()->getNullDate();

$asset_name = 'com_pftasks.task.'.$this->item->id;
$canEdit	= ($user->authorise('core.edit', $asset_name));
$canEditOwn	= ($user->authorise('core.edit.own', $asset_name) && $this->item->created_by == $uid);
?>
<div id="projectfork" class="item-page view-task">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="btn-toolbar btn-toolbar-top">
        <?php echo $this->toolbar;?>
    </div>

    <div class="page-header">
	    <h2><?php echo $this->escape($item->title); ?></h2>
	</div>

    <?php echo $item->event->beforeDisplayContent;?>

	<div class="item-description">

        <dl class="article-info dl-horizontal pull-right">
    		<dt class="project-title">
    			<?php echo JText::_('JGRID_HEADING_PROJECT');?>:
    		</dt>
    		<dd class="project-data">
    			<a href="<?php echo JRoute::_(PFprojectsHelperRoute::getDashboardRoute($item->project_slug));?>"><?php echo $item->project_title;?></a>
    		</dd>
            <?php if($item->milestone_id) : ?>
        		<dt class="milestone-title">
        			<?php echo JText::_('JGRID_HEADING_MILESTONE');?>:
        		</dt>
        		<dd class="milestone-data">
        			<a href="<?php echo JRoute::_(PFmilestonesHelperRoute::getMilestoneRoute($item->project_slug, $item->milestone_slug));?>"><?php echo $item->milestone_title;?></a>
        		</dd>
            <?php endif; ?>
            <?php if($item->list_id) : ?>
        		<dt class="list-title">
        			<?php echo JText::_('JGRID_HEADING_TASKLIST');?>:
        		</dt>
        		<dd class="list-data">
        			<a href="<?php echo JRoute::_(PFtasksHelperRoute::getTasksRoute($item->project_slug, $item->milestone_slug, $item->list_slug));?>">
                        <?php echo $item->list_title;?>
                    </a>
        		</dd>
            <?php endif; ?>
            <?php if($item->start_date != $nulldate): ?>
        		<dt class="start-title">
        			<?php echo JText::_('JGRID_HEADING_START_DATE');?>:
        		</dt>
        		<dd class="start-data">
        		  <?php echo JHtml::_('pfhtml.label.datetime', $item->start_date); ?>
        		</dd>
            <?php endif; ?>
            <?php if($item->end_date != $nulldate): ?>
        		<dt class="due-title">
        			<?php echo JText::_('JGRID_HEADING_DEADLINE');?>:
        		</dt>
        		<dd class="due-data">
        			<?php echo JHtml::_('pfhtml.label.datetime', $item->end_date); ?>
        		</dd>
            <?php endif; ?>
    		<dt class="owner-title">
    			<?php echo JText::_('JGRID_HEADING_CREATED_BY');?>:
    		</dt>
    		<dd class="owner-data">
    			 <?php echo JHtml::_('pfhtml.label.author', $item->author, $item->created); ?>
    		</dd>
            <dt class="assigned-title">
    			<?php echo JText::_('COM_PROJECTFORK_FIELDSET_ASSIGNED_USERS');?>:
    		</dt>
    		<dd class="assigned-data">
    			 <?php echo JHtml::_('pftasks.assignedLabel', $item->id, $item->id, $item->users); ?>
    		</dd>
            <?php if (PFApplicationHelper::enabled('com_pfrepo') && count($item->attachments)) : ?>
                <dt class="attachment-title">
        			<?php echo JText::_('COM_PROJECTFORK_FIELDSET_ATTACHMENTS'); ?>:
        		</dt>
        		<dd class="attachment-data">
                     <?php echo JHtml::_('pfrepo.attachments', $item->attachments); ?>
        		</dd>
            <?php endif; ?>
    	</dl>
        <?php echo $item->text; ?>
        <div class="clearfix"></div>
	</div>

	<hr />

    <?php echo $item->event->afterDisplayContent;?>

</div>