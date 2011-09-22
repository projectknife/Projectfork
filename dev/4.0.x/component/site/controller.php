<?php
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class ProjectforkController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function display($cachable = false, $urlparams = false)
	{
		$cachable = true;
        $safeurlparams = array();

		parent::display($cachable, $safeurlparams);
		return $this;
	}
}
?>