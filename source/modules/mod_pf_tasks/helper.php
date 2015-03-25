<?php
/**
* @package      Projectfork Tasks
*
* @author       Tobias Kuhn (eaxs)
* @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


/**
 * Module helper class
 *
 */
abstract class modPFtasksHelper
{
    /**
     * Method to get a list of tasks
     *
     * @return    array    $items    The tasks
     */
    public static function getItems($params)
    {
        JLoader::register('PFtasksModelTasks', JPATH_SITE . '/components/com_pftasks/models/tasks.php');

        $model = JModelLegacy::getInstance('Tasks', 'PFtasksModel', array('ignore_request' => true));

        // Set application parameters in model
		$app = JFactory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

        // Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 10));
		$model->setState('filter.published', 1);

        // Set project filter
        if (!(int) $params->get('tasks_of')) {
            $model->setState('filter.project', PFApplicationHelper::getActiveProjectId());
        }
        else {
            $project = (int) $params->get('project');
            if ($project) {
                $model->setState('filter.project', $project);
            }
            else {
                $model->setState('filter.project', PFApplicationHelper::getActiveProjectId());
            }
        }

        // Set completition filter
        $model->setState('filter.complete', $params->get('filter_complete'));

        // Sort and order
        $model->setState('list.ordering', $params->get('sort'));
		$model->setState('list.direction', $params->get('order'));

        $items = $model->getItems();

        return $items;
    }
}
