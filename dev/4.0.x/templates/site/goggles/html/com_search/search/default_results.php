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

<ul class="list-striped search-results<?php echo $this->pageclass_sfx; ?>">
	<?php foreach($this->results as $result) : ?>
		<li>
			<?php if ($this->params->get('show_date')) : ?>
				<span class="pull-right label label-info result-created<?php echo $this->pageclass_sfx; ?>">
					<?php echo JText::sprintf('JGLOBAL_CREATED_DATE_ON', $result->created); ?>
				</span>
			<?php endif; ?>
			<h5 class="result-title">
				<?php echo $this->pagination->limitstart + $result->count.'. ';?>
				<?php if ($result->href) :?>
					<a href="<?php echo JRoute::_($result->href); ?>"<?php if ($result->browsernav == 1) :?> target="_blank"<?php endif;?>>
						<?php echo $this->escape($result->title);?>
					</a>
				<?php else:?>
					<?php echo $this->escape($result->title);?>
				<?php endif; ?>
				<?php if ($result->section) : ?>
					<small class="result-category">
						<span class="small<?php echo $this->pageclass_sfx; ?>">
							(<?php echo $this->escape($result->section); ?>)
						</span>
					</small>
				<?php endif; ?>
			</h5>
			
			<div class="result-text">
				<?php echo $result->text; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ul>

<div class="pagination">
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
