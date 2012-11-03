<?php
/**
 * @package      Projectfork
 * @subpackage   Milestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Create shortcuts to some parameters.
$item    = &$this->item;
$user	 = &$this->user;
$params	 = $item->params;
$canEdit = $item->params->get('access-edit');
$uid	 = $user->get('id');

$asset_name = 'com_pfmilestones.milestone.' . $item->id;
$canEdit	= ($user->authorise('core.edit', $asset_name) || $user->authorise('core.edit', $asset_name));
$canEditOwn	= (($user->authorise('core.edit.own', $asset_name) || $user->authorise('core.edit.own', $asset_name)) && $item->created_by == $uid);
?>
<div id="projectfork" class="item-page<?php echo $this->pageclass_sfx?> view-milestone">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

	<div class="page-header">
		<h2><?php echo $this->escape($item->title); ?></h2>
	</div>

	<dl class="article-info dl-horizontal pull-right">
		<dt class="project-title">
			<?php echo JText::_('JGRID_HEADING_PROJECT');?>:
		</dt>
		<dd class="project-data">
			<a href="<?php echo JRoute::_(ProjectforkHelperRoute::getDashboardRoute($item->project_slug));?>"><?php echo $item->project_title;?></a>
		</dd>
		<?php if($item->start_date != JFactory::getDBO()->getNullDate()): ?>
			<dt class="start-title">
				<?php echo JText::_('JGRID_HEADING_START_DATE');?>:
			</dt>
			<dd class="start-data">
				<?php echo JHtml::_('date', $item->start_date, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
			</dd>
		<?php endif; ?>
		<?php if($item->end_date != JFactory::getDBO()->getNullDate()): ?>
			<dt class="due-title">
				<?php echo JText::_('JGRID_HEADING_DEADLINE');?>:
			</dt>
			<dd class="due-data">
				<?php echo JHtml::_('date', $item->end_date, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
			</dd>
		<?php endif;?>
		<dt class="owner-title">
			<?php echo JText::_('JGRID_HEADING_CREATED_BY');?>:
		</dt>
		<dd class="owner-data">
			 <?php echo $this->escape($item->author);?>
		</dd>
	</dl>

	<div class="actions btn-toolbar">
		<div class="btn-group">
			<?php if($canEdit || $canEditOwn) : ?>
			   <a class="btn" href="<?php echo JRoute::_('index.php?option=com_pfmilestones&task=form.edit&id='.intval($this->item->id).':'.$this->item->alias);?>">
			       <i class="icon-edit"></i> <?php echo JText::_('COM_PROJECTFORK_ACTION_EDIT');?>
			   </a>
			<?php endif; ?>
            <?php if (PFApplicationHelper::enabled('com_pftasks')) : ?>
    			<a class="btn" href="<?php echo JRoute::_(PFtasksHelperRoute::getTasksRoute($this->item->project_slug, $this->item->slug));?>">
                    <i class="icon-th-list"></i> <?php echo $this->item->lists;?> <?php echo JText::_('JGRID_HEADING_TASKLISTS');?>
                </a>
    			<a class="btn" href="<?php echo JRoute::_(PFtasksHelperRoute::getTasksRoute($this->item->project_slug, $this->item->slug));?>">
                    <i class="icon-ok"></i> <?php echo $this->item->tasks;?> <?php echo JText::_('JGRID_HEADING_TASKS');?>
                </a>
            <?php endif; ?>
            <?php echo $item->event->afterDisplayTitle;?>
		</div>
	</div>

    <?php echo $item->event->beforeDisplayContent;?>

	<div class="item-description">
		<?php echo $this->escape($item->text); ?>
	</div>
	<hr />

    <?php echo $item->event->afterDisplayContent;?>
</div>