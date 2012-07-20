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
                //alert(resp);

                if(resp.data) {
                    var cNode = jQuery('#comment-node-' + parent_id);

                    if(cNode.length > 0) {
                        cNode.append(resp.data);
                    }
                    else {
                        jQuery('#comment-item-' + parent_id).append(resp.data);
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
        if (parent_id > 0) {
            jQuery('#comment-editor-' + parent_id).remove();
        }
        else {
            jQuery('#jform_description_' + parent_id).val('');
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
                        cNode.append(resp.data);
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


    editComment: function(id, parent_id)
    {
        var cform = jQuery(document.commentForm);

    }


}