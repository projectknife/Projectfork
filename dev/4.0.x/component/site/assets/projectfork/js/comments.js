var PFcomments =
{
    cancel: function()
    {
        var editor = jQuery('#comment-editor').detach();
        var root   = jQuery('#comment-node-0');

        root.after(editor);

        jQuery('#jform_parent_id').val(0);
    },

    save: function()
    {
        var f = jQuery('#commentForm');
        var c = jQuery('#jform_description', f);
        var t = jQuery('input[name|="task"]', f);

        if (jQuery.trim(c) == '') {
            alert('Please enter a description');
            return;
        }

        // Override the task value
        t.val('commentform.save');

        // Serialize the form
        var d = f.serializeArray();

        // empty the comment text
        c.val('');

        // Do the ajax request
        jQuery.ajax(
        {
            url: f.attr('action'),
            data: jQuery.param(d),
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'html',
            success: function(resp)
            {
                /*var mc   = jQuery('#system-message-container');
                mc.append(resp);
                return true;*/
                resp  = jQuery.parseJSON(resp);

                if (typeof resp.messages != 'undefined') {
                    var msgs = resp.messages;
                    var mc   = jQuery('#system-message-container');
                    var l    = msgs.length;
                    var x    = 0;

                    if (resp.success == "true") {
                        if (l > 0) {
                           for (x = 0; x < l; x++)
                           {
                               mc.append('<div class="alert alert-success"><a class="close" data-dismiss="alert" href="#">×</a>' + msgs[x] + '</div>');
                           }
                        }
                    }
                    else {
                        if (l > 0) {
                           for (x = 0; x < l; x++)
                           {
                               mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>' + msgs[x] + '</div>');
                           }
                        }
                    }
                }
            },
            error: function(resp, e, msg)
            {
                var mc = jQuery('#system-message-container');
                if(msg.length > 0) {
                    mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>' + msg + '</div>');
                }
                else {
                    mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>Request Failed!</div>');
                }
            },
            complete: function()
            {
                t.val('');

                // Move the editor back to its original position
                PFcomments.cancel();

                // Reload the comments
                PFcomments.reload();
            }
        });
    },

    add: function(event)
    {
        var i = event.data.i;
        var editor  = jQuery('#comment-editor').detach();
        var item    = jQuery('#comment-item-' + i);
        var content = jQuery('.comment-content', item);
        var cb      = jQuery('#cb' + i).val();

        content.append(editor);

        jQuery('#jform_parent_id').val(cb);
    },

    trash: function(event)
    {
        var i  = event.data.i;
        var f = jQuery('#commentForm');
        var t = jQuery('input[name|="task"]', f);

        // Check the box
        jQuery('#cb' + i).attr('checked', true);

        // Override the task value
        t.val('comments.trash');

        // Serialize the form
        var d = f.serializeArray();

        // Do the ajax request
        jQuery.ajax(
        {
            url: f.attr('action'),
            data: jQuery.param(d),
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'html',
            success: function(resp)
            {
                /*var mc   = jQuery('#system-message-container');
                mc.append(resp);
                return true;*/
                resp  = jQuery.parseJSON(resp);

                if (typeof resp.messages != 'undefined') {
                    var msgs = resp.messages;
                    var mc   = jQuery('#system-message-container');
                    var l    = msgs.length;
                    var x    = 0;

                    if (resp.success == "true") {
                        if (l > 0) {
                           for (x = 0; x < l; x++)
                           {
                               mc.append('<div class="alert alert-success"><a class="close" data-dismiss="alert" href="#">×</a>' + msgs[x] + '</div>');
                           }
                        }
                    }
                    else {
                        if (l > 0) {
                           for (x = 0; x < l; x++)
                           {
                               mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>' + msgs[x] + '</div>');
                           }
                        }
                    }
                }
            },
            error: function(resp, e, msg)
            {
                var mc = jQuery('#system-message-container');
                if(msg.length > 0) {
                    mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>' + msg + '</div>');
                }
                else {
                    mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>Request Failed!</div>');
                }
            },
            complete: function()
            {
                t.val('');

                // Move the editor back to its original position
                PFcomments.cancel();

                // Reload the comments
                PFcomments.reload();
            }
        });
    },

    init: function(reload)
    {
        var editor = jQuery('#comment-editor');
        var root   = jQuery('#comment-node-0');

        if (editor.length > 0) {
            if (typeof reload == 'undefined') {
                jQuery('#btn_comment_save', editor).click(this.save);
                jQuery('#btn_comment_cancel', editor).click(this.cancel);
            }
        }

        var btns_add   = jQuery('.btn-add-reply',   root);
        var btns_trash = jQuery('.btn-trash-reply', root);

        for(var it = 0; it < btns_add.length; it++)
        {
            var btn = jQuery(btns_add[it]);
            btn.bind('click', {i: it}, function(event){PFcomments.add(event);});
        }

        for(var it = 0; it < btns_trash.length; it++)
        {
            var btn = jQuery(btns_trash[it]);
            btn.bind('click', {i: it}, function(event){PFcomments.trash(event);});
        }
    },

    reload: function()
    {
        var rq = 'index.php?option=com_projectfork&view=comments';
        rq = rq + '&filter_context=' + jQuery('#jform_context').val();
        rq = rq + '&filter_item_id=' + jQuery('#jform_item_id').val();
        rq = rq + '&tmpl=component';
        rq = rq + '&layout=default_items';

        // Do the ajax request
        jQuery.ajax(
        {
            url: rq,
            type: 'GET',
            dataType: 'html',
            success: function(resp)
            {
                jQuery('#comment-node-0').empty();
                jQuery('#comment-node-0').append(resp);
            },
            error: function(resp, e, msg)
            {
                var mc = jQuery('#system-message-container');
                if(msg.length > 0) {
                    mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>' + msg + '</div>');
                }
                else {
                    mc.append('<div class="alert alert-error"><a class="close" data-dismiss="alert" href="#">×</a>Request Failed x!</div>');
                }
            },
            complete: function()
            {
                PFcomments.init(true);
            }
        });
    }
}