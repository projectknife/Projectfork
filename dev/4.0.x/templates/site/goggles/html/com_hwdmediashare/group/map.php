<?php
/**
 * @version    SVN $Id: map.php 129 2012-01-11 13:01:09Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2012 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      11-Jan-2012 10:44:03
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

?>
<div class="media-group-map" style="height:500px;">
  <?php echo ($this->group->map); ?>
  <div class="clear"></div>
</div>
