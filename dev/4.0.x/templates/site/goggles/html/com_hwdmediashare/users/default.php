<?php
/**
 * @version    SVN $Id: default.php 269 2012-03-22 10:07:58Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      14-Nov-2011 21:01:30
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$user           = JFactory::getUser();
$canAdd         = $user->authorise('core.create', 'com_hwdmediashare');

?>
<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
  <div id="hwd-container"> <a name="top" id="top"></a>
    <!-- Media Navigation -->
    <?php echo hwdMediaShareHelperNavigation::getInternalNavigation(); ?>
    <!-- Media Header -->
	
    <div class="media-header">
	
	<!-- View Type -->
      <div class="btn-group pull-right">
        <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('details')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_DETAILS'); ?>">
		<i class="icon-file"></i> <?php echo JText::_('COM_HWDMS_DETAILS'); ?></a>
        <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('list')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_LIST'); ?>">
		<i class="icon-list"></i> <?php echo JText::_('COM_HWDMS_LIST'); ?></a>
        <?php if ($user->id) :?>
        <a href="<?php echo JRoute::_('index.php?option=com_hwdmediashare&view=user&id='.$user->id); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_MY_CHANNEL'); ?>"><i class="icon-user"></i> <?php echo JText::_('COM_HWDMS_MY_CHANNEL'); ?></a>
        <?php endif; ?>
      </div>
	
	
	<div class="page-header">
      <h2><?php echo JText::_('COM_HWDMS_USER_CHANNELS'); ?></h2>
	  </div>
      
      <div class="clear"></div>
      <!-- Search Filters -->
      <fieldset class="filters">
        <?php if ($this->params->get('global_list_filter_search') != 'hide') :?>
          <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
          <input type="text" name="filter_search" id="filter_search" class="span2" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_HWDMS_SEARCH_IN_TITLE'); ?>" />
          <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
          <button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_filter_pagination') != 'hide') : ?>
        <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160; <?php echo $this->pagination->getLimitBox(); ?>
        <?php endif; ?>
        <?php if ($this->display != 'list') :?>
        <?php if ($this->params->get('global_list_filter_ordering') != 'hide') :?>
          <label class="filter-order-lbl" for="filter_order"><?php echo JText::_('COM_HWDMS_ORDER'); ?></label>
          <select onchange="this.form.submit()" size="1" class="inputbox span1" name="filter_order" id="filter_order">
            <?php if ($this->params->get('global_list_meta_title') != 'hide') :?><option value="a.title"<?php echo ($listOrder == 'a.title' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_TITLE' ); ?></option><?php endif; ?>
            <?php if ($this->params->get('global_list_meta_likes') != 'hide') :?><option value="a.likes"<?php echo ($listOrder == 'a.likes' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_LIKES' ); ?></option><?php endif; ?>
            <?php if ($this->params->get('global_list_meta_likes') != 'hide') :?><option value="a.dislikes"<?php echo ($listOrder == 'a.dislikes' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_DISLIKES' ); ?></option><?php endif; ?>
            <?php if ($this->params->get('global_list_meta_created') != 'hide') :?><option value="a.created"<?php echo ($listOrder == 'a.created' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_CREATED' ); ?></option><?php endif; ?>
            <?php if ($this->params->get('global_list_meta_hits') != 'hide') :?><option value="a.hits"<?php echo ($listOrder == 'a.hits' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'JGLOBAL_HITS' ); ?></option><?php endif; ?>
          </select>
        <?php endif; ?>
        <?php endif; ?>
        <!-- @TODO add hidden inputs -->
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
        <input type="hidden" name="limitstart" value="" />
      </fieldset>
      <div class="clear"></div>
    </div>
	<hr />
    <?php echo $this->loadTemplate($this->display); ?>
    <!-- Pagination -->
    <div class="pagination"> <?php echo $this->pagination->getPagesLinks(); ?> </div>
  </div>
</form>