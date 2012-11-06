/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


/**
 * A collection of task related functions
 *
 */
var PFtask =
{
    /**
     * Function to mark a task as complete/incomplete
     *
     * @param  integer   i     The item number
     * @param  string    fi    The form id (optional)
     */
    complete: function(i, fi)
    {
        var cid  = 'cb' + i;
        var btn  = jQuery('#complete-btn-' + i);
        var c    = jQuery('#complete' + i);

        btn.addClass('disabled');
        var rq = PFlist.listItemTask(cid, 'tasks.complete', fi, true);

        rq.done(function(resp)
        {
            btn.removeClass('disabled');

            if (resp != false) {
                btn.removeClass('btn-danger');

                var v = c.val();

                if (v == '0') {
                    c.val('1');
                    btn.addClass('btn-success');
                    btn.addClass('active');
                }
                else {
                    c.val('0');
                    btn.removeClass('btn-success');
                    btn.removeClass('active');
                }
            }
            else {
                btn.addClass('btn-danger');
            }
        });
    },

    priority: function(i, v, t, fi)
    {
        var cid  = 'cb' + i;
        var p    = jQuery('#priority' + i);

        p.val(v);

        var rq = PFlist.listItemTask(cid, 'tasks.priority', fi, true);

        rq.done(function(resp)
        {
            if (resp != false) {
                var l = jQuery('#priority_' + i + '_label');

                if (l.length) {
                    if (!l.hasClass('label')) {
                        l.addClass('label');
                    }

                    l.removeClass('label-success');
                    l.removeClass('label-info');
                    l.removeClass('label-warning');
                    l.removeClass('label-important');

                    if (v > 1) {
                        if (v == 2) {
                            l.addClass('label-success');
                        }
                        if (v == 3) {
                            l.addClass('label-info');
                        }
                        if (v == 4) {
                            l.addClass('label-warning');
                        }
                        if (v == 5) {
                            l.addClass('label-important');
                        }

                        l.html('<i class="icon-warning"></i> ' + t);
                    }
                    else {
                        l.removeClass('label');
                        l.html('');
                    }
                }
            }
        });
    }
}