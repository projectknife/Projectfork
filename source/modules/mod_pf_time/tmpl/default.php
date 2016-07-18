<?php
/**
* @package      Projectfork Timesheet Module
*
* @author       ANGEK DESIGN (Kon Angelopoulos)
* @copyright    Copyright (C) 2013 - 2015 ANGEK DESIGN. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();

$doc   			= JFactory::getDocument();

$script = '
function getData(l){
	var formURL = jQuery("#adminPFTEForm").attr("action");
	postData = "limitstart="+l+"&action=filter";
	jQuery.ajax(
	{
		url: formURL,
		type: "POST",
		data: postData,
		success: function(data, textStatus,jqXHR)
		{
			jQuery("#timedata").html(data);
			jQuery("[rel=popover]").popover({trigger: "hover"});
		}
	});
}
jQuery(function() {
	jQuery(":button").click(function () {
		this.addClass("clicked");
	});
	var postData = jQuery("#adminPFTEForm").serialize();
	postData += "&action=filter";
	var formURL = jQuery("#adminPFTEForm").attr("action");
	jQuery.ajax(
	{
		url: formURL,
		type: "POST",
		data: postData,
		success: function(data, textStatus,jqXHR)
		{
			jQuery("#timedata").html(data);
			jQuery("[rel=popover]").popover({trigger: "hover"});
		}
	});


	jQuery("#adminPFTEForm").submit(function(e)
	{
		var a = jQuery(".dfilter.clicked").val();
		jQuery(":button").removeClass("clicked");
		if (a == "export"){
			jQuery.fileDownload(jQuery(this).attr("action"), {
				successCallback: function(){
					document.cookie="fileDownload=false; expires=Thu, 01 Jan 1990 12:00:00 GMT; path=/";
				},
				httpMethod: "POST",
				data: jQuery(this).serialize() + "&action=export"
			});
			e.preventDefault();
		}
		else if (a == "filter"){
			var postData = jQuery(this).serialize();
			postData += "&action=filter";
			var formURL = jQuery(this).attr("action");
			jQuery.ajax(
			{
				url: formURL,
				type: "POST",
				data: postData,
				success: function(data, textStatus,jqXHR)
				{
					jQuery("#timedata").html(data);
					jQuery("[rel=popover]").popover({trigger: "hover"});
				}
			});
			e.preventDefault();
		}
		else {
			e.preventDefault();
		}
	});
});
';
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

$doc->addScriptDeclaration( $script );
$doc->addStyleDeclaration( $style );
modPFtimeHelper::loadMedia();
?>

<form id="adminPFTEForm" name="adminPFTEForm" class="adminPFTEForm form-horizontal" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>">
	<div class="row-fluid">
        <div class="span12">
            <div class="btn-group pull-left">
                <label for="filter_start_date">
                    <?php echo JText::_('MOD_PF_TIME_CONFIG_FILTER_DATE_FROM'); ?>
                    <?php
                        $cal = JHTML::calendar('', 'filter_start_date', 'filter_start_date', '%Y-%m-%d');
                        echo str_replace('id="filter_start_date"', 'id="filter_start_date" class="input-small"', $cal);
                    ?>
                </label>
            </div>
            <div class="btn-group pull-left">
                <label for="filter_end_date">
                    <?php echo JText::_('MOD_PF_TIME_CONFIG_FILTER_DATE_TO'); ?>
                    <?php
                        $cal = JHtml::calendar(date('y-m-d', strtotime('now')), 'filter_end_date', 'filter_end_date', '%Y-%m-%d');
                        echo str_replace('id="filter_end_date"', 'id="filter_end_date" class="input-small"', $cal);
                    ?>
                </label>
            </div>
            <div class="btn-group pull-left">
                <button class="btn dfilter" value="filter"><?php echo JText::_('MOD_PF_TIME_CONFIG_DISPLAY_LABEL'); ?></button>
            </div>
            <div style="clear:both !important"></div>
        </div>
    </div>
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" name="filter_project" id="filter.project" value="<?php echo PFApplicationHelper::getActiveProjectId(); ?>" />
</form>
<div id="timedata"></div>