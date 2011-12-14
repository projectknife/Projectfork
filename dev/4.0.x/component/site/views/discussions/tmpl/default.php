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
<div id="projectfork" class="category-list view-discussions">
    <div class="cat-items">
    
        <h2>Discussions <input type="button" class="button" value="New Discussion" /></h2>
        
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
            
            <div class="items-more">
				<h3>Comments</h3>
				<div class="contact-form" class="comment-form">
					<form class="form-validate" method="post" action="#" id="contact-form">
						<fieldset>
							<legend>6 comments</legend>
							<dl>
								<dt><label title="" class="hasTip required" for="jform_contact_name" id="jform_contact_name-lbl">Name<span class="star">&nbsp;*</span></label></dt>
								<dd><input type="text" size="30" class="required" value="" id="jform_contact_name" name="jform[contact_name]" aria-required="true" required="required"></dd>
								<dt><label title="" class="hasTip required" for="jform_contact_email" id="jform_contact_email-lbl">Email<span class="star">&nbsp;*</span></label></dt>
								<dd><input type="email" size="30" value="" id="jform_contact_email" class="validate-email required" name="jform[contact_email]" aria-required="true" required="required"></dd>
								<dt><label title="" class="hasTip required" for="jform_contact_emailmsg" id="jform_contact_emailmsg-lbl">Subject<span class="star">&nbsp;*</span></label></dt>
								<dd><input type="text" size="60" class="required" value="" id="jform_contact_emailmsg" name="jform[contact_subject]" aria-required="true" required="required"></dd>
								<dt><label title="" class="hasTip required" for="jform_contact_message" id="jform_contact_message-lbl" aria-invalid="true">Comment<span class="star">&nbsp;*</span></label></dt>
								<dd><textarea class="required" rows="10" cols="50" id="jform_contact_message" name="jform[contact_message]" aria-required="true" required="required" aria-invalid="true"></textarea></dd>
								<dt><label title="" class="hasTip" for="jform_contact_email_copy" id="jform_contact_email_copy-lbl">Send notification</label></dt>
								<dd><input type="checkbox" value="" id="jform_contact_email_copy" name="jform[contact_email_copy]"></dd>
								<dt></dt>
								<dd><button type="submit" class="button validate">Post Comment</button>
									<input type="hidden" value="com_contact" name="option">
									<input type="hidden" value="contact.submit" name="task">
									<input type="hidden" value="" name="return">
									<input type="hidden" value="1:name" name="id">
									<input type="hidden" value="1" name="9dd4c34ea61fc9fb1b22f327a4c831f8">				</dd>
							</dl>
						</fieldset>
					</form>
					<div class="categories-list comments-list">
						<ul>
							<li class="first">
								<div class="cat-list-row1 comment-info">
									<span class="item-avatar">
										<a href="#" id="avatar-1">avatar</a>
									</span>
									<span class="item-title">
										<a href="#" id="comment-1">Firstname Lastname</a>
									</span>
									<span class="item-date">
										2 weeks ago
									</span>
								</div>
								<div class="category-desc comment-desc">
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
									</p>
									<ul class="actions">
										<li class="edit-icon">
											<span><a href="#" class="button">Edit</a></span>
										</li>
										<li class="reply-icon">
											<span><a href="#" class="button">Reply</a></span>
										</li>
									</ul>
								</div>
								<!-- Inline Replies -->
									<ul>
										<li class="first">
											<div class="cat-list-row1 comment-info">
												<span class="item-avatar">
													<a href="#" id="avatar-1">avatar</a>
												</span>
												<span class="item-title">
													<a href="#" id="comment-1">Firstname Lastname</a>
												</span>
												<span class="item-date">
													2 weeks ago
												</span>
											</div>
											<div class="category-desc comment-desc">
												<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
												</p>
												<ul class="actions">
													<li class="edit-icon">
														<span><a href="#" class="button">Edit</a></span>
													</li>
													<li class="reply-icon">
														<span><a href="#" class="button">Reply</a></span>
													</li>
												</ul>
											</div>
										</li>
										<li class="last">
											<div class="cat-list-row1 comment-info">
												<span class="item-avatar">
													<a href="#" id="avatar-1">avatar</a>
												</span>
												<span class="item-title">
													<a href="#" id="comment-1">Firstname Lastname</a>
												</span>
												<span class="item-date">
													2 weeks ago
												</span>
											</div>
											<div class="category-desc comment-desc">
												<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
												</p>
												<ul class="actions">
													<li class="edit-icon">
														<span><a href="#" class="button">Edit</a></span>
													</li>
													<li class="reply-icon">
														<span><a href="#" class="button">Reply</a></span>
													</li>
												</ul>
											</div>
										</li>
									</ul>
								<!-- Inline Replies -->
							</li>
							<li class="last">
								<div class="cat-list-row1 comment-info">
									<span class="item-avatar">
										<a href="#" id="avatar-1">avatar</a>
									</span>
									<span class="item-title">
										<a href="#" id="comment-1">Firstname Lastname</a>
									</span>
									<span class="item-date">
										3 weeks ago
									</span>
								</div>
								<div class="category-desc comment-desc">
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
									</p>
									<ul class="actions">
										<li class="edit-icon">
											<span><a href="#" class="button">Edit</a></span>
										</li>
										<li class="reply-icon">
											<span><a href="#" class="button">Reply</a></span>
										</li>
									</ul>
								</div>
								<!-- Inline Replies -->
									<ul>
										<li class="first">
											<div class="cat-list-row1 comment-info">
												<span class="item-avatar">
													<a href="#" id="avatar-1">avatar</a>
												</span>
												<span class="item-title">
													<a href="#" id="comment-1">Firstname Lastname</a>
												</span>
												<span class="item-date">
													3 weeks ago
												</span>
											</div>
											<div class="category-desc comment-desc">
												<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
												</p>
												<ul class="actions">
													<li class="edit-icon">
														<span><a href="#" class="button">Edit</a></span>
													</li>
													<li class="reply-icon">
														<span><a href="#" class="button">Reply</a></span>
													</li>
												</ul>
											</div>
										</li>
										<li class="last">
											<div class="cat-list-row1 comment-info">
												<span class="item-avatar">
													<a href="#" id="avatar-1">avatar</a>
												</span>
												<span class="item-title">
													<a href="#" id="comment-1">Firstname Lastname</a>
												</span>
												<span class="item-date">
													3 weeks ago
												</span>
											</div>
											<div class="category-desc comment-desc">
												<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
												</p>
												<ul class="actions">
													<li class="edit-icon">
														<span><a href="#" class="button">Edit</a></span>
													</li>
													<li class="reply-icon">
														<span><a href="#" class="button">Reply</a></span>
													</li>
												</ul>
											</div>
										</li>
									</ul>
								<!-- Inline Replies -->
							</li>
						</ul>
					</div>
				</div>
			</div>
            
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