var Projectfork =
{
    bulkAction: function(el)
    {
        var idx    = el.selectedIndex;
        var action = el.options[idx].value;

        if(action.length > 0) Joomla.submitbutton(action);
    }
}