<?php
/**
* @package      Projectfork
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


/**
 * Abstract class for Projectfork HTML elements
 *
 */
abstract class JHtmlProjectfork
{
    /**
     * Renders an input field with a select button for choosing a project
     *
     * @param     int       $value         The state value
     * @param     bool      $can_change    
     *
     * @return    string                   The input field html
     */
    public static function filterProject($value = 0, $can_change = true)
    {
        JHtml::_('behavior.modal', 'a.modal');

        $doc = JFactory::getDocument();
        $app = JFactory::getApplication();

        // Get currently active project data
        $active_id    = (int) $app->getUserState('com_projectfork.project.active.id', 0);
        $active_title = $app->getUserState('com_projectfork.project.active.title', '');

        if (!$active_title) $active_title = JText::_('COM_PROJECTFORK_SELECT_PROJECT');

        // Set the JS functions
        $link = 'index.php?option=com_projectfork&amp;view=projects&amp;layout=modal&amp;tmpl=component&amp;function=pfSelectActiveProject';
        $rel  = "{handler: 'iframe', size: {x: 800, y: 450}}";

        $js_clear = 'document.id(\'filter_project_title\').value = \'\';'
                  . 'document.id(\'filter_project\').value = \'0\';'
                  . 'this.form.submit();';

        $js_select = 'SqueezeBox.open(\'' . $link.'\', ' . $rel.');';

        $js_head = "
        function pfSelectActiveProject(id, title) {
            document.getElementById('filter_project').value = id;
            document.getElementById('filter_project_title').value = title;
            SqueezeBox.close();
            Joomla.submitbutton('');
        }";
        $doc->addScriptDeclaration($js_head);

        // Setup the buttons
        $btn_clear = '';
        if ($active_id && $can_change) {
            $btn_clear = '<button type="button" class="btn" onclick="' . $js_clear.'"><i class="icon-remove"></i> '.JText::_('JSEARCH_FILTER_CLEAR').'</button>';
        }

        $btn_select = '';
        if ($can_change) {
            $btn_select = '<button type="button" class="btn" onclick="' . $js_select.'" title="'.JText::_('JSELECT').'"><i class="icon-briefcase"></i> ' . $active_title.'</button>';
        }

        // HTML output
        $html = '<span class="btn-group">'
                . $btn_select
                . $btn_clear
                . '</span>'
                .'<span class="btn-group">'
                . '<input type="hidden" name="filter_project_title" id="filter_project_title" class="btn disabled input-small" readonly="readonly" value="' . $active_title.'" />'
                . '<input type="hidden" name="filter_project" id="filter_project" value="' . $active_id.'" />'
                . '</span>';

        return $html;
    }


    /**
     * Translates a numerical priority value to a string label
     *
     * @param     int       $value    The priority
     *
     * @return    string    $html     The corresponding string label
     */
    public static function priorityToString($value = 0)
    {
        switch((int) $value)
        {
            case 1:
                $class = 'label-success very-low-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_VERY_LOW');
                break;

            case 2:
                $class = 'label-success low-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_LOW');
                break;

