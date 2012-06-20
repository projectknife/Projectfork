<?php
/**
 * @version    SVN $Id: default_details.php 234 2012-03-06 10:41:48Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      15-Nov-2011 10:26:33
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$user = JFactory::getUser();

$row=0;
$counter=0;
$leadingcount=0;
$introcount=0;
?>
<!-- View Container -->

<div class="media-details-view">
	<?php foreach ($this->items[$this->parent->id] as $id => $item) :
$id= ($id-$leadingcount)+1;
$rowcount=( ((int)$id-1) %	(int) $this->columns) +1;
$row = $counter / $this->columns ;
$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
$canEdit = $user->authorise('core.edit', 'com_hwdmediashare.'.$this->view_item.'.'.$item->id);
$canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.'.$this->view_item.'.'.$item->id);
$canDelete = $user->authorise('core.delete', 'com_hwdmediashare.'.$this->view_item.'.'.$item->id);

// Load cateogry media
JRequest::setVar('category_id',$item->id);
$item->media = $this->getCategoryMedia($item);

$hwdTooltipPos = "position: {edge: 'left', position: 'right'}";
if ($rowcount == $this->columns):
	$hwdTooltipPos = "position: {edge: 'right', position: 'left'}";
endif;
?>
	<?php if (count($item->media) > 0) : ?>
	<!-- Tooltip container-->
	<div class="tipContainer" id="tipContainer<?php echo $item->id; ?>"><?php echo $this->loadTemplate('media_list'); ?></div>
	<?php endif; ?>
	<!-- Row -->
	<?php if ($rowcount == 1) : ?>
	<div class="row-fluid items-row cols-<?php echo (int) $this->columns;?> <?php echo 'row-'.$row ; ?>">
		<?php endif; ?>
		<!-- Column -->
		<div id="hwd-tooltip-<?php echo $item->id; ?>" class="item column-<?php echo $rowcount;?><?php echo ($item->published != '1' ? ' system-unpublished' : false); ?> span<?php echo round((12/$this->columns));?>"> 
			<!-- Cell -->
			<?php if ($item->published != '1') : ?>
			<div class="system-unpublished">
				<?php endif; ?>
				<?php if ($this->params->get('global_list_meta_title') != 'hide') :?>
				<h3> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getCategoryRoute($item->slug)); ?>"> <?php echo $this->escape(JHtmlString::truncate($item->title, $this->params->get('global_list_title_truncate'))); ?> </a> </h3>
				<?php endif; ?>
				<!-- Thumbnail Image -->
				<div class="thumbnail media-item">
					<?php if ($canEdit || $canDelete): ?>
					<!-- Actions -->
					<ul class="media-nav">
						<li><a href="#" class="pagenav-manage"><?php echo JText::_('COM_HWDMS_MANAGE'); ?> </a>
							<ul class="media-subnav">
								<?php if ($canEdit) : ?>
								<li><?php echo JHtml::_('hwdicon.edit', $this->view_item, $item, $this->params); ?></li>
								<?php endif; ?>
								<?php if ($canEditState) : ?>
								<?php if ($item->published != '1') : ?>
								<li><?php echo JHtml::_('hwdicon.publish', $this->view_item, $item, $this->params); ?></li>
								<?php else : ?>
								<li><?php echo JHtml::_('hwdicon.unpublish', $this->view_item, $item, $this->params); ?></li>
								<?php endif; ?>
								<?php endif; ?>
								<?php if ($canDelete) : ?>
								<li><?php echo JHtml::_('hwdicon.delete', $this->view_item, $item, $this->params); ?></li>
								<?php endif; ?>
							</ul>
						</li>
					</ul>
					<?php endif; ?>
					<!-- Media Type -->
					<?php if ($this->params->get('global_list_meta_thumbnail') != 'hide') :?>
					<div class="media-item-format-<?php echo $this->elementType; ?>"> <img src="<?php echo JHtml::_('hwdicon.overlay', $this->elementType, $item); ?>" alt="<?php echo $this->elementName; ?>" /> </div>
					<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getCategoryRoute($item->slug)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item, $this->elementType)); ?>" border="0" alt="<?php echo $this->escape($item->title); ?>" style="max-width:100%;" /> </a>
					<?php endif; ?>
				</div>
				<!-- Clears Item and Information -->
				<div class="clear"></div>
				<!-- Item Meta -->
				<?php if ($this->params->get('category_list_meta_category_desc') != 'hide') :?>
				<div class="media-category-description"> <?php echo $this->escape(JHtmlString::truncate(strip_tags($item->description), $this->params->get('global_list_desc_truncate'))); ?> </div>
				<hr />
				<?php endif; ?>
				<?php if ($this->params->get('category_list_meta_subcategory_count') != 'hide' || $this->params->get('category_list_meta_media_count') != 'hide') :?>
				<ul class="unstyled">
					<li class="article-info-term"><?php echo JText::_('COM_HWDMS_DETAILS'); ?> </li>
					<?php if ($this->params->get('category_list_meta_media_count') != 'hide') :?>
					<li class="media-info-count"> <?php echo JText::_('COM_HWDMS_MEDIA'); ?> (<?php echo (int) $item->numitems; ?>)</li>
					<?php endif; ?>
					<?php if ($this->params->get('category_list_meta_subcategory_count') != 'hide' && count($item->getChildren()) > 0) :?>
					<li class="media-info-subcategories"> <?php echo JText::_('COM_HWDMS_SUBCATEGORIES'); ?> (<?php echo (int) count($item->getChildren()); ?>)</li>
					<?php endif; ?>
				</ul>
				<?php endif; ?>
				<?php if ($item->published != '1') : ?>
			</div>
			<?php endif; ?>
			<div class="item-separator"></div>
		</div>
		<?php if (count($item->media) > 0) : ?>
		<!-- Tooltip Javascript --> 
		<script type="text/javascript">
    window.addEvent('domready', function() {
            document.id('hwd-tooltip-<?php echo $item->id; ?>').addEvent('mouseenter', function() {
                    ToolTip.instance(this, {
                            autohide: true,
                            <?php echo $hwdTooltipPos; ?>
                    }, new Element('div', {
                            html: document.id('tipContainer<?php echo $item->id; ?>').innerHTML
                            }
                    )).show();
            });
    });
    </script>
		<?php endif; ?>
		<?php if (($rowcount == $this->columns) or (($counter + 1) == count($this->items[$this->parent->id]))): ?>
		<span class="row-separator"></span> </div>
	<?php endif; ?>
	<?php $counter++; ?>
	<?php endforeach; ?>
</div>
