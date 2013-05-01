/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */


/**
 * A collection of time recording related functions
 *
 */
var PFtimerec =
{
    fn: null,
    fe: null,
    te: null,
    pe: null,

    setForm: function(n)
    {
        PFtimerec.fn = n;
        PFtimerec.fe = jQuery('#' + n);
    },


    setTicker: function(tn, tpn)
    {
        PFtimerec.te = jQuery('#' + tn, PFtimerec.fe);
        PFtimerec.pe = jQuery('#' + tpn).children('div.bar');
    },


    save: function(i)
    {
        var cid = 'cb' + i;
        var btn = jQuery('#btn-rec-save-' + i);

        if (btn.hasClass('disabled')) return true;

        btn.addClass('disabled');
        var rq = PFlist.listItemTask(cid, 'recorder.save', PFtimerec.fn, true);

        rq.done(function(resp)
        {
            if (!Projectfork.isJsonString(resp)) return false;
            btn.removeClass('disabled');
            btn.removeClass('btn-primary');
            btn.addClass('btn-success');

            setTimeout(function()
            {
                btn.removeClass('btn-success');
                btn.addClass('btn-primary');
                jQuery('#rec-edit-' + i).collapse('hide');
            }, 2000);
        });

        rq.error(function(resp, e, msg)
        {
            btn.removeClass('disabled');
            btn.removeClass('btn-primary');
            btn.addClass('btn-danger');

            setTimeout(function()
            {
                btn.removeClass('btn-danger');
                btn.addClass('btn-primary');
            }, 2000);
        });
    },


    closeEdit: function(i)
    {
        jQuery('#rec-edit-' + i).collapse('hide');
    },


    remove: function(i, c)
    {
        jQuery('input[name|="complete"]', PFtimerec.fe).val(c);

        var cid = 'cb' + i;
        var rq  = PFlist.listItemTask(cid, 'recorder.delete', PFtimerec.fn, true);

        rq.done(function(resp)
        {
            if (!Projectfork.isJsonString(resp)) return false;

            jQuery('#rec-' + i).hide('fast', function() {this.remove();});
        });

        return rq;
    },


    togglePause: function(i)
    {
        var cid  = 'cb' + i;
        var btn  = jQuery('#btn-rec-state-' + i);
        var c    = jQuery('#rec-state-' + i);

        if (btn.hasClass('disabled')) return true;

        btn.addClass('disabled');
        var rq = PFlist.listItemTask(cid, 'recorder.pause', PFtimerec.fn, true);

        rq.done(function(resp)
        {
            if (!Projectfork.isJsonString(resp)) {
                btn.removeClass('disabled');
                btn.removeClass('btn-success');
                btn.addClass('btn-danger');
                return false;
            }

            btn.removeClass('disabled');
            btn.removeClass('btn-danger');

            if (parseInt(c.val()) == 0) {
                c.val(1);
                btn.removeClass('btn-success');
                btn.removeClass('active');
            }
            else {
                c.val(0);
                btn.addClass('btn-success');
                btn.addClass('active');
            }
        });

        rq.error(function(resp, e, msg)
        {
            btn.removeClass('btn-success');
            btn.removeClass('disabled');
            btn.addClass('btn-danger');
        });

        return rq;
    },


    pauseAll: function()
    {
        PFtimerec.setAll(0);
    },


    startAll: function()
    {
        PFtimerec.setAll(1);
    },


    setAll: function(s, qr, ir)
    {
        if (typeof s == 'undefined') s = 2;

        if (typeof qr == 'undefined') {
            var recs = jQuery('.recording', PFtimerec.fe);
            var i  = 0;
            var v  = 0;
            var q  = [];
            var f = false;

            if (recs.length == 0) return true;

            for(i = 0; i < recs.length; i++)
            {
                f = false;
                if (s == 2) {
                    f = true;
                }
                else {
                    v = parseInt(jQuery('#rec-state-' + i).val());

                    if (s == 1 && v > 0)  f = true;
                    if (s == 0 && v == 0) f = true;
                }

                if (f) q.push(i);
            }

            PFtimerec.setAll(s, q, 0);
        }
        else {
            PFtimerec.togglePause(qr[ir]).done(function(){
                ir++;
                if (qr.length > ir) {
                    PFtimerec.setAll(s, qr, ir);
                }
            });
        }
    },


    removeAll: function(c, qr, ir)
    {
        if (typeof qr == 'undefined') {
            var recs = jQuery('.recording', PFtimerec.fe);
            var i  = 0;
            var q  = [];

            if (recs.length == 0) return true;

            for(i = 0; i < recs.length; i++)
            {
                q.push(i);
            }

            PFtimerec.removeAll(c, q, 0);
        }
        else {
            PFtimerec.remove(qr[ir], c).done(function(){
                ir++;
                if (qr.length > ir) {
                    PFtimerec.removeAll(c, qr, ir);
                }
            });
        }
    },


    punch: function()
    {
        var btns = PFtimerec.fe.find('.btn-rec-state');

        if (btns.length) {
            btns.each(function(idx)
            {
                var btn = jQuery(this);

                if (btn.hasClass('active')) {
                    btn.addClass('disabled');
                    btn.children('i').removeClass('icon-play-2').addClass('icon-loop');
                }
            });

            var rq = PFlist.submitform('recorder.punch', PFtimerec.fn, true);

            rq.done(function(resp)
            {
                btns.each(function(idx)
                {
                    var btn = jQuery(this);

                    if (btn.hasClass('active')) {
                        btn.removeClass('disabled');
                        btn.children('i').removeClass('icon-loop').addClass('icon-play-2');
                    }
                });

                if (!Projectfork.isJsonString(resp)) return false;

                resp = jQuery.parseJSON(resp);

                if (resp.success == "true") {
                    if (typeof resp.data != 'undefined') {
                        jQuery.each(resp.data, function(i, v)
                        {
                            jQuery("#rec-time-" + i).empty().append(v);
                        });
                    }
                }
            });

            rq.error(function(resp, e, msg)
            {
                btns.each(function(idx)
                {
                    var btn = jQuery(this);

                    if (btn.hasClass('active')) {
                        btn.removeClass('disabled');
                        btn.children('i').removeClass('icon-loop').addClass('icon-play-2');
                    }
                });
            });
        }
    },

    tick: function()
    {
        var bs = PFtimerec.fe.find('.btn-rec-state');
        var v  = parseInt(PFtimerec.te.val());
        var a  = 0;

        if (bs.length) {
            bs.each(function(idx)
            {
                if (jQuery(this).hasClass('active')) a += 1;
            });
        }

        if (a) {
            v += 1;
        }
        else {
            v = 0;
        }

        if (v >= 60) {
            v = 0;
            PFtimerec.punch();
        }

        // Update the progress bar
        PFtimerec.pe.css('width', (v * 1.66) + '%');

        PFtimerec.te.val(v);
    }
}