<?php
/**
* @package      pkg_projectfork
* @subpackage   mod_pf_tasks
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


$show_date     = $params->get('show_deadline');
$show_assigned = $params->get('show_assigned');
$show_priority = $params->get('show_priority');
?>
<ul class="list-striped list-condensed">
    <?php
    foreach ($items AS $i => $item) :
        // Deadline and completition date
        $date = JHtml::_(
            'pfhtml.label.datetime',
            ($item->complete ? $item->completed : $item->end_date),
            true,
            ($item->complete ? array('past-class' => 'label-success', 'past-icon' => 'calendar') : array())
        );
        ?>
        <li>
            <span class="task-name item-name">
                <?php
                if ($show_date) :
                    if ($item->complete) :
                        echo '<i class="icon-checkbox"></i>';
                    else :
                        echo '<i class="icon-checkbox-unchecked"></i>';
                    endif;
                endif;
                ?>
                <a href="<?php echo JRoute::_(PFtasksHelperRoute::getTaskRoute($item->slug, $item->project_slug, $item->milestone_slug, $item->list_slug));?>">
                    <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </span>
            <span class="pull-right">
                <?php
                    if ($show_assigned) :
                        echo JHtml::_('pftasks.assignedLabel', $item->id, $i, $item->users) . ' ';
                    endif;

                    if ($show_priority) :
                        echo JHtml::_('pftasks.priorityLabel', $item->id, $i, $item->priority) . ' ';
                    endif;

                    if ($show_date) :
                        echo $date;
                    endif;
                ?>
            </span>
            <div class="clearfix"></div>
        </li>
    <?php endforeach; ?>
</ul>
