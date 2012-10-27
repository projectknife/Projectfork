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
?>
<div id="projectfork" class="category-list<?php echo $this->pageclass_sfx;?> view-users">

    <?php if ($this->params->get('show_page_heading', 1)) : ?>
        <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php endif; ?>

    <div class="clearfix"></div>

    <div class="grid">
        <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post">
            <div class="filters btn-toolbar btn-toolbar-top">
                <?php echo $this->toolbar; ?>
                <div class="filter-project btn-group">
                    <?php echo JHtml::_('pfhtml.project.filter');?>
                </div>
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
                $asset_name = 'com_users&task=profile.edit&user_id=.' . $item->id;
                $slug       = $item->id.':'.JFilterOutput::stringURLSafe($item->username);
                ?>
                <li class="span2">
                    <div class="thumbnail">
                        <a href="<?php echo PFusersHelperRoute::getUserRoute($slug);?>">
                            <?php echo JHtml::_('projectfork.avatar.image', $item->id, $item->name);?>
                        </a>
                        <div class="caption">
                            <h4>
                                <a href="<?php echo PFusersHelperRoute::getUserRoute($slug);?>">
                                    <?php echo $this->escape($item->name);?>
                                </a>
                            </h4>
                            <h5>
                                <?php echo $this->escape($item->username);?>
                            </h5>
                        </div>
                    </div>
                </li>
                <?php
                $k = 1 - $k;
                endforeach;
                ?>
            </ul>

            <div class="filters btn-toolbar">
                <div class="btn-group filter-order">
                    <select name="filter_order" class="inputbox input-medium" onchange="this.form.submit()">
                        <?php echo JHtml::_('select.options', $this->sort_options, 'value', 'text', $list_order, true);?>
                    </select>
                </div>
                <div class="btn-group folder-order-dir">
                    <select name="filter_order_Dir" class="inputbox input-medium" onchange="this.form.submit()">
                        <?php echo JHtml::_('select.options', $this->order_options, 'value', 'text', $list_dir, true);?>
                    </select>
                </div>
                <div class="btn-group display-limit">
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
                <?php if ($this->pagination->get('pages.total') > 1) : ?>
                    <div class="btn-group pagination">
                        <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
                        <?php echo $this->pagination->getPagesLinks(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <input type="hidden" id="boxchecked" name="boxchecked" value="0" />
            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </form>
    </div>
</div>
