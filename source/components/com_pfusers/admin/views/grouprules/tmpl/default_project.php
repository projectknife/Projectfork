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

$rule_group_id = $this->item->id;
if (!$rule_group_id) $rule_group_id = $this->item->parent_id;
?>
<div class="well well-small" id="alcm-group-<?php echo (int) $this->item->id; ?>">
    <?php if ($this->item->id > 0) : ?>
        <div class="pull-right">
            <button type="button" class="btn btn-small btn-danger" onclick="PFform.alcmRemoveGroup(<?php echo $this->item->id; ?>)">
                <?php echo JText::_('COM_PROJECTFORK_REMOVE'); ?>
            </button>
        </div>
    <?php endif; ?>
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
        <?php if (!in_array($this->item->id, $this->public_groups)) : ?>
            <fieldset>
                <legend><?php echo JText::_('COM_PROJECTFORK_MANAGE_GROUP_MEMBERS'); ?></legend>
                <div class="span6">
                    <div class="formelm control-group">
                        <div class="control-label">
                            <?php echo JText::_('COM_PROJECTFORK_ADD_USERS'); ?>
                        </div>
                        <div class="controls">
                            <input type="hidden" id="add_user_group_<?php echo (int) $this->item->id; ?>" class="input-xlarge" size="80" name="jform[add_groupuser][<?php echo (int) $this->item->id; ?>]"/>
                        </div>
                    </div>
                </div>
                <?php if ($this->item->id > 0) : ?>
                <div class="span6">
                    <div class="formelm control-group">
                        <div class="control-label">
                            <?php echo JText::_('COM_PROJECTFORK_REMOVE_USERS'); ?>
                        </div>
                        <div class="controls">
                            <input type="hidden" id="rm_user_group_<?php echo (int) $this->item->id; ?>" class="input-xlarge" size="80" name="jform[rm_groupuser][<?php echo (int) $this->item->id; ?>]"/>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </fieldset>
            <hr />
        <?php endif; ?>
        <fieldset>
            <legend><?php echo JText::_('COM_PROJECTFORK_MANAGE_GROUP_PERMISSIONS'); ?></legend>

        <?php foreach ($this->item->actions AS $com => $actions)
        {
            $com_title = JText::_($com);

            if (strpos($com_title, '-') !== false) {
                $tmp = explode('-', $com_title);
                $com_title = trim($tmp[1]);
            }

            if ($com == 'com_pfprojects') {
                $rules    = $this->getAssetRules();
                $asset_id = $this->asset_id;
            }
            else {
                $asset_id = $this->getComponentProjectAssetId($com, $this->project_id);
                $rules    = $this->getAssetRules($com, $asset_id);
            }
            ?>
            <table class="group-rules table table-striped table-condensed">
                <thead>
                    <tr>
                        <th width="20%" nowrap="nowrap" class="nowrap"><?php echo $com_title; ?></th>
                        <th width="20%"><?php echo JText::_('JLIB_RULES_ACTION'); ?></th>
                        <th width="30%"><?php echo JText::_('JLIB_RULES_SELECT_SETTING'); ?></th>
                        <?php if ($this->item->parent_id > 0) : ?>
                            <th class="hidden-phone"><?php echo JText::_('JLIB_RULES_CALCULATED_SETTING'); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($actions AS $action)
                    {
                        $sid   = 'jform_rules_' . $action->name . '_' . $this->item->id;
                        $title = JText::_($action->title);
                        $desc  = htmlspecialchars($title . '::' . JText::_($action->description), ENT_COMPAT, 'UTF-8');

                        $rule       = $rules->allow($action->name, $rule_group_id);
                        $calculated = JAccess::checkGroup($rule_group_id, $action->name, $asset_id);

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
                            <td></td>
                            <td>
                                <label class="hasTip" for="<?php echo $sid; ?>" title="<?php echo $desc; ?>">
                                    <?php echo JText::_($action->title); ?>
                                </label>
                            </td>
                            <td><?php echo $this->getActionHTML($action, $com, $selected); ?></td>
                            <?php if ($this->item->parent_id > 0) : ?>
                                <td class="hidden-phone">
                                    <?php echo $this->getCalculated($action, $calculated, $rule); ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
            <?php
        }
        ?>
        </fieldset>
    </div>
    <input type="hidden" name="jform[rules][<?php echo $this->component; ?>][]" value="<?php echo (int) $this->item->id; ?>" />
    <?php if (!in_array($this->item->id, $this->public_groups)) : ?>
        <script type="text/javascript">
        jQuery('#add_user_group_<?php echo (int) $this->item->id; ?>').select2(
        {
            allowClear: true,
            minimumInputLength: 0,
            multiple: true,
            ajax:
            {
                url: 'index.php?option=com_pfusers&view=groupusers&id=<?php echo (int) $this->item->id; ?>&filter_type=exclude&tmpl=component&layout=select2&format=json',
                dataType: 'json',
                quietMillis: 200,
                data: function (term, page)
                {
                    return {filter_search: term, limit: 10, limitstart: ((page - 1) * 10)};
                },
                results: function (data, page)
                {
                    var more = (page * 10) < data.total;
                    return {results: data.items, more: more};
                }
            }
        });
        <?php if ($this->item->id > 0) : ?>
            jQuery('#rm_user_group_<?php echo (int) $this->item->id; ?>').select2(
            {
                allowClear: true,
                minimumInputLength: 0,
                multiple: true,
                ajax:
                {
                    url: 'index.php?option=com_pfusers&view=groupusers&id=<?php echo (int) $this->item->id; ?>&filter_type=include&tmpl=component&layout=select2&format=json',
                    dataType: 'json',
                    quietMillis: 200,
                    data: function (term, page)
                    {
                        return {filter_search: term, limit: 10, limitstart: ((page - 1) * 10)};
                    },
                    results: function (data, page)
                    {
                        var more = (page * 10) < data.total;
                        return {results: data.items, more: more};
                    }
                }
            });
        <?php endif; ?>
        </script>
    <?php endif; ?>
</div>