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
    }
}