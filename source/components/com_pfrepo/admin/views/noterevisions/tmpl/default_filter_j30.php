<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();
?>
<div id="filter-bar" class="btn-toolbar">

    <div class="filter-search btn-group pull-left">
        <label for="filter_search" class="element-invisible">
            <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
        </label>
        <input type="text" id="filter_search" name="filter_search"
            data-toggle="tooltip" data-placement="bottom" placeholder="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"
            value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
            title="<?php echo $this->escape(JText::_('COM_PROJECTFORK_SEARCH_FILTER_TOOLTIP')); ?>"
        />
    </div>

    <div class="btn-group pull-left">
        <button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
            <i class="icon-search"></i>
        </button>
        <button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>">
            <i class="icon-remove"></i>
        </button>
    </div>

</div>
<div class="clr clearfix"></div>
<script type="text/javascript">
jQuery('#filter_search').tooltip();
</script>
