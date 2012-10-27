<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

$user     = JFactory::getUser();
$ul_open  = false;
$level    = 1;
$uid      = $user->get('id');
?>
<?php
foreach($this->items AS $i => $item) :
    if ($item->level > $level) :
        $ul_open = true;
        ?>
        <li id="comment-node-<?php echo $item->id;?>">
            <ul class="unstyled offset1">
        <?php
    elseif ($item->level < $level) :
        if ($item->level == 1) $ul_open = false;
        $tmp_level = $level;
        while($tmp_level > $item->level)
        {
            ?>
                </ul>
            </li>
            <?php
            $tmp_level--;
        }
    endif;
    $level = $item->level;

    $can_create = $this->access->get('core.create');
    $can_trash  = ($this->access->get('core.edit.state') || ($this->access->get('core.edit.own') && $item->created_by == $uid));
    ?>
    <li id="comment-item-<?php echo $i; ?>">
        <div class="comment-item">
	        <div class="row-fluid">
	            <div class="span1">
                    <a href="#"><img class="thumbnail" width="90" src="<?php echo JHtml::_('projectfork.avatar.path', $item->created_by);?>" alt="" /></a>
                </div>
                <div class="span11">
	                <div class="comment-content">
	                    <blockquote>
	                    	<span class="item-date small pull-right">
			                    <?php echo JHtml::date($item->created); ?>
			                </span>
                            <?php echo nl2br($item->description); ?>
                            <small><cite title="<?php echo $item->author_name; ?>"><?php echo $item->author_name; ?></cite></small>
	                        <div class="btn-group comment-item-actions">
	                            <?php if ($can_create) : ?>
                                    <a class="btn btn-mini btn-add-reply" href="javascript:void(0)">
                                        <i class="icon-comment"></i> <?php echo JText::_('COM_PROJECTFORK_ACTION_REPLY'); ?>
    	                            </a>
                                <?php endif; ?>
                                <?php if ($can_trash) : ?>
    	                            <a class="btn btn-mini btn-trash-reply" href="javascript:void(0);">
                                        <i class="icon-remove"></i> <?php echo JText::_('COM_PROJECTFORK_ACTION_DELETE'); ?>
    	                            </a>
                                    <div style="display: none !important;">
                                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                                    </div>
                                <?php endif; ?>
	                        </div>
	                    </blockquote>
	                </div>
                </div>
	        </div>
        </div>
    </li>
    <?php
endforeach;

if ($ul_open) {
    while($level > 1)
    {
        ?>
            </ul>
        </li>
        <?php
        $level--;
    }
    $ul_open = false;
}
