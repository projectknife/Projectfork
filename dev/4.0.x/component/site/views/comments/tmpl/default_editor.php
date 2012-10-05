<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

$user = JFactory::getUser();
?>
<ul class="unstyled" id="comment-editor">
    <li>
        <div class="comment-editor">
            <div class="row-fluid">
        	   <div class="span1">
			       <a href="#">
                       <img class="pull-left thumbnail" width="90" src="<?php echo JHtml::_('projectfork.avatar.path', $user->id);?>" alt="" />
                   </a>
        	   </div>
        	   <div class="span11">
                   <span class="item-title editor-title">
				       <?php echo JText::_('COM_PROJECTFORK_WRITE_COMMENT'); ?>
				   </span>

				   <div class="comment-editor-input">
				       <textarea id="jform_description" class="input-xxlarge" name="jform[description]"></textarea>
				       <div class="comment-form-actions">
				           <a id="btn_comment_save" class="btn btn-mini btn-info" href="javascript:void(0);">
                               <i class="icon-ok icon-white"></i> <?php echo JText::_('COM_PROJECTFORK_ACTION_POST_COMMENT'); ?>
				           </a>
				           <a id="btn_comment_cancel" class="btn btn-mini" href="javascript:void(0);">
                               <i class="icon-remove"></i> <?php echo JText::_('COM_PROJECTFORK_ACTION_CANCEL'); ?>
				           </a>
				       </div>
        	       </div>

        	   </div>
            </div>
			<hr />
        </div>
   </li>
</ul>
