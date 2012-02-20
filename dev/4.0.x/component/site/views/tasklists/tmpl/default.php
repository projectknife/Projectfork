<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
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
?>
<div id="projectfork" class="category-list view-tasklists">
    <div class="cat-items">
    	<div class="page-header">
	        <h2>Task Lists <input type="button" class="button btn btn-info" value="New Task List" /></h2>
        </div>
        <form name="adminForm" id="adminForm" action="index.php">
            
            <fieldset class="filters">
            	<span class="display-bulk-actions">
            			<select onchange="this.form.submit()" size="1" class="inputbox" name="bulk" id="bulk">
            			<option selected="selected" value="">Bulk Actions</option>
            			<option value="0">Copy</option>
            			<option value="1">Delete</option>
            		</select>
            	</span>
				<span class="display-owner">
						<select onchange="this.form.submit()" size="1" class="inputbox" name="owner" id="owner">
						<option selected="selected" value="">Select Owner</option>
						<option value="0">All</option>
					</select>
				</span>
				<span class="display-status">
						<select onchange="this.form.submit()" size="1" class="inputbox" name="status" id="status">
						<option selected="selected" value="">Select Status</option>
						<option value="0">All</option>
					</select>
				</span>
				<span class="display-limit">
						<select onchange="this.form.submit()" size="1" class="inputbox" name="limit" id="limit">
						<option value="5">5</option>
						<option selected="selected" value="10">10</option>
						<option value="15">15</option>
						<option value="20">20</option>
						<option value="25">25</option>
						<option value="30">30</option>
						<option value="50">50</option>
						<option value="100">100</option>
						<option value="0">All</option>
					</select>
				</span>

				<input type="hidden" value="" name="filter_order">
				<input type="hidden" value="" name="filter_order_Dir">
				<input type="hidden" value="" name="limitstart">
			</fieldset>
            
            <div class="accordion" id="accordion2">
                <div class="accordion-group">
                  <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
                      Task List One
                    </a>
                  </div>
                  <div id="collapseOne" class="accordion-body in collapse" style="height: auto; ">
                    <div class="accordion-inner">
                      <table class="category table table-striped">
                      	<tbody>
                      		<tr class="cat-list-row0">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn active" data-toggle="button"><i class="icon-ok tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      		<tr class="cat-list-row1">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn btn-success" data-toggle="button"><i class="icon-ok icon-white tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      		<tr class="cat-list-row0">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn btn-success" data-toggle="button"><i class="icon-ok icon-white tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      		<tr class="cat-list-row1">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn btn-success" data-toggle="button"><i class="icon-ok icon-white tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      	</tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <hr />
                <div class="accordion-group">
                  <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
                      Task List Two
                    </a>
                  </div>
                  <div id="collapseTwo" class="accordion-body collapse" style="height: 0px; ">
                    <div class="accordion-inner">
                      <table class="category table table-striped">
                      	<tbody>
                      		<tr class="cat-list-row0">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn btn-success" data-toggle="button"><i class="icon-ok icon-white tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      		<tr class="cat-list-row1">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn btn-success" data-toggle="button"><i class="icon-ok icon-white tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      		<tr class="cat-list-row0">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn btn-success" data-toggle="button"><i class="icon-ok icon-white tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      		<tr class="cat-list-row1">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn active" data-toggle="button"><i class="icon-ok tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      	</tbody>
                      </table>
                    </div>
                  </div>
                </div>
                <hr />
                <div class="accordion-group">
                  <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseThree">
                      Task List Three
                    </a>
                  </div>
                  <div id="collapseThree" class="accordion-body collapse">
                    <div class="accordion-inner">
                      <table class="category table table-striped">
                      	<tbody>
                      		<tr class="cat-list-row0">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn active" data-toggle="button"><i class="icon-ok tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      		<tr class="cat-list-row1">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn btn-success" data-toggle="button"><i class="icon-ok icon-white tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      		<tr class="cat-list-row0">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn btn-success" data-toggle="button"><i class="icon-ok icon-white tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      		<tr class="cat-list-row1">
                      			<td class="list-order">
                      				<i class="icon-align-justify"></i>
                      			</td>
                      			<td class="list-title">
                      			<a class="btn active" data-toggle="button"><i class="icon-ok tip" title="Complete?"></i></a>
                      			<a href="/projectfork_4/index.php?option=com_projectfork&view=task&Itemid=479">
                      			Long and descriptive task name</a>
                      			</td>
                      			<td class="list-actions">
                      				<div class="btn-group">
                      				  <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                      				    <span class="caret"></span>
                      				  </a>
                      				  <ul class="dropdown-menu">
                      				    <li><a href="#">View</a></li>
                      				    <li><a href="#">Edit</a></li>
                      				    <li><a href="#">Delete</a></li>
                      				  </ul>
                      				</div>
                      			</td>
                      			<td class="list-owner">
                      				<small>Firstname L.</small>										
                      			</td>
                      			<td class="list-date">
                      				<small>12/04/2011</small>					
                      			</td>
                      		</tr>
                      	</tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            
        </form>
    </div>
</div>