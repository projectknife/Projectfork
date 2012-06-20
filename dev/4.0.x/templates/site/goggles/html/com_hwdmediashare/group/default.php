<?php
/**
 * @version    SVN $Id: default.php 224 2012-03-01 22:09:22Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      16-Nov-2011 19:45:40
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$mediaSelectOptions = array("1" => "COM_HWDMS_AUDIO", "2" => "COM_HWDMS_DOCUMENT", "3" => "COM_HWDMS_IMAGE", "4" => "COM_HWDMS_VIDEO");
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$user = JFactory::getUser();
$canEdit = $user->authorise('core.edit', 'com_hwdmediashare.group.'.$this->group->id);
$canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.group.'.$this->group->id);
$canDelete = $user->authorise('core.delete', 'com_hwdmediashare.group.'.$this->group->id);
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');

?>

<form action="<?php echo JRoute::_('index.php?option=com_hwdmediashare'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<div id="hwd-container"> <a name="top" id="top"></a> 
		<!-- Media Navigation --> 
		<?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?> 
		<!-- Media Header -->
		<div class="media-header"> 
			<!-- View Type -->
			<div class="btn-group pull-right"> <span class="btn">(<?php echo (int) $this->group->nummembers; ?>) <?php echo JText::_('COM_HWDMS_MEMBERS'); ?></span> <span class="btn">(<?php echo (int) $this->group->nummedia; ?>) <?php echo JText::_('COM_HWDMS_MEDIA'); ?></span>
				<?php if ($this->group->ismember) : ?>
				<a title="<?php echo JText::_('COM_HWDMS_LEAVE_GROUP'); ?>" class="btn" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=group.leave&id=' . $this->group->id . '&return=' . $this->return . '&tmpl=component'); ?>"><i class="icon-minus-sign"></i> <?php echo JText::_('COM_HWDMS_LEAVE_GROUP'); ?></a>
				<?php else: ?>
				<a title="<?php echo JText::_('COM_HWDMS_JOIN_GROUP'); ?>" class="btn" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=group.join&id=' . $this->group->id . '&return=' . $this->return . '&tmpl=component'); ?>"><i class="icon-plus-sign"></i> <?php echo JText::_('COM_HWDMS_JOIN_GROUP'); ?></a>
				<?php endif; ?>
			</div>
			<div class="page-header">
				<h2><?php echo $this->escape($this->group->title); ?></h2>
			</div>
			<div class="clear"></div>
			<!-- Description -->
			<div class="row-fluid media-album-description"> 
				
				<!-- Thumbnail Image -->
				<div class="span3 media-item">
					<?php if ($canEdit || $canDelete): ?>
					<!-- Actions -->
					<ul class="media-nav">
						<li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
							<ul class="media-subnav">
								<?php if ($canEdit) : ?>
								<li><?php echo JHtml::_('hwdicon.edit', 'group', $this->group, $this->params); ?></li>
								<?php endif; ?>
								<?php if ($canEditState) : ?>
								<?php if ($this->group->published != '1') : ?>
								<li><?php echo JHtml::_('hwdicon.publish', 'group', $this->group, $this->params); ?></li>
								<?php else : ?>
								<li><?php echo JHtml::_('hwdicon.unpublish', 'group', $this->group, $this->params); ?></li>
								<?php endif; ?>
								<?php endif; ?>
								<?php if ($canDelete) : ?>
								<li><?php echo JHtml::_('hwdicon.delete', 'group', $this->group, $this->params); ?></li>
								<?php endif; ?>
							</ul>
						</li>
					</ul>
					<?php endif; ?>
					<!-- Media Type -->
					<div class="media-item-format-3"> <img src="<?php echo JHtml::_('hwdicon.overlay', 3); ?>" alt="Group" /> </div>
					<div class="thumbnail">
					<img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($this->group, 3)); ?>" border="0" alt="<?php echo $this->escape($this->group->title); ?>" style="width:120px;" /> </div>
					</div>
				<div class="span3">
					<ul class="unstyled">
						<li class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </li>
						<li class="media-info-createdby"> <?php echo JText::sprintf('COM_HWDMS_CREATED_BY', '<a href="'.JRoute::_(hwdMediaShareHelperRoute::getUserRoute($this->group->created_user_id)).'">'.htmlspecialchars($this->group->author, ENT_COMPAT, 'UTF-8').'</a>'); ?></li>
						<li class="media-info-created"> <?php echo JText::sprintf('COM_HWDMS_CREATED_ON', JHtml::_('date', $this->group->created, $this->params->get('global_list_date_format'))); ?></li>
						<li class="media-info-hits"> <?php echo JText::_('COM_HWDMS_VIEWS'); ?> (<?php echo (int) $this->group->hits; ?>)</li>
						<li class="media-info-like"> <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=group.like&id=' . $this->group->id . '&return=' . $this->return . '&tmpl=component'); ?>"><?php echo JText::_('COM_HWDMS_LIKE'); ?></a> (<?php echo $this->escape($this->group->likes); ?>) <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=group.dislike&id=' . $this->group->id . '&return=' . $this->return . '&tmpl=component'); ?>"><?php echo JText::_('COM_HWDMS_DISLIKE'); ?></a> (<?php echo $this->escape($this->group->dislikes); ?>) </li>
						<li class="media-info-report"> <a title="<?php echo JText::_('COM_HWDMS_REPORT'); ?>" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&task=groupform.report&id=' . $this->group->id . '&return=' . $this->return . '&tmpl=component'); ?>" class="modal" rel="{handler: 'iframe', size: {x: 500, y: 250}}"><?php echo JText::_('COM_HWDMS_REPORT'); ?> </a> </li>
					</ul>
					<!-- Custom fields -->
					<ul class="unstyled">
						<?php foreach ($this->group->customfields['fields'] as $group => $groupFields) : ?>
						<li class="media-article-info-term"><?php echo JText::_( $group ); ?></li>
						<?php foreach ($groupFields as $field) :
          $field	= JArrayHelper::toObject ( $field );
          $field->value = $this->escape( $field->value );
          ?>
						<li class="media-createdby hasTip" title="" for="jform_<?php echo $field->id;?>" id="jform_<?php echo $field->id;?>-lbl"> <?php echo JText::_( $field->name );?> <?php echo $this->escape($field->value); ?> </li>
						<?php endforeach; ?>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="span6"> <?php echo  JHtml::_('content.prepare',$this->group->description); ?> </div>
			</div>
		</div>
		<div class="clear"></div>
		<div class="media-group-container">
			<div class="row-fluid">
				<div class="span12">
					<h2><?php echo JText::_('COM_HWDMS_GROUP_MEDIA_MAP'); ?></h2>
					<div class="media-group-map" style="height:300px;"> <?php echo ($this->group->map); ?>
						<div class="clear"></div>
					</div>
					<p class="readmore"> <a class="btn modal" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=group&id='.$this->group->id.'&layout=map&tmpl=component'); ?>" rel="{handler: 'iframe', size: {x: 840, y: 550}}"> <?php echo JText::_('COM_HWDMS_ENLARGE_MAP'); ?> </a> </p>
				</div>
			</div>
			<hr />
			<div class="row-fluid">
				<div class="span8"> 
					<!-- Comments -->
					<div class="media-comments">
						<h3><?php echo JText::_('COM_HWDMS_GROUP_ACTIVITY'); ?></h3>
						<div id="member-profile">
							<fieldset>
								<legend><?php echo JText::_('COM_HWDMS_WRITE_A_COMMENT'); ?></legend>
								<div class="control-group">
									<label><a href="#"> <img width="50" height="50" border="0" src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($user, 5)); ?>" /> </a> </label>
									<div class="controls">
										<textarea class="required" rows="10" cols="50" id="jform_comment" name="jform[comment]" required="required" style="width:200px; margin-bottom:10px; height: 50px;"></textarea>
									</div>
								</div>
								<div class="clear"></div>
								<div class="control-group">
									<div class="controls"> <?php echo $this->getRecaptcha(); ?> </div>
								</div>
								<div class="form-actions">
									<input class="btn" type="submit" value="<?php echo JText::_('COM_HWDMS_COMMENT'); ?>" />
								</div>
								<input type="hidden" name="task" value="activity.comment" />
								<input type="hidden" name="id" value="<?php echo $this->group->id; ?>" />
								<input type="hidden" name="element_type" value="3" />
								<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
							</fieldset>
						</div>
						<div class="categories-list"> 
							<?php echo $this->getActivities($this->group->activities); ?> 
						</div>
						<a class="btn modal" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=activities&element_type=3&element_id='.$this->group->id.'&tmpl=component'); ?>" rel="{handler: 'iframe', size: {x: 800, y: 500}}"> <?php echo JText::_('COM_HWDMS_VIEW_ALL_ACTIVITY'); ?> </a>
					</div>
					<div class="item-separator"></div>
				</div>
				<div class="span4"> <?php echo JHtml::_('sliders.start', 'media-group-slider', array('allowAllClose' => 1)); ?> <?php echo JHtml::_('sliders.panel', JText::_('COM_HWDMS_MEMBERS'), 'members'); ?>
					<div class="row-fluid">
						<?php if (count($this->members) == 0) : ?>
						<?php echo JText::_('COM_HWDMS_NO_MEMBERS'); ?>
						<?php endif; ?>
						
						<?php foreach ($this->members as $id => &$item) : ?>
						<div class="span3">
							<a class="hasTip" title="<?php echo $item->title;?>::Media (20)<br />Views(100)" href="#">
								<img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item, 5)); ?>" />
							</a>
						</div>
						<?php endforeach; ?>
					</div>
					<div class="clear"></div>
					<a class="btn modal" style="margin-bottom:20px;" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=group&id='.$this->group->id.'&layout=members&tmpl=component'); ?>" rel="{handler: 'iframe', size: {x: 800, y: 500}}"> <?php echo JText::_('COM_HWDMS_VIEW_ALL_MEMBERS'); ?> </a>
					<?php  echo JHtml::_('sliders.panel',JText::_('COM_HWDMS_MEDIA'), 'media'); ?>
						<?php echo $this->loadTemplate('gallery'); ?>
					<a class="btn modal" style="margin-bottom:20px;" href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=group&id='.$this->group->id.'&layout=media&tmpl=component'); ?>" rel="{handler: 'iframe', size: {x: 800, y: 500}}"> <?php echo JText::_('COM_HWDMS_VIEW_ALL_MEDIA'); ?> </a>
					<?php echo JHtml::_('sliders.end'); ?>
					<div class="item-separator"></div>
				</div>
				<span class="row-separator"></span> </div>
		</div>
	</div>
</form>
