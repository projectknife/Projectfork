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

<form id="adminPFTEForm" name="adminPFTEForm" class="adminPFTEForm" method="post" action="<?php echo htmlspecialchars(JFactory::getURI()->toString()); ?>">
	<div class="cat-list-row">
		<div class="filter-search-buttons btn-group pull-left" style="width:100%">  
			<div class="drange controls pull-left">				
					<label style="width:50px" class="control-label pull-left" for="dpstart"><?php echo JText::_('MOD_PF_TIME_CONFIG_FILTER_DATE_FROM'); ?></label>					
					<?php echo JHTML::calendar('','filter_start_date','filter_start_date','%Y-%m-%d'); ?>
					<label style="width:50px" class="control-label pull-left" for="dpend"><?php echo JText::_('MOD_PF_TIME_CONFIG_FILTER_DATE_TO'); ?></label>				
					<?php echo JHTML::calendar(date('y-m-d', strtotime('now')),'filter_end_date','filter_end_date','%Y-%m-%d'); ?>					
				</div>
			<div class="filters btn-group pull-right">				
				<button class="btn dfilter" value="filter"><?php echo JText::_('MOD_PF_TIME_CONFIG_DISPLAY_LABEL'); ?></button>
				<button class="btn dfilter" value="export"><?php echo JText::_('MOD_PF_TIME_CONFIG_EXPORT_LABEL'); ?></button>
				</div>
	</div>		
	</div>		<div class="clearfix"> </div>
	<?php echo JHtml::_('form.token'); ?>	
	<input type="hidden" name="filter_project" id="filter.project" value="<?php echo PFApplicationHelper::getActiveProjectId(); ?>" />
</form>
<div id="timedata"></div>