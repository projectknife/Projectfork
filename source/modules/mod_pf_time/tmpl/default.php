<?php
/**
* @package      Projectfork Timesheet Module
*
* @author       ANGEK DESIGN (Kon Angelopoulos)
* @copyright    Copyright (C) 2013 ANGEK DESIGN. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

//$show_date     	= $params->get('filter_own');
$show_monetary 	= $params->get('show_monetary');
$show_date		= $params->get('show_date');
$show_author	= $params->get('show_author');
$user       	= JFactory::getUser();
$uid        	= $user->get('id');

$menu 			= new PFMenuContext();

$doc   			= JFactory::getDocument();

$style = '.task-title > a {'
			. 'margin-left:10px;'
			. 'margin-right:10px;'
			. '}'
			. '.margin-none {'
			. 'margin: 0;'
			. '}'
			. '.list-striped .dropdown-menu li {'
			. 'background-color:transparent;'
			. 'padding: 0;'
			. 'border-bottom-width: 0;'
			. '}'
			. '.list-striped .dropdown-menu li.divider {'
			. 'background-color: rgba(0, 0, 0, 0.1);'
			. 'margin: 2px 0;'
			. '}'
			. '.label {'
			. 'margin-left: 3px'
			. '}';
$doc->addStyleDeclaration( $style );
?>
	<div class="cat-list-row">
		<ul class="list-tasks list-striped list-condensed unstyled" id="tasklist">
    <?php
	$x = 0;
	$k = 0;

    foreach ($items AS $i => $item) :
		$exists 			= ((int) $item->task_exists > 0);
    ?>
			<li id="list-item-<?php echo $x; ?>" class="clearfix ">
				<div class="task-row clearfix">
					<span class="task-title">
						<?php if ($exists) : ?>
						<a class="pull-left" href="<?php echo JRoute::_(PFtasksHelperRoute::getTaskRoute($item->task_slug, $item->project_slug, $item->milestone_slug, $item->list_slug));?>"
						rel="popover" title="<?php echo htmlspecialchars($item->task_title, ENT_QUOTES, 'UTF-8'); ?>" data-content="<?php echo htmlspecialchars($item->description, ENT_QUOTES, 'UTF-8'); ?>">
						<?php echo htmlspecialchars($item->task_title, ENT_QUOTES, 'UTF-8'); ?>
						</a>
						<span class="dropdown pull-left">
						<?php
							$menu->start(array('class' => 'btn-mini btn-link'));
							$itm_icon = 'icon-menu-2';
							$itm_txt  = 'COM_PROJECTFORK_DETAILS_LABEL';
							$itm_link = '#collapse-' . $x;
							$menu->itemCollapse($itm_icon, $itm_txt, $itm_link);
							$menu->end();
							echo $menu->render(array('class' => 'btn-mini btn-link', 'pull' => 'left'));
						?>
						</span>					
						<?php else : ?>
							<?php echo htmlspecialchars($item->task_title, ENT_QUOTES, 'UTF-8'); ?>
						<?php endif; ?>
					</span>
					<span class="pull-right"><?php echo JHtml::_('time.format', $item->log_time); ?></span>
				</div>
				<div id="collapse-<?php echo $x; ?>" class="collapse">
					<hr />
					<small class="task-description"><?php echo JText::_('COM_PROJECTFORK_FIELD_PROJECT_LABEL').":".htmlspecialchars($item->project_title, ENT_COMPAT, 'UTF-8');?></small>					
					<?php if ($show_author) : ?>
						<span class="label user">
							<span aria-hidden="true" class="icon-user icon-white"></span>
						<?php echo htmlspecialchars($item->author_name, ENT_COMPAT, 'UTF-8');?>
						</span>
						<input type="hidden" id="assigned<?php echo $i;?>" name="assigned[<?php echo $item->id;?>]" />
					<?php endif; ?>
					<?php if ($show_date) : ?>
					<span class="label label-success"><span aria-hidden="true" class="icon-calendar"></span> <?php echo JHtml::_('date', $item->log_date, JText::_('DATE_FORMAT_LC4'));?></span>
					 <?php endif; ?>
					<?php 
					if ($show_monetary) : 
						echo "<span class=\"label label-inverse\">".JText::_('COM_PROJECTFORK_TIME_TRACKING_RATE').": ".JHtml::_('pfhtml.format.money', $item->rate)."</span>";
						if (!$item->billable) :
							echo "<span class=\"label label-info\">".JText::_('COM_PROJECTFORK_TIME_TRACKING_UNBILLABLE').": ".JHtml::_('pfhtml.format.money', $item->billable_total)."</span>";
						else :
							echo "<span class=\"label label-success\">".JText::_('COM_PROJECTFORK_TIME_TRACKING_BILLABLE').": ".JHtml::_('pfhtml.format.money', $item->billable_total)."</span>";
						endif;						 
					endif; 
					?>	        	
				</div>
		   </li>
			
<?php
	$x++;
	$k = 1 - $k;
	endforeach;
?>
		</ul>	
	</div>		