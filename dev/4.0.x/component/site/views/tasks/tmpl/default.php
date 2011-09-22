<?php
// No direct access
defined('_JEXEC') or die;
?>

<div id="projectfork" class="category-list view-tasks">
	<div class="cat-items">
		<h2>Tasks</h2>
		<ul class="actions">
			<li class="new-icon">
				<span title=""><a href="#">New Task</a></span>
			</li>
			<li class="reorder-icon">
				<span title=""><a href="#">Reorder</a></span>
			</li>
			<li class="copy-icon">
				<span title=""><a href="#">Copy</a></span>
			</li>
			<li class="delete-icon">
				<span title=""><a href="#">Delete</a></span>
			</li>
		</ul>
		<form id="adminForm" name="adminForm" method="post" action="http://localhost:8888/projectfork_4/index.php?option=com_content&amp;view=category&amp;id=19&amp;Itemid=260">
			<fieldset class="filters filter">
				<span class="display-milestone">
					<label for="milestone">Milestone</label>
						<select onchange="this.form.submit()" size="1" class="inputbox" name="milestone" id="milestone">
						<option selected="selected" value="">Select Milestone</option>
						<option value="0">All</option>
					</select>
				</span>
				<span class="display-user">
					<label for="user">User</label>
						<select onchange="this.form.submit()" size="1" class="inputbox" name="user" id="user">
						<option selected="selected" value="">Select User</option>
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
				<span class="display-priority">
					<label for="priority">Priority</label>
						<select onchange="this.form.submit()" size="1" class="inputbox" name="priority" id="priority">
						<option selected="selected" value="">Select Priority</option>
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
					<a title="Click to sort by this column" href="javascript:tableOrdering('a.title','asc','');">Title</a>				</th>
					
					
					<th id="tableOrdering3" class="list-author">
					<a title="Click to sort by this column" href="javascript:tableOrdering('author','asc','');">Author</a>				</th>
					
					<th id="tableOrdering4" class="list-date">
					<a title="Click to sort by this column" href="javascript:tableOrdering('a.hits','asc','');">Due</a>				</th>
				</tr>
			</thead>
		
			<tbody>
				<tr class="cat-list-row0">
					<td class="list-select">
						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb0">
					</td>
					<td class="list-title">
					<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=8:beginners&amp;catid=19&amp;Itemid=260">
					Beginners</a>
					<ul class="actions">
						<li class="edit-icon">
							<span title=""><a href="#">Edit</a></span>
						</li>
						<li class="complete-icon">
							<span title=""><a href="#">Complete</a></span>
						</li>
						<li class="comments-icon">
							<span title=""><a href="#">Comments</a></span>
						</li>
					</ul>
					</td>
					
					
					<td class="list-author">
					
					Firstname Lastname											</td>
					
					<td class="list-date">
					12/04/2011					</td>
				
				</tr>
				<tr class="cat-list-row1">
					<td class="list-select">
						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb1">
					</td>
					<td class="list-title">
					<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=21:getting-help&amp;catid=19&amp;Itemid=436">
					Getting Help</a>
					<ul class="actions">
						<li class="edit-icon">
							<span title=""><a href="#">Edit</a></span>
						</li>
						<li class="complete-icon">
							<span title=""><a href="#">Complete</a></span>
						</li>
						<li class="comments-icon">
							<span title=""><a href="#">Comments</a></span>
						</li>
					</ul>
					</td>
					
					
					<td class="list-author">
					
					Firstname Lastname											</td>
					
					<td class="list-date">
					12/03/2011					</td>
				
				</tr>
				<tr class="cat-list-row0">
					<td class="list-select">
						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb2">
					</td>
					<td class="list-title">
					<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=22:getting-started&amp;catid=19&amp;Itemid=437">
					Getting Started</a>
					<ul class="actions">
						<li class="edit-icon">
							<span title=""><a href="#">Edit</a></span>
						</li>
						<li class="complete-icon">
							<span title=""><a href="#">Complete</a></span>
						</li>
						<li class="comments-icon">
							<span title=""><a href="#">Comments</a></span>
						</li>
					</ul>
					</td>
					
					
					<td class="list-author">
					
					Firstname Lastname											</td>
					
					<td class="list-date">
					12/02/2011					</td>
				
				</tr>
				<tr class="cat-list-row1">
					<td class="list-select">
						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb3">
					</td>
					<td class="list-title">
					<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=24:joomla&amp;catid=19&amp;Itemid=260">
					Joomla!</a>
					<ul class="actions">
						<li class="edit-icon">
							<span title=""><a href="#">Edit</a></span>
						</li>
						<li class="complete-icon">
							<span title=""><a href="#">Complete</a></span>
						</li>
						<li class="comments-icon">
							<span title=""><a href="#">Comments</a></span>
						</li>
					</ul>
					</td>
					
					
					<td class="list-author">
					
					Firstname Lastname											</td>
					
					<td class="list-date">
					12/01/2011					</td>
				
				</tr>
				<tr class="cat-list-row0">
					<td class="list-select">
						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb4">
					</td>
					<td class="list-title">
					<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=32:parameters&amp;catid=19&amp;Itemid=453">
					Parameters</a>
					<ul class="actions">
						<li class="edit-icon">
							<span title=""><a href="#">Edit</a></span>
						</li>
						<li class="complete-icon">
							<span title=""><a href="#">Complete</a></span>
						</li>
						<li class="comments-icon">
							<span title=""><a href="#">Comments</a></span>
						</li>
					</ul>
					</td>
					
					
					<td class="list-author">
					
					Firstname Lastname											</td>
					
					<td class="list-date">
					11/30/2011					</td>
				
				</tr>
				<tr class="cat-list-row1">
					<td class="list-select">
						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb5">
					</td>
					<td class="list-title">
					<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=35:professionals&amp;catid=19&amp;Itemid=260">
					Professionals</a>
					<ul class="actions">
						<li class="edit-icon">
							<span title=""><a href="#">Edit</a></span>
						</li>
						<li class="complete-icon">
							<span title=""><a href="#">Complete</a></span>
						</li>
						<li class="comments-icon">
							<span title=""><a href="#">Comments</a></span>
						</li>
					</ul>
					</td>
					
					
					<td class="list-author">
					
					Firstname Lastname											</td>
					
					<td class="list-date">
					11/29/2011					</td>
				
				</tr>
				<tr class="cat-list-row0">
					<td class="list-select">
						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb6">
					</td>
					<td class="list-title">
					<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=38:sample-sites&amp;catid=19&amp;Itemid=238">
					Sample Sites</a>
					
					</td>
					
					
					<td class="list-author">
					
					Firstname Lastname											</td>
					
					<td class="list-date">
					11/28/2011					</td>
				
				</tr>
				<tr class="cat-list-row1">
					<td class="list-select">
						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb7">
					</td>
					<td class="list-title">
					<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=47:the-joomla-community&amp;catid=19&amp;Itemid=279">
					The Joomla! Community</a>
					<ul class="actions">
						<li class="edit-icon">
							<span title=""><a href="#">Edit</a></span>
						</li>
						<li class="complete-icon">
							<span title=""><a href="#">Complete</a></span>
						</li>
						<li class="comments-icon">
							<span title=""><a href="#">Comments</a></span>
						</li>
					</ul>
					</td>
					
					
					<td class="list-author">
					
					Firstname Lastname											</td>
					
					<td class="list-date">
					11/27/2011					</td>
				
				</tr>
				<tr class="cat-list-row0">
					<td class="list-select">
						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb8">
					</td>
					<td class="list-title">
					<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=48:the-joomla-project&amp;catid=19&amp;Itemid=278">
					The Joomla! Project</a>
					<ul class="actions">
						<li class="edit-icon">
							<span title=""><a href="#">Edit</a></span>
						</li>
						<li class="complete-icon">
							<span title=""><a href="#">Complete</a></span>
						</li>
						<li class="comments-icon">
							<span title=""><a href="#">Comments</a></span>
						</li>
					</ul>
					</td>
					
					
					<td class="list-author">
					
					Firstname Lastname											</td>
					
					<td class="list-date">
					11/26/2011					</td>
				
				</tr>
				<tr class="cat-list-row1">
					<td class="list-select">
						<input type="checkbox" onclick="isChecked(this.checked);" value="16" name="cid[]" id="cb9">
					</td>
					<td class="list-title">
					<a href="/projectfork_4/index.php?option=com_content&amp;view=article&amp;id=50:upgraders&amp;catid=19&amp;Itemid=260">
					Upgraders</a>
					<ul class="actions">
						<li class="edit-icon">
							<span title=""><a href="#">Edit</a></span>
						</li>
						<li class="complete-icon">
							<span title=""><a href="#">Complete</a></span>
						</li>
						<li class="comments-icon">
							<span title=""><a href="#">Comments</a></span>
						</li>
					</ul>
					</td>
					
					
					<td class="list-author">
					
					Firstname Lastname											</td>
					
					<td class="list-date">
					11/25/2011					</td>
				
				</tr>
			</tbody>
		</table>
		
		
		<div class="pagination">
		
			<p class="counter">
			Page 1 of 2			
			</p>
			
			<ul><li class="pagination-start"><span class="pagenav">Start</span></li><li class="pagination-prev"><span class="pagenav">Prev</span></li><li><span class="pagenav">1</span></li><li><a class="pagenav" href="/projectfork_4/index.php?option=com_content&amp;view=category&amp;id=19&amp;Itemid=260&amp;limitstart=10" title="2">2</a></li><li class="pagination-next"><a class="pagenav" href="/projectfork_4/index.php?option=com_content&amp;view=category&amp;id=19&amp;Itemid=260&amp;limitstart=10" title="Next">Next</a></li><li class="pagination-end"><a class="pagenav" href="/projectfork_4/index.php?option=com_content&amp;view=category&amp;id=19&amp;Itemid=260&amp;limitstart=10" title="End">End</a></li>
			</ul>	
		</div>
	</form>
</div>


</div>