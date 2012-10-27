<?php
/**
 * @version    $Id: default.php 153 2012-01-25 13:30:43Z dhorsfall $
 * @package    hwdMediaShare
 * @copyright  Copyright (C) 2007 - 2011 Highwood Design Limited. All rights reserved.
 * @license    GNU General Public License http://www.gnu.org/copyleft/gpl.html
 * @author     Dave Horsfall
 * @since      15-Apr-2011 10:13:15
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.modal');
JHtml::_('behavior.framework', true);
JHtml::_('behavior.tooltip');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr" >
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<?php echo JURI::base( true ); ?>/media/com_hwdmediashare/assets/css/hwd.css" type="text/css" />
<link rel="stylesheet" href="<?php echo JURI::base( true ); ?>/media/system/css/modal.css" type="text/css" />
<script src="<?php echo JURI::base( true ); ?>/media/system/js/core.js" type="text/javascript"></script>
<script src="<?php echo JURI::base( true ); ?>/media/system/js/mootools-core.js" type="text/javascript"></script>
<script src="<?php echo JURI::base( true ); ?>/media/system/js/mootools-more.js" type="text/javascript"></script>
<script src="<?php echo JURI::base( true ); ?>/media/system/js/tabs.js" type="text/javascript"></script>
<script src="<?php echo JURI::base( true ); ?>/media/com_hwdmediashare/assets/javascript/hwd.js" type="text/javascript"></script>
<script src="<?php echo JURI::base( true ); ?>/media/com_hwdmediashare/assets/javascript/Carousel.js" type="text/javascript"></script>
<script src="<?php echo JURI::base( true ); ?>/media/com_hwdmediashare/assets/javascript/Carousel.Extra.js" type="text/javascript"></script>
<script src="<?php echo JURI::base( true ); ?>/media/com_hwdmediashare/assets/javascript/Carousel.Rotate3D.js" type="text/javascript"></script>
<script src="<?php echo JURI::base( true ); ?>/media/com_hwdmediashare/assets/javascript/PeriodicalExecuter.js" type="text/javascript"></script>
<script src="<?php echo JURI::base( true ); ?>/media/system/js/modal.js" type="text/javascript"></script>
<link rel="stylesheet" href="media/com_hwdmediashare/assets/css/hwd.css" type="text/css" />
<script type="text/javascript">
var key = <?php echo $this->key; ?>;
</script>
</head>
<body class="media-slideshow">
<div id="hwd-container">
        <div class="media-slideshow-view">
                <!-- Item Media -->
                <div class="media-item" id="media-item" style="width:100%;"> </div>
                <div class="clear"></div>
        </div>
        <div class="clear"></div>

        <div id="media-slideshow-toggle" class="media-slideshow-toggle">
                <div class="media-slideshow-tab">
                        <a id="slideshow-tab" href="#" class="slideshow-tab"><span id="slideshow-status">Hide</span></a>
                        <span id="slideshow-position"> <span id="current-position">1</span> / <?php echo count($this->items); ?></span>
                        <span id="slideshow-title"> <span id="current-title"></span> </span>
                        <div class="clear"></div>
                </div>

                <div id="media-slideshow-container" class="media-slideshow-container">
                        <div class="slide-previous">
                                <a href="#page-p" class="pagenav">&laquo;</a>
                        </div>
                        <div class="slide">
                                <div id="slide">
                                        <?php $counter = 0; 
                                        foreach ($this->items as $id => &$item) : ?>
                                                <div style="max-height:150px;" onclick="loadMedia('<?php echo $counter; ?>')"> <img src="<?php echo JRoute::_(hwdMediaShareDownloads::thumbnail($item)); ?>" id="image-slideshow-<?php echo $counter; ?>" rel="{'id':'<?php echo $item->id; ?>','title':'<?php echo $this->escape($item->title); ?>'}" title="<?php echo $this->escape($item->title); ?>" /> </div>
                                        <?php $counter++;
                                        endforeach; ?>
                                </div>
                        </div>
                        <div class="slide-next">
                                        <a href="#page-p" class="pagenav">&raquo;</a>
                        </div>
                        <div class="clear"></div>
                </div>
        </div>
</div>
</body>
</html>
