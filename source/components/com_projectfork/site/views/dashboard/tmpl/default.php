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

$nulldate = JFactory::getDbo()->getNullDate();

$details_in     = ($state->get('project.request') ? 'in ' : '');
$details_active = ($state->get('project.request') ? ' active' : '');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-dashboard">

    <?php if ($params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="cat-items">

        <form id="adminForm" name="adminForm" method="post" action="<?php echo JRoute::_(PFprojectsHelperRoute::getDashboardRoute($state->get('filter.project'))); ?>">
        
        	<?php if($state->get('filter.project')) : ?>
                <div class="btn-group pull-right">
    			    <a data-toggle="collapse" data-target="#project-details" class="btn<?php echo $details_active;?>">
                        <?php echo JText::_('COM_PROJECTFORK_DETAILS_LABEL'); ?> <span class="caret"></span>
                    </a>
    			</div>
            <?php endif; ?>

            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar;?>
                <?php echo JHtml::_('pfhtml.project.filter');?>
            </div>

            <?php if($item) echo $item->event->afterDisplayTitle; ?>

            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>

            <div class="clearfix"></div>

            <?php if ($item) echo $item->event->beforeDisplayContent; ?>

            <?php if($state->get('filter.project')) : ?>
                <div class="<?php echo $details_in;?>collapse" id="project-details">
                    <div class="well">
                        <div class="item-description">

                            <?php echo $item->text; ?>

                            <dl class="article-info dl-horizontal pull-right">
                        		<?php if($item->start_date != $nulldate): ?>
                        			<dt class="start-title">
                        				<?php echo JText::_('JGRID_HEADING_START_DATE'); ?>:
                        			</dt>
                        			<dd class="start-data">
                                        <?php echo JHtml::_('pfhtml.label.datetime', $item->start_date); ?>
                        			</dd>
                        		<?php endif; ?>
                        		<?php if($item->end_date != $nulldate): ?>
                        			<dt class="due-title">
                        				<?php echo JText::_('JGRID_HEADING_DEADLINE'); ?>:
                        			</dt>
                        			<dd class="due-data">
                                        <?php echo JHtml::_('pfhtml.label.datetime', $item->end_date); ?>
                        			</dd>
                        		<?php endif;?>
                        		<dt class="owner-title">
                        			<?php echo JText::_('JGRID_HEADING_CREATED_BY'); ?>:
                        		</dt>
                        		<dd class="owner-data">
                                     <?php echo JHtml::_('pfhtml.label.author', $item->author, $item->created); ?>
                        		</dd>
                                <?php if ($item->params->get('website')) : ?>
                                    <dt class="owner-title">
                            			<?php echo JText::_('COM_PROJECTFORK_FIELD_WEBSITE_LABEL'); ?>:
                            		</dt>
                            		<dd class="owner-data">
                                        <a href="<?php echo $item->params->get('website');?>" target="_blank">
                                            <?php echo JText::_('COM_PROJECTFORK_FIELD_WEBSITE_VISIT_LABEL');?>
                                        </a>
                            		</dd>
                                <?php endif; ?>
                                <?php if ($item->params->get('email')) : ?>
                                    <dt class="owner-title">
                            			<?php echo JText::_('COM_PROJECTFORK_FIELD_EMAIL_LABEL'); ?>:
                            		</dt>
                            		<dd class="owner-data">
                                        <a href="mailto:<?php echo $item->params->get('email');?>" target="_blank">
                                            <?php echo $item->params->get('email');?>
                                        </a>
                            		</dd>
                                <?php endif; ?>
                                <?php if ($item->params->get('phone')) : ?>
                                    <dt class="owner-title">
                            			<?php echo JText::_('COM_PROJECTFORK_FIELD_PHONE_LABEL'); ?>:
                            		</dt>
                            		<dd class="owner-data">
                                        <?php echo $item->params->get('phone');?>
                            		</dd>
                                <?php endif; ?>
                                <?php if (PFApplicationHelper::enabled('com_pfrepo') && count($item->attachments)) : ?>
                                    <dt class="owner-title">
                            			<?php echo JText::_('COM_PROJECTFORK_FIELDSET_ATTACHMENTS'); ?>:
                            		</dt>
                            		<dd class="owner-data">
                                         <?php echo JHtml::_('pfrepo.attachments', $item->attachments); ?>
                            		</dd>
                                <?php endif; ?>
                        	</dl>

                            <div class="clearfix"></div>

                            <hr />


                    	</div>
                    </div>
                </div>
                <div class="clearfix"></div>
            <?php endif; ?>
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>

        <!-- Begin Dashboard Modules -->
        <?php if(count(JModuleHelper::getModules('pf-dashboard-top'))) : ?>
        <div class="row-fluid">
        	<div class="span12">
        		<?php echo $modules->render('pf-dashboard-top', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <?php endif; ?>
        <?php if(count(JModuleHelper::getModules('pf-dashboard-left')) || count(JModuleHelper::getModules('pf-dashboard-right'))) : ?>
        <div class="row-fluid">
        	<div class="span6">
        		<?php echo $modules->render('pf-dashboard-left', array('style' => 'xhtml'), null); ?>
        	</div>
        	<div class="span6">
        		<?php echo $modules->render('pf-dashboard-right', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <?php endif; ?>
        <?php if(count(JModuleHelper::getModules('pf-dashboard-bottom'))) : ?>
        <div class="row-fluid">
        	<div class="span12">
        		<?php echo $modules->render('pf-dashboard-bottom', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <?php endif; ?>
        <!-- End Dashboard Modules -->

        <?php if ($item) echo $item->event->afterDisplayContent; ?>

	</div>
</div>