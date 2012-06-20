<?php
/**
 * @version    SVN $Id: default_share.php 269 2012-03-22 10:07:58Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      18-Nov-2011 10:01:46
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal');
JHtml::_('behavior.framework', true);

?>

<div class="edit">
	<form action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
		<fieldset>
			<legend><?php echo JText::_('COM_HWDMS_PERMALINK'); ?></legend>
			<div class="control-group">
				<div class="controls"> <?php echo $this->form->getInput('permalink'); ?> </div>
			</div>
		</fieldset>
		<fieldset>
			<legend><?php echo JText::_('COM_HWDMS_EMBED_CODE'); ?></legend>
			<div class="control-group">
				<div class="controls"> <?php echo $this->form->getInput('embed_code'); ?> </div>
			</div>
		</fieldset>
		<fieldset>
			<legend><?php echo JText::_('COM_HWDMS_SEND_TO_A_FRIEND'); ?></legend>
			<div class="control-group"> <?php echo $this->form->getLabel('mailto'); ?>
				<div class="controls"> <?php echo $this->form->getInput('mailto'); ?> </div>
			</div>
			<div class="control-group"> <?php echo $this->form->getLabel('sender'); ?>
				<div class="controls"> <?php echo $this->form->getInput('sender'); ?> </div>
			</div>
			<div class="control-group"> <?php echo $this->form->getLabel('from'); ?>
				<div class="controls"> <?php echo $this->form->getInput('from'); ?> </div>
			</div>
			<div class="control-group"> <?php echo $this->form->getLabel('subject'); ?>
				<div class="controls"> <?php echo $this->form->getInput('subject'); ?> </div>
			</div>
			<div class="form-actions">
				<button class="btn" onclick="return Joomla.submitbutton('send');"> <?php echo JText::_('COM_HWDMS_SEND'); ?> </button>
			</div>
			<input type="hidden" name="option" value="com_mailto" />
			<input type="hidden" name="task" value="send" />
			<input type="hidden" name="tmpl" value="component" />
			<input type="hidden" name="link" value="<?php echo $this->mailtoLink; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
		<fieldset>
			<legend><?php echo JText::_('COM_HWDMS_FACEBOOK'); ?></legend>
			<div class="control-group">
				<div class="controls">
					<div id="fb-root"></div>
					<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
					<div class="fb-like" data-href="<?php echo hwdMediaShareMedia::getPermalink(JRequest::getInt('id')); ?>" data-send="true" data-width="290" data-show-faces="true"></div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<legend><?php echo JText::_('COM_HWDMS_TWITTER'); ?></legend>
			<div class="control-group">
				<div class="controls"> <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo hwdMediaShareMedia::getPermalink(JRequest::getInt('id')); ?>" data-text="<?php //echo $this->escape($this->item->title); ?>" data-size="large">Tweet</a> 
					<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script> 
				</div>
			</div>
		</fieldset>
		<fieldset>
			<legend><?php echo JText::_('COM_HWDMS_GOOGLEPLUS'); ?></legend>
			<div class="control-group">
				<div class="controls"> 
					<!-- Place this tag where you want the +1 button to render -->
					<g:plusone annotation="inline" href="<?php echo hwdMediaShareMedia::getPermalink(JRequest::getInt('id')); ?>"></g:plusone>
					
					<!-- Place this render call where appropriate --> 
					<script type="text/javascript">
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script> 
				</div>
			</div>
		</fieldset>
	</form>
</div>
