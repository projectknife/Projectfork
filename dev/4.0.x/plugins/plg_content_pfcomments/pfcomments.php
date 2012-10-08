<?php
/**
 * @package      Projectfork Comments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Comments plugin.
 *
 */
class plgContentPfcomments extends JPlugin
{
    /**
     * The current item context
     *
     * @var    string
     */
    protected $item_context;

    /**
     * The current item id
     *
     * @var    integer
     */
    protected $item_id;


    /**
     * Triggers the comment plugin after/below the item output.
     *
     * @param     string     $context       The current item context
     * @param     object     $item          The actual item data
     * @param     object     $params        The item parameters
     * @param     integer    $limitstart    Optional list limit (not being used though)
     *
     * @return    string                    The comment form and user comments HTML
     */
    public function onContentAfterDisplay($context, &$item, &$params, $ls = 0)
    {
        // List of valid contexts.
        // The context tells us which kind of item we're dealing with.
        $context_items = array('com_projectfork.project',
                               'com_projectfork.milestone',
                               'com_projectfork.task',
                               'com_projectfork.user',
                               'com_projectfork.note'
                              );

        // Check if the context is supported. Return empty string if its not.
        if (!in_array($context, $context_items)) {
            return '';
        }

        // Check if the plugin is disabled. Return empty string if it is.
        if (!JPluginHelper::isEnabled('content', 'pfcomments')) {
            return '';
        }

        // Dont show comments through the plugin if the output is not in HTML
        if (JFactory::getDocument()->getType() != 'html') {
            return '';
        }

        // Assign protected vars
        $this->item_context = $context;
        $this->item_id      = (isset($item->id) ? intval($item->id) : 0);

        // Add include paths
        JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/html');

        // Load comments JS
        JHtml::_('projectfork.script.comments');

        return $this->display();
    }


    public function display()
    {
        $doc  = JFactory::getDocument();

        $url  = 'index.php?option=com_projectfork&view=comments';
        $url .= '&filter_context=' . $this->item_context;
        $url .= '&filter_item_id=' . $this->item_id;
        $url .= '&tmpl=component';

        $js = array();
        $js[] = "jQuery(document).ready(function()";
        $js[] = "{";
        $js[] = "    jQuery.ajax(";
        $js[] = "    {";
        $js[] = "        url: '" . $url . "',";
        $js[] = "        type: 'GET',";
        $js[] = "        success: function(resp)";
        $js[] = "        {";
        $js[] = "            jQuery('#comments').append(resp);";
        $js[] = "            var comments = PFcomments.init();";
        $js[] = "        }";
        $js[] = "    });";
        $js[] = "});";

        $doc->addScriptDeclaration(implode("\n", $js));

        return '<div class="items-more" id="comments"></div>';
    }
}
