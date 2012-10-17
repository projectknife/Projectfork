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
                    PFlist.displayException(resp);
                }
                else {
                    resp = jQuery.parseJSON(resp);

                    if (nomsg != true) {
                        PFlist.displayMsg(resp);
                    }
                }
            },
            error: function(resp, e, msg)
            {
                PFlist.displayMsg(resp, msg);
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
    * Method to display the ajax response messages
    *
    * @param    object    resp    The ajax response object
    * @param    string    err     The error message
    */
    displayMsg: function(resp, err)
    {
        var mc = jQuery('#system-message-container');

        if (typeof mc == 'undefined') {
            return false;
        }

        if (resp.length != 0 && typeof resp.length != 'undefined' && typeof resp.success != 'undefined') {
            if (resp.success == "true") {
                var msg_class = 'success';
            }
            else {
                var msg_class = 'error';
            }

            if (typeof resp.messages != 'undefined') {
                var l = resp.messages.length;
                var x = 0;

                if (l > 0) {
                    for (x = 0; x < l; x++)
                    {
                        mc.append('<div class="alert alert-' + msg_class + '"><a class="close" data-dismiss="alert" href="#">×</a>' + resp.messages[x] + '</div>');
                    }
                }
            }
        }
        else {
            if (typeof err != 'undefined') {
                mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>' + err + '</div>');
            }
            else {
                mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>Request failed!</div>');
            }
        }
    },


    displayException: function(msg)
    {
        var mc = jQuery('#system-message-container');

        if (typeof mc == 'undefined') {
            alert(msg);
        }
        else {
            mc.append(msg);
        }
    }
}
