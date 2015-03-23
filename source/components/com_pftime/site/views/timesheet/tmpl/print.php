<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfmilestones
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2015 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

// Include css
JHtml::_('stylesheet', 'com_projectfork/projectfork/print.css', false, true, false, false, false);


$db       = JFactory::getDbo();
$query    = $db->getQuery(true);
$nulldate = $db->getNullDate();

$params = JComponentHelper::getParams('com_projectfork');
$date_format = $params->get('date_format');

if (!$date_format) {
    $date_format = JText::_('DATE_FORMAT_LC4');
}

$list_total_time = 0;
$list_total_billable = 0.00;

$doc = JFactory::getDocument();
$doc->addScriptDeclaration('
jQuery(document).ready(function()
{
    window.focus();
    window.print();
});
');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones-print">

    <?php if ($this->state->get('filter.project')) : ?>
        <h2>
            <?php echo $this->escape(PFApplicationHelper::getActiveProjectTitle()); ?>
        </h2>
    <?php endif; ?>

    <table class="table table-striped cat-items">
        <thead>
            <tr>
                <th><?php echo JText::_('JGRID_HEADING_TASK'); ?></th>
                <th class="nowrap" style="width: 20%"><?php echo JText::_('JGRID_HEADING_AUTHOR'); ?></th>
                <th class="nowrap" style="width: 8%"><?php echo JText::_('JGRID_HEADING_DATE'); ?></th>
                <th class="nowrap" style="width: 12%"><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_TIME'); ?></th>
                <th class="nowrap" style="width: 8%"><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_RATE'); ?></th>
                <th class="nowrap" style="width: 8%"><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_BILLABLE'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($this->items AS $i => $item) :
                if ($item->log_time > 0) {
                    $list_total_time += (int) $item->log_time;
                }

                if ((float) $item->billable_total > 0.00) {
                    $list_total_billable += (float) $item->billable_total;
                }
                ?>
                <tr>
                    <td><?php echo $this->escape($item->task_title); ?></td>
                    <td><?php echo $this->escape($item->author_name); ?></td>
                    <td><?php echo JHtml::_('date', $item->log_date, JText::_('DATE_FORMAT_LC4')); ?></td>
                    <td><?php echo JHtml::_('time.format', $item->log_time); ?></td>
                    <td><?php echo JHtml::_('pfhtml.format.money', $item->rate);?></td>
                    <td><?php echo JHtml::_('pfhtml.format.money', $item->billable_total);?></td>
                </tr>
                <?php
            endforeach;
            ?>
        </tbody>
        <tfoot>
    		<tr>
    			<th><?php echo JText::_('COM_PROJECTFORK_TIME_TRACKING_TOTALS');?></th>
    			<th></th>
                <th></th>
                <th><?php echo JHtml::_('time.format', $list_total_time); ?></th>
    			<th></th>
        		<th><?php echo JHtml::_('pfhtml.format.money', $list_total_billable);?></th>
    		</tr>
       	</tfoot>
    </table>
</div>
