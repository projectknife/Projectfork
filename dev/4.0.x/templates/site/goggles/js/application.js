$('.typeahead').typeahead()
$('.tabs').button()
$('*[rel=tooltip]').tooltip()
$('*[rel=popover]').popover()
$(".alert-message").alert()
$(window).bind("load resize", function() {
 var windowHeight = "height:"+($(window).height()-45)+"px"; // height of full document
 var windowWidth = "width:"+($(window).width()-30)+"px"; // width of full document
 $('.side-nav, .fluid-content.main, .modal.full').attr('style', windowHeight);
});