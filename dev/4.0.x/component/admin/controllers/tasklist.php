<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controllerform');


class ProjectforkControllerTasklist extends JControllerForm
{
    /**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 */
    protected $text_prefix = "COM_PROJECTFORK_TASKLIST";


    /**
     * Class constructor.
     *
     * @param     array              $config    A named array of configuration variables
     * @return    jcontrollerform
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }
}
