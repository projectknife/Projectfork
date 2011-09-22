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

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<div class="category-list project-projects" id="projectfork">
    <div class="cat-items">
    
        <h2>Projects</h2>
        
        <ul class="actions">
            <li><a href="index.php">New</a></li>    
            <li><a href="index.php">Copy</a></li>    
            <li><a href="index.php">Archive</a></li>    
            <li><a href="index.php">Delete</a></li>    
        </ul>
        
        <dl class="article-info">
            <dd class="category-name">Company: <a href="index.php">Pixelpraise</a></dd>
            <dd class="hits">Open Projects: 5</dd>
            <dd class="hits">Archived Projects: 0</dd>
            <dd class="hits">Unapproved Projects: 0</dd>
        </div>
        
        <form name="adminForm" id="adminForm" action="index.php">
        
            <fieldset class="filters">
                <div class="display-search">
                    Search&nbsp;
                    <input type="text" class="inputbox" name="searchword" id="searchword"/>
                    <button class="button" onclick="this.form.submit()" name="Search">Search</button>
                </div>
                <div class="display-state">
                    State&nbsp;
                    <select id="state" class="inputbox" onchange="this.form.submit()" size="1" name="state">
                        <option value="0">Active</option>
                        <option value="1">Archived</option>
                        <option value="2">Unapproved</option>
                    </select>
                </div>
                <div class="display-limit">
                    Display #&nbsp;
                    <select id="limit" class="inputbox" onchange="this.form.submit()" size="1" name="limit">
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
                    <input type="hidden" value="" name="filter_order"/>
                    <input type="hidden" value="" name="filter_order_Dir"/>
                    <input type="hidden" value="" name="limitstart"/>
                </div>
            </fieldset>
            
            <table class="category">
                <thead>
                    <tr>
                        <th id="tableOrdering" class="list-title">
                            <a title="Click to sort by this column" href="javascript:tableOrdering('a.title','asc','');">Title</a>
                        </th>
                        <th id="tableOrdering1" class="list-authors">
                            <a title="Click to sort by this column" href="javascript:tableOrdering('a.title','asc','');">Author</a>
                        </th>
                        <th id="tableOrdering2" class="list-deadlines">
                            <a title="Click to sort by this column" href="javascript:tableOrdering('a.title','asc','');">Deadline</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="cat-list-row0">
                        <td class="list-title"><a href="index.php">Web Design Project 1</a></td>
                        <td class="list-author">Super User</td>
                        <td class="list-deadline">11-25-2011</td>
                    </tr>
                    <tr class="cat-list-row1">
                        <td class="list-title"><a href="index.php">Web Design Project 2</a></td>
                        <td class="list-author">Super User</td>
                        <td class="list-deadline">11-25-2011</td>
                    </tr>
                    <tr class="cat-list-row0">
                        <td class="list-title"><a href="index.php">Web Design Project 3</a></td>
                        <td class="list-author">Super User</td>
                        <td class="list-deadline">11-25-2011</td>
                    </tr>
                    <tr class="cat-list-row1">
                        <td class="list-title"><a href="index.php">Web Design Project 4</a></td>
                        <td class="list-author">Super User</td>
                        <td class="list-deadline">11-25-2011</td>
                    </tr>
                    <tr class="cat-list-row0">
                        <td class="list-title"><a href="index.php">Web Design Project 5</a></td>
                        <td class="list-author">Super User</td>
                        <td class="list-deadline">11-25-2011</td>
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