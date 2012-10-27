<?php
/**
 * @package      Projectfork
 * @subpackage   Dashboard
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Create shortcuts
$item    = &$this->item;
$params  = &$this->params;
$state   = &$this->state;
$modules = &$this->modules;
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-dashboard">

    <?php if ($params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="cat-items">

        <form id="adminForm" name="adminForm" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>">

            <fieldset class="filters btn-toolbar btn-toolbar-top">
                    <div class="filter-project btn-group">
                        <?php echo JHtml::_('pfhtml.project.filter');?>
                        <?php if($item) echo $item->event->afterDisplayTitle; ?>
                    </div>
            </fieldset>

            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>

            <?php if($state->get('filter.project')) : ?>
                <div class="btn-group pull-right">
    			    <a data-toggle="collapse" data-target="#project-details" class="btn"><?php echo JText::_('COM_PROJECTFORK_DETAILS_LABEL'); ?> <span class="caret"></span></a>
    			</div>
            <?php endif; ?>

            <div class="clearfix"></div>

            <?php if ($item) echo $item->event->beforeDisplayContent; ?>

            <?php if($state->get('filter.project')) : ?>
                <div class="collapse" id="project-details">
                    <div class="well btn-toolbar">
                        <div class="item-description">

                            <?php echo $item->text; ?>

                            <dl class="article-info dl-horizontal pull-right">
                        		<?php if($item->start_date != JFactory::getDBO()->getNullDate()): ?>
                        			<dt class="start-title">
                        				<?php echo JText::_('JGRID_HEADING_START_DATE'); ?>:
                        			</dt>
                        			<dd class="start-data">
                        				<?php echo JHtml::_('date', $item->start_date, $this->escape( $params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        			</dd>
                        		<?php endif; ?>
                        		<?php if($item->end_date != JFactory::getDBO()->getNullDate()): ?>
                        			<dt class="due-title">
                        				<?php echo JText::_('JGRID_HEADING_DEADLINE'); ?>:
                        			</dt>
                        			<dd class="due-data">
                        				<?php echo JHtml::_('date', $item->end_date, $this->escape( $params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        			</dd>
                        		<?php endif;?>
                        		<dt class="owner-title">
                        			<?php echo JText::_('JGRID_HEADING_CREATED_BY'); ?>:
                        		</dt>
                        		<dd class="owner-data">
                        			 <?php echo $this->escape($item->author);?>
                        		</dd>
                        	</dl>

                            <div class="clearfix"></div>

                    	</div>
                    </div>
                </div>
                <div class="clearfix"></div>
            <?php endif; ?>
        </form>

        <!-- Begin Dashboard Modules -->
        <div class="row-fluid">
        	<div class="span12">
        		<?php echo $modules->render('pf-dashboard-top', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <div class="row-fluid">
        	<div class="span6">
        		<?php echo $modules->render('pf-dashboard-left', array('style' => 'xhtml'), null); ?>
        	</div>
        	<div class="span6">
        		<?php echo $modules->render('pf-dashboard-right', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <div class="row-fluid">
        	<div class="span12">
        		<?php echo $modules->render('pf-dashboard-bottom', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <!-- End Dashboard Modules -->

        <?php if ($item) echo $item->event->afterDisplayContent; ?>

	</div>
</div>