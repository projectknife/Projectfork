<?php
/**
 * @package      pkg_projectfork
 * @subpackage   plg_content_pfcommments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
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
     * Supported plugin contexts
     *
     * @var    array
     */
    protected $contexts = array(
        'com_pfprojects.project',
        'com_pfmilestones.milestone',
        'com_pftasks.tasklist',
        'com_pftasks.task',
        'com_pfrepo.directory',
        'com_pfrepo.note',
        'com_pfdesigns.design',
        'com_pfdesigns.revision'
    );

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
        $context_items = array('com_pfprojects.project',
                               'com_pfmilestones.milestone',
                               'com_pftasks.task',
                               'com_pfusers.user',
                               'com_pfrepo.note',
                               'com_pfdesigns.design',
                               'com_pfdesigns.revision'
                              );

        // Check if the context is supported. Return empty string if its not.
        if (!in_array($context, $context_items)) return '';

        // Check if the plugin is disabled. Return empty string if it is.
        if (!JPluginHelper::isEnabled('content', 'pfcomments')) return '';

        // Dont show comments through the plugin if the output is not in HTML
        if (JFactory::getDocument()->getType() != 'html') return '';

        // Set context
        $this->item_context = $context;
        $this->item_id      = (isset($item->id) ? intval($item->id) : 0);

        // Load comments JS
        JHtml::_('pfhtml.script.comments');

        return $this->display();
    }


    /**
     * "onContentAfterDelete" event handler
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     *
     * @return    boolean                True
     */
    public function onContentAfterDelete($context, $table)
    {
        // Do nothing if the plugin is disabled
        if (!JPluginHelper::isEnabled('content', 'pfcomments')) return true;

        $context = $this->unalias($context);

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        $this->deleteFromContext($context, $table->id);

        return true;
    }


    public function display()
    {
        $doc  = JFactory::getDocument();

        $url  = 'index.php?option=com_pfcomments&view=comments';
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


    /**
     * Method to unalias the context
     *
     * @param     string    $context    The context alias
     *
     * @return    string    $context    The actual context
     */
    protected function unalias($context)
    {
        switch ($context)
        {
            case 'com_pfprojects.form':
                return 'com_pfprojects.project';
                break;

            case 'com_pfmilestones.form':
                return 'com_pfmilestones.milestone';
                break;

            case 'com_pftasks.tasklistform':
                return 'com_pftasks.tasklist';
                break;

            case 'com_pftasks.taskform':
                return 'com_pftasks.task';
                break;

            case 'com_pfrepo.directoryform':
                return 'com_pfrepo.directory';
                break;

            case 'com_pfrepo.noteform':
                return 'com_pfrepo.note';
                break;

            case 'com_pfdesigns.designform':
                return 'com_pfdesigns.design';
                break;

            case 'com_pfdesigns.revisionform':
                return 'com_pfdesigns.revision';
                break;
        }

        return $context;
    }


    /**
     * Method to delete all comments from the given context
     *
     * @param     string     $context    The context
     * @param     integer    $id         The context id
     *
     * @return    void
     */
    protected function deleteFromContext($context, $id)
    {
        static $imported = false;

        if (!$imported) {
            jimport('projectfork.library');
            JLoader::register('PFtableComment', JPATH_ADMINISTRATOR . '/components/com_pfcomments/tables/comment.php');

            $imported = true;
        }

        $table = JTable::getInstance('Comment', 'PFtable');

        if (!$table) return;

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Get comments
        $query->select('id')
              ->from('#__pf_comments')
              ->where('context = ' . $db->quote($context))
              ->where('item_id = ' . (int) $id)
              ->where('level = 1');

        $db->setQuery($query);
        $pks = (array) $db->loadColumn();

        // Delete comments
        foreach ($pks AS $pk)
        {
            $table->delete((int) $pk);
        }
    }
}
