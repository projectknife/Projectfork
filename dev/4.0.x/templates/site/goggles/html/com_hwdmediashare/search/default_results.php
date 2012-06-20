<?php
/**
 * @version    SVN $Id: default_results.php 234 2012-03-06 10:41:48Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      25-Nov-2011 17:33:20
 */

// no direct access
defined('_JEXEC') or die;
?>

<div class="search-results<?php echo $this->pageclass_sfx; ?>">
	<?php foreach($this->results as $result) : ?>
	<h3 class="result-title"> <?php echo $this->pagination->limitstart + $result->count.'. ';?>
		<?php if ($result->href) :?>
		<a href="<?php echo JRoute::_($result->href); ?>"<?php if ($result->browsernav == 1) :?> target="_blank"<?php endif;?>> <?php echo $this->escape($result->title);?> </a>
		<?php else:?>
		<?php echo $this->escape($result->title);?>
		<?php endif; ?>
	</h3>
	<?php if ($result->section) : ?>
	<p class="result-category"> <span class="small<?php echo $this->pageclass_sfx; ?>"> (<?php echo $this->escape($result->section); ?>) </span> </p>
	<?php endif; ?>
	<p class="result-text">
	<div class="image-left" style="max-width:100px;"> <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaItemRoute($result->id)); ?>"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($result)); ?>" border="0" alt="<?php echo $this->escape($result->title); ?>" style="max-width:100%;" /> </a> </div>
	<?php echo $result->text; ?>
	<div class="clear"></div>
	</p>
	<?php if ($this->params->get('show_date')) : ?>
	<p class="result-created<?php echo $this->pageclass_sfx; ?>"> <?php echo JText::sprintf('JGLOBAL_CREATED_DATE_ON', $result->created); ?> </p>
	<?php endif; ?>
	<?php endforeach; ?>
</div>
<div class="pagination"> <?php echo $this->pagination->getPagesLinks(); ?> </div>
