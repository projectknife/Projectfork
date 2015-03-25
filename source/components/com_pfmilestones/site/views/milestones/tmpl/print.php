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
                <th><?php echo JText::_('JGLOBAL_TITLE'); ?></th>
                <th class="nowrap center" style="width: 8%"><?php echo JText::_('COM_PROJECTFORK_TASKS'); ?></th>
                <th class="nowrap" style="width: 8%"><?php echo JText::_('COM_PROJECTFORK_FIELD_START_DATE_LABEL'); ?></th>
                <th class="nowrap" style="width: 8%"><?php echo JText::_('COM_PROJECTFORK_FIELD_DEADLINE_LABEL'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($this->items AS $i => $item) :
                ?>
                <tr>
                    <td>
                        <?php echo $this->escape($item->title);?>
                        <div style="clear: both;"></div>
                        <small><?php echo $this->escape($item->description); ?></small>
                    </td>
                    <td class="center"><?php echo $item->completed_tasks . '/' . $item->tasks; ?></td>
                    <td>
                        <?php
                        if ($item->start_date != $nulldate) {
                            echo JHtml::_('date', $item->start_date, $date_format);
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($item->end_date != $nulldate) {
                            echo JHtml::_('date', $item->end_date, $date_format);
                        }
                        ?>
                    </td>
                </tr>
                <?php
            endforeach;
            ?>
        </tbody>
    </table>
</div>
