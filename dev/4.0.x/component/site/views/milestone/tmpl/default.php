<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
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

// Create shortcuts to some parameters.
$params	 = $this->item->params;
$canEdit = $this->item->params->get('access-edit');
$user	 = JFactory::getUser();
?>
<div id="projectfork" class="item-page<?php echo $this->pageclass_sfx?> view-milestone">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
	    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <?php if ($params->get('show_title')) : ?>
    	<h2><?php echo $this->escape($this->item->title); ?></h2>
    <?php endif; ?>


	<dl class="article-info">
		<dt class="article-info-term">Details</dt>
        <dd class="created-by">
            <?php echo JText::_('JGRID_HEADING_CREATED_BY');?>:
            <?php echo $this->escape($this->item->author);?>
        </dd>
		<dd class="created-on">
            <?php echo JText::_('JGRID_HEADING_CREATED_ON');?>:
            <?php echo JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2'));?>
        </dd>
        <?php if($this->item->start_date != JFactory::getDBO()->getNullDate()): ?>
            <dd class="start-date">
                <?php echo JText::_('JGRID_HEADING_START_DATE');?>:
    			<?php echo JHtml::_('date', $this->item->start_date, JText::_('DATE_FORMAT_LC2'));?>
    		</dd>
        <?php endif; ?>
        <?php if($this->item->end_date != JFactory::getDBO()->getNullDate()): ?>
    		<dd class="due-date">
    			<?php echo JText::_('JGRID_HEADING_DEADLINE');?>:
                <?php echo JHtml::_('date', $this->item->end_date, JText::_('DATE_FORMAT_LC2'));?>
    		</dd>
        <?php endif;?>
	</dl>
    <!--
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
    -->
	<div class="item-description">
		<p>
		<?php echo $this->item->description; ?>
		</p>
	</div>
    <!--
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
						<!-- Inline Replies --><!--
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
						<!-- Inline Replies --><!--
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
						<!-- Inline Replies --><!--
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
						<!-- Inline Replies --><!--
					</li>
				</ul>
			</div>
		</div>
	</div>-->
</div>