            case 3:
                $class = 'label-info medium-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_MEDIUM');
                break;

            case 4:
                $class = 'label-warning high-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_HIGH');
                break;

            case 5:
                $class = 'label-important very-high-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_VERY_HIGH');
                break;

            default:
                $class = 'label-success very-low-priority';
                $text  = JText::_('COM_PROJECTFORK_PRIORITY_VERY_LOW');
                break;
        }

        $html = '<span class="label ' . $class.'">' . $text.'</span>';

        return $html;
    }


    /**
     * Returns priority select list option objects
     *
     * @return    array    $options    The object list
     */
    public static function priorityOptions()
    {
        $options   = array();
        $options[] =  JHtml::_('select.option', '1', JText::_('COM_PROJECTFORK_PRIORITY_VERY_LOW'));
        $options[] =  JHtml::_('select.option', '2', JText::_('COM_PROJECTFORK_PRIORITY_LOW'));
        $options[] =  JHtml::_('select.option', '3', JText::_('COM_PROJECTFORK_PRIORITY_MEDIUM'));
        $options[] =  JHtml::_('select.option', '4', JText::_('COM_PROJECTFORK_PRIORITY_HIGH'));
        $options[] =  JHtml::_('select.option', '5', JText::_('COM_PROJECTFORK_PRIORITY_VERY_HIGH'));

        return $options;
    }


    /**
     * Method to format a floating point value according the configured monetary settings
     *
     * @param     float    $value      The amount of money
     * @param     int      $project    Optional project id from which to use the settings
     *
     * @return    array    $options    The object list
     */
    public static function moneyFormat($value = 0.00, $project = 0)
    {
        $value  = (float) $value;
        $params = ProjectforkHelper::getProjectParams((int) $project);

        $nf_dec   = $params->get('decimal_delimiter', '.');
        $nf_th    = $params->get('thousands_delimiter', ',');
        $currency = $params->get('currency_sign', '$');

        $html = array();

        if ($params->get('currency_position') == '0') {
            $html[] = $currency . '&nbsp;';
        }

        $html[] = number_format($value, 2, $nf_dec, $nf_th);

        if ($params->get('currency_position') == '1') {
            $html[] = '&nbsp;' . $currency;
        }

        return implode('', $html);
    }


    /**
     * Returns a truncated text. Also strips html tags
     *
     * @param     string    $text     The text to truncate
     * @param     int       $chars    The new length of the string
     *
     * @return    string              The truncated string
     */
    public static function truncate($text = '', $chars = 40)
    {
        $truncated = strip_tags($text);
        $length    = strlen($truncated);

        if (($length + 3) < $chars) return $truncated;

        return substr($truncated, 0, $chars).'...';
    }


    /**
     * Adds a JS script declaration to the doc header which enables
     * ajax reordering of list items.
     *
     * @param     string    $list    The CSS list id selector
     * @param     string    $view    The component view
     *
     * @return    void               
     */
    public static function ajaxReorder($list, $view)
    {
        $doc = JFactory::getDocument();
        $js  = array();

        $js[] = "window.addEvent('domready', function() {";
        $js[] = "    new Sortables('$list', {";
        $js[] = "        clone:false,";
        $js[] = "        constrain:true,";
        $js[] = "        revert: true,";
        $js[] = "        handle:'.icon-move',";
        $js[] = "        onStart: function(el, clone) {el.erase('style'); el.set('class', 'alert-info')},";
        $js[] = "        onComplete: function(el) {";
        $js[] = "            var order_array = new Array();";
        $js[] = "            var cid_array   = new Array();";
        $js[] = "            var i  = 0;";
        $js[] = "            $$('#$list li').each(function(li) {";
        $js[] = "                if (li.get('alt')) {";
        $js[] = "                    cid_array[i]   = 'cid[]=' + li.get('alt');";
        $js[] = "                    order_array[i] = 'order[]=' + i;";
        $js[] = "                    i++;";
        $js[] = "                }";
        $js[] = "            });";
        $js[] = "            var order = order_array.join('&');";
        $js[] = "            var cid   = cid_array.join('&');";
        $js[] = "            var token = '" . JSession::getFormToken() . "=1'";
        $js[] = "            ";
        $js[] = "            var req = new Request({";
        $js[] = "                url:'" . htmlspecialchars(JFactory::getURI()->toString()) . "',";
        $js[] = "                method:'post',";
        $js[] = "                autoCancel:true,";
        $js[] = "                format:'json',";
        $js[] = "                data:'option=com_projectfork&task=" . $view . ".saveorder&' + cid + '&' + order + '&tmpl=component&' + token,";
        $js[] = "                onSuccess: function(responseText, responseXML) {";
        $js[] = "                    var resp = JSON.decode(responseText);";
        $js[] = "                    if (resp.success == true) {";
        $js[] = "                        el.morph('.alert-success', {duration: 500});";
        $js[] = "                    } else {";
        $js[] = "                        el.morph('.alert-error', {duration: 500});";
        $js[] = "                        alert(resp.message);";
        $js[] = "                    }";
        $js[] = "                },";
        $js[] = "                onFailure: function(xhr) {";
        $js[] = "                    el.morph('.alert-error', {duration: 500});";
        $js[] = "                    alert('Reorder failed. Request returned: ' + xhr);";
        $js[] = "                },";
        $js[] = "                onException: function(headerName, value) {";
        $js[] = "                    el.morph('.alert-error', {duration: 500});";
        $js[] = "                    alert('Reorder failed. Header exception: ' + headerName + ' - ' + value);";
        $js[] = "                }";
        $js[] = "            }).send();";
        $js[] = "        }";
        $js[] = "    })";
        $js[] = "});";

        $doc->addScriptDeclaration(implode("\n", $js));
    }


    /**
     * Adds a JS script declaration to the doc header which enables
     * ajax based task completition through checkboxes.
     *
     * @return    void    
     */
    public static function ajaxCompleteTask()
    {
        $doc = JFactory::getDocument();
        $js  = array();

        $js[] = "function setTaskComplete(tid, complete) {";
        $js[] = "    var token = '" . JSession::getFormToken() . "=1';";
        $js[] = "    var el = document.id('task-'+tid);";
        $js[] = "    if (complete == true) {var cv = 1;} else { var cv = 0;}";
        $js[] = "    var req = new Request({";
        $js[] = "        url:'". htmlspecialchars(JFactory::getURI()->toString()) . "',";
        $js[] = "        method:'post',";
        $js[] = "        autoCancel:true,";
        $js[] = "        format:'json',";
        $js[] = "        data:'option=com_projectfork&task=tasks.complete&cid[]='+tid+'&complete['+tid+']='+cv+'&tmpl=component&' + token,";
        $js[] = "        onSuccess: function(responseText, responseXML) {";
        $js[] = "            var resp = JSON.decode(responseText);";
        $js[] = "            if (resp.success == true) {";
        $js[] = "                if (complete == 1) {";
        $js[] = "                    el.set('class', 'task-complete');";
        $js[] = "                    var children = el.getElements('.btn-mini');";
        $js[] = "                    children.each(function(child, idx) { child.toggleClass('disabled'); });";
        $js[] = "                } else {";
        $js[] = "                    el.set('class', 'task-incomplete');";
        $js[] = "                    var children = el.getElements('.btn-mini');";
        $js[] = "                    children.each(function(child, idx) { child.toggleClass('disabled'); });";
        $js[] = "                }";
        $js[] = "            } else {";
        $js[] = "                el.morph('.alert-error', {duration: 500});";
        $js[] = "                alert(resp.message);";
        $js[] = "            }";
        $js[] = "        },";
        $js[] = "        onFailure: function(xhr) {";
        $js[] = "            el.morph('.alert-error', {duration: 500});";
        $js[] = "            alert('Update failed. Request returned: ' + xhr);";
        $js[] = "        },";
        $js[] = "        onException: function(headerName, value) {";
        $js[] = "            el.morph('.alert-error', {duration: 500});";
        $js[] = "            alert('Update failed. Header exception: ' + headerName + ' - ' + value);";
        $js[] = "        }";
        $js[] = "    }).send();";
        $js[] = "}";

        $doc->addScriptDeclaration(implode("\n", $js));
    }


    /**
     * Loads projectfork CSS files
     *
     * @return    void    
     */
    public static function CSS()
    {
        if (!defined('COM_PROJECTFORK_CSS')) {
            $params = JComponentHelper::getParams('com_projectfork');
            $doc    = JFactory::getDocument();
            $uri    = JFactory::getURI();

            if ($doc->getType() == 'html' && $params->get('projectfork_css', '1') == '1') {
                $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/projectfork/css/icons.css');
                $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/projectfork/css/layout.css');
                $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/projectfork/css/theme.css');
            }

            define('COM_PROJECTFORK_CSS', 1);
        }
    }


    /**
     * Loads projectfork JS files
     *
     * @return    void    
     */
    public static function JS()
    {
        if (!defined('COM_PROJECTFORK_JS')) {
            jimport( 'joomla.application.component.helper' );

            $doc  = JFactory::getDocument();
            $uri  = JFactory::getURI();

            if ($doc->getType() == 'html') {
                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/projectfork/js/projectfork.js');
                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/projectfork/js/comments.js');
            }

            define('COM_PROJECTFORK_JS', 1);
        }
    }


    /**
     * Loads bootstrap CSS files
     *
     * @return    void    
     */
    public static function boostrapCSS()
    {
        if (!defined('COM_PROJECTFORK_BOOSTRAP_CSS')) {
            $params = JComponentHelper::getParams('com_projectfork');
            $doc    = JFactory::getDocument();
            $uri    = JFactory::getURI();

            if ($doc->getType() == 'html' && $params->get('bootstrap_css', '1') == '1') {
                $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/bootstrap/css/bootstrap.min.css');
                $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/bootstrap/css/bootstrap-responsive.min.css');
            }

            define('COM_PROJECTFORK_BOOTSTRAP_CSS', 1);
        }
    }


    /**
     * Loads bootstrap JS files
     *
     * @return    void    
     */
    public static function boostrapJS()
    {
        if (!defined('COM_PROJECTFORK_BOOSTRAP_JS')) {
            $params = JComponentHelper::getParams('com_projectfork');
            $doc    = JFactory::getDocument();
            $uri    = JFactory::getURI();

            if ($doc->getType() == 'html' && $params->get('bootstrap_js', '1') == '1') {
                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/bootstrap/js/bootstrap.min.js');
            }

            define('COM_PROJECTFORK_BOOTSTRAP_JS', 1);
        }
    }


    /**
     * Loads bootstrap JS files
     *
     * @return    void    
     */
    public static function jQuery()
    {
        if (!defined('COM_PROJECTFORK_JQUERY')) {
            $params = JComponentHelper::getParams('com_projectfork');
            $doc    = JFactory::getDocument();
            $uri    = JFactory::getURI();

            if ($doc->getType() == 'html' && $params->get('jquery', '1') == '1') {
                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/jquery/jquery.min.js');
                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/jquery/jquery.noconflict.js');
            }

            define('COM_PROJECTFORK_JQUERY', 1);
        }
    }


    /**
     * Loads jquery-flot JS files
     *
     * @return    void    
     */
    public static function jQueryFlot()
    {
        if (!defined('COM_PROJECTFORK_JQUERY_FLOT')) {
            $doc = JFactory::getDocument();
            $uri = JFactory::getURI();

            if ($doc->getType() == 'html') {
                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/flot/jquery.flot.min.js');
                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/flot/jquery.flot.pie.min.js');
                $doc->addScript($uri->base(true).'/components/com_projectfork/assets/flot/jquery.flot.resize.min.js');
            }

            define('COM_PROJECTFORK_JQUERY_FLOT', 1);
        }
    }
}
