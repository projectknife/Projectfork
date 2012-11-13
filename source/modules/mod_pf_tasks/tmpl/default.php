<?php
/**
* @package      Projectfork Tasks
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

?>
<ul class="list-striped list-condensed">
    <?php foreach ($items AS $i => $item) : ?>
        <li>
            <span class="task-name">
                <a href="<?php echo JRoute::_(PFtasksHelperRoute::getTaskRoute($item->slug, $item->project_slug, $item->milestone_slug, $item->list_slug));?>">
                    <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </span>
            <span class="pull-right">
                <?php if ($params->get('show_assigned')) : ?>
                    <?php echo JHtml::_('pftasks.assignedLabel', $item->id, $i, $item->users); ?>
                <?php endif; ?>
                <?php if ($params->get('show_priority')) : ?>
                    <?php echo JHtml::_('pftasks.priorityLabel', $item->id, $i, $item->priority); ?>
                <?php endif; ?>
                <?php if ($params->get('show_deadline')) : ?>
                    <?php echo JHtml::_('pfhtml.label.datetime', $item->end_date); ?>
                <?php endif; ?>
            </span>
            <div class="clearfix"></div>
        </li>
    <?php endforeach; ?>
</ul>
