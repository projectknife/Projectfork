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
        var e = jQuery('#' + fn + '_elements', f);
        var t = jQuery('input[name|="task"]', f);
        var v = jQuery('input[name|="view"]', f).val();

        // Set the value of the "elements" and "task" field
        e.val(fs);
        t.val(v + '.reload');

        // Serialize the form
        var d = f.serializeArray();

        // Do the ajax request
        jQuery.ajax(
        {
            url: f.attr('action'),
            data: jQuery.param(d) + '&tmpl=component&format=json',
            type: 'POST',
            processData: true,
            cache: false,
            dataType: 'html',
            success: function(resp)
            {
                resp = jQuery.parseJSON(resp);

                if (resp.success == "true") {
                    if (typeof resp.data != 'undefined') {
                        for(var i = 0; i < els.length; i++)
                        {
                            var eln = jQuery.trim(els[i]);
                            var elo = jQuery('#' + fn + '_' + eln + '_reload');
                            var eld = resp.data[eln];

                            if (elo.length > 0 && eld.length > 0) {
                                elo.empty();
                                elo.append(eld);
                            }
                        }
                    }
                }
            },
            error: function(resp, e, msg)
            {
                alert(msg);
            },
            complete: function()
            {
                e.val('');
                t.val('');
            }
        });
    },


    /**
     * Function that shows or hides the access level select list
     * depending on the selected access action
     *
     * @param    string    el    The access action list
     * @param    string    fn    The form name (Optional)
     */
    accessAction: function(el, fn)
    {
        if (typeof fn == 'undefined') {
            fn = 'jform';
        }

        if (typeof el == 'undefined') {
            el = '#' + fn + '_access_action';
        }

        var a = jQuery('#' + fn + '_access_element');
        var t = jQuery('#' + fn + '_access_title_element');

        if (a.length > 0) {
            if (jQuery(el).val() == '0') {
                a.show();
                t.hide();
            }
            else {
                a.hide();
                t.show();
            }
        }
    },


    /**
     * Function for selecting a group when creating a new
     * access level. This will also select all child groups
     * and apply the disabled state.
     *
     * @param    string    el    The group checkbox that was clicked
     */
    accessGroupToggle: function(el)
    {
        var cb = jQuery(el);
        var v  = cb.val();
        var c  = jQuery('input.childof-' + v);
        var l  = c.length;
        var i  = 0;

        if (cb.is(':checked')) {
            // Force select all child groups
            for(i = 0; i < l; i++)
            {
                var cbc = jQuery(c[i]);

                if (cbc.length) {
                    cbc.prop('checked', true);
                    cbc.prop('disabled', true);
                }
            }
        }
        else {
            // Release all child groups
            for(i = 0; i < l; i++)
            {
                var cbc = jQuery(c[i]);

                if (cbc.length) {
                    cbc.prop('checked', false);
                    cbc.prop('disabled', false);
                }
            }
        }
    },

    radio2btngroup: function()
    {
        // Turn radios into btn-group
        jQuery('.radio.btn-group label').addClass('btn');

        jQuery('.btn-group label:not(.active)').click(function()
        {
            var label = jQuery(this);
            var input = jQuery('#' + label.attr('for'));

            if (!input.prop('checked')) {
              label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');

              if (input.val()== '') {
                  label.addClass('active btn-primary');
              }
              else if (input.val() == 0) {
                  label.addClass('active btn-danger');
              }
              else {
                  label.addClass('active btn-success');
              }

              input.prop('checked', true);
          }
      });

      jQuery(".btn-group input[checked=checked]").each(function()
      {
          if (jQuery(this).val() == '') {
              jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-primary');
          }
          else if (jQuery(this).val() == 0) {
              jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-danger');
          }
          else {
              jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-success');
          }
      });
    }
}

