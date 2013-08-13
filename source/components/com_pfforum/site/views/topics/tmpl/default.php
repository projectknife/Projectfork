<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfforum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


JHtml::_('pfhtml.script.listform');

$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');

$filter_in  = ($this->state->get('filter.isset') ? 'in ' : '');
$repo_enabled  = PFApplicationHelper::enabled('com_pfrepo');

$doc   = JFactory::getDocument();
$style = '.row-topics .well,.row-topics .btn-toolbar {'
        . 'margin-bottom: 0;'
        . '}'
        . '.list-comments img,.collapse-comments img {'
        . 'margin-right: 10px;'
        . '}'
        . '.img-avatar {'
        . 'max-height: 50px;'
        . 'max-width: 50px;'
        . 'margin-right: 10px;'
        . '}'
        . '.well-item {'
        . 'margin-left: 60px;'
        . '}'
        . '.collapse-comments blockquote {'
        . 'margin-left: 50px;'
        . '}'
        . '.collapse-comments .btn-toolbar {'
        . 'margin: 0 0 0 50px;'
        . '}';
$doc->addStyleDeclaration( $style );
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-topics">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="cat-items">

        <form name="adminForm" id="adminForm" action="<?php echo JRoute::_(PFforumHelperRoute::getTopicsRoute()); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('pfhtml.project.filter');?>
                </div>
            </div>

            <div class="<?php echo $filter_in;?>collapse" id="filters">
                <div class="btn-toolbar clearfix">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>

                    <?php if (is_numeric($this->state->get('filter.project'))) : ?>
                        <div class="filter-author btn-group pull-left">
                            <select id="filter_author" name="filter_author" class="inputbox input-small" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_AUTHOR');?></option>
                                <?php echo JHtml::_('select.options', $this->authors, 'value', 'text', $this->state->get('filter.author'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->access->get('core.edit.state') || $this->access->get('core.edit')) : ?>
                        <div class="filter-published btn-group pull-left">
                            <select name="filter_published" class="inputbox input-small" onchange="this.form.submit()">
                                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                                <?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="btn-group filter-order pull-left">
                        <select name="filter_order" class="inputbox input-small" onchange="this.form.submit()">
                            <?php echo JHtml::_('select.options', $this->sort_options, 'value', 'text', $list_order, true);?>
                        </select>
                    </div>
                    <div class="btn-group folder-order-dir pull-left">
                        <select name="filter_order_Dir" class="inputbox input-small" onchange="this.form.submit()">
                            <?php echo JHtml::_('select.options', $this->order_options, 'value', 'text', $list_dir, true);?>
                        </select>
                    </div>
                    <?php if ($this->state->get('filter.project')) : ?>
                        <div class="clearfix clr"></div>
                        <hr />
                        <div class="filter-labels">
                            <?php echo JHtml::_('pfhtml.label.filter', 'com_pfforum.topic', $this->state->get('filter.project'), $this->state->get('filter.labels'));?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row-striped row-discussions row-topics">
            <?php
            $k = 0;
            foreach($this->items AS $i => $item) :
                $access = PFforumHelper::getActions($item->id);

                $can_edit     = $access->get('core.edit');
                $can_change   = $access->get('core.edit.state');
                $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);

                // Prepare the watch button
                $watch = '';

                if ($uid) {
                    $options = array('div-class' => '', 'a-class' => 'btn-mini');
                    $watch = JHtml::_('pfhtml.button.watch', 'topics', $i, $item->watching, $options);
                }
            ?>
            	<!-- Begin Topic -->
    			<div class="row-fluid row-<?php echo $k;?>">
    				<div class="span12">
                        <a href="<?php echo JRoute::_(PFforumHelperRoute::getTopicRoute($item->slug, $item->project_slug));?>">
                        <img title="<?php echo $this->escape($item->author_name);?>"
                             src="<?php echo JHtml::_('projectfork.avatar.path', $item->created_by);?>"
                             class="img-circle img-avatar pull-left hasTooltip"
                             rel="tooltip"
                        />
                        </a>
    					<div class="well well-small well-item">
    						<span class="small muted pull-right"><?php echo JHtml::_('date', $item->created, $this->params->get('date_format', JText::_('DATE_FORMAT_LC2'))); ?></span>
    						<?php if ($can_change || $uid) : ?>
		                        <label for="cb<?php echo $i; ?>" class="checkbox pull-left">
		                            <?php echo JHtml::_('pf.html.id', $i, $item->id); ?>
		                        </label>
		                    <?php endif; ?>
    						<h4>
	    						<a href="<?php echo JRoute::_(PFforumHelperRoute::getTopicRoute($item->slug, $item->project_slug));?>">
		                            <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
		                            <?php echo $this->escape($item->title);?>
		                        </a>
    						</h4>
    						<div class="well-description">
    							<?php echo JHtml::_('pf.html.truncate', $item->description, 300); ?>
    						</div>
    					</div>
    					<div class="btn-toolbar margin-none">
                			<?php if ($can_edit || $can_edit_own) : ?>
    	    	    			<div class="btn-group">
    	    	    			    <a class="btn btn-mini" href="<?php echo JRoute::_('index.php?option=com_pfforum&task=topicform.edit&id=' . $item->id);?>">
    	    	    			        <span aria-hidden="true" class="icon-pencil"></span> <?php echo JText::_('COM_PROJECTFORK_ACTION_EDIT'); ?>
    	    	    			    </a>
    	    	    			</div>
	    	    			<?php endif; ?>
    	    				<div class="btn-group">
    	    					<a class="btn btn-mini" href="<?php echo JRoute::_(PFforumHelperRoute::getTopicRoute($item->slug, $item->project_slug));?>">
    	    			       	    <span aria-hidden="true" class="icon-comment"></span> <?php echo JText::plural('COM_PROJECTFORK_N_REPLIES', (int) $item->replies); ?>
    	    			        </a>
    	    			    </div>

                			<?php echo $watch; ?>
                		</div>
    				</div>
    			</div>
    			<!-- End Topic -->
            <?php
            $k = 1 - $k;
            endforeach;
            ?>
            </div>
            <br />
			<?php if ($this->pagination->get('pages.total') > 1) : ?>
			    <div class="pagination center">
			        <?php echo $this->pagination->getPagesLinks(); ?>
			    </div>
			    <p class="counter center"><?php echo $this->pagination->getPagesCounter(); ?></p>
			<?php endif; ?>
            <div class="filters center">
                <span class="display-limit">
                    <?php echo $this->pagination->getLimitBox(); ?>
                </span>
            </div>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
