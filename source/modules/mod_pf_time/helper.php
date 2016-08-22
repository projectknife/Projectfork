<?php
/**
* @package      Projectfork Timesheet Module
*
* @author       ANGEK DESIGN (Kon Angelopoulos)
* @copyright    Copyright (C) 2013 - 2015 ANGEK DESIGN. All rights reserved.
* @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
**/

defined('_JEXEC') or die();


/**
 * Module helper class
 *
 */
abstract class modPFtimeHelper
{
	public static function loadMedia()
    {
        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerPFtimeScript');
        }
    }
	public static function exportCSV()
	{
		$dispatcher	= JDispatcher::getInstance();
        $dispatcher->register('onBeforeCompileHead', 'prepareData');
	}

	public static function displayData()
	{
		$dispatcher	= JDispatcher::getInstance();
        $dispatcher->register('onBeforeRender', 'prepareData');
        prepareData();
	}
}

class moduleHelperPagination extends JPagination
{

	protected function _buildDataObject()
	{
		$data = new stdClass;

		// Build the additional URL parameters string.
		$params = '';

		if (!empty($this->additionalUrlParams))	{
			foreach ($this->additionalUrlParams as $key => $value) {
				$params .= '&' . $key . '=' . $value;
			}
		}

		$data->all = new JPaginationObject(JText::_('JLIB_HTML_VIEW_ALL'), $this->prefix);

		if (!$this->viewall) {
			$data->all->base = '0';
			$data->all->link = 'javascript:getData()';
		}

		// Set the start and previous data objects.
		$data->start 		= new JPaginationObject(JText::_('JLIB_HTML_START'), $this->prefix);
		$data->previous 	= new JPaginationObject(JText::_('JPREV'), $this->prefix);

		if ($this->pagesCurrent > 1) {
			$page 					= ($this->pagesCurrent - 2) * $this->limit;

			$data->start->base 		= '0';
			$data->start->link 		= 'javascript:getData(0)';
			$data->previous->base 	= $page;
			$data->previous->link 	= 'javascript:getData('.$page.')';
		}

		// Set the next and end data objects.
		$data->next 	= new JPaginationObject(JText::_('JNEXT'), $this->prefix);
		$data->end 		= new JPaginationObject(JText::_('JLIB_HTML_END'), $this->prefix);

		if ($this->pagesCurrent < $this->pagesTotal) {
			$next 		= $this->pagesCurrent * $this->limit;
			$end 		= ($this->pagesTotal - 1) * $this->limit;

			$data->next->base 	= $next;
			//$data->next->link = 'javascript:getData('.$next.')';
			$data->next->link 	= 'javascript:getData('.$next.')';
			$data->end->base 	= $end;
			$data->end->link 	= 'javascript:getData('.$end.')';
		}

		$data->pages = array();
		$stop = $this->pagesStop;

		for ($i = $this->pagesStart; $i <= $stop; $i++) {
			$offset = ($i - 1) * $this->limit;

			$data->pages[$i] = new JPaginationObject($i, $this->prefix);

			if ($i != $this->pagesCurrent || $this->viewall) {
				$data->pages[$i]->base 		= $offset;
				$data->pages[$i]->link 		= 'javascript:getData('.$offset.')';;
			}
			else {
				$data->pages[$i]->active 	= true;
			}
		}

		return $data;
	}

	public function getListFooter($ajax = false)
	{
		if(!$ajax) {
			return parent::getListFooter();
		}

		$app = JFactory::getApplication();

		$list 					= array();
		$list['prefix'] 		= $this->prefix;
		$list['limit'] 			= $this->limit;
		$list['limitstart'] 	= $this->limitstart;
		$list['total'] 			= $this->total;
		$list['limitfield'] 	= $this->getLimitBox();
		$list['pagescounter'] 	= $this->getPagesCounter();

		//override getPageLinks to introduce ajax pagination...
		$list['pageslinks'] 	= $this->getPagesLinks();

		$chromePath 			= JPATH_THEMES . '/' . $app->getTemplate() . '/html/pagination.php';

		if (file_exists($chromePath)) {
			include_once $chromePath;

			if (function_exists('pagination_list_footer')) {
				return pagination_list_footer($list);
			}
		}

		return $this->_list_footer($list);
	}

