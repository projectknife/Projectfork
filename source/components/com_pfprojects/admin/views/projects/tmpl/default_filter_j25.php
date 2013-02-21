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


$list_order  = $this->escape($this->state->get('list.ordering'));
$list_dir    = $this->escape($this->state->get('list.direction'));
?>
<fieldset id="filter-bar">
    <div class="filter-search fltlft">
        <label class="filter-search-lbl" for="filter_search">
            <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
        </label>
        <input type="text" name="filter_search" id="filter_search" class="hasTip"
            placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"
            value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
            title="::<?php echo $this->escape(JText::_('COM_PROJECTFORK_SEARCH_FILTER_TOOLTIP')); ?>"
        />
        <button type="submit" class="btn">
            <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
        </button>
		<button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();">
            <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
        </button>
    </div>

    <div class="filter-select fltrt">
        <div class="fltrt">
            <select name="filter_author_id" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'));?>
            </select>
        </div>
        <div class="fltrt">
            <select name="filter_access" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'), true);?>
            </select>
        </div>
        <div class="fltrt">
            <select name="filter_category" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_pfprojects'), 'value', 'text', $this->state->get('filter.category'), true);?>
            </select>
        </div>
        <div class="fltrt">
            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
            </select>
        </div>
    </div>

    <div class="clr"></div>
</fieldset>

