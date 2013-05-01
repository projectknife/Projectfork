<?php
/**
 * @package      Projectfork
 * @subpackage   Users
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


$list_order = $this->escape($this->state->get('list.ordering'));
$list_dir   = $this->escape($this->state->get('list.direction'));
$user       = JFactory::getUser();
$uid        = $user->get('id');

$doc =& JFactory::getDocument();
$style = '.text-large {'
        . 'font-size: 16px;'
        . 'display: block;'
        . '}' 
        . '.row-fluid .thumbnails.thumbnails-users > li[class*="span"]:first-child,.thumbnails.thumbnails-users > li[class*="span"] {'
        . 'margin-left: 0.7em;'
        . 'margin-bottom: 0.7em;'
        . '}'
        . '.thumbnails-users .img-circle {'
        . 'margin: 5px auto;'
        . '}'
        . '.thumbnails-users .img-polaroid {'
        . 'height: 197px;'
        . '}';
$doc->addStyleDeclaration( $style );
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-users">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="grid">
        <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post">
            <div class="btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar; ?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('pfhtml.project.filter');?>
                </div>
            </div>

            <div class="collapse" id="filters">
                <div class="btn-toolbar clearfix">
                    <div class="filter-search btn-group pull-left">
                        <input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" />
                    </div>
                    <div class="filter-search-buttons btn-group pull-left">
                        <button type="submit" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                        <button type="button" class="btn" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
                    </div>
                    <div class="btn-group filter-order pull-left">
                        <select name="filter_order" class="inputbox input-small" onchange="this.form.submit()">
                            <?php echo JHtml::_('select.options', $this->sort_options, 'value', 'text', $list_order, true);?>
                        </select>
                    </div>
                    <div class="btn-group folder-order-dir pull-left">
                        <select name="filter_order_Dir" class="inputbox input-small" onchange="this.form.submit()">
                            <?php echo JHtml::_('select.options', $this->order_options, 'value', 'text', $list_dir, true);?>
                        </select>
                    </div>
                </div>
            </div>

            <ul class="thumbnails thumbnails-users">
                <?php
                $k = 0;
                foreach($this->items AS $i => $item) :
                $asset_name = 'com_users&task=profile.edit&user_id=.' . $item->id;
                $slug       = $item->id.':'.JFilterOutput::stringURLSafe($item->username);
                ?>
                <li class="span3">
                    	<div class="img-polaroid center">
			    			<a href="<?php echo PFusersHelperRoute::getUserRoute($slug);?>">
			    				<img title="<?php echo $this->escape($item->name);?>"
		                             src="<?php echo JHtml::_('projectfork.avatar.path', $item->id);?>"
		                             class="img-circle hasTooltip"
		                             style="height:128px;width:auto;"
		                        />
			    			</a>
			    			<div class="text-large">
				    			<a href="<?php echo PFusersHelperRoute::getUserRoute($slug);?>">
                                    <?php echo $this->escape($item->name);?>
                                </a>
			    			</div>
			    			<div class="small"><?php echo $this->escape($item->username);?></div>
			    			<div class="small"><a href="mailto:<?php echo $this->escape($item->email);?>"><?php echo $this->escape($item->email);?></a></div>
			    		</div>
                </li>
                <?php
                $k = 1 - $k;
                endforeach;
                ?>
            </ul>
			<?php if ($this->pagination->get('pages.total') > 1) : ?>
			    <div class="pagination center">
			        <?php echo $this->pagination->getPagesLinks(); ?>
			    </div>
			    <p class="counter center"><?php echo $this->pagination->getPagesCounter(); ?></p>
			<?php endif; ?>
            <div class="filters center">
                <span class="display-limit">
                    <?php echo $this->pagination->getLimitBox(); ?>
                </span>
            </div>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
