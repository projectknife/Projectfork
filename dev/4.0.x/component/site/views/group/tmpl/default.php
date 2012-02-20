<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
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
<div id="projectfork" class="item-page view-group">
	<h2>Group Name <input type="button" class="button" value="View Dashboard" /></h2>
	<ul class="actions">
						<li class="print-icon">
			<a rel="nofollow" onclick="window.open(this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;" title="Print" href="#"><img alt="Print" src="/projectfork_4/media/system/images/printButton.png"></a>			</li>
		
					<li class="email-icon">
			<a onclick="window.open(this.href,'win2','width=400,height=350,menubar=yes,resizable=yes'); return false;" title="Email" href="#"><img alt="Email" src="/projectfork_4/media/system/images/emailButton.png"></a>			</li>
		
					<li class="edit-icon">
			<span title="" class="hasTip"><a href="#"><img alt="Edit" src="/projectfork_4/media/system/images/edit.png"></a></span>			</li>
		
	
	</ul>
	<dl class="article-info">
		<dt class="article-info-term">Details</dt>
		<dd class="department-name">
			Department: <a href="#">Department Name</a>		
		</dd>
		<dd class="start-date">
			Started on Saturday, 01 January 2011 00:00	
		</dd>
		<dd class="due-date">
			Due by Saturday, 01 January 2011 00:00	
		</dd>
		<dd class="owner">
			Assigned to <a href="#">Firstname Lastname</a>		
		</dd>
	</dl>
	<div id="article-index" class="project-stats">
		<ul>
			<li class="comment-stats">
				<a class="toclink" href="#comment-1">6</a> Comments
			</li>
			<li class="file-stats">
				<a class="toclink" href="#">15</a> Tasks
			</li>
			<li class="dependencies-stats">
				<a class="toclink" href="#">2</a> Dependencies
			</li>
			<li class="user-stats">
				<a class="toclink" href="#">1</a> Users
			</li>
		</ul>
	</div>
	<div class="item-description">
		<p>
		Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
		</p>
	</div>
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
</div>