	public function getPagesLinks()
	{
		$app = JFactory::getApplication();

		// we need to override this so we can have ajax pagination.
		$data 			= $this->_buildDataObject();

		$list 			= array();
		$list['prefix'] = $this->prefix;

		$itemOverride 	= false;
		$listOverride 	= false;

		$chromePath 	= JPATH_THEMES . '/' . $app->getTemplate() . '/html/pagination.php';

		if (file_exists($chromePath)) {
			include_once $chromePath;

			if (function_exists('pagination_item_active') && function_exists('pagination_item_inactive')) {
				$itemOverride = true;
			}

			if (function_exists('pagination_list_render')) {
				$listOverride = true;
			}
		}

		// Build the select list
		if ($data->all->base !== null) {
			$list['all']['active'] 		= true;
			$list['all']['data'] 		= ($itemOverride) ? pagination_item_active($data->all) : $this->_item_active($data->all);
		}
		else {
			$list['all']['active'] 		= false;
			$list['all']['data'] 		= ($itemOverride) ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}

		if ($data->start->base !== null) {
			$list['start']['active'] 	= true;
			$list['start']['data'] 		= ($itemOverride) ? pagination_item_active($data->start) : $this->_item_active($data->start);
		}
		else {
			$list['start']['active'] 	= false;
			$list['start']['data'] 		= ($itemOverride) ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}

		if ($data->previous->base !== null) {
			$list['previous']['active'] = true;
			$list['previous']['data'] 	= ($itemOverride) ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
		}
		else {
			$list['previous']['active'] = false;
			$list['previous']['data'] 	= ($itemOverride) ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}

		// Make sure it exists
		$list['pages'] = array();

		foreach ($data->pages as $i => $page) {
			if ($page->base !== null) {
				$list['pages'][$i]['active'] 	= true;
				$list['pages'][$i]['data'] 		= ($itemOverride) ? pagination_item_active($page) : $this->_item_active($page);
			}
			else {
				$list['pages'][$i]['active'] 	= false;
				$list['pages'][$i]['data'] 		= ($itemOverride) ? pagination_item_inactive($page) : $this->_item_inactive($page);
			}
		}

		if ($data->next->base !== null) {
			$list['next']['active'] 			= true;
			$list['next']['data'] 				= ($itemOverride) ? pagination_item_active($data->next) : $this->_item_active($data->next);
		}
		else {
			$list['next']['active'] 			= false;
			$list['next']['data'] 				= ($itemOverride) ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}

		if ($data->end->base !== null) {
			$list['end']['active'] 				= true;
			$list['end']['data'] 				= ($itemOverride) ? pagination_item_active($data->end) : $this->_item_active($data->end);
		}
		else {
			$list['end']['active'] 				= false;
			$list['end']['data'] 				= ($itemOverride) ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}

		if ($this->total > $this->limit) {
			return ($listOverride) ? pagination_list_render($list) : $this->_list_render($list);
		}
		else {
			return '';
		}
	}
}

function triggerPFtimeScript()
{
    JHtml::_('script', 'mod_pf_time/jquery.fileDownload.js', false, true, false, false, false);
}

