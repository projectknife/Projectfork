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

$doc = JFactory::getDocument();

// Add Highcharts JavaScript
$doc->addScript('components/com_projectfork/assets/js/jquery.min.js');
$doc->addScript('components/com_projectfork/assets/js/jquery.noconflict.js');
$doc->addScript('components/com_projectfork/assets/highcharts/highcharts.js');
$doc->addScript('components/com_projectfork/assets/highcharts/modules/exporting.js');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-dashboard">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>


    <div class="cat-items">

        <fieldset class="filters">
            <?php if($this->params->get('filter_fields')) : ?>
                <span class="filter-project">
                    <?php echo JHtml::_('projectfork.filterProject');?>
                </span>
            <?php endif; ?>
        </fieldset>
        
        <!-- Begin Highcharts -->
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

<!--hicharts-->
<script type="text/javascript">
var chart;
jQuery(document).ready(function() {
	chart = new Highcharts.Chart({
		chart: {
			renderTo: 'container',
			type: 'spline'
		},
		title: {
			text: 'Completed Tasks'
		},
		xAxis: {
			type: 'datetime',
			dateTimeLabelFormats: { // don't display the dummy year
				month: '%e. %b',
				year: '%b'
			}
		},
		yAxis: {
			title: {
				text: 'Tasks'
			},
			min: 0
		},
		tooltip: {
			formatter: function() {
					return '<b>'+ this.series.name +'</b><br/>'+
					Highcharts.dateFormat('%e. %b', this.x) +': '+ this.y +' k';
			}
		},
		
		series: [{
			name: 'Project One',
			// Define the data points. All series have a dummy year
			// of 1970/71 in order to be compared on the same x axis. Note
			// that in JavaScript, months start at 0 for January, 1 for February etc.
			data: [
				[Date.UTC(1970,  9, 27), 0   ],
				[Date.UTC(1970, 10, 10), 0.6 ],
				[Date.UTC(1970, 10, 18), 0.7 ],
				[Date.UTC(1970, 11,  2), 0.8 ],
				[Date.UTC(1970, 11,  9), 0.6 ],
				[Date.UTC(1970, 11, 16), 0.6 ],
				[Date.UTC(1970, 11, 28), 0.67],
				[Date.UTC(1971,  0,  1), 0.81],
				[Date.UTC(1971,  0,  8), 0.78],
				[Date.UTC(1971,  0, 12), 0.98],
				[Date.UTC(1971,  0, 27), 1.84],
				[Date.UTC(1971,  1, 10), 1.80],
				[Date.UTC(1971,  1, 18), 1.80],
				[Date.UTC(1971,  1, 24), 1.92],
				[Date.UTC(1971,  2,  4), 2.19],
				[Date.UTC(1971,  2, 11), 2.29],
				[Date.UTC(1971,  2, 15), 2.23],
				[Date.UTC(1971,  2, 25), 2.11],
				[Date.UTC(1971,  3,  2), 2.26],
				[Date.UTC(1971,  3,  6), 2.22],
				[Date.UTC(1971,  3, 13), 2.1 ],
				[Date.UTC(1971,  4,  3), 2.1 ],
				[Date.UTC(1971,  4, 26), 1.9 ],
				[Date.UTC(1971,  5,  9), 2.0],
				[Date.UTC(1971,  5, 12), 2.1]
			]
		}, {
			name: 'Project Two',
			data: [
				[Date.UTC(1970,  9, 18), 0   ],
				[Date.UTC(1970,  9, 26), 0.2 ],
				[Date.UTC(1970, 11,  1), 0.47],
				[Date.UTC(1970, 11, 11), 0.55],
				[Date.UTC(1970, 11, 25), 1.38],
				[Date.UTC(1971,  0,  8), 1.38],
				[Date.UTC(1971,  0, 15), 1.38],
				[Date.UTC(1971,  1,  1), 1.38],
				[Date.UTC(1971,  1,  8), 1.48],
				[Date.UTC(1971,  1, 21), 1.5 ],
				[Date.UTC(1971,  2, 12), 1.89],
				[Date.UTC(1971,  2, 25), 2.0 ],
				[Date.UTC(1971,  3,  4), 1.94],
				[Date.UTC(1971,  3,  9), 1.91],
				[Date.UTC(1971,  3, 13), 1.75],
				[Date.UTC(1971,  3, 19), 1.6 ],
				[Date.UTC(1971,  4, 25), 1.9 ],
				[Date.UTC(1971,  4, 31), 2.1],
				[Date.UTC(1971,  5,  7), 2.3]
			]
		}, {
			name: 'Project Three',
			data: [
				[Date.UTC(1970,  9,  9), 0   ],
				[Date.UTC(1970,  9, 14), 0.15],
				[Date.UTC(1970, 10, 28), 0.35],
				[Date.UTC(1970, 11, 12), 0.46],
				[Date.UTC(1971,  0,  1), 0.59],
				[Date.UTC(1971,  0, 24), 0.58],
				[Date.UTC(1971,  1,  1), 0.62],
				[Date.UTC(1971,  1,  7), 0.65],
				[Date.UTC(1971,  1, 23), 0.77],
				[Date.UTC(1971,  2,  8), 0.77],
				[Date.UTC(1971,  2, 14), 0.79],
				[Date.UTC(1971,  2, 24), 0.86],
				[Date.UTC(1971,  3,  4), 0.8 ],
				[Date.UTC(1971,  3, 18), 0.94],
				[Date.UTC(1971,  3, 24), 0.9 ],
				[Date.UTC(1971,  4, 16), 1.1],
				[Date.UTC(1971,  4, 21), 1.2]
			]
		}]
	});
});
</script>

<script type="text/javascript">
var chart;
jQuery(document).ready(function() {
	chart = new Highcharts.Chart({
		chart: {
			renderTo: 'container2',
			type: 'bar'
		},
		title: {
			text: 'Projects By The Numbers'
		},
		xAxis: {
			categories: ['Project One', 'Project Two', 'Project Three']
		},
		yAxis: {
			min: 0,
			title: {
				text: 'Project Items'
			}
		},
		legend: {
			backgroundColor: '#FFFFFF',
			reversed: true
		},
		tooltip: {
			formatter: function() {
				return ''+
					this.series.name +': '+ this.y +'';
			}
		},
		plotOptions: {
			series: {
				stacking: 'normal'
			}
		},
			series: [{
			name: 'Milestones',
			data: [2, 3, 2]
		}, {
			name: 'Task Lists',
			data: [5, 4, 7]
		}, {
			name: 'Tasks',
			data: [23, 45, 32]
		}]
	});
});
</script>

<script type="text/javascript">
var chart;
jQuery(document).ready(function() {
	chart = new Highcharts.Chart({
			chart: {
				renderTo: 'container3',
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false
			},
			title: {
				text: 'Tasks Completed By User'
			},
			tooltip: {
				formatter: function() {
					return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
				}
			},
			plotOptions: {
				pie: {
					allowPointSelect: true,
					cursor: 'pointer',
					dataLabels: {
						enabled: false
					},
					showInLegend: true
				}
			},
			series: [{
				type: 'pie',
				name: 'Browser share',
				data: [
					['Captain America',   45.0],
					['Iron Man',       26.8],
					{
						name: 'The Hulk',
						y: 12.8,
						sliced: true,
						selected: true
					},
					['Hawkeye',    8.5],
					['Black Widow',     6.2],
					['Nick Fury',   0.7]
				]
			}]
		});
});
</script>