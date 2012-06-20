<?php
/**
 * @version    SVN $Id: media.php 224 2012-03-01 22:09:22Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      08-Jan-2012 12:12:40
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$mediaSelectOptions = array("1" => "COM_HWDMS_AUDIO", "2" => "COM_HWDMS_DOCUMENT", "3" => "COM_HWDMS_IMAGE", "4" => "COM_HWDMS_VIDEO");
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm">
  <div id="hwd-container"> <a name="top" id="top"></a>
    <!-- Media Header -->
    <div class="media-header">
      <h2 class="media-media-title"><?php echo JText::sprintf( 'COM_HWDMS_USERXS_MEDIA', $this->escape($this->channel->title)); ?></h2>
      <!-- View Type -->
      <div class="pull-right btn-group">
       <a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getSelfRoute('details')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_DETAILS'); ?>"><i class="icon-file"></i> <?php echo JText::_('COM_HWDMS_DETAILS'); ?></a> 
			<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaRoute('gallery')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_GALLERY'); ?>"><i class="icon-th"></i> <?php echo JText::_('COM_HWDMS_GALLERY'); ?></a> 
			<a href="<?php echo JRoute::_(hwdMediaShareHelperRoute::getMediaRoute('list')); ?>" class="btn" title="<?php echo JText::_('COM_HWDMS_LIST'); ?>"><i class="icon-list"></i> <?php echo JText::_('COM_HWDMS_LIST'); ?></a> 
      </div>
      <div class="clear"></div>
      <!-- Search Filters -->
      <fieldset class="filters">
        <?php if ($this->params->get('global_list_filter_search') != 'hide') :?>
        <legend class="hidelabeltxt"> <?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?> </legend>
        <div class="filter-search">
          <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
          <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_HWDMS_SEARCH_IN_TITLE'); ?>" />
          <button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
          <button type="button" class="btn" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_filter_pagination') != 'hide') : ?>
        <div class="display-limit"> <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160; <?php echo $this->pagination->getLimitBox(); ?> </div>
        <?php endif; ?>
        <?php if ($this->display != 'list') :?>
        <?php if ($this->params->get('global_list_filter_ordering') != 'hide') :?>
        <div class="display-limit">
          <label class="filter-order-lbl" for="filter_order"><?php echo JText::_('COM_HWDMS_ORDER'); ?></label>
          <select onchange="this.form.submit()" size="1" class="inputbox" name="filter_order" id="filter_order">
            <?php if ($this->params->get('global_list_meta_title') != 'hide') :?><option value="a.title"<?php echo ($listOrder == 'a.title' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_TITLE' ); ?></option><?php endif; ?>
            <?php if ($this->params->get('global_list_meta_likes') != 'hide') :?><option value="a.likes"<?php echo ($listOrder == 'a.likes' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_LIKES' ); ?></option><?php endif; ?>
            <?php if ($this->params->get('global_list_meta_likes') != 'hide') :?><option value="a.dislikes"<?php echo ($listOrder == 'a.dislikes' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_DISLIKES' ); ?></option><?php endif; ?>
            <?php if ($this->params->get('global_list_meta_created') != 'hide') :?><option value="a.created"<?php echo ($listOrder == 'a.created' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'COM_HWDMS_CREATED' ); ?></option><?php endif; ?>
            <?php if ($this->params->get('global_list_meta_hits') != 'hide') :?><option value="a.hits"<?php echo ($listOrder == 'a.hits' ? ' selected="selected"' : false); ?>><?php echo JText::_( 'JGLOBAL_HITS' ); ?></option><?php endif; ?>
          </select>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <?php if ($this->params->get('global_list_filter_media') != 'hide') :?>
        <div class="display-limit">
          <select name="filter_mediaType" class="inputbox" onchange="this.form.submit()">
            <option value=""><?php echo JText::_('COM_HWDMS_LIST_SELECT_MEDIA_TYPE');?></option>
            <?php echo JHtml::_('select.options', $mediaSelectOptions, 'value', 'text', $this->state->get('filter.mediaType'), true);?>
          </select>
        </div>
        <?php endif; ?>
        <!-- @TODO add hidden inputs -->
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
        <input type="hidden" name="limitstart" value="" />
      </fieldset>
      <div class="clear"></div>
    </div>
    <?php 
    $this->setLayout('default');
    echo $this->loadTemplate('media_'.$this->display); 
    ?>
    <!-- Pagination -->
    <div class="pagination"> <?php echo $this->pagination->getPagesLinks(); ?> </div>
  </div>
</form>
