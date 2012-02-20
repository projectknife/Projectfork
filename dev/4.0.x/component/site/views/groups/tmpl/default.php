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
<div id="projectfork" class="category-list view-groups">
    <div class="cat-items">
    	<div class="page-header">
        	<h2>Groups <input type="button" class="button btn btn-info" value="New Group" /></h2>
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
            
            <table class="category table table-striped">
               <thead>
	               	<tr>
	               		<th id="tableOrdering" class="list-select">
	               			<input type="checkbox" onclick="checkAll(2);" value="" name="toggle">
	               		</th>
	               		<th id="tableOrdering2" class="list-title">
	               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.title','asc','');">Title</a></th>
	               		<th id="tableOrdering3" class="list-actions span1">
	               			<a title="Click to sort by this column" href="javascript:tableOrdering('a.title','asc','');"></a>			
	               		</th>
	               		<th id="tableOrdering3" class="list-tags">
	               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.tags','asc','');">Tags</a></th>
	               		<th id="tableOrdering4" class="list-owner">
	               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.owner','asc','');">Owner</a></th>
	               		<th id="tableOrdering5" class="list-comments">
	               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.comments','asc','');">Comments</a></th>
	               		<th id="tableOrdering6" class="list-tasks">
	               		<a title="Click to sort by this column" href="javascript:tableOrdering('a.tasks','asc','');">Tasks</a></th>
	               		<th id="tableOrdering7" class="list-date">
	               			<a title="Click to sort by this column" href="javascript:tableOrdering('a.title','asc','');">Due</a>
	               		</th>
	               	</tr>
               </thead>
               <tbody>
					<tr class="cat-list-row0">
	               		<td class="list-select">
	               			<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
	               		</td>
	               		<td class="list-title">
		               		<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">Joomla Template Design</a>
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
	               		<td class="list-tags">
		               		<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>
	               		</td>
	               		
	               		<td class="list-owner">
               				<small>Firstname L.</small>									
	               		</td>
	               		
	               		<td class="list-comments">
	               			<a class="btn"><i class="icon-comment"></i> 6</a>			
	               		</td>
	               		<td class="list-tasks">
	               			<a class="btn"><i class="icon-ok"></i> 6</a>					
	               		</td>
	               		<td class="list-date">
	               			<small>12/04/2011</small>					
	               		</td>
	               	</tr>
	               	<tr class="cat-list-row1">
               			<td class="list-select">
               				<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
               			</td>
               			<td class="list-title">
               	   		<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">Joomla Template Design</a>
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
               			<td class="list-tags">
               	   		<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>
               			</td>
               			
               			<td class="list-owner">
               				<small>Firstname L.</small>									
               			</td>
               			
               			<td class="list-comments">
               				<a class="btn"><i class="icon-comment"></i> 6</a>			
               			</td>
               			<td class="list-tasks">
               				<a class="btn"><i class="icon-ok"></i> 6</a>					
               			</td>
               			<td class="list-date">
               				<small>12/04/2011</small>					
               			</td>
               		</tr>
               		<tr class="cat-list-row0">
               				<td class="list-select">
               					<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
               				</td>
               				<td class="list-title">
               		   		<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">Joomla Template Design</a>
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
               				<td class="list-tags">
               		   		<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>
               				</td>
               				
               				<td class="list-owner">
               					<small>Firstname L.</small>									
               				</td>
               				
               				<td class="list-comments">
               					<a class="btn"><i class="icon-comment"></i> 6</a>			
               				</td>
               				<td class="list-tasks">
               					<a class="btn"><i class="icon-ok"></i> 6</a>					
               				</td>
               				<td class="list-date">
               					<small>12/04/2011</small>					
               				</td>
               			</tr>
               			<tr class="cat-list-row1">
               				<td class="list-select">
               					<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
               				</td>
               				<td class="list-title">
               		  		<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">Joomla Template Design</a>
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
               				<td class="list-tags">
               		  		<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>
               				</td>
               				
               				<td class="list-owner">
               					<small>Firstname L.</small>									
               				</td>
               				
               				<td class="list-comments">
               					<a class="btn"><i class="icon-comment"></i> 6</a>			
               				</td>
               				<td class="list-tasks">
               					<a class="btn"><i class="icon-ok"></i> 6</a>					
               				</td>
               				<td class="list-date">
               					<small>12/04/2011</small>					
               				</td>
               			</tr>
               			<tr class="cat-list-row0">
           					<td class="list-select">
           						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
           					</td>
           					<td class="list-title">
           			   		<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">Joomla Template Design</a>
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
           					<td class="list-tags">
           			   		<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>
           					</td>
           					
           					<td class="list-owner">
           						<small>Firstname L.</small>									
           					</td>
           					
           					<td class="list-comments">
           						<a class="btn"><i class="icon-comment"></i> 6</a>			
           					</td>
           					<td class="list-tasks">
           						<a class="btn"><i class="icon-ok"></i> 6</a>					
           					</td>
           					<td class="list-date">
           						<small>12/04/2011</small>					
           					</td>
           				</tr>
           				<tr class="cat-list-row1">
           					<td class="list-select">
           						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
           					</td>
           					<td class="list-title">
           			  		<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">Joomla Template Design</a>
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
           					<td class="list-tags">
           			  		<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>
           					</td>
           					
           					<td class="list-owner">
           						<small>Firstname L.</small>									
           					</td>
           					
           					<td class="list-comments">
           						<a class="btn"><i class="icon-comment"></i> 6</a>			
           					</td>
           					<td class="list-tasks">
           						<a class="btn"><i class="icon-ok"></i> 6</a>					
           					</td>
           					<td class="list-date">
           						<small>12/04/2011</small>					
           					</td>
           				</tr>
           				<tr class="cat-list-row0">
           					<td class="list-select">
           						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
           					</td>
           					<td class="list-title">
           						<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">Joomla Template Design</a>
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
           					<td class="list-tags">
           						<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>
           					</td>
           					
           					<td class="list-owner">
           						<small>Firstname L.</small>									
           					</td>
           					
           					<td class="list-comments">
           						<a class="btn"><i class="icon-comment"></i> 6</a>			
           					</td>
           					<td class="list-tasks">
           						<a class="btn"><i class="icon-ok"></i> 6</a>					
           					</td>
           					<td class="list-date">
           						<small>12/04/2011</small>					
           					</td>
           				</tr>
           				<tr class="cat-list-row1">
           					<td class="list-select">
           						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
           					</td>
           					<td class="list-title">
           						<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">Joomla Template Design</a>
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
           					<td class="list-tags">
           						<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>
           					</td>
           					
           					<td class="list-owner">
           						<small>Firstname L.</small>									
           					</td>
           					
           					<td class="list-comments">
           						<a class="btn"><i class="icon-comment"></i> 6</a>			
           					</td>
           					<td class="list-tasks">
           						<a class="btn"><i class="icon-ok"></i> 6</a>					
           					</td>
           					<td class="list-date">
           						<small>12/04/2011</small>					
           					</td>
           				</tr>
           				<tr class="cat-list-row0">
           					<td class="list-select">
           						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
           					</td>
           					<td class="list-title">
           						<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">Joomla Template Design</a>
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
           					<td class="list-tags">
           						<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>
           					</td>
           					
           					<td class="list-owner">
           						<small>Firstname L.</small>									
           					</td>
           					
           					<td class="list-comments">
           						<a class="btn"><i class="icon-comment"></i> 6</a>			
           					</td>
           					<td class="list-tasks">
           						<a class="btn"><i class="icon-ok"></i> 6</a>					
           					</td>
           					<td class="list-date">
           						<small>12/04/2011</small>					
           					</td>
           				</tr>
           				<tr class="cat-list-row1">
           					<td class="list-select">
           						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
           					</td>
           					<td class="list-title">
           						<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">Joomla Template Design</a>
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
           					<td class="list-tags">
           						<span class="tag"><a href="#">Design</a></span>, <span class="tag"><a href="#">Joomla</a></span>
           					</td>
           					
           					<td class="list-owner">
           						<small>Firstname L.</small>									
           					</td>
           					
           					<td class="list-comments">
           						<a class="btn"><i class="icon-comment"></i> 6</a>			
           					</td>
           					<td class="list-tasks">
           						<a class="btn"><i class="icon-ok"></i> 6</a>					
           					</td>
           					<td class="list-date">
           						<small>12/04/2011</small>					
           					</td>
           				</tr>
                </tbody>
            </table>
            <div class="pagination">
                <ul>
                    <li class="pagination-start disabled"><a><span class="pagenav">Start</span></a></li>
                    <li class="pagination-prev disabled"><a><span class="pagenav">Prev</span></a></li>
                    <li class="disabled"><a><span class="pagenav">1</span></a></li>
                    <li><a title="2" href="index.php?start=10" class="pagenav">2</a></li>
                    <li class="pagination-next"><a title="Next" href="index.php?start=10" class="pagenav">Next</a></li>
                    <li class="pagination-end"><a title="End" href="index.php?start=10" class="pagenav">End</a></li>
                </ul>	
                <p class="counter">Page 1 of 2</p>
            </div>
        </form>
    </div>
</div>