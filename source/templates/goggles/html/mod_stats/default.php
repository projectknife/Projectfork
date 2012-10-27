<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_stats
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<ul class="stats-module unstyled">
<?php foreach ($list as $item) : ?>
	<li><i class="icon-th-list"></i> <strong><?php echo $item->title;?></strong> <?php echo $item->data;?></li>
<?php endforeach; ?>
</ul>
