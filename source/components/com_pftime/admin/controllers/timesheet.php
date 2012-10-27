<?php
/**
 * @package      Projectfork
 * @subpackage   Timetracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controlleradmin');


/**
 * Timesheet controller class.
 *
 */
class PFtimeControllerTimesheet extends JControllerAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = "COM_PROJECTFORK_TIMESHEET";


    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the PHP class name.
     * @return    jmodel
     */
    public function getModel($name = 'Time', $prefix = 'PFtimeModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }
}
