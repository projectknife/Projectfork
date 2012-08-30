var Projectfork =
{
    bulkAction: function(el)
    {
        var idx    = el.selectedIndex;
        var action = el.options[idx].value;

        if(action.length > 0) Joomla.submitbutton(action);
    },


    postComment: function(parent_id)
    {
        var cform = jQuery(document.commentForm);
        var id    = jQuery('input[name|="id"]', cform).val();
        var task  = jQuery('input[name|="task"]', cform).val();

        jQuery('input[name|="task"]', cform).val('commentform.apply');
        jQuery('#jform_parent_id', cform).val(parent_id);

        var form_data = cform.serializeArray();

        jQuery('#jform_description_' + parent_id, cform).attr('disabled', true);

        jQuery.ajax(
        {
            url: cform.attr('action'),
            data: jQuery.param(form_data),
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'json',
            success: function(resp)
            {
                jQuery('#jform_description_' + parent_id, cform).attr('disabled', false);
                jQuery('#jform_description_' + parent_id, cform).val('');

                if (parent_id > 0) {
                    jQuery('#comment-editor-' + parent_id).hide("fast");
                }

                if(resp.data) {
                    if (task == 'commentform.edit') {
                        var cItem = jQuery('#comment-item-' + parent_id + ' .comment-item').first();
                        cItem.empty();

                        if(cItem.length > 0) {
                            jQuery('input[name|="task"]', cform).val('');
                            jQuery('input[name|="id"]', cform).val('0');
                            cItem.html(resp.data);
                        }
                    }
                    else {
                        var cNode = jQuery('#comment-node-' + parent_id);

                        if(cNode.length > 0) {
                            cNode.append(resp.data);
                        }
                        else {
                            jQuery('#comment-item-' + parent_id).append(resp.data);
                        }
                    }

                }
            },
            error: function(resp, e, msg)
            {
                jQuery('#jform_description_' + parent_id, cform).attr('disabled', false);
                jQuery('#jform_description_' + parent_id, cform).val('');

                if(msg.length > 0) {
                    alert(msg);
                }
                else {
                    alert(resp.message);
                }
            }
        });
    },


    cancelComment: function(parent_id)
    {
        var cform = jQuery(document.commentForm);
        var task  = jQuery('input[name|="task"]', cform).val();
        var id    = jQuery('input[name|="id"]', cform).val();

        if (task == 'commentform.edit' && id > 0) {
            jQuery('input[name|="task"]', cform).val('commentform.cancel');

            var form_data = cform.serializeArray();

            jQuery.ajax(
            {
                url: cform.attr('action'),
                data: jQuery.param(form_data),
                type: 'POST',
                processData: true,
                cache: false,
                dataType: 'json',
                success: function(resp)
                {
                    if(resp.data) {
                        var cItem = jQuery('#comment-item-' + parent_id + ' .comment-item').first();
                        cItem.empty();

                        if(cItem.length > 0) {
                            jQuery('input[name|="task"]', cform).val('');
                            jQuery('input[name|="id"]', cform).val('0');
                            cItem.html(resp.data);
                        }
                    }
                },
                error: function(resp, e, msg)
                {
                    if(msg.length > 0) {
                        alert(msg);
                    }
                    else {
                        alert(resp.message);
                    }
                }
            });
        }
        else {
            if (parent_id > 0) {
                jQuery('#comment-editor-' + parent_id).remove();
            }
            else {
                jQuery('#jform_description_' + parent_id).val('');
            }
        }
    },


    showEditor: function(parent_id)
    {
        var cform = jQuery(document.commentForm);

        jQuery('input[name|="task"]', cform).val('commentform.loadEditor');
        jQuery('#jform_parent_id', cform).val(parent_id);

        var form_data = cform.serializeArray();

        jQuery.ajax(
        {
            url: cform.attr('action'),
            data: jQuery.param(form_data),
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'json',
            success: function(resp)
            {
                if(resp.data) {
                    var cNode = jQuery('#comment-node-' + parent_id);

                    if(cNode.length > 0) {
                        cNode.prepend(resp.data);
                    }
                    else {
                        jQuery('#comment-item-' + parent_id).append(resp.data);
                    }
                }
                else {
                    alert(resp.message);
                }
            },
            error: function(resp, e, msg)
            {
                if(msg.length > 0) {
                    alert(msg);
                }
                else {
                    alert(resp.message);
                }
            }
        });
    },


    editComment: function(id)
    {
        var cform = jQuery(document.commentForm);

        jQuery('input[name|="task"]', cform).val('commentform.edit');
        jQuery('input[name|="id"]', cform).val(id);

        var form_data = cform.serializeArray();

        jQuery.ajax(
        {
            url: cform.attr('action'),
            data: jQuery.param(form_data),
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'json',
            success: function(resp)
            {
                if(resp.data) {
                    jQuery('#comment-item-' + id + " .comment-item").first().html(resp.data);
                }
                else {
                    alert(resp.message);
                }
            },
            error: function(resp, e, msg)
            {
                if(msg.length > 0) {
                    alert(msg);
                }
                else {
                    alert(resp.message);
                }
            }
        });
    },


    trashComment: function(id)
    {
        var cform = jQuery(document.commentForm);

        jQuery('input[name|="task"]', cform).val('comments.trash');
        jQuery('input[name|="id"]', cform).val(id);

        var form_data = cform.serializeArray();

        jQuery.ajax(
        {
            url: cform.attr('action'),
            data: jQuery.param(form_data),
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'json',
            success: function(resp)
            {
                if(resp.success == true) {
                    var cNode = jQuery('#comment-node-' + id);
                    var cItem = jQuery('#comment-item-' + id);

                    if (cNode.length > 0) {
                        jQuery('#comment-node-' + id).remove();
                    }

                    if (cItem.length > 0) {
                        jQuery('#comment-item-' + id).remove();
                    }
                }
                else {
                    alert(resp.message);
                }
            },
            error: function(resp, e, msg)
            {
                if(msg.length > 0) {
                    alert(msg);
                }
                else {
                    alert(resp.message);
                }
            }
        });

        jQuery('input[name|="task"]', cform).val('');
        jQuery('input[name|="id"]', cform).val('0');
    }



}