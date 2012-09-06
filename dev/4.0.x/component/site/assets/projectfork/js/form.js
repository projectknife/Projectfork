/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


/**
 * A collection of form related functions
 *
 */
var PFform =
{
    /**
     * Function that dynamically reloads the specified form fields
     *
     * @param    string    fs    The form fields to reload (comma separated)
     * @param    string    fi    The form ID (Optional)
     * @param    string    fn    The form name (Optional)
     */
    reload: function(fs, fi, fn)
    {
        if (typeof fi == 'undefined') {
            fi = 'item-form';
        }

        if (typeof fn == 'undefined') {
            fn = 'jform';
        }

        // Get elements from string
        var els = fs.split(',');

        // Get the form
        var f = jQuery('#' + fi);
        var e = jQuery('input[name|="elements"]', f);
        var t = jQuery('input[name|="task"]', f);

        // Set the value of the "elements" and "task" field
        e.val(fs);
        t.val('reload');

        // Serialize the form
        var d = f.serializeArray();

        // Do the ajax request
        jQuery.ajax(
        {
            url: f.attr('action'),
            data: jQuery.param(d) + '&format=json',
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'html',
            success: function(resp)
            {
                resp = jQuery.parseJSON(resp);

                if (resp.success == "true") {
                    if (resp.data.length > 0) {
                        for(var i = 0; i < els.length; i++)
                        {
                            var eln = els[i];
                            var elo = jQuery('#' + fn + '_' + eln);
                            var eld = resp.data[eln];

                            if (elo.length > 0 && eld.length > 0) {
                                elo.replaceWith(eld);
                            }
                        }
                    }
                }
            },
            complete: function()
            {
                e.val('');
                t.val('');
            }
        });
    }
}