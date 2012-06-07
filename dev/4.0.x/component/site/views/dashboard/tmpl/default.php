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
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-dashboard">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>


    <div class="cat-items">

        <form id="adminForm" name="adminForm" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>">

            <fieldset class="filters">
                <?php if($this->params->get('filter_fields')) : ?>
                    <span class="filter-project">
                        <?php echo JHtml::_('projectfork.filterProject');?>
                    </span>
                <?php endif; ?>
            </fieldset>

            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>

        </form>


        <!-- Begin Highcharts
        <div class="row-fluid">
        	<div class="span12">
        		<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
        	</div>
        </div>
        <div class="row-fluid">
        	<div class="span6">
        		<div id="container2" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
        	</div>
        	<div class="span6">
        		<div id="container3" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
        	</div>
        </div>
        <!-- End Highcharts -->

        <?php echo $this->modules->render('pf-dasboard-top', array('style' => 'rounded'), null); ?>

        <!--Project List Module Begin
        <table class="category project-list table table-striped">
           <thead>
               	<tr>
               		<th id="tableOrdering" class="list-title">
               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.title','asc','');">Current Projects</a></th>
               		<th id="tableOrdering1" class="list-milestones">
               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.milestones','asc','');">Milestones</a></th>
               		<th id="tableOrdering2" class="list-tasks">
               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.tasks','asc','');">Tasks</a></th>
               	</tr>
           </thead>
           <tbody>
        		<tr class="cat-list-row0">
               		<td class="list-title">
               		<a href="#">
               		Project Number One</a>
               		</td>
               		<td class="list-milestones">
               		<span title=""><a href="#">5</a></span>
               		</td>

               		<td class="list-tasks">
               		<span title=""><a href="#">25</a></span>
               		</td>
               	</tr>
               <tr class="cat-list-row1">
               		<td class="list-title">
               		<a href="#">
               		Project Number Two</a>
               		</td>
               		<td class="list-milestones">
               		<span title=""><a href="#">4</a></span>
               		</td>

               		<td class="list-tasks">
               		<span title=""><a href="#">12</a></span>
               		</td>
               	</tr>
               	<tr class="cat-list-row0">
               			<td class="list-title">
               			<a href="#">
               			Project Number Three</a>
               			</td>
               			<td class="list-milestones">
               			<span title=""><a href="#">7</a></span>
               			</td>

               			<td class="list-tasks">
               			<span title=""><a href="#">42</a></span>
               			</td>
               		</tr>
            </tbody>
        </table>
        -->
        <!--Project List Module End-->

        <!--Mini Calendar Module Begin
        <table class="category mini-calendar table table-striped table-bordered table-condensed">
        	<thead>
        		<tr>
        			<th><span class="calendar-title">Sun</span></th>
        			<th><span class="calendar-title">Mon</span></th>
        			<th><span class="calendar-title">Tue</span></th>
        			<th><span class="calendar-title">Wed</span></th>
        			<th><span class="calendar-title">Thu</span></th>
        			<th><span class="calendar-title">Fri</span></th>
        			<th><span class="calendar-title">Sat</span></th>
        		</tr>
        	</thead>
        	<tbody>
        		<tr class="calendar-row0">
        			<td class="calendar-weekend">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">18</span>
        					</div>
        					<div class="calendar-events">

        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekday">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">19</span>
        					</div>
        					<div class="calendar-events">

        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekday">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">20</span>
        					</div>
        					<div class="calendar-events">
        						<div class="calendar-item">
        							<span class="overdue">
        								<a href="#">Deliver Design</a>
        							</span>
        						</div>
        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekday">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">21</span>
        					</div>
        					<div class="calendar-events">

        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekday">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">22</span>
        					</div>
        					<div class="calendar-events">

        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekday today">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">23</span>
        					</div>
        					<div class="calendar-events">
        						<div class="calendar-item">
        							<span class="">
        								<a href="#">Chop Up HTML/CSS</a>
        							</span>
        						</div>
        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekend">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">24</span>
        					</div>
        					<div class="calendar-events">
        						<div class="calendar-item">
        							<span class="">
        								<a href="#">Develop Template</a>
        							</span>
        						</div>
        					</div>
        				</div>
        			</td>
        		</tr>
        		<tr class="calendar-row0">
        			<td class="calendar-weekend">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">25</span>
        					</div>
        					<div class="calendar-events">
        						<div class="calendar-item">
        							<span class="">
        								<a href="#">Program Tasks</a>
        							</span>
        						</div>
        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekday">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">26</span>
        					</div>
        					<div class="calendar-events">

        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekday">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">27</span>
        					</div>
        					<div class="calendar-events">

        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekday">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">28</span>
        					</div>
        					<div class="calendar-events">
        						<div class="calendar-item">
        							<span class="">
        								<a href="#">Program User Groups</a>
        							</span>
        						</div>
        						<div class="calendar-item">
        							<span class="">
        								<a href="#">Another Task For Today</a>
        							</span>
        						</div>
        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekday">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">29</span>
        					</div>
        					<div class="calendar-events">

        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekday">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">30</span>
        					</div>
        					<div class="calendar-events">

        					</div>
        				</div>
        			</td>
        			<td class="calendar-weekend">
        				<div class="calendar-day">
        					<div class="calendar-date">
        						<span class="date small">1</span>
        					</div>
        					<div class="calendar-events">

        					</div>
        				</div>
        			</td>
        		</tr>
        	</tbody>
        </table>
        -->
        <!--Mini Calendar Module End-->

        <!--Due Date Module Begin
        <table class="category due-list table table-striped">
           <thead>
               	<tr>
               		<th id="tableOrdering" class="list-title">
               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.title','asc','');">Due</a></th>
               		<th id="tableOrdering1" class="list-milestones">
               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.milestones','asc','');">Milestone</a></th>
               		<th id="tableOrdering2" class="list-owner">
               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.tasks','asc','');">Owner</a></th>
               	</tr>
           </thead>
           <tbody>
        		<tr class="cat-list-row0">
               		<td class="list-title">
               		<span class="due-date overdue">
               		3 days overdue</span>
               		</td>
               		<td class="list-milestones">
               		<span title=""><a href="#">Deliver Design</a></span>
               		</td>

               		<td class="list-owner">
               		<span title=""><a href="#">Kyle Ledbetter</a></span>
               		</td>
               	</tr>
               <tr class="cat-list-row1">
               		<td class="list-title">
               		<span class="due-date">
               		Today</span>
               		</td>
               		<td class="list-milestones">
               		<span title=""><a href="#">Chop Up HTML/CSS</a></span>
               		</td>

               		<td class="list-owner">
               		<span title=""><a href="#">Kyle Ledbetter</a></span>
               		</td>
               	</tr>
               	<tr class="cat-list-row0">
           			<td class="list-title">
           			<span class="due-date">
           			Tomorrow</span>
           			</td>
           			<td class="list-milestones">
           			<span title=""><a href="#">Develop Template</a></span>
           			</td>

           			<td class="list-owner">
           			<span title=""><a href="#">Kyle Ledbetter</a></span>
           			</td>
           		</tr>
           		<tr class="cat-list-row1">
           			<td class="list-title">
           			<span class="due-date">
           			2 days</span>
           			</td>
           			<td class="list-milestones">
           			<span title=""><a href="#">Program Tasks</a></span>
           			</td>

           			<td class="list-owner">
           			<span title=""><a href="#">Tobias Kuhn</a></span>
           			</td>
           		</tr>
           		<tr class="cat-list-row0">
           			<td class="list-title">
           			<span class="due-date">
           			Next week</span>
           			</td>
           			<td class="list-milestones">
           			<span title=""><a href="#">Program User Groups</a></span>
           			</td>

           			<td class="list-owner">
           			<span title=""><a href="#">Tobias Kuhn</a></span>
           			</td>
           		</tr>
            </tbody>
        </table>
        -->
        <!--Due Date Module End-->

        <!--Activity Stream Module Begin
        <table class="category activity-list table table-striped">
           <thead>
               	<tr>
               		<th class="list-title" colspan="5">
               		Recent Activity</th>
               	</tr>
           </thead>
           <tbody>
           		<tr class="activity-date cat-list-row1">
           			<td class="list-date" colspan="5">
           				<span class="date-item">Today</span>
           			</td>
           		</tr>
        		<tr class="cat-list-row0">
               		<td class="activity-type">
	           		<span class="label label-warning label-file">File</span>
               		</td>
               		<td class="list-item">
               			<span title=""><a href="#">image_name.jpg</a></span>
               		</td>
               		<td class="list-action">
               			<span class="">uploaded by</span>
               		</td>
               		<td class="list-owner">
               			<span class="">Kyle L.</span>
               		</td>
               		<td class="list-time">
               			<span class="">11:55AM</span>
               		</td>
               	</tr>
               	<tr class="cat-list-row1">
           			<td class="activity-type">
           	   		<span class="label label-success label-milestone">Milestone</span>
           			</td>
           			<td class="list-item">
           				<span title=""><a href="#">Finish Markup</a></span>
           			</td>
           			<td class="list-action">
           				<span class="">Assigned to</span>
           			</td>
           			<td class="list-owner">
           				<span class="">Kyle L.</span>
           			</td>
           			<td class="list-time">
           				<span class="">10:25AM</span>
           			</td>
           		</tr>
           		<tr class="cat-list-row0">
           			<td class="activity-type">
           				<span class="label label-info label-task">Task</span>
           			</td>
           			<td class="list-item">
           				<span title=""><a href="#">Dashboard Markup</a></span>
           			</td>
           			<td class="list-action">
           				<span class="">Assigned to</span>
           			</td>
           			<td class="list-owner">
           				<span class="">Kyle L.</span>
           			</td>
           			<td class="list-time">
           				<span class="">9:22AM</span>
           			</td>
           		</tr>
           		<tr class="activity-date cat-list-row1">
           				<td class="list-date" colspan="5">
           					<span class="date-item">Yesterday</span>
           				</td>
           			</tr>
           		<tr class="cat-list-row0">
       				<td class="activity-type">
       		   		<span class="label label-info label-task-list">Task List</span>
       				</td>
       				<td class="list-item">
       					<span title=""><a href="#">Markup Tasks</a></span>
       				</td>
       				<td class="list-action">
       					<span class="">created by</span>
       				</td>
       				<td class="list-owner">
       					<span class="">Kyle L.</span>
       				</td>
       				<td class="list-time">
       					<span class="">8:52PM</span>
       				</td>
       			</tr>
       			<tr class="cat-list-row1">
       				<td class="activity-type">
       		  		<span class="label label-comment">Comment</span>
       				</td>
       				<td class="list-item">
       					<span title=""><a href="#">Re: Projects Markup</a></span>
       				</td>
       				<td class="list-action">
       					<span class="">Posted by</span>
       				</td>
       				<td class="list-owner">
       					<span class="">Tobias K.</span>
       				</td>
       				<td class="list-time">
       					<span class="">7:02PM</span>
       				</td>
       			</tr>
       			<tr class="cat-list-row0">
       				<td class="activity-type">
       					<span class="label label-important label-project">Project</span>
       				</td>
       				<td class="list-item">
       					<span title=""><a href="#">Projectfork 4.0</a></span>
       				</td>
       				<td class="list-action">
       					<span class="">Created by</span>
       				</td>
       				<td class="list-owner">
       					<span class="">Tobias K.</span>
       				</td>
       				<td class="list-time">
       					<span class="">9:29AM</span>
       				</td>
       			</tr>
       			<tr class="activity-date cat-list-row1">
       					<td class="list-date" colspan="5">
       						<span class="date-item">Weds Sep 21, 2011</span>
       					</td>
       				</tr>
       			<tr class="cat-list-row0">
       				<td class="activity-type">
       					<span class="label label-comment">Comment</span>
       				</td>
       				<td class="list-item">
       					<span title=""><a href="#">Re: Projects Markup</a></span>
       				</td>
       				<td class="list-action">
       					<span class="">Posted by</span>
       				</td>
       				<td class="list-owner">
       					<span class="">Tobias K.</span>
       				</td>
       				<td class="list-time">
       					<span class="">7:02PM</span>
       				</td>
       			</tr>
       			<tr class="cat-list-row1">
       				<td class="activity-type">
       					<span class="label label-comment">Comment</span>
       				</td>
       				<td class="list-item">
       					<span title=""><a href="#">Re: file_name.jpg</a></span>
       				</td>
       				<td class="list-action">
       					<span class="">Posted by</span>
       				</td>
       				<td class="list-owner">
       					<span class="">Tobias K.</span>
       				</td>
       				<td class="list-time">
       					<span class="">7:00PM</span>
       				</td>
       			</tr>
       			<tr class="cat-list-row0">
   					<td class="activity-type">
   			   		<span class="label label-file">File</span>
   					</td>
   					<td class="list-item">
   						<span title=""><a href="#">file_name.jpg</a></span>
   					</td>
   					<td class="list-action">
   						<span class="">uploaded by</span>
   					</td>
   					<td class="list-owner">
   						<span class="">Kyle L.</span>
   					</td>
   					<td class="list-time">
   						<span class="">11:55AM</span>
   					</td>
   				</tr>
            </tbody>
        </table>
        -->
        <!--Activity Stream Module End-->
	</div>
</div>