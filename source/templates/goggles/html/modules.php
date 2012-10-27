<?php
defined('_JEXEC') or die;
/**
 * GRID Module 
 * (i.e. <jdoc:include type="modules" name="banner" grid="<?php echo $bannergridcount;?>" style="jomGrid" />)
 */
function modChrome_jomGrid($module, &$params, &$attribs) {
	
if (!empty ($module->content)) : ?>
<div class="moduletable<?php echo $params->get( 'moduleclass_sfx' ); ?> grid_<?php echo $attribs['grid'] ?> <?php echo print_r($module->module);?>">
	<?php if ($module->showtitle) : ?>
	<h3><?php echo ($module->title); ?></h3>
    <?php endif; ?>
	<?php echo $module->content; ?>
</div>
<?php endif;
}