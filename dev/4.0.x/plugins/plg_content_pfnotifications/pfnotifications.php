<?php
/**
 * @package      Projectfork Notifications
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Projectfork Notifications plugin.
 *
 */
class plgContentPfnotifications extends JPlugin
{
    /**
     * The item before it is saved/updated
     *
     * @var    object
     */
    protected $before;

    /**
     * The item after it has been saved/updated
     *
     * @var    object
     */
    protected $after;


    public function onContentBeforeSave($context, $table, $is_new = false)
    {

    }


    public function onContentAfterSave()
    {

    }
}