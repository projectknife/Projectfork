<?php
/**
* @package      Projectfork Timesheet Module
*
* @author       ANGEK DESIGN (Kon Angelopoulos)
* @copyright    Copyright (C) 2013 ANGEK DESIGN. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


/**
 * Module helper class
 *
 */
abstract class modPFtimeHelper
{
    /**
     * Method to get a list of tasks
     *
     * @return    array    $items    The tasks
     */
    public static function getItems($params)
    {
		$user  	= JFactory::getUser();
		
        JLoader::register('PFtimeModelTimesheet', JPATH_SITE . '/components/com_pftime/models/timesheet.php');

        $model = JModelLegacy::getInstance('Timesheet', 'PFtimeModel', array('ignore_request' => true));

        // Set application parameters in model
		
		$app = JFactory::getApplication();
		$appParams = $app->getParams();
		
		$model->setState('params', $appParams);
		
		if (intval($params->get('filter_own')) == 1) {
			$model->setState('filter.author',$user->get('id'));
		}
        // Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 5));

		$project = PFApplicationHelper::getActiveProjectId();
		$model->setState('filter.project', $project);
 	
        $items = $model->getItems();
	
        return $items;		
    }	
}
