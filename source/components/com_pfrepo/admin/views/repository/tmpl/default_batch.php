<?php
/**
 * @package      Projectfork
 * @subpackage   Repository
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$project = (int) $this->state->get('filter.project');
$dir     = (int) $this->state->get('filter.parent_id');

if ($project && $dir) :
?>
    <fieldset class="batch">
    	<legend><?php echo JText::_('COM_PROJECTFORK_BATCH_OPTIONS');?></legend>
    	<?php echo JHtml::_('pfrepo.batchItem', $project, $dir);?>
    	<button type="submit" class="btn btn-primary" onclick="Joomla.submitbutton('repository.batch');">
    		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
    	</button>
    	<button type="button" class="btn" onclick="document.id('batch-category-id').value='';document.id('batch-access').value='';">
    		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
    	</button>
    </fieldset>
<?php
endif;