/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


/**
 * A collection of list form related functions
 *
 */
var PFlist =
{
    /**
     * Function to enable/disable the list bulk action button.
     *
     */
    toggleBulkButton: function()
    {
        var box = jQuery('#boxchecked');
        var btn = jQuery('#btn-bulk');

        if (box.length && btn.length) {
            var v   = box.val();
            if (v == '0') {
                btn.addClass('disabled');
                btn.removeClass('btn-info');
            }
            else {
                btn.addClass('btn-info');
                btn.removeClass('disabled');
            }
        }
    },


    /**
     * Function to perform an action on a list item
     *
     * @param    string    cid    The checkbox id
     * @param    string    act    The task to perform
     * @param    string    fi     The form id (optional)
     * @param    string    nomsg  If set to true, will suppress success messages
     *
     * @return   mixed            The ajax response on success, False on error
     */
    listItemTask: function(cid, act, fi, nomsg)
    {
        var cb = jQuery('#' + cid);

        if (typeof cb == 'undefined') {
            return false;
        }

        if (cb.is(':checked') == false) {
            cb.trigger('click');
        }

        var rq = PFlist.submitform(act, fi, nomsg);

        rq.done(function(resp)
        {
            if (cb.is(':checked')) {
                cb.trigger('click');
            }
        });

        return rq;
    },


    /**
     * Function to perform a bulk action on the selected items
     *
     * @param    string    act    The task to perform
     * @param    string    fi     The form id (optional)
     * @param    string    nomsg  If set to true, will suppress success messages
     *
     * @return   mixed            The ajax response on success, False on error
     */
    submitform: function(act, fi, nomsg)
    {
        if (typeof fi == 'undefined') {
            fi = 'adminForm';
        }

        // Get the form
        var f  = jQuery('#' + fi);
        var t  = jQuery('input[name|="task"]', f);
        var tv = t.val();

        // Set the value of the "task" field
        t.val(act);

        // Serialize the form
        var d = f.serializeArray();

        // Do the ajax request
        var rq = jQuery.ajax(
        {
            url: f.attr('action'),
            data: jQuery.param(d) + '&tmpl=component&format=json',
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'html',
            success: function(resp)
            {
                if (Projectfork.isJsonString(resp) == false) {
                    Projectfork.displayException(resp);
                }
                else {
                    resp = jQuery.parseJSON(resp);

                    if (nomsg != true) {
                        Projectfork.displayMsg(resp);
                    }
                }
            },
            error: function(resp, e, msg)
            {
                Projectfork.displayMsg(resp, msg);
            },
            complete: function()
            {
                // Reset the task value
                t.val(tv);
            }
        });

        return rq;
    },


    /**
     * Function to enable drag and drop sorting on a list of elements
     *
     * @param    string    ls     The list selector
     * @param    string    v      The name of the view
     * @param    string    fi     The form id (optional)
     */
    sortable: function(ls, v, fi)
    {
        jQuery(ls).sortable(
        {
            update: function(event, ui)
            {
                if (typeof fi == 'undefined') {
                    fi = 'adminForm';
                }

                var c   = jQuery(this).children('li');
                var cbs = jQuery('#' + fi).find('input[name|="cid[]"]');

                for(i = 0; i < c.length; i++)
                {
                    var el = jQuery(c[i])
                    var o  = jQuery('input[name|="order[]"]', el);

                    if (o.length) {
                        o.val(i);
                    }
                }

                cbs.each(function(idx)
                {
                    var cb = jQuery(this);

                    if(cb.attr('type') == 'checkbox') {
                        if(cb.is(':checked') == false) {
                            cb.trigger('click');
                        }
                    }
                });

                var rq = PFlist.submitform(v + '.saveorder', fi, true);

                rq.done(function(resp)
                {
                    cbs.each(function(idx)
                    {
                        var cb = jQuery(this);

                        if(cb.attr('type') == 'checkbox') {
                            if(cb.is(':checked') == true) {
                                cb.trigger('click');
                            }
                        }
                    });
                });
            }
	   });
	   jQuery(ls).disableSelection();
    },


    /**
     * Function to set the focus/target on a particular list item.
     * This is currently used when selecting something from a modal window
     *
     * @param    string    i     The item iterator
     */
    setTarget: function(i)
    {
        var f = jQuery('#target-item');

        if (f.length) {
            f.val(i);
        }
    }
}
