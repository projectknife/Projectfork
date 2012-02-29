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

$item      = $this->item;
$db        = JFactory::getDbo();
$null_date = $db->getNullDate();
?>
<div id="projectfork" class="item-page view-project">

	<h2><?php echo $this->escape($item->title);?></h2>

    <input type="button" class="button" value="View Dashboard" />

	<ul class="actions">
	    <li class="print-icon">
		    <a rel="nofollow" onclick="window.open(this.href,'win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no'); return false;" title="Print" href="#">
                <img alt="Print" src="/projectfork_4/media/system/images/printButton.png"/>
            </a>
        </li>
        <li class="email-icon">
		    <a onclick="window.open(this.href,'win2','width=400,height=350,menubar=yes,resizable=yes'); return false;" title="Email" href="#">
                <img alt="Email" src="/projectfork_4/media/system/images/emailButton.png"/>
            </a>
        </li>
		<li class="edit-icon">
		    <span title="" class="hasTip"><a href="#"><img alt="Edit" src="/projectfork_4/media/system/images/edit.png"/></a></span>
        </li>
	</ul>

	<dl class="article-info">
		<dt class="article-info-term">Details</dt>
		<dd class="start-date">
			<?php echo JText::_('COM_PROJECTFORK_DATE_STARTED_ON');?>&nbsp;
            <?php
            if($item->start_date == $null_date) {
                echo JHtml::_('date', $item->created, $this->escape(JText::_('DATE_FORMAT_LC2')));
            }
            else {
                echo JHtml::_('date', $item->start_date, $this->escape(JText::_('DATE_FORMAT_LC2')));
            }
            ?>
		</dd>
        <?php if($item->end_date != $null_date) : ?>
    		<dd class="due-date">
    			<?php echo JText::_('COM_PROJECTFORK_DATE_DUE_BY');?>&nbsp;
                <?php echo JHtml::_('date', $item->end_date, $this->escape(JText::_('DATE_FORMAT_LC2'))); ?>
    		</dd>
        <?php endif; ?>
		<dd class="createdby">
			<?php echo JText::_('COM_PROJECTFORK_PROJECT_MANAGER');?>: <?php echo htmlspecialchars($item->author);?>
		</dd>
	</dl>

	<div id="article-index" class="project-stats">
		<ul>
			<li class="milestone-stats">
				<a class="toclink" href="#">4</a> Milestones
			</li>
			<li class="task-stats">
				<a class="toclink" href="#">25</a> Tasks
			</li>
			<li class="file-stats">
				<a class="toclink" href="#">15</a> Files
			</li>
			<li class="comment-stats">
				<a class="toclink" href="#">54</a> Comments
			</li>
			<li class="company-stats">
				<a class="toclink" href="#">2</a> Companies
			</li>
			<li class="department-stats">
				<a class="toclink" href="#">6</a> Departments
			</li>
			<li class="user-stats">
				<a class="toclink" href="#">12</a> Users
			</li>
		</ul>
	</div>

	<div class="item-description">
		<?php echo $item->description;?>
	</div>

	<div class="items-more">
		<h3>Project Milestones</h3>
		<ol>
			<li>
				<a href="#">Milestone 1</a>
			</li>
			<li>
				<a href="#">Milestone 2</a>
			</li>
			<li>
				<a href="#">Milestone 3</a>
			</li>
			<li>
				<a href="#">Milestone 4</a>
			</li>
		</ol>
	</div>

</div>