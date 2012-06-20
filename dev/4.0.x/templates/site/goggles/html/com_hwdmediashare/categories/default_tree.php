<?php
/**
 * @version    $Id: default_tree.php 234 2012-03-06 10:41:48Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2007 - 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      15-Apr-2011 10:13:15
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$user = JFactory::getUser();

?>

<div class="categories-list">
	<?php
  $class = ' class="first"';
  if (count($this->items[$this->parent->id]) > 0 && $this->maxLevelcat != 0) :
  ?>
	<?php foreach($this->items[$this->parent->id] as $id => $item) :
  $canEdit = $user->authorise('core.edit', 'com_hwdmediashare.category.'.$item->id);
  $canEditState = $user->authorise('core.edit.state', 'com_hwdmediashare.category.'.$item->id);
  $canDelete = $user->authorise('core.delete', 'com_hwdmediashare.category.'.$item->id);
  ?>
	<?php
    if(!isset($this->items[$this->parent->id][$id + 1]))
    {
            $class = ' class="last"';
    }
    ?>
	<div class="row-fluid">
		<?php $class = ''; ?>
		<?php if ($this->params->get('global_list_meta_title') != 'hide') :?>
		<h3> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getCategoryRoute($item->id));?>"> <?php echo $this->escape($item->title); ?> </a> </h3>
		<?php endif; ?>
		<div class="row-fluid"> 
			<!-- Thumbnail Image -->
			<div class="thumbnail media-item span3">
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
				<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getCategoryRoute($item->slug)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item, $this->elementType)); ?>" border="0" alt="<?php echo $this->escape($item->title); ?>" style="width:100px;max-width:100%;;" /> </a>
				<?php endif; ?>
			</div>
			<?php if ($this->params->get('category_list_meta_category_desc') != 'hide') :?>
			<div class="span9"> <?php echo $this->escape(JHtmlString::truncate(strip_tags($item->description), $this->params->get('global_list_desc_truncate'))); ?>
				<?php if ($this->params->get('category_list_meta_subcategory_count') != 'hide' || $this->params->get('category_list_meta_media_count') != 'hide') :?>
				<?php if ($this->params->get('category_list_meta_media_count') != 'hide') :?>
				<p>
				<span><?php echo JText::_('COM_HWDMS_MEDIA'); ?></span> <span>(<?php echo (int) $item->numitems; ?>)</span>
				<?php endif; ?>
				<?php if ($this->params->get('category_list_meta_subcategory_count') != 'hide' && count($item->getChildren()) > 0) :?>
				<span><?php echo JText::_('COM_HWDMS_SUBCATEGORIES'); ?></span> <span>(<?php echo (int) count($item->getChildren()); ?>)</span>
				<?php endif; ?>
				</p>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
		<?php if(count($item->getChildren()) > 0) :
        $this->items[$item->id] = $item->getChildren();
        $this->parent = $item;
        $this->maxLevelcat--;
        echo $this->loadTemplate('tree');
        $this->parent = $item->getParent();
        $this->maxLevelcat++;
      endif; ?>
	</div>
	<hr />
	<?php endforeach; ?>
	<?php endif; ?>
</div>
