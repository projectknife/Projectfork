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


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user	    = JFactory::getUser();
$uid	    = $user->get('id');
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-users">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="grid">
        <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post">
            <div class="filters btn-toolbar btn-toolbar-top">
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('projectfork.filterProject');?>
                </div>
                <?php if($uid) : ?>
					<div class="btn-group">
						<a data-toggle="collapse" data-target="#filters" class="btn"><i class="icon-list"></i> <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?> <span class="caret"></span></a>
					</div>
				<?php endif; ?>

			</div>

            <div class="clearfix"> </div>

			<div class="collapse" id="filters">
				<div class="well btn-toolbar">
                    <div class="filter-search btn-group pull-left">
        			    <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
        			</div>
        			<div class="filter-search-buttons btn-group pull-left">
			            <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
    			        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
    			    </div>
                    <div class="clearfix"> </div>
				</div>
			</div>

            <div class="clearfix"> </div>

			<ul class="thumbnails">
                <?php
                $k = 0;
                foreach($this->items AS $i => $item) :
                $asset_name = 'com_users&task=profile.edit&user_id=.'.$item->id;
	            $canEdit	= ($user->authorise('core.edit', $asset_name));
                $slug       = $item->id.':'.JFilterOutput::stringURLSafe($item->username);
                ?>
                <li class="span2">
                    <div class="thumbnail">
                        <a href="<?php echo ProjectforkHelperRoute::getUserRoute($slug);?>">
                            <?php echo JHtml::image('com_projectfork/projectfork/icons/avatar.jpg', $this->escape($item->name), null, true);?>
                        </a>
                        <div class="caption">
                        	<h4>
                        	    <a href="<?php echo ProjectforkHelperRoute::getUserRoute($slug);?>">
                        	        <?php echo $this->escape($item->name);?>
                        	    </a>
                        	</h4>
                            <h5>
                                <?php echo $this->escape($item->username);?>
                            </h5>
                            <?php if($canEdit) : ?>
                            <div class="btn-group">
                            <?php /* need to find how to view other user profiles
                            	<a class="btn btn-mini" href="<?php echo JRoute::_('index.php?option=com_users&view=profile&user_id='.$this->escape($item->id));?>">
                            	    <i class="icon-user"></i> <?php echo JText::_('COM_PROJECTFORK_PROFILE');?>
                            	</a>
                            */ ?>
                            	   <!--<a class="btn btn-mini" href="<?php echo JRoute::_('index.php?option=com_users&task=profile.edit&user_id='.$this->escape($item->id));?>">
                            	       <i class="icon-edit"></i> <?php echo JText::_('COM_PROJECTFORK_ACTION_EDIT');?>
                            	   </a>-->
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <?php
                $k = 1 - $k;
                endforeach;
                ?>
			</ul>

			<div class="filters btn-toolbar">
				<?php if($this->pagination->get('pages.total') > 1) : ?>
				    <div class="btn-group pagination">
				        <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
				        <?php echo $this->pagination->getPagesLinks(); ?>
				    </div>
				<?php endif; ?>
		        <div class="btn-group display-limit">
		            <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
		            <?php echo $this->pagination->getLimitBox(); ?>
		        </div>
			</div>

            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="filter_order" value="<?php echo $list_order; ?>" />
	        <input type="hidden" name="filter_order_Dir" value="<?php echo $list_dir; ?>" />
            <input type="hidden" name="task" value="" />
	        <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
