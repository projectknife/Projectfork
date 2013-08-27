<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfusers
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


if (count($this->item->children) > 5) {
    $this->item->children = array_slice($this->item->children, 0, 5);
    $this->item->children[] = JText::_('COM_PROJECTFORK_AND_MORE') . '...';
}
?>
<div class="well well-small" id="alcm-group-<?php echo (int) $this->item->id; ?>">
    <div class="pull-right">
        <button type="button" class="btn btn-small btn-danger" onclick="PFform.alcmRemoveGroup(<?php echo $this->item->id; ?>)">
            <?php echo JText::_('COM_PROJECTFORK_REMOVE'); ?>
        </button>
    </div>
    <a href="javascript:void(0);" onclick="PFform.alcmToggleActions(<?php echo $this->item->id; ?>)" style="font-weight: bold;">
        <?php echo $this->escape($this->item->title); ?>
    </a>
    <?php if (count($this->item->children)) : ?>
        <p class="small">
            <?php echo JText::_('COM_PROJECTFORK_INCLUDING') . ': '; ?>
            <?php echo implode(', ', $this->item->children); ?>
        </p>
    <?php endif; ?>

    <div id="alcm-actions-<?php echo (int) $this->item->id; ?>" style="display: none;">
        <hr />
        <table class="group-rules table table-striped table-condensed">
            <thead>
                <tr>
                    <th width="20%"><?php echo JText::_('JLIB_RULES_ACTION'); ?></th>
                    <th width="30%"><?php echo JText::_('JLIB_RULES_SELECT_SETTING'); ?></th>
                    <?php if ($this->item->parent_id > 0) : ?>
                        <th><?php echo JText::_('JLIB_RULES_CALCULATED_SETTING'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($this->item->actions AS $action)
                {
                    $sid   = 'jform_rules_' . $action->name . '_' . $this->item->id;
                    $title = JText::_($action->title);
                    $desc  = htmlspecialchars($title . '::' . JText::_($action->description), ENT_COMPAT, 'UTF-8');
                    $rule  = $this->rules->allow($action->name, $this->item->id);

                    $calculated = JAccess::checkGroup($this->item->id, $action->name, $this->asset_id);

                    if ($rule === true) {
                        $selected = '1';
                    }
                    elseif ($rule === false) {
                        $selected = '0';
                    }
                    else {
                        $selected = '';
                    }
                    ?>
                    <tr>
                        <td>
                            <label class="hasTip" for="<?php echo $sid; ?>" title="<?php echo $desc; ?>">
                                <?php echo JText::_($action->title); ?>
                            </label>
                        </td>
                        <td><?php echo $this->getActionHTML($action, $this->component, $selected); ?></td>
                        <?php if ($this->item->parent_id > 0) : ?>
                            <td>
                                <?php echo $this->getCalculated($action, $calculated, $rule); ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="jform[rules][]" value="<?php echo (int) $this->item->id; ?>" />
</div>