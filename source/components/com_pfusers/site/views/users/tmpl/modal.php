<?php
/**
 * @package      Projectfork
 * @subpackage   Users
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('behavior.tooltip');

$field	   = JRequest::getCmd('field');
$function  = 'jSelectUser_'.$field;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$is_admin  = JFactory::getUser()->authorise('core.admin', 'com_users');
?>
<form action="<?php echo JRoute::_('index.php?option=com_pfusers&view=users&layout=modal&tmpl=component&groups=' . JRequest::getVar('groups', '', 'default', 'BASE64').'&excluded='.JRequest::getVar('excluded', '', 'default', 'BASE64'));?>" method="post" name="adminForm" id="adminForm">

    <fieldset class="filter">
			<label for="filter_search"><?php echo JText::_('JSEARCH_FILTER'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" size="40" />
			<button type="submit" class="btn btn-primary"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
	</fieldset>

	<table class="category table table-striped">
		<thead>
			<tr>
				<th id="tableOrdering0" class="list-name">
					<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
				</th>
				<th id="tableOrdering1" class="list-username" width="25%">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder); ?>
				</th>
                <?php if ($is_admin) : ?>
    				<th id="tableOrdering1" class="list-groups" width="25%">
    					<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_GROUPS', 'group_names', $listDirn, $listOrder); ?>
    				</th>
                <?php endif; ?>
			</tr>
		</thead>
		<tbody>
		<?php
			$i = 0;
			foreach ($this->items as $item) : ?>
			<tr class="cat-list-row<?php echo $i % 2; ?>">
				<td class="list-name">
					<a class="pointer" style="cursor: pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $item->id; ?>', '<?php echo $this->escape(addslashes($item->name)); ?>');">
						<?php echo $item->name; ?></a>
				</td>
				<td class="list-username" align="center">
					<?php echo $item->username; ?>
				</td>
                <?php if ($is_admin) : ?>
    				<td class="list-groups" align="left">
    					<?php echo nl2br($item->group_names); ?>
    				</td>
                <?php endif; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

    <?php if($this->pagination->get('pages.total') > 1) : ?>
        <div class="pagination">
            <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
    <?php endif; ?>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="field" value="<?php echo $this->escape($field); ?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
