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
<div id="projectfork" class="category-list view-projects">
    <div class="cat-items">
    
        <h2>Projects</h2>
        
        <ul class="actions">
            <li class="new-icon">
            	<span class="readmore"><a href="index.php">New</a></span>
            </li>    
            <li class="copy-icon">
            	<span class="readmore"><a href="index.php">Copy</a></span>
            </li>    
            <li class="archive-icon">
            	<span class="readmore"><a href="index.php">Archive</a></span>
            </li>    
            <li class="delete-icon">
            	<span class="readmore"><a href="index.php">Delete</a></span>
            </li>    
        </ul>
        
        
        <form name="adminForm" id="adminForm" action="index.php">
            
            <fieldset class="filters filter">
				<span class="display-company">
					<label for="company">Company</label>
						<select onchange="this.form.submit()" size="1" class="inputbox" name="company" id="company">
						<option selected="selected" value="">Select Company</option>
						<option value="0">All</option>
					</select>
				</span>
				<span class="display-department">
					<label for="department">Department</label>
						<select onchange="this.form.submit()" size="1" class="inputbox" name="department" id="department">
						<option selected="selected" value="">Select Department</option>
						<option value="0">All</option>
					</select>
				</span>
				<span class="display-status">
					<label for="status">Status</label>
						<select onchange="this.form.submit()" size="1" class="inputbox" name="status" id="status">
						<option selected="selected" value="">Select Status</option>
						<option value="0">All</option>
					</select>
				</span>
				<span class="display-limit">
					<label for="limit">Limit</label>
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
            
            <table class="category">
               <thead>
	               	<tr>
	               		<th id="tableOrdering" class="list-select">
	               			<input type="checkbox" onclick="checkAll(2);" value="" name="toggle">
	               		</th>
	               		<th id="tableOrdering2" class="list-title">
	               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.title','asc','');">Title</a></th>
	               		
	               		<th id="tableOrdering3" class="list-tags">
	               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.tags','asc','');">Tags</a></th>
	               		
	               		<th id="tableOrdering4" class="list-owner">
	               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.owner','asc','');">Owner</a></th>
	               		
	               		<th id="tableOrdering5" class="list-milestones">
	               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.milestones','asc','');">Milestones</a></th>
	               		
	               		<th id="tableOrdering6" class="list-tasks">
	               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.tasks','asc','');">Tasks</a></th>
	               	</tr>
               </thead>
               <tbody>
					<tr class="cat-list-row0">
	               		<td class="list-select">
	               			<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
	               		</td>
	               		<td class="list-title">
	               		<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">
	               		Joomla Template Design</a>
	               		<ul class="actions">
	               			<li class="edit-icon">
	               				<span title=""><a href="#">Edit</a></span>
	               			</li>
	               			<li class="tasks-icon">
	               				<span title=""><a href="#">View Tasks</a></span>
	               			</li>
	               		</ul>
	               		</td>
	               		<td class="list-tags">
	               		<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>									
	               		</td>
	               		
	               		<td class="list-owner">
	               		Firstname Lastname											
	               		</td>
	               		
	               		<td class="list-milestones">
	               		5					
	               		</td>
	               		
	               		<td class="list-tasks">
	               		25					
	               		</td>
	               	
	               	</tr>
	               	<tr class="cat-list-row1">
	               		<td class="list-select">
	               			<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb1">
	               		</td>
	               		<td class="list-title">
	               		<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=21:getting-help&amp;catid=19&amp;Itemid=436">
	               		Joomla Extension Development</a>
	               		<ul class="actions">
	               			<li class="edit-icon">
	               				<span title=""><a href="#">Edit</a></span>
	               			</li>
	               			<li class="tasks-icon">
	               				<span title=""><a href="#">View Tasks</a></span>
	               			</li>
	               		</ul>
	               		</td>
	               		<td class="list-tags">
	               		<span class="tag"><a href="#">Programming</a></span>, <span class="tag"><a href="#">Joomla</a></span>									
	               		</td>
	               		
	               		<td class="list-owner">
	               		
	               		Firstname Lastname											</td>
	               		
	               		<td class="list-milestones">
	               		5					
	               		</td>
	               		
	               		<td class="list-tasks">
	               		25					
	               		</td>
	               	
	               	</tr>
                </tbody>
            </table>
            
            <div class="pagination">
			    <p class="counter">Page 1 of 2</p>
		        <ul>
                    <li class="pagination-start"><span class="pagenav">Start</span></li>
                    <li class="pagination-prev"><span class="pagenav">Prev</span></li>
                    <li><span class="pagenav">1</span></li>
                    <li><a title="2" href="index.php?start=10" class="pagenav">2</a></li>
                    <li class="pagination-next"><a title="Next" href="index.php?start=10" class="pagenav">Next</a></li>
                    <li class="pagination-end"><a title="End" href="index.php?start=10" class="pagenav">End</a></li>
                </ul>	
            </div>
            
        </form>
    </div>
</div>