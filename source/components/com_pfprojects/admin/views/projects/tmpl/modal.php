<?php
/**
 * @package      Projectfork
 * @subpackage   Projects
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$func       = $this->escape(JRequest::getCmd('function', 'pfSelectActiveProject'));
$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$filter_cat = $this->state->get('filter.category') > 0 ? true : false;

$txt_notset  = JText::_('DATE_NOT_SET');
$txt_cat     = JText::_('JCATEGORY');
$txt_nocat   = JText::_('COM_PROJECTFORK_UNCATEGORISED');
$date_format = JText::_('DATE_FORMAT_LC4');

if (!$this->is_j25) :
    JHtml::_('formbehavior.chosen', 'select');
endif;
?>
<form action="<?php echo JRoute::_('index.php?option=com_pfprojects&view=projects&layout=modal&tmpl=component&function=' . $func);?>" method="post" name="adminForm" id="adminForm">
    <?php if ($this->is_j25) : ?>
        <!-- Joomla 2.5 Filters -->
        <fieldset class="filter">
            <div class="left">
                <input type="text" name="filter_search" id="filter_search"
                    placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"
                    value="<?php echo $this->escape($this->state->get('filter.search')); ?>" size="30"
                    title="<?php echo $this->escape(JText::_('COM_PROJECTFORK_SEARCH_FILTER_TOOLTIP')); ?>"
                    />
                <button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
                <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
            </div>
            <div class="clr"></div>
            <hr />
            <div class="left">
                <select name="filter_author_id" class="inputbox" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                    <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'));?>
                </select>
                <select name="filter_access" class="inputbox" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
                    <?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'), true);?>
                </select>
                <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                    <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                </select>
                <select name="filter_category" class="inputbox" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
                    <?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_pfprojects'), 'value', 'text', $this->state->get('filter.category'));?>
                </select>
            </div>
            <div class="clr"></div>
        </fieldset>
        <div class="clr"></div>
        <!-- Joomla 2.5 Filters -->
    <?php else : ?>
        <!-- Joomla 3+ Filters -->
        <fieldset class="filter clearfix">
            <div class="btn-toolbar">
                <div class="filter-search btn-group pull-left">
                    <label for="filter_search" class="element-invisible">
                        <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
                    </label>
                    <input type="text" id="filter_search" name="filter_search"
                        data-toggle="tooltip" data-placement="right" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"
                        value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                        title="<?php echo $this->escape(JText::_('COM_PROJECTFORK_SEARCH_FILTER_TOOLTIP')); ?>"
                    />
                </div>
                <div class="btn-group pull-left">
                    <button class="btn" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
                        <i class="icon-search"></i>
                    </button>
                    <button class="btn" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>">
                        <i class="icon-remove"></i>
                    </button>
                </div>
                <div class="clearfix"></div>
            </div>
            <hr class="hr-condensed" />
            <div class="filters">
                <div class="btn-group pull-left">
                    <select name="filter_author_id" class="input-medium" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                        <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'));?>
                    </select>
                </div>
                <div class="btn-group pull-left">
                    <select name="filter_access" class="input-medium" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
                        <?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'), true);?>
                    </select>
                </div>
                <div class="btn-group pull-left">
                    <select name="filter_published" class="input-medium" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                        <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                    </select>
                </div>
                <div class="btn-group pull-left">
                    <select name="filter_category" class="input-medium" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
                        <?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_pfprojects'), 'value', 'text', $this->state->get('filter.category'));?>
                    </select>
                </div>
            </div>
        </fieldset>
        <script type="text/javascript">
        jQuery('#filter_search').tooltip();
        </script>
        <!-- Joomla 3+ Filters -->
    <?php endif; ?>

    <table class="adminlist table table-striped">
        <thead>
            <tr>
                <th class="title">
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $list_dir, $list_order); ?>
                </th>
                <th width="24%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JAUTHOR', 'author_name', $list_dir, $list_order); ?>
                </th>
                <th width="20%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ACCESS', 'access_level', $list_dir, $list_order); ?>
                </th>
                <th width="1%" class="nowrap">
                    <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $list_dir, $list_order); ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->items as $i => $item) :
            $js = 'if (window.parent) window.parent.' . $func . '('
                . "'" . (int) $item->id . "', "
                . "'" . $this->escape(addslashes($item->title)) . "'"
                . ');';
            ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td>
                    <a class="pointer" onclick="<?php echo $js; ?>" style="cursor: pointer;" href="javascript:void(0);">
                        <?php echo $this->escape($item->title); ?>
                    </a>

                    <?php if (!$filter_cat) : ?>
                        <div class="small">
                            <?php echo $txt_cat . ': ' . ($item->category_title ? $this->escape($item->category_title) : $txt_nocat); ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="small">
                    <?php echo $this->escape($item->author_name); ?>
                </td>
                <td class="small">
                    <?php echo $this->escape($item->access_level); ?>
                </td>
                <td class="small">
                    <?php echo (int) $item->id; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <?php if ($this->is_j25) : ?>
            <tfoot>
                <tr>
                    <td colspan="4">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
        <?php endif; ?>
    </table>

    <?php if (!$this->is_j25) echo $this->pagination->getListFooter();  ?>

    <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
