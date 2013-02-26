<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
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

    <div class="fltlft">
        <?php echo JHtml::_('pfhtml.project.filter');?>
    </div>

    <div class="filter-select fltrt">
        <?php if ($this->state->get('filter.project')) : ?>
            <div class="fltrt">
                <select name="filter_author_id" class="inputbox" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                    <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author_id'));?>
                </select>
            </div>
        <?php endif; ?>
        <div class="fltrt">
            <select name="filter_context" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_CONTEXT');?></option>
                <?php echo JHtml::_('select.options', $this->contexts, 'value', 'text', $this->state->get('filter.context'), true);?>
            </select>
    	</div>
        <?php if ((int) $this->state->get('filter.project') > 0 && $this->state->get('filter.context') != '') : ?>
            <div class="fltrt">
                <select name="filter_item_id" class="inputbox" onchange="this.form.submit()">
                    <option value=""><?php echo JText::_('JOPTION_SELECT_CONTEXT_ITEM');?></option>
                    <?php echo JHtml::_('select.options', $this->cntxt_items, 'value', 'text', $this->state->get('filter.item_id'));?>
                </select>
            </div>
        <?php endif; ?>
        <div class="fltrt">
            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
            </select>
        </div>
    </div>

    <div class="clr"></div>
</fieldset>

