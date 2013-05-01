!function ($) {
  $(function(){
		jQuery('*[rel=tooltip]').tooltip()
		jQuery('*[rel=popover]').popover({trigger: "hover"})
	})
}(window.jQuery)