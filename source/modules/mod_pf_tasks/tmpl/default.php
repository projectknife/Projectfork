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

if ($show_priority) :
    $doc   = JFactory::getDocument();
    $style = '.complete {'
            . 'opacity:0.5;'
            . '}'
            . '.task-title > a {'
            . 'margin-left:10px;'
            . 'margin-right:10px;'
            . '}'
            . '.margin-none {'
            . 'margin: 0;'
            . '}'
            . '.priority-1 {'
            . 'border-left:2px solid #CCC;'
            . '}'
            . '.priority-2 {'
            . 'border-left:2px solid #468847;'
            . '}'
            . '.priority-3 {'
            . 'border-left:2px solid #3a87ad;'
            . '}'
            . '.priority-4 {'
            . 'border-left:2px solid #c09853;'
            . '}'
            . '.priority-5 {'
            . 'border-left:2px solid #b94a48;'
            . '}'
            . '.row-striped.row-tasks {'
            . 'line-height: 30px;'
            . '}'
            . '.row-striped .img-circle {'
            . 'margin: 0 10px 0 0;'
            . '}';
    $doc->addStyleDeclaration( $style );
endif;
?>
<div class="row-striped row-tasks">
    <?php
    foreach ($items AS $i => $item) :
        // Deadline and completition date
        $date = JHtml::_(
            'pfhtml.label.datetime',
            ($item->complete ? $item->completed : $item->end_date),
            true,
            ($item->complete ? array('past-class' => 'label-inverse', 'past-icon' => 'calendar') : array())
        );
        ?>
        <div class="row-fluid priority-<?php echo $item->priority; ?>">
            <div class="span12">
                <?php if ($show_assigned) :
                    foreach ($item->users AS $usr) :
                    ?>
                    <img title="<?php echo $usr->name;?>"
                         width="30"
                         src="<?php echo JHtml::_('projectfork.avatar.path', $usr->user_id);?>"
                         class="img-circle hasTooltip pull-left width-30"
                    />
                    <?php endforeach; ?>
                <?php endif; ?>
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
                <?php
                    if ($show_date) :
                        // echo '<span class="pull-right small muted">' . JHtml::_('date', $item->end_date, JText::_('M d')) . '</span>';
                        echo '<div class="pull-right">' . $date . '</div>';
                    endif;
                ?>
                <div class="clearfix"></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
