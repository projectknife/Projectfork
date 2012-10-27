<?php
/**
 * @version    SVN $Id: default_subcategories.php 224 2012-03-01 22:09:22Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      06-Dec-2011 17:13:47
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>
<?php if (count($this->subcategories) > 0) :?>
<?php  echo JHtml::_('sliders.start', 'media-category-slider', array('startOffset' => 1)); ?>
<?php  echo JHtml::_('sliders.panel', JText::_('COM_HWDMS_SUBCATEGORIES'), 'subcategories'); ?>

<div class="media-categories-lists">
	<ul>
		<?php foreach($this->subcategories as $id => $item) : ?>
		<li><a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getCategoryRoute($item->id));?>"><?php echo $this->escape($item->title); ?></a> </li>
		<?php endforeach; ?>
	</ul>
	<div class="clear"></div>
</div>
<?php echo JHtml::_('sliders.end'); ?>
<?php endif; ?>
