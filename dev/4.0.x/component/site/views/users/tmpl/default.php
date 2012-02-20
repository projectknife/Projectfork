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
<div id="projectfork" class="category-list view-users">
    <div class="cat-items">
    	<div class="page-header">
        	<h2>Users <input type="button" class="button btn btn-info" value="New User" /></h2>
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
            <ul class="thumbnails">
	            <li class="span2">
	              <div class="thumbnail">
	                <a href="#"><img src="http://placehold.it/260x180" alt=""></a>
	                <div class="caption">
	                  <h5><a href="#">User Name</a></h5>
	                  <p><a href="#">Group Name</a></p>
	                </div>
	              </div>
	            </li>
	            <li class="span2">
	              <div class="thumbnail">
	                <a href="#"><img src="http://placehold.it/260x180" alt=""></a>
	                <div class="caption">
	                  <h5><a href="#">User Name</a></h5>
	                  <p><a href="#">Group Name</a></p>
	                </div>
	              </div>
	            </li>
	            <li class="span2">
	              <div class="thumbnail">
	                <a href="#"><img src="http://placehold.it/260x180" alt=""></a>
	                <div class="caption">
	                  <h5><a href="#">User Name</a></h5>
	                  <p><a href="#">Group Name</a></p>
	                </div>
	              </div>
	            </li>
	            <li class="span2">
	              <div class="thumbnail">
	                <a href="#"><img src="http://placehold.it/260x180" alt=""></a>
	                <div class="caption">
	                  <h5><a href="#">User Name</a></h5>
	                  <p><a href="#">Group Name</a></p>
	                </div>
	              </div>
	            </li>
	            <li class="span2">
	              <div class="thumbnail">
	                <a href="#"><img src="http://placehold.it/260x180" alt=""></a>
	                <div class="caption">
	                  <h5><a href="#">User Name</a></h5>
	                  <p><a href="#">Group Name</a></p>
	                </div>
	              </div>
	            </li>
	            <li class="span2">
	              <div class="thumbnail">
	                <a href="#"><img src="http://placehold.it/260x180" alt=""></a>
	                <div class="caption">
	                  <h5><a href="#">User Name</a></h5>
	                  <p><a href="#">Group Name</a></p>
	                </div>
	              </div>
	            </li>
	            <li class="span2">
	              <div class="thumbnail">
	                <a href="#"><img src="http://placehold.it/260x180" alt=""></a>
	                <div class="caption">
	                  <h5><a href="#">User Name</a></h5>
	                  <p><a href="#">Group Name</a></p>
	                </div>
	              </div>
	            </li>
	            <li class="span2">
	              <div class="thumbnail">
	                <a href="#"><img src="http://placehold.it/260x180" alt=""></a>
	                <div class="caption">
	                  <h5><a href="#">User Name</a></h5>
	                  <p><a href="#">Group Name</a></p>
	                </div>
	              </div>
	            </li>
	            <li class="span2">
	              <div class="thumbnail">
	                <a href="#"><img src="http://placehold.it/260x180" alt=""></a>
	                <div class="caption">
	                  <h5><a href="#">User Name</a></h5>
	                  <p><a href="#">Group Name</a></p>
	                </div>
	              </div>
	            </li>
	            <li class="span2">
	              <div class="thumbnail">
	                <a href="#"><img src="http://placehold.it/260x180" alt=""></a>
	                <div class="caption">
	                  <h5><a href="#">User Name</a></h5>
	                  <p><a href="#">Group Name</a></p>
	                </div>
	              </div>
	            </li>
	          </ul>
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