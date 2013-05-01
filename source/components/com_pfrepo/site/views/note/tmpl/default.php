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


// Create shortcuts to some parameters.
$item    = &$this->item;
$params	 = $item->params;
$canEdit = $item->params->get('access-edit');
$user	 = JFactory::getUser();
$uid	 = $user->get('id');

$asset_name = 'com_pfrepo.note.'.$this->item->id;
$canEdit	= ($user->authorise('core.edit', $asset_name));
$canEditOwn	= ($user->authorise('core.edit.own', $asset_name) && $this->item->created_by == $uid);
?>
<div id="projectfork" class="item-page view-task">
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
			<a href="<?php echo JRoute::_(PFprojectsHelperRoute::getDashboardRoute($item->project_slug));?>"><?php echo $item->project_title;?></a>
		</dd>
		<dt class="owner-title">
			<?php echo JText::_('JGRID_HEADING_CREATED_BY');?>:
		</dt>
		<dd class="owner-data">
			 <?php echo $this->escape($this->item->author);?>
		</dd>
	</dl>
	<div class="actions btn-toolbar">
		<div class="btn-group">
			<?php if(($canEdit || $canEditOwn) && !$this->rev) : ?>
			   <a class="btn" href="<?php echo JRoute::_('index.php?option=com_pfrepo&task=noteform.edit&id='.intval($item->id).':'.$item->alias);?>">
			       <i class="icon-edit"></i> <?php echo JText::_('COM_PROJECTFORK_ACTION_EDIT');?>
			   </a>
			<?php endif; ?>

            <?php echo $item->event->afterDisplayTitle;?>
		</div>
	</div>

    <?php echo $item->event->beforeDisplayContent;?>

	<div class="item-description">
		<?php echo $item->text; ?>
	</div>
	<hr />

    <?php echo $item->event->afterDisplayContent;?>

</div>