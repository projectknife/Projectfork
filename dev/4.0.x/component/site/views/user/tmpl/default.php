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
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-user">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="cat-items">

        <form id="adminForm" name="adminForm" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>">

            <fieldset class="filters btn-toolbar">
                    <div class="filter-project btn-group">
                        <?php echo JHtml::_('projectfork.filterProject');?>
                    </div>
            </fieldset>

            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>


            <div class="clearfix"></div>

                <div id="user-details">
                    <div class="well btn-toolbar">
                        <div class="item-description">

                            <div class="thumbnail pull-left">
                                <img alt="" src="http://placehold.it/260x180"/>
                            </div>


                            <dl class="article-info dl-horizontal pull-right">
                    		    <dt class="username-title">
                    				<?php echo JText::_('COM_PROJECTFORK_USER_USERNAME');?>:
                    			</dt>
                    			<dd class="username-data">
                    				<?php echo $this->escape($this->item->username);?>
                    			</dd>
                                <dt class="name-title">
                    				<?php echo JText::_('COM_PROJECTFORK_USER_NAME');?>:
                    			</dt>
                    			<dd class="name-data">
                    				<?php echo $this->escape($this->item->name);?>
                    			</dd>
                                <?php if($this->item->registerDate != JFactory::getDBO()->getNullDate()): ?>
                        			<dt class="regdate-title">
                        				<?php echo JText::_('COM_PROJECTFORK_USER_REG_DATE');?>:
                        			</dt>
                        			<dd class="regdate-data">
                        				<?php echo JHtml::_('date', $this->item->registerDate, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        			</dd>
                        		<?php endif; ?>
                                <?php if($this->item->lastvisitDate != JFactory::getDBO()->getNullDate()): ?>
                        			<dt class="visitdate-title">
                        				<?php echo JText::_('COM_PROJECTFORK_USER_VISIT_DATE');?>:
                        			</dt>
                        			<dd class="visitdate-data">
                        				<?php echo JHtml::_('date', $this->item->lastvisitDate, $this->escape( $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))));?>
                        			</dd>
                        		<?php endif; ?>
                        	</dl>

                            <div class="clearfix"></div>
                    	</div>
                    </div>
                </div>

                <div class="clearfix"></div>

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

	</div>
</div>