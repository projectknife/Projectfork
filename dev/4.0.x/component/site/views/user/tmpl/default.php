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

defined('_JEXEC') or die;

$item = &$this->item;
$user = JFactory::getUser();
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-user">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="cat-items">

        <form id="item-form" name="adminForm" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" enctype="multipart/form-data">

            <fieldset class="filters btn-toolbar">
                    <div class="filter-project btn-group">
                        <?php echo JHtml::_('projectfork.filterProject');?>
                        <?php if($item) echo $item->event->afterDisplayTitle; ?>
                    </div>
            </fieldset>
            <div class="clearfix"></div>

            <?php if($item) echo $item->event->beforeDisplayContent;?>

                <div id="user-details">
                    <div class="well">
                        <div class="item-description">
	                        <?php if ($user->id == $item->id) : ?>
                                <img alt="<?php echo $this->escape($this->item->name);?>"
                                     src="<?php echo JHtml::_('projectfork.avatar.path', $item->id);?>"
                                     class="thumbnail pull-left"
                                     style="cursor: pointer;"
                                     width="90"
                                     onclick="jQuery('#avatar-file').click();"
                                />
                                <div style="display: none;">
                                    <input type="file" name="avatar" id="avatar-file" onchange="Joomla.submitform('user.avatar', document.getElementById('item-form'))"/>
                                </div>
                            <?php else : ?>
                                <img alt="<?php echo $this->escape($this->item->name);?>"
                                     src="<?php echo JHtml::_('projectfork.avatar.path', $item->id);?>"
                                     class="thumbnail pull-left"
                                     width="90"
                                />
                            <?php endif; ?>
                            <h5><?php echo $this->escape($this->item->name);?></h5>
	                        <hr class="hr-condensed" />
	                        <ul class="unstyled">
                    			<li class="username-item">
                    				<i class="icon-user" rel="tooltip" title="<?php echo JText::_('COM_PROJECTFORK_USER_USERNAME');?>"></i> <?php echo $this->escape($this->item->username);?>
                    			</li>
                                <?php if($this->item->registerDate != JFactory::getDBO()->getNullDate()): ?>
                        			<li class="regdate-item">
                        				<i class="icon-calendar" rel="tooltip" title="<?php echo JText::_('COM_PROJECTFORK_USER_REG_DATE');?>"></i> <?php echo JHtml::_('date', $this->item->registerDate, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        			</li>
                        		<?php endif; ?>
                                <?php if($this->item->lastvisitDate != JFactory::getDBO()->getNullDate()): ?>
                        			<li class="visitdate-item">
                        				<i class="icon-calendar" rel="tooltip" title="<?php echo JText::_('COM_PROJECTFORK_USER_VISIT_DATE');?>"></i> <?php echo JHtml::_('date', $this->item->lastvisitDate, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        			</li>
                        		<?php endif; ?>
                        	</dl>
                        	<div class="clearfix"></div>
                    	</div>
                    </div>
                </div>

                <div class="clearfix"></div>


            <input type="hidden" name="task" value="" />
            <input type="hidden" name="id" value="<?php echo (int) $item->id;?>" />
            <input type="hidden" name="view" value="<?php echo htmlspecialchars($this->get('Name'), ENT_COMPAT, 'UTF-8');?>" />
	        <?php echo JHtml::_( 'form.token' ); ?>
        </form>

        <!-- Begin Dashboard Modules -->
        <div class="row-fluid">
        	<div class="span12">
        		<?php echo $this->modules->render('pf-dashboard-top', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <div class="row-fluid">
        	<div class="span6">
        		<?php echo $this->modules->render('pf-dashboard-left', array('style' => 'xhtml'), null); ?>
        	</div>
        	<div class="span6">
        		<?php echo $this->modules->render('pf-dashboard-right', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <div class="row-fluid">
        	<div class="span12">
        		<?php echo $this->modules->render('pf-dashboard-bottom', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
        <!-- End Dashboard Modules -->

        <?php if($item) echo $item->event->afterDisplayContent;?>

	</div>
</div>