<?php
/**
 * @package      pkg_projectfork
 * @subpackage   plg_content_pfforum
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Forum Content Plugin Class
 *
 */
class plgContentPFforum extends JPlugin
{
    /**
     * Supported plugin contexts
     *
     * @var    array
     */
    protected $contexts = array(
        'com_pfprojects.project', 'com_pfprojects.form',
        'com_pfforum.topic', 'com_pfforum.topicform'
    );


    /**
     * "onContentAfterSave" event handler
     *
     * @param     string     $context    The item context
     * @param     object     $table      The item table object
     * @param     boolean    $is_new     New item indicator (True is new, False is update)
     *
     * @return    boolean                True
     */
    public function onContentAfterSave($context, $table, $is_new = false)
    {
        // Do nothing if the plugin is disabled
        if (!JPluginHelper::isEnabled('content', 'pfforum')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        // Do nothing if this is a new item
        if ($is_new) return true;

        $context = $this->unalias($context);

        // Update access
        $this->updateAccess($context, $table->id, $table->access);

        // Update publishing state
        $this->updatePubState($context, $table->id, $table->state);

        return true;
    }


    /**
     * "onContentChangeState" event handler
     *
     * @param     string     $context    The item context
     * @param     array      $pks        The item id's whose state was changed
     * @param     integer    $value      New state to which the items were changed
     *
     * @return    boolean                True
     */
    public function onContentChangeState($context, $pks, $value)
    {
        // Do nothing if the plugin is disabled
        if (!JPluginHelper::isEnabled('content', 'pfforum')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        $context = $this->unalias($context);

        // Update publishing state
        foreach ($pks AS $id)
        {
            $this->updatePubState($context, $id, $value);
        }

        return true;
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
        if (!JPluginHelper::isEnabled('content', 'pfforum')) return true;

        // Check if the context is supported
        if (!in_array($context, $this->contexts)) return true;

        $context = $this->unalias($context);

        $this->deleteFromContext($context, $table->id);

        return true;
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
            case 'com_pfprojects.project':
            case 'com_pfprojects.form':
                return 'com_pfprojects.project';
                break;

            case 'com_pfforum.topic':
            case 'com_pfforum.topicform':
                return 'com_pfforum.topic';
                break;
        }

        return $context;
    }


    /**
     * Method to delete all topics and replies from the given context
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
            JLoader::register('PFtableTopic', JPATH_ADMINISTRATOR . '/components/com_pfforum/tables/topic.php');
            JLoader::register('PFtableReply', JPATH_ADMINISTRATOR . '/components/com_pfforum/tables/reply.php');

            $imported = true;
        }

        $topic_table = JTable::getInstance('Topic', 'PFtable');
        $reply_table = JTable::getInstance('Reply', 'PFtable');

        if (!$topic_table || !$reply_table) return;

        $db      = JFactory::getDbo();
        $query   = $db->getQuery(true);
        $topics  = array();
        $replies = array();

        $fields = array(
            'com_pfprojects.project' => 'project_id',
            'com_pfforum.topic'      => 'topic_id'
        );

        // Get all replies
        $query->select('id')
              ->from('#__pf_replies')
              ->where($fields[$context] . ' = ' . (int) $id);

        $db->setQuery($query);
        $replies = (array) $db->loadColumn();

        // Get all topics
        if ($context != 'com_pfforum.topic') {
            $query->clear();
            $query->select('id')
                  ->from('#__pf_topics')
                  ->where($fields[$context] . ' = ' . (int) $id);

            $db->setQuery($query);
            $topics = (array) $db->loadColumn();
        }

        // Delete replies
        foreach ($replies AS $pk)
        {
            $reply_table->delete((int) $pk);
        }

        // Delete topics
        foreach ($topics AS $pk)
        {
            $topic_table->delete((int) $pk);
        }
    }


    /**
     * Method to update the publishing state of all topics and replies
     * associated with the given context
     *
     * @param     string     $context    The context name
     * @param     integer    $id         The context item id
     * @param     integer    $state      The new publishing state
     *
     * @return    void
     */
    protected function updatePubState($context, $id, $state)
    {
        // Do nothing on publish
        if ($state == '1') return;

        $fields = array(
            'com_pfprojects.project' => 'project_id',
            'com_pfforum.topic'      => 'topic_id'
        );

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Update replies
        $query->update('#__pf_replies')
              ->set('state = ' . $state)
              ->where($fields[$context] . ' = ' . (int) $id)
              ->where(($state == 0 ? 'state NOT IN(-2,0,2)' : 'state <> -2'));

        $db->setQuery($query);
        $db->execute();

        // Update topics
        if ($context != 'com_pfforum.topic') {
            $query->clear();
            $query->update('#__pf_topics')
                  ->set('state = ' . $state)
                  ->where($fields[$context] . ' = ' . (int) $id)
                  ->where(($state == 0 ? 'state NOT IN(-2,0,2)' : 'state <> -2'));

            $db->setQuery($query);
            $db->execute();
        }
    }


    /**
     * Method to update the access level of topics and replies
     * associated with the given context
     *
     * @param     string     $context    The context name
     * @param     integer    $id         The context id
     * @param     integer    $access     The access level
     *
     * @return    void
     */
    protected function updateAccess($context, $id, $access)
    {
        jimport('projectfork.library');

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $allowed = PFAccessHelper::getAccessTree($access);

        $fields = array(
            'com_pfprojects.project' => 'project_id',
            'com_pfforum.topic'      => 'topic_id'
        );

        // Update replies
        $query->update('#__pf_replies')
              ->set('access = ' . (int) $access)
              ->where($fields[$context] . ' = ' . (int) $id);

        if (count($allowed) == 1) {
            $query->where('access <> ' . (int) $allowed[0]);
        }
        elseif (count($allowed) > 1) {
            $query->where('access NOT IN( ' . implode(', ', $allowed) . ')');
        }

        $db->setQuery($query);
        $db->execute();


        if ($context == 'com_pfforum.topic') return;

        // Update topics
        $query->clear();
        $query->update('#__pf_topics')
              ->set('access = ' . (int) $access)
              ->where($fields[$context] . ' = ' . (int) $id);

        if (count($allowed) == 1) {
            $query->where('access <> ' . (int) $allowed[0]);
        }
        elseif (count($allowed) > 1) {
            $query->where('access NOT IN( ' . implode(', ', $allowed) . ')');
        }

        $db->setQuery($query);
        $db->execute();
    }
}
