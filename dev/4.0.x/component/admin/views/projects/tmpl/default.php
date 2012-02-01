<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user	    = JFactory::getUser();
$uid	    = $user->get('id');
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$save_order = $list_order == 'p.title';
?>
<form action="<?php echo JRoute::_('index.php?option=com_projectfork&view=projects'); ?>" method="post" name="adminForm" id="adminForm">

    <fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />

			<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

            <select name="filter_access" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<select name="filter_manager_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_MANAGER');?></option>
				<?php echo JHtml::_('select.options', $this->managers, 'value', 'text', $this->state->get('filter.manager_id'));?>
			</select>
		</div>
	</fieldset>
	<div class="clr"></div>

    <table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'p.title', $list_dir, $list_order); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'p.state', $list_dir, $list_order); ?>
				</th>
                <th width="10%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'p.access', $list_dir, $list_order); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_MANAGED_BY', 'p.created_by', $list_dir, $list_order); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JDATE', 'p.created', $list_dir, $list_order); ?>
				</th>
                <th width="5%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_STARTDATE', 'p.start_date', $list_dir, $list_order); ?>
				</th>
                <th width="5%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ENDDATE', 'p.end_date', $list_dir, $list_order); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'p.id', $list_dir, $list_order); ?>
				</th>
			</tr>
		</thead>
        <tbody>
		<?php foreach ($this->items as $i => $item) :
            $item->max_ordering = 0; //??
			$ordering	= ($list_order == 'p.title');
			$canCreate	= $user->authorise('core.create',		'com_projectfork.project.'.$item->id);
			$canEdit	= $user->authorise('core.edit',			'com_projectfork.project.'.$item->id);
			$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0;
			$canEditOwn	= $user->authorise('core.edit.own',		'com_projectfork.project.'.$item->id) && $item->created_by == $uid;
			$canChange	= $user->authorise('core.edit.state',	'com_projectfork.project.'.$item->id) && $canCheckin;
            ?>
            <tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'articles.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit || $canEditOwn) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_projectfork&task=project.edit&id='.$item->id);?>">
							<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'projects.', $canChange, 'cb'); ?>
				</td>
                <td class="center">
					<?php echo $this->escape($item->access_level); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->manager_name); ?>
				</td>
				<td class="center nowrap">
					<?php echo JHtml::_('date',$item->created, JText::_('DATE_FORMAT_LC4')); ?>
				</td>
                <td class="center nowrap">
					<?php echo (($item->start_date == $this->nulldate) ? JText::_('DATE_NOT_SET') : JHtml::_('date',$item->start_date, JText::_('DATE_FORMAT_LC4'))); ?>
				</td>
                <td class="center nowrap">
					<?php echo (($item->end_date == $this->nulldate) ? JText::_('DATE_NOT_SET') : JHtml::_('date',$item->end_date, JText::_('DATE_FORMAT_LC4'))); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
    </table>


    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>