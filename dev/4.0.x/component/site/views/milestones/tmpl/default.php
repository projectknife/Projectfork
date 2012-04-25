<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user	    = JFactory::getUser();
$uid	    = $user->get('id');

$action_count = count($this->actions);
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-milestones">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>


    <?php echo $this->toolbar;?>


    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_projectfork&view=milestones'); ?>" method="post">

            <fieldset class="filters">
				<?php if($this->params->get('filter_fields')) : ?>
                    <span class="filter-search">
    			        <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
    			        <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
    			        <button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
    		        </span>
                    <?php if ($this->user->authorise('core.edit.state', 'com_projectfork') || $this->user->authorize('milestone.edit.state', 'com_projectfork')
                          ||  $this->user->authorise('core.edit', 'com_projectfork') || $this->user->authorize('milestone.edit', 'com_projectfork')) : ?>
        				<span class="filter-published">
        				    <select name="filter_published" class="inputbox" onchange="this.form.submit()">
        				        <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
        				        <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'),
                                                    'value', 'text', $this->state->get('filter.published'),
                                                    true
                                                   );
                                ?>
        				    </select>
        				</span>
                    <?php endif; ?>
                    <span class="filter-project">
                        <?php echo JHtml::_('projectfork.filterProject');?>
                    </span>
                <?php endif; ?>
				<?php if ($this->params->get('show_pagination_limit')) : ?>
		            <span class="filter-limit">
			            <?php echo $this->pagination->getLimitBox(); ?>
		            </span>
		        <?php endif; ?>
			</fieldset>


            <?php if($this->pagination->get('pages.total') > 1 && $this->params->get('show_pagination')) : ?>
                <div class="pagination">
                    <?php if ($this->params->get('show_pagination_results')) : ?>
    				    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
    				<?php endif; ?>
    		        <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>