var Projectfork =
{
    /**
     * Function to watch an item
     *
     * @param    integer   i      The item number
     * @param    string    v      The name of the view
     * @param    string    fi     The form id (optional)
     * @param    string    nomsg  If set to true, will suppress success messages
     */
    watchItem: function(i, v, fi, nomsg)
    {
        var cid  = 'cb' + i;
        var c    = jQuery('#watch-' + v + '-' + i);
        var btn  = jQuery('#watch-btn-' + v + '-' + i);

        if (btn.length) {
            if (btn.hasClass('disabled') == true) {
                return;
            }
        }

        btn.addClass('disabled');

        if (c.val() == '1') {
            var act = v + '.unwatch';
            var rq  = PFlist.listItemTask(cid, act, fi, true);
        }
        else {
            var act = v + '.watch';
            var rq  = PFlist.listItemTask(cid, act, fi, true);
        }

        rq.done(function(resp)
        {
            if (Projectfork.isJsonString(resp)) {
                resp = jQuery.parseJSON(resp);

                if (btn.length && resp.success == "true") {
                    if (c.val() == '0') {
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
            }
            else {
                btn.addClass('btn-danger');
            }

            btn.removeClass('disabled');
        });
    },

    isJsonString: function(str)
    {
        if (typeof str == 'undefined') {
            return false;
        }

        var l = str.length;
        var e = l - 1;

        if (l == 0) {
            return false;
        }

        if (str[0] != '{' && str[0] != '[') {
            return false;
        }

        if (str[e] != '}' && str[e] != ']') {
            return false;
        }

        return true;
    }
}