function displayData($items)
{
	jimport('joomla.html.pagination');

	$module 			= JModuleHelper::getModule('mod_pf_time');
	$params 			= new JRegistry();
	$params->loadString($module->params);

	$list_limit 		= (int) $params->get('list_limit');
	$show_monetary 		= (int) $params->get('show_monetary');
	$show_date 			= (int) $params->get('show_date');
	$show_author		= (int) $params->get('show_author');

	$menu 				= new PFMenuContext();
	$app 				= JFactory::getApplication();
	$limitstart 		= (int) JRequest::getVar('limitstart',0);
	$stotal_time		= 0;
	$total_time			= 0;

	$html ='
		<div class="cat-list-row">
		<ul class="list-tasks list-striped list-condensed unstyled" id="tasklist">';

	$x = 0;
	$k = 0;

    foreach ($items AS $i => $item) {
		$total_time += $item->log_time;
		if ($limitstart == 0) {
			if ($i > $list_limit - 1 ) continue;
		}
		else {
			if ( ($i < $limitstart) || ($i >= ($limitstart  + $list_limit)) ) continue;
		}

		$stotal_time += $item->log_time;

		$items[$i]->slug 			= $items[$i]->id;
		$items[$i]->project_slug 	= $items[$i]->project_alias ? ($items[$i]->project_id . ':' . $items[$i]->project_alias) : $items[$i]->project_id;
		$items[$i]->task_slug 		= $items[$i]->task_alias ? ($items[$i]->task_id . ':' . $items[$i]->task_alias) : $items[$i]->task_id;
		$items[$i]->milestone_slug 	= $items[$i]->milestone_alias ? ($items[$i]->milestone_id . ':' . $items[$i]->milestone_alias) : $items[$i]->milestone_id;
		$items[$i]->list_slug 		= $items[$i]->list_alias ? ($items[$i]->list_id . ':' . $items[$i]->list_alias) : $items[$i]->list_id;

		$exists 					= ((int) $item->task_exists > 0);
		$html .= '
			<li id="list-item-'.$x.'" class="clearfix ">
				<div class="task-row clearfix">
					<span class="task-title">';
						if ($exists) {
							$html .= '<a class="pull-left" href="'.JRoute::_(PFtasksHelperRoute::getTaskRoute($item->task_slug, $item->project_slug, $item->milestone_slug, $item->list_slug)).'"
						rel="popover" title="'. htmlspecialchars($item->task_title, ENT_QUOTES, "UTF-8").'" data-content="'. htmlspecialchars($item->description, ENT_QUOTES, "UTF-8").'">'
						 . htmlspecialchars($item->task_title, ENT_QUOTES, 'UTF-8'). '</a>
						<span class="dropdown pull-left">';

							$menu->start(array('class' => 'btn-mini btn-link'));
							$itm_icon = 'icon-menu-2';
							$itm_txt  = 'COM_PROJECTFORK_DETAILS_LABEL';
							$itm_link = '#collapse-' . $x;
							$menu->itemCollapse($itm_icon, $itm_txt, $itm_link);
							$menu->end();
							$html .= $menu->render(array('class' => 'btn-mini btn-link', 'pull' => 'left'));

						$html .= '</span>';
						}
						else {
							$html .=  htmlspecialchars($item->task_title, ENT_QUOTES, 'UTF-8');
						}
					$html .= '</span>
					<span class="pull-right">' . JHtml::_('time.format', $item->log_time). '</span>
				</div>
				<div id="collapse-'.$x.'" class="collapse">
					<hr />
					<small class="task-description">' . JText::_('COM_PROJECTFORK_FIELD_PROJECT_LABEL').':'.htmlspecialchars($item->project_title, ENT_COMPAT, 'UTF-8').'</small>';

					if ($show_author) {
						$html .= '<span class="label user">
							<span aria-hidden="true" class="icon-user icon-white"></span>' . htmlspecialchars($item->author_name, ENT_COMPAT, 'UTF-8') . '</span>
						<input type="hidden" id="assigned'. $i.'" name="assigned['.$item->id.']" />';
					}

					if ($show_date) {
						$html .= '<span class="label label-success"><span aria-hidden="true" class="icon-calendar"></span>' . JHtml::_('date', $item->log_date, JText::_('DATE_FORMAT_LC4')) . '</span>';
					}

					if ($show_monetary) {
						$html .= '<span class="label label-inverse">'.JText::_('COM_PROJECTFORK_TIME_TRACKING_RATE').': '.JHtml::_('pfhtml.format.money', $item->rate).'</span>';
						if (!$item->billable) {
							$html .= '<span class="label label-info">'.JText::_('COM_PROJECTFORK_TIME_TRACKING_UNBILLABLE').': '.JHtml::_('pfhtml.format.money', $item->billable_total).'</span>';
						}
						else {
							$html .= '<span class="label label-success">'.JText::_('COM_PROJECTFORK_TIME_TRACKING_BILLABLE').': '.JHtml::_('pfhtml.format.money', $item->billable_total).'</span>';
						}
					}

				$html .= '</div>
		   </li>';

		$x++;
		$k = 1 - $k;
	}

	$html .= '<li id="list-item-'.$x++.'" class="clearfix ">';
	$html .= '<div class="task-row clearfix">';
	if ($total_time == $stotal_time) {
		$html .= '			<span class="task-title">'.JText::_('MOD_PF_TIME_TOTAL').'</span><span class="pull-right">'.JHtml::_('time.format', $total_time).'</span>';
	}
	else {
		$html .= '			<span class="task-title">'.JText::_('MOD_PF_TIME_TOTAL').'</span><span class="pull-right">'.JHtml::_('time.format', $stotal_time).' / '.JHtml::_('time.format', $total_time).'</span>';
	}
	$html .= '</div></li>';
	$html .= '	</ul>
	</div>';
	$pageNav = new moduleHelperPagination( count($items), $limitstart, $list_limit );
	$html .= $pageNav->getListFooter(true);

	echo $html;
	$app->close();
}

function prepareData()
    {

	$module 			= JModuleHelper::getModule('mod_pf_time');
	$params 			= new JRegistry();
	$params->loadString($module->params);

	$action 			= JRequest::getVar('action',null);

	$filter_author 		= $params->get('filter_own',null);
	$filter_start_date 	= JRequest::getVar('filter_start_date',null);
	$filter_end_date 	= JRequest::getVar('filter_end_date',null);
	$filter_project 	= JRequest::getVar('filter_project',null);

	$app 				= JFactory::getApplication();
	$db 				= JFactory::getDBO();
	$query 				= $db->getQuery(true);
	$user  				= JFactory::getUser();
	$access 			= PFtasksHelper::getActions();
	$taskdata			= null;

	// Select the required fields from the table.
	$query->select(
			'a.id, a.project_id, a.task_id, a.task_title, a.description, '
			. 'a.checked_out, a.checked_out_time, a.state, a.access, a.rate, a.billable,'
			. 'a.created, a.created_by, a.log_date, a.log_time '
	);

	$query->from('#__pf_timesheet AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', '#__users AS uc ON uc.id = a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level');
        $query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author.
        $query->select('ua.name AS author_name');
        $query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Join over the projects for the project title.
        $query->select('p.title AS project_title, p.alias AS project_alias');
        $query->join('LEFT', '#__pf_projects AS p ON p.id = a.project_id');

        // Join over the tasks for the task title.
        $query->select('t.id AS task_exists, t.alias AS task_alias, t.estimate');
        $query->join('LEFT', '#__pf_tasks AS t ON t.id = a.task_id');

        // Join over the milestones for the milestone alias.
        $query->select('m.id AS milestone_id, m.alias AS milestone_alias');
        $query->join('LEFT', '#__pf_milestones AS m ON m.id = t.milestone_id');

        // Join over the task lists for the list alias.
        $query->select('l.id AS list_id, l.alias AS list_alias');
        $query->join('LEFT', '#__pf_task_lists AS l ON l.id = t.list_id');

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $levels = implode(',', $user->getAuthorisedViewLevels());

            $query->where('a.access IN (' . $levels . ')');
        }

        // Calculate billable amount
        $query->select('CASE WHEN (a.billable = 1 AND a.rate > 0 AND a.log_time > 0) '
                       . 'THEN ((a.log_time / 60) * (a.rate / 60)) '
                       . 'ELSE "0.00"'
                       . 'END AS billable_total');


	// Filter fields
	$filters 					= array();
	$filters['a.project_id'] 	= array('INT-NOTZERO', $filter_project );

	if ($filter_author == 1) {
		$filters['a.created_by'] 	= array('INT-NOTZERO', $user->get('id') );
	}

	// Apply Filter
	PFQueryHelper::buildFilter($query, $filters);

	//finally add our own date range filter
	if ($filter_start_date && $filter_end_date) {
		$query->where("a.log_date between '$filter_start_date' AND '$filter_end_date'");
	}
	else if ($filter_start_date) {
		$query->where("a.log_date >= '$filter_start_date'");
	}
	else if ($filter_end_date) {
		$query->where("a.log_date <= '$filter_end_date'");
	}

	// Add the list ordering clause.
	$order_col = 'a.log_date';
	$order_dir = 'desc';

	$query->order($db->escape($order_col . ' ' . $order_dir));
	$query->group('a.id');
	$db->setQuery($query);

	$taskdata 	= $db->loadObjectList();

	if ($action == 'export') {
		exportData($taskdata);
	}
	else {
		displayData($taskdata);
	}
}

function exportData($taskdata)
{
	$user 				= JFactory::getUser();
		$app = JFactory::getApplication();

	$module 			= JModuleHelper::getModule('mod_pf_time');
	$params 			= new JRegistry();
	$params->loadString($module->params);

	$show_monetary 		= (int) $params->get('show_monetary');
	$show_date 			= (int) $params->get('show_date');
	$show_author		= (int) $params->get('show_author');

	$csv_header = array(
		JText::_('MOD_PF_TIME_CSV_PROJECT'),
		JText::_('MOD_PF_TIME_CSV_TITLE'),
		JText::_('MOD_PF_TIME_CSV_DESC'),
		JText::_('MOD_PF_TIME_CSV_MILE'),
		JText::_('MOD_PF_TIME_CSV_TLKIST'),
		JText::_('MOD_PF_TIME_CSV_AUTHOR'),
		JText::_('MOD_PF_TIME_CSV_DATE'),
		JText::_('MOD_PF_TIME_CSV_RATE'),
		JText::_('MOD_PF_TIME_CSV_HRS'),
		JText::_('MOD_PF_TIME_CSV_TOTAL'),
		JText::_('MOD_PF_TIME_CSV_BILLABLE')
	);

	$csv				= implode(',', $csv_header)."\r\n";

	foreach ($taskdata as $result){
		if (!property_exists($result,'milestone_title')) {
			$result->milestone_title = '-';
		}
		if (!property_exists($result,'list_title')) {
			$result->list_title = "-";
		}
		$amount 	= 0;
		$billable 	= 'No';
		$csv 		.= "\"$result->project_title\",";
		$csv 		.= "\"$result->task_title\",";
		$csv 		.= "\"$result->description\",";

		$csv 		.= "\"$result->milestone_title\",";
		$csv 		.= "\"$result->list_title\",";

		if ($user->authorise('core.edit','com_pftime') || $show_author) {
			$csv 		.= "\"$result->author_name\",";
		}
		else {
			$csv 		.= ",";
		}

		$ldate 		= explode(" ",$result->log_date);
		$logdate	= $ldate[0];

		if ($user->authorise('core.edit','com_pftime') || $show_date) {
			$csv 		.= "$logdate,";
		}
		else {
			$csv 		.= ",";
		}

		if ($user->authorise('core.edit','com_pftime') || $show_monetary) {
			$csv 		.= "$result->rate,";
		}
		else {
			$csv		.= ",";
		}
		$hrs 		= $result->log_time/3600;
		$csv 		.= "$hrs,";

		$rate 		= $result->rate;

		if (!$result->rate) {
			$rate = 0;
		}

		if ($user->authorise('core.edit','com_pftime') || $show_monetary) {
			$amount = $rate * $hrs;
		}
		else {
			$amount = "";
		}
		$csv 	.= "$amount,";

		if ($result->billable == 1){
			$billable = "Yes";
		}

		$csv 	.= $billable."\r\n";
    }

	header('Set-Cookie:fileDownload=true; path=/');
	header('Content-type: text/csv');
	header('Content-Disposition: attachment; filename="timesheet.csv"');

	echo $csv;

	$app->close();
}
