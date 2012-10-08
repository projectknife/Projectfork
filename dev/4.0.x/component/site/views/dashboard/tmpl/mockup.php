<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
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

// Create shortcuts
$item    = &$this->item;
$params  = &$this->params;
$state   = &$this->state;
$modules = &$this->modules;
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-dashboard">

    <?php if ($params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($params->get('page_heading')); ?> <small>mockup</small></h1>
    <?php endif; ?>

    <div class="cat-items">

        <form id="adminForm" name="adminForm" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>">

            <fieldset class="filters btn-toolbar btn-toolbar-top">
                    <div class="filter-project btn-group">
                        <?php echo JHtml::_('projectfork.filterProject');?>
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

            <?php if($item) echo $item->event->beforeDisplayContent;?>

            <?php if($state->get('filter.project')) : ?>
                <div class="collapse" id="project-details">
                    <div class="well btn-toolbar">
                        <div class="item-description">

                            <?php echo $item->text; ?>

                            <dl class="article-info dl-horizontal pull-right">
                        		<?php if($item->start_date != JFactory::getDBO()->getNullDate()): ?>
                        			<dt class="start-title">
                        				<?php echo JText::_('JGRID_HEADING_START_DATE');?>:
                        			</dt>
                        			<dd class="start-data">
                        				<?php echo JHtml::_('date', $item->start_date, $this->escape( $params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        			</dd>
                        		<?php endif; ?>
                        		<?php if($item->end_date != JFactory::getDBO()->getNullDate()): ?>
                        			<dt class="due-title">
                        				<?php echo JText::_('JGRID_HEADING_DEADLINE');?>:
                        			</dt>
                        			<dd class="due-data">
                        				<?php echo JHtml::_('date', $item->end_date, $this->escape( $params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        			</dd>
                        		<?php endif;?>
                        		<dt class="owner-title">
                        			<?php echo JText::_('JGRID_HEADING_CREATED_BY');?>:
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
        	<div class="span8">
        		<?php echo $modules->render('pf-dashboard-left', array('style' => 'xhtml'), null); ?>
        		<!-- begin placeholder activity stream -->
        		<div class="btn-toolbar pull-right">
					<div class="btn-group" data-toggle="buttons-radio">
						<button type="button" class="btn btn-mini active">All</button>
						<button type="button" class="btn btn-mini"><i class="icon-briefcase hasTip" title="Projects"></i></button>
						<button type="button" class="btn btn-mini"><i class="icon-flag hasTip" title="Milestones"></i></button>
						<button type="button" class="btn btn-mini"><i class="icon-checkbox hasTip" title="Tasks"></i></button>
						<button type="button" class="btn btn-mini"><i class="icon-clock hasTip" title="Time"></i></button>
						<button type="button" class="btn btn-mini"><i class="icon-comments-2 hasTip" title="Discussions"></i></button>
						<button type="button" class="btn btn-mini"><i class="icon-flag-2 hasTip" title="Files"></i></button>
					</div>
        		</div>
        		<h3>Activity Stream</h3>
        		<div class="clearfix"></div>
        		<div class="row-striped row-condensed row-activity">
        			<!-- begin item -->
        			<div class="row-fluid">
        				<a href="#"><img class="thumbnail pull-left" src="http://placehold.it/32x32/624287/FFF/&amp;text=Avatar" alt=""></a>
        				<div class="offset1">
	        				<span class="label label-info thumbnail pull-right"><i class="icon-comment hasTip" title="Comment"></i></span>
	        				<div class="activity"><strong class="activity-user"><a href="#">Firstname Lastname</a></strong> commented on a task <a href="#">task name</a></div>
	        				<div class="small">Truncated text from the posted comment...</div>
	        				<small class="small muted">about an hour ago</small>
        				</div>
        			</div>
        			<!-- end item -->
        			<!-- begin item -->
        			<div class="row-fluid">
        				<a href="#"><img class="thumbnail pull-left" src="http://placehold.it/32x32/624287/FFF/&amp;text=Avatar" alt=""></a>
        				<div class="offset1">
        					<span class="label label-warning thumbnail pull-right"><i class="icon-flag-2 hasTip" title="File"></i></span>
	        				<div class="activity"><strong class="activity-user"><a href="#">Firstname Lastname</a></strong> uploaded a file <a href="#">filename.doc</a></div>
	        				<div class="small">Truncated text from the uploaded file description...</div>
	        				<small class="small muted">about 4 hours ago</small>
        				</div>
        			</div>
        			<!-- end item -->
        			<!-- begin item -->
        			<div class="row-fluid">
        				<a href="#"><img class="thumbnail pull-left" src="http://placehold.it/32x32/624287/FFF/&amp;text=Avatar" alt=""></a>
        				<div class="offset1">
	        				<span class="label label-warning thumbnail pull-right"><i class="icon-flag-2 hasTip" title="File"></i></span>
	        				<div class="activity"><strong class="activity-user"><a href="#">Firstname Lastname</a></strong> uploaded a file <a href="#">filename.jpg</a></div>
	        				<div class="small"><img class="thumbnail" src="http://placehold.it/100x100/624287/FFF/&amp;text=JPG" alt=""></div>
	        				<small class="small muted">about 4 hours ago</small>
        				</div>
        			</div>
        			<!-- end item -->
        			<!-- begin item -->
        			<div class="row-fluid">
        				
        				<a href="#"><img class="thumbnail pull-left" src="http://placehold.it/32x32/624287/FFF/&amp;text=Avatar" alt=""></a>
        				<div class="offset1">
        					<span class="label label-important thumbnail pull-right"><i class="icon-briefcase hasTip" title="Project"></i></span>
	        				<div class="activity"><strong class="activity-user"><a href="#">Firstname Lastname</a></strong> add a project <a href="#">Project Name</a></div>
	        				<div class="small"><img class="thumbnail" src="http://placehold.it/150x40/624287/FFF/&amp;text=LOGO" alt=""></div>
	        				<div class="small">Truncated text from the project description...</div>
	        				<small class="small muted">about 4 hours ago</small>
        				</div>
        			</div>
        			<!-- end item -->
        			<!-- begin item -->
        			<div class="row-fluid">
        				
        				<a href="#"><img class="thumbnail pull-left" src="http://placehold.it/32x32/624287/FFF/&amp;text=Avatar" alt=""></a>
        				<div class="offset1">
        					<span class="label label-success thumbnail pull-right"><i class="icon-checkbox hasTip" title="Task"></i></span>
	        				<div class="activity"><strong class="activity-user"><a href="#">Firstname Lastname</a></strong> completed a task <a href="#">Task Name</a></div>
	        				<div class="small">Truncated text from the task description...</div>
	        				<small class="small muted">1 day ago</small>
        				</div>
        			</div>
        			<!-- end item -->
        			<!-- begin item -->
        			<div class="row-fluid">
        				
        				<a href="#"><img class="thumbnail pull-left" src="http://placehold.it/32x32/624287/FFF/&amp;text=Avatar" alt=""></a>
        				<div class="offset1">
        					<span class="label label-inverse thumbnail pull-right"><i class="icon-flag hasTip" title="Milestone"></i></span>
	        				<div class="activity"><strong class="activity-user"><a href="#">Firstname Lastname</a></strong> edited a milestone <a href="#">Project Name</a></div>
	        				<div class="small">Truncated text from the milestone description...</div>
	        				<small class="small muted">2 days ago</small>
        				</div>
        			</div>
        			<!-- end item -->
        			<!-- begin item -->
        			<div class="row-fluid">
        				
        				<a href="#"><img class="thumbnail pull-left" src="http://placehold.it/32x32/624287/FFF/&amp;text=Avatar" alt=""></a>
        				<div class="offset1">
        					<span class="label label-info thumbnail pull-right"><i class="icon-clock hasTip" title="Timesheet"></i></span>
	        				<div class="activity"><strong class="activity-user"><a href="#">Firstname Lastname</a></strong> logged 3 hours for <a href="#">Task Name</a></div>
	        				<div class="small">Truncated text from the time description...</div>
	        				<small class="small muted">3 days ago</small>
        				</div>
        			</div>
        			<!-- end item -->
        			<!-- begin item -->
        			<div class="row-fluid">
        				<a href="#"><img class="thumbnail pull-left" src="http://placehold.it/32x32/624287/FFF/&amp;text=Avatar" alt=""></a>
        				<div class="offset1">
	        				<span class="label thumbnail pull-right"><i class="icon-user hasTip" title="Avatar"></i></span>
	        				<div class="activity"><strong class="activity-user"><a href="#">Firstname Lastname</a></strong> uploaded a new avatar</div>
	        				<div class="small"><img class="thumbnail" src="http://placehold.it/100x100/624287/FFF/&amp;text=AVATAR" alt=""></div>
	        				<small class="small muted">5 days ago</small>
        				</div>
        			</div>
        			<!-- end item -->
        			<!-- begin item -->
        			<div class="row-fluid">
        				<a href="#"><img class="thumbnail pull-left" src="http://placehold.it/32x32/624287/FFF/&amp;text=Avatar" alt=""></a>
        				<div class="offset1">
	        				<span class="label label-warning thumbnail pull-right"><i class="icon-comments-2 hasTip" title="Discussion"></i></span>
	        				<div class="activity"><strong class="activity-user"><a href="#">Firstname Lastname</a></strong> posted a new discussion <a href="#">discussion name</a></div>
	        				<div class="small">Truncated text from the topic description...</div>
	        				<small class="small muted">1 week ago</small>
        				</div>
        			</div>
        			<!-- end item -->
        			<!-- begin item -->
        			<div class="row-fluid">
        				<a href="#"><img class="thumbnail pull-left" src="http://placehold.it/32x32/624287/FFF/&amp;text=Avatar" alt=""></a>
        				<div class="offset1">
	        				<span class="label label-success thumbnail pull-right"><i class="icon-list-view hasTip" title="Task List"></i></span>
	        				<div class="activity"><strong class="activity-user"><a href="#">Firstname Lastname</a></strong> added a new task list <a href="#">discussion name</a></div>
	        				<div class="small">Truncated text from the task list description...</div>
	        				<small class="small muted">2 weeks ago</small>
        				</div>
        			</div>
        			<!-- end item -->
        		</div>
        		<!-- end placeholder activity stream -->
        	</div>
        	<div class="span4">
        		<!-- begin placeholder tasks module -->
        		<h4 class="text-error">Overdue</h4>
        		<ul class="list-striped list-condensed">
        			<li><small class="small muted pull-right">2 weeks ago</small> <span class="task-name"><a href="#">Project Name</a></span></li>
        			<li><small class="small muted pull-right">1 week ago</small> <span class="task-name"><a href="#">Task Name</a></span></li>
        			<li><small class="small muted pull-right">4 days ago</small> <span class="task-name"><a href="#">Milestone Name</a></span></li>
        			<li><small class="small muted pull-right">Yesterday</small> <span class="task-name"><a href="#">Task Name</a></span></li>
        		</ul>
        		<h4 class="text-warning">Today</h4>
        		<ul class="list-striped list-condensed">
        			<li><small class="small muted pull-right">09:30 AM</small> <span class="task-name"><a href="#">Task Name</a></span></li>
        			<li><small class="small muted pull-right">11:00 AM</small> <span class="task-name"><a href="#">Project Name</a></span></li>
        			<li><small class="small muted pull-right">04:45 PM</small> <span class="task-name"><a href="#">Task Name</a></span></li>
        		</ul>
        		<h4 class="text-success">Upcoming</h4>
        		<ul class="list-striped list-condensed">
        			<li><small class="small muted pull-right">Tomorrow</small> <span class="task-name"><a href="#">Milestone Name</a></span></li>
        			<li><small class="small muted pull-right">Thursday</small> <span class="task-name"><a href="#">Task Name</a></span></li>
        			<li><small class="small muted pull-right">Friday</small> <span class="task-name"><a href="#">Task Name</a></span></li>
        			<li><small class="small muted pull-right">Next Week</small> <span class="task-name"><a href="#">Project Name</a></span></li>
        		</ul>
        		<!-- end placeholder tasks module -->
        		<?php echo $modules->render('pf-dashboard-right', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <div class="row-fluid">
        	<div class="span12">
        		<?php echo $modules->render('pf-dashboard-bottom', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <!-- End Dashboard Modules -->

        <?php if($item) echo $item->event->afterDisplayContent;?>

	</div>
</div>