<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<div class="search-results<?php echo $this->pageclass_sfx; ?>">
	<?php foreach($this->results as $result) : ?>
	<div class="row-fluid">
		<h3 class="result-title"> 
			<!-- <?php echo $this->pagination->limitstart + $result->count.'. ';?> -->
			<?php if ($result->href) :?>
			<a href="<?php echo JRoute::_($result->href); ?>"<?php if ($result->browsernav == 1) :?> target="_blank"<?php endif;?>> <?php echo $this->escape($result->title);?> </a>
			<?php else:?>
			<?php echo $this->escape($result->title);?>
			<?php endif; ?>
		</h3>
		<?php if ($result->section) : ?>
		<p class="result-category"> <span class="label label-info small<?php echo $this->pageclass_sfx; ?>"> <?php echo $this->escape($result->section); ?> </span> </p>
		<?php endif; ?>
		<p class="result-text"> <?php echo $result->text; ?> </p>
		<?php if ($this->params->get('show_date')) : ?>
		<p class="result-created<?php echo $this->pageclass_sfx; ?>"> <small><?php echo JText::sprintf('JGLOBAL_CREATED_DATE_ON', $result->created); ?></small> </p>
		<?php endif; ?>
	</div>
	<hr />
	<?php endforeach; ?>
</div>
<div class="pagination"> <?php echo $this->pagination->getPagesLinks(); ?> </div>
