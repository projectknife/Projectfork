jQuery('.typeahead').typeahead()
jQuery('.tabs').button()
jQuery('*[rel=tooltip]').tooltip()
jQuery('*[rel=popover]').popover()
jQuery(".alert-message").alert()
jQuery(window).bind("load resize", function() {
 var windowHeight = "height:"+(jQuery(window).height()-45)+"px"; // height of full document
 var windowWidth = "width:"+(jQuery(window).width()-30)+"px"; // width of full document
 jQuery('.side-nav, .fluid-content.main, .modal.full').attr('style', windowHeight);
});