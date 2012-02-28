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


JHtml::_('behavior.multiselect');


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$db         = JFactory::getDbo();
$null_date  = $db->getNullDate();
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-projects">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <?php if($this->params->get('show_subheading', 1) or $this->params->get('page_subheading')) : ?>
	<h2>
        <?php
        if($this->params->get('page_subheading')) {
            echo $this->escape($this->params->get('page_subheading'));
        }
        else {
            echo $this->escape($this->params->get('page_title'));
        }
        ?>
	</h2>
	<?php endif; ?>

	<input type="button" class="button btn btn-info" value="<?php echo JText::_('COM_PROJECTFORK_NEW_PROJECT');?>" />


    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_projectfork&view=projects'); ?>" method="post">

            <fieldset class="filters">
            	<div class="display-bulk-actions">
            	    <select onchange="Projectfork.bulkAction(this);" size="1" class="inputbox" name="bulk" id="bulk">
            		    <option selected="selected" value=""><?php echo JText::_('COM_PROJECTFORK_BULK_ACTIONS');?></option>
            			<option value="project.publish"><?php echo JText::_('COM_PROJECTFORK_ACTION_PUBLISH');?></option>
        			    <option value="project.unpublish"><?php echo JText::_('COM_PROJECTFORK_ACTION_UNPUBLISH');?></option>
        			    <option value="project.archive"><?php echo JText::_('COM_PROJECTFORK_ACTION_ARCHIVE');?></option>
        			    <option value="project.copy"><?php echo JText::_('COM_PROJECTFORK_ACTION_COPY');?></option>
        			    <option value="project.delete"><?php echo JText::_('COM_PROJECTFORK_ACTION_DELETE');?></option>
            	    </select>
            	</div>
                <?php if($this->params->get('filter_field')) : ?>
                    <div class="filter-search">
    			        <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
    			        <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
    			        <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
    			        <button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
    		        </div>
                <?php endif; ?>
                <?php if($this->params->get('filter_state')) : ?>
    				<div class="display-published">
    				    <select name="filter_published" class="inputbox" onchange="this.form.submit()">
    				        <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
    				        <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'),
                                                'value', 'text', $this->state->get('filter.published'),
                                                true
                                               );
                            ?>
    				    </select>
    				</div>
                <?php endif; ?>
				<?php if ($this->params->get('show_pagination_limit')) : ?>
		            <div class="display-limit">
			            <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			            <?php echo $this->pagination->getLimitBox(); ?>
		            </div>
		        <?php endif; ?>
			</fieldset>

            <table class="category table table-striped">
                <?php if ($this->params->get('show_headings')) :?>
                    <thead>
    	                <tr>
    	               	    <th id="tableOrdering0" class="list-select">
    	               			<input type="checkbox" onclick="checkAll(<?php echo count($this->items);?>);" value="" name="toggle" />
    	               		</th>
    	               		<th id="tableOrdering1" class="list-title">
    	               		    <a title="<?php echo JText::_('COM_PROJECTFORK_SORT_COL_DESC');?>" href="javascript:tableOrdering('a.title','asc','');">
                                    <?php echo JText::_('COM_PROJECTFORK_TITLE');?>
                                </a>
                            </th>
    	               		<th id="tableOrdering2" class="list-actions span1">
    	               			<a title="<?php echo JText::_('COM_PROJECTFORK_SORT_COL_DESC');?>" href="javascript:tableOrdering('a.title','asc','');"></a>
    	               		</th>
    	               		<?php if($this->params->get('show_manager_col')) : ?>
                            <th id="tableOrdering3" class="list-owner">
    	               		    <a title="<?php echo JText::_('COM_PROJECTFORK_SORT_COL_DESC');?>" href="javascript:tableOrdering('author_name','asc','');">
                                   <?php echo JText::_('COM_PROJECTFORK_MANAGER');?>
                                </a>
                            </th>
                            <?php endif; ?>
                            <?php if($this->params->get('show_mscount_col')) : ?>
    	               		<th id="tableOrdering4" class="list-milestones">
    	               		    <a title="<?php echo JText::_('COM_PROJECTFORK_SORT_COL_DESC');?>" href="javascript:tableOrdering('a.milestones','asc','');">
                                    <?php echo JText::_('COM_PROJECTFORK_MILESTONES');?>
                                </a>
                            </th>
                            <?php endif; ?>
                            <?php if($this->params->get('show_tcount_col')) : ?>
    	               		<th id="tableOrdering5" class="list-tasks">
    	               		    <a title="<?php echo JText::_('COM_PROJECTFORK_SORT_COL_DESC');?>" href="javascript:tableOrdering('a.tasks','asc','');">
                                    <?php echo JText::_('COM_PROJECTFORK_TASKS');?>
                                </a>
                            </th>
                            <?php endif; ?>
                            <?php if($this->params->get('show_sdate_col')) : ?>
    	               		<th id="tableOrdering6" class="list-tasks">
    	               		    <a title="<?php echo JText::_('COM_PROJECTFORK_SORT_COL_DESC');?>" href="javascript:tableOrdering('a.start_date','asc','');">
                                    <?php echo JText::_('COM_PROJECTFORK_SDATE');?>
                                </a>
                            </th>
                            <?php endif; ?>
                            <?php if($this->params->get('show_edate_col')) : ?>
    	               		<th id="tableOrdering6" class="list-tasks">
    	               		    <a title="<?php echo JText::_('COM_PROJECTFORK_SORT_COL_DESC');?>" href="javascript:tableOrdering('a.end_date','asc','');">
                                    <?php echo JText::_('COM_PROJECTFORK_EDATE');?>
                                </a>
                            </th>
                            <?php endif; ?>
                            <?php if($this->params->get('show_access_col')) : ?>
    	               		<th id="tableOrdering7" class="list-tasks">
    	               		    <a title="<?php echo JText::_('COM_PROJECTFORK_SORT_COL_DESC');?>" href="javascript:tableOrdering('a.access_level','asc','');">
                                    <?php echo JText::_('COM_PROJECTFORK_ACCESS');?>
                                </a>
                            </th>
                            <?php endif; ?>
    	               	</tr>
                   </thead>
               <?php endif; ?>
               <tbody>
                    <?php
                    $k = 0;
                    foreach($this->items AS $i => $item) :
                    ?>
                        <tr class="cat-list-row<?php echo $k;?>">
    	               		<td class="list-select">
    	               			<input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo intval($item->id);?>" name="cid[]" id="cb<?php echo $i;?>"/>
    	               		</td>
    	               		<td class="list-title">
    	               		    <a href="<?php echo JRoute::_('index.php?option=com_projectfork&view=project&id='.intval($item->id));?>">
                                    <?php echo $this->escape($item->title);?>
                                </a>
    	               		</td>
    	               		<td class="list-actions">
    	               			<div class="btn-group">
    	               			  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
    	               			    <span class="caret"></span>
    	               			  </a>
    	               			  <ul class="dropdown-menu">
    	               			    <li><a href="#">Edit</a></li>
    	               			    <li><a href="#">Delete</a></li>
    	               			  </ul>
    	               			</div>
    	               		</td>
                            <?php if($this->params->get('show_manager_col')) : ?>
        	               		<td class="list-owner">
        	               			<small><?php echo $this->escape($item->author_name);?></small>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('show_mscount_col')) : ?>
        	               		<td class="list-milestones">
        		               		<a class="btn"><i class="icon-map-marker"></i> <?php echo (int) $item->milestones;?></a>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('show_mscount_col')) : ?>
        	               		<td class="list-tasks">
        		               		<a class="btn"><i class="icon-ok"></i> <?php echo (int) $item->tasks;?></a>
        	               		</td>
                            <?php endif; ?>

                            <?php if($this->params->get('show_sdate_col')) : ?>
    	               		    <td class="list-sdate">
        		               	    <?php if($item->start_date == $null_date) {
                                        echo JText::_('COM_PROJECTFORK_DATE_NOT_SET');
                                    }
                                    else {
                                        echo JHtml::_('date', $item->start_date, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC2'))));
                                    }
        		               		?>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('show_edate_col')) : ?>
    	               		    <td class="list-edate">
                                    <?php if($item->end_date == $null_date) {
                                        echo JText::_('COM_PROJECTFORK_DATE_NOT_SET');
                                    }
                                    else {
                                        echo JHtml::_('date', $item->end_date, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC2'))));
                                    }
        		               		?>
        	               		</td>
                            <?php endif; ?>
                            <?php if($this->params->get('show_access_col')) : ?>
    	               		    <td class="list-access">
        		               		<?php echo $this->escape($item->access_level);?>
        	               		</td>
                            <?php endif; ?>

    	               	</tr>
                    <?php
                    $k = 1 - $k;
                    endforeach;
                    ?>
                </tbody>
            </table>

            <?php if($this->pagination->get('pages.total') > 1) : ?>
                <div class="pagination">
                    <?php if ($this->params->def('show_pagination_results', 1)) : ?>
    				    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
    				<?php endif; ?>
    		        <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php endif; ?>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="limitstart" value=""/>
            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>