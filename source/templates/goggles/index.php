<?php
	/*** @copyright Copyright Pixel Praise LLC © 2012. All rights reserved. */
	// no direct access
	defined('_JEXEC') or die;

    // Include the document helper
    JLoader::register('TemplateHelperDocument', dirname(__FILE__) . '/helpers/document.php');

	$app = JFactory::getApplication();
	$doc = JFactory::getDocument();

	// Settings for Joomla 3.0.x
	if (version_compare(JVERSION, '3.0.0', 'ge')) {
		// Add JavaScript Frameworks
		JHtml::_('bootstrap.framework');
	}
	// Settings for Joomla 2.5.x
	else {
		// Detect bootstrap and jQuery in document header
	    $isset_jquery = TemplateHelperDocument::headContains('jquery', 'script');
	    $isset_bsjs   = TemplateHelperDocument::headContains('bootstrap', 'script');
	    $isset_bscss  = TemplateHelperDocument::headContains('bootstrap', 'stylesheet');

	    if ($this->params->get('bootstrap_javascript', 1)) {
	        if (!$isset_jquery) {
	            $doc->addScript($this->baseurl . '/templates/' . $this->template . '/js/jquery.js');
	        }

	        if (!$isset_bsjs) {
	            $doc->addScript($this->baseurl . '/templates/' . $this->template . '/js/bootstrap.min.js');
	        }

	    }

	    // Add 2.5 System Stylesheets
		$doc->addStyleSheet('templates/system/css/general.css');
		$doc->addStyleSheet('templates/system/css/system.css');
	}

	$doc->addScript($this->baseurl . '/templates/' . $this->template . '/js/application.js');

	// Add Template Stylesheet
	$doc->addStyleSheet('templates/'.$this->template.'/css/template.css');

    // Register component route helper classes
    $pid = (int) $app->getUserState('com_projectfork.project.active.id');

    if (jimport('projectfork.library')) {
        $components = array(
            'com_pfprojects',
            'com_pfmilestones',
            'com_pftasks',
            'com_pftime',
            'com_pfrepo',
            'com_pfforum'
        );

        foreach ($components AS $component)
        {
            $route_helper = JPATH_SITE . '/components/' . $component . '/helpers/route.php';
            $class_name   = 'PF' . str_replace('com_pf', '', $component) . 'HelperRoute';

            if (file_exists($route_helper)) {
                JLoader::register($class_name, $route_helper);
            }
        }
    }

    // Have to find the project repo base dir
    if ($pid) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('attribs')
              ->from('#__pf_projects')
              ->where('id = ' . $db->quote($pid));

        $db->setQuery($query);
        $project_attribs = $db->loadResult();

        $project_params = new JRegistry;
        $project_params->loadString($project_attribs);

        $repo_dir = (int) $project_params->get('repo_dir');
    }
    else {
        $repo_dir = 1;
    }

    // Prepare component base links
    $link_tasks    = (class_exists('PFtasksHelperRoute') ? PFtasksHelperRoute::getTasksRoute() : 'index.php?option=com_pftasks');
    $link_projects = (class_exists('PFprojectsHelperRoute') ? PFprojectsHelperRoute::getProjectsRoute() : 'index.php?option=com_pfprojects');
    $link_time     = (class_exists('PFtimeHelperRoute') ? PFtimeHelperRoute::getTimesheetRoute() : 'index.php?option=com_pftime');
    $link_ms       = (class_exists('PFmilestonesHelperRoute') ? PFmilestonesHelperRoute::getMilestonesRoute() : 'index.php?option=com_pfmilestones');
    $link_forum    = (class_exists('PFforumHelperRoute') ? PFforumHelperRoute::getTopicsRoute() : 'index.php?option=com_pfforum');
    $link_repo     = (class_exists('PFrepoHelperRoute') ? PFrepoHelperRoute::getRepositoryRoute($pid, $repo_dir) : 'index.php?option=com_pfrepo&filter_project=' . $pid . '&parent_id=' . $repo_dir);
?>
<!DOCTYPE html>
<html>
<head>
	<jdoc:include type="head" />
    <?php
    // Detecting Home
    $site_app = JFactory::getApplication('Site');
    $menu     = $site_app->getMenu();

    if ($menu->getActive() == $menu->getDefault()) :
    $siteHome = 1;
    else:
    $siteHome = 0;
    endif;

    // Add current user information
    $user = JFactory::getUser();

    // Grad the Itemid
    $itemid = JRequest::getint( 'Itemid' );

    // Detecting Active Variables
    $option = JRequest::getCmd('option', '');
    $view = JRequest::getCmd('view', '');
    $layout = JRequest::getCmd('layout', '');
    $task = JRequest::getCmd('task', '');
    $itemid = JRequest::getCmd('Itemid', '');
    $sitename = $app->getCfg('sitename');
    if($task == "edit" || $layout == "form" ) :
    $fullWidth = 1;
    else:
    $fullWidth = 0;
    endif;

    // Added by jseliga
    // Determine Name to Display
    if ($this->params->get('nameDisplay') == "full") {
        $displayName = $user->name;
    }
    elseif ($this->params->get('nameDisplay') == "email") {
        $displayName = $user->email;
    }
    else {
        $displayName = $user->username;
    }

    $document = JFactory::getDocument();

    // Adjusting content width
    if ($this->countModules('position-7') && $this->countModules('right')) :
    	$span = "span6";
    elseif ($this->countModules('position-7') && !$this->countModules('right')) :
    	$span = "span10";
    elseif (!$this->countModules('position-7') && $this->countModules('right')) :
    	$span = "span8";
    else :
    	$span = "span12";
    endif;

    // Logo file or site title param
	if ($this->params->get('logoFile'))
	{
		$logo = '<img src="'. JURI::root() . $this->params->get('logoFile') .'" alt="'. $sitename .'" />';
	} else {
		$logo = '<img src="'. JURI::root() . '/templates/' . $this->template . '/img/logo.png' .'" alt="'. $sitename .'" />';
	}
	?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<?php if($this->params->get('color')):?>
		<style type="text/css">
			.navbar-inverse .navbar-inner{
				background: <?php echo $this->params->get('color');?>;
			}
			.sidebar-nav h3{
				color: <?php echo $this->params->get('color');?>;
			}
		</style>
	<?php endif;?>
</head>

<body class="site <?php echo $option . " view-" . $view . " layout-" . $layout . " task-" . $task . " itemid-" . $itemid . " ";?>  <?php if($siteHome): echo "homepage";endif;?> ">
	<!-- Top Navigation -->
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container-fluid"> <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </a> <a class="brand" href="<?php echo $this->baseurl; ?>"><?php echo $sitename;?></a>
				<div class="nav-collapse">
					<jdoc:include type="modules" name="position-1" style="none" />
					<ul class="nav pull-right">
						<?php if($user->id):?>
						<li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<?php echo $displayName; ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li class=""><a href="<?php echo JRoute::_('index.php?option=com_users&view=profile&Itemid='. $itemid);?>"><?php echo JText::_('TPL_GOGGLES_PROFILE');?></a></li>
								<li class="">
                                    <a href="<?php echo JRoute::_($link_tasks . '&filter_assigned=' . $user->id);?>">
                                        <?php echo JText::_('TPL_GOGGLES_MY_TASKS');?>
                                    </a>
                                </li>
								<li class="divider"></li>
								<li class=""><a href="<?php echo JRoute::_('index.php?option=com_users&task=user.logout&'. JSession::getFormToken() .'=1');?>"><?php echo JText::_('TPL_GOGGLES_LOGOUT');?></a></li>
							</ul>
						</li>
						<?php endif;?>
					</ul>
				</div>
				<!--/.nav-collapse -->
			</div>
		</div>
	</div>
	<!-- Header -->
	<div class="header">
		<div class="container-fluid">
			<div class="row-fluid">
				<div class="span2">
					<a href="<?php echo $this->baseurl; ?>"><?php echo $logo;?></a>
				</div>
				<div class="span10 navbar-search">
					<jdoc:include type="modules" name="searchload" style="none" />
					<jdoc:include type="modules" name="position-0" style="none" />
				</div>
			</div>
		</div>
	</div>
	<!-- Container -->
	<div class="container-fluid">
		<div class="row-fluid">
			<?php if ($this->countModules('position-7')) : ?>
			<div id="sidebar" class="span2">
				<jdoc:include type="modules" name="create" style="xhtml" />
				<!-- Begin Sidebar -->
				<?php if ($user->id && $this->params->get('createButton')) : ?>
				<div class="hidden-phone">
	                <div class="btn-group">
					  <a href="#" class="btn btn-large btn-info btn-wide dropdown-toggle" data-toggle="dropdown">
					    <?php echo JText::_('TPL_GOGGLES_CREATE');?>
					    <span class="caret"></span>
					  </a>
					  <ul class="dropdown-menu">
					  	<?php
					  		if($user->authorise('core.create', 'com_pfprojects')) :
					  	?>
					    	<li><a href="<?php echo JRoute::_($link_projects . '&task=form.add');?>"><i class="icon-briefcase"></i> <?php echo JText::_('TPL_GOGGLES_NEW_PROJECT');?></a></li>
					    <?php
					    	endif;
					    	if($user->authorise('core.create', 'com_pfmilestones')) :
					    ?>
					    	<li><a href="<?php echo JRoute::_($link_ms . '&task=form.add');?>"><i class="icon-flag"></i> <?php echo JText::_('TPL_GOGGLES_NEW_MILESTONE');?></a></li>
					    <?php
					    	endif;
					    	if($user->authorise('core.create', 'com_pftasks')) :
					    ?>
					    	<li><a href="<?php echo JRoute::_($link_tasks . '&task=tasklistform.add');?>"><i class="icon-list-view"></i> <?php echo JText::_('TPL_GOGGLES_NEW_TASKLIST');?></a></li>
					    <?php
					    	endif;
					    	if($user->authorise('core.create', 'com_pftasks')) :
					    ?>
					    	<li><a href="<?php echo JRoute::_($link_tasks . '&task=taskform.add');?>"><i class="icon-checkbox"></i> <?php echo JText::_('TPL_GOGGLES_NEW_TASK');?></a></li>
					    <?php
					    	endif;
					    	if($user->authorise('core.create', 'com_pftime')) :
					    ?>
					    	<li><a href="<?php echo JRoute::_($link_time . '&task=form.add');?>"><i class="icon-clock"></i> <?php echo JText::_('TPL_GOGGLES_NEW_TIME');?></a></li>
					    <?php
					    	endif;
					    	if($user->authorise('core.create', 'com_pfforum')) :
					    ?>
					    	<li><a href="<?php echo JRoute::_($link_forum . '&task=topicform.add');?>"><i class="icon-comments-2"></i> <?php echo JText::_('TPL_GOGGLES_NEW_TOPIC');?></a></li>
					    <?php
					    	endif;
					    	if($user->authorise('core.create', 'com_pfrepo') && $app->getUserState('com_projectfork.project.active.id')) :
					    ?>
					    	<li><a href="<?php echo JRoute::_($link_repo . '&task=fileform.add');?>"><i class="icon-upload"></i> <?php echo JText::_('TPL_GOGGLES_NEW_FILE');?></a></li>
					    <?php
					    	endif;
					    ?>
					  </ul>
					  </div>
					  <hr />
				</div>
                <?php endif; ?>

                <?php if ($user->id && $this->params->get('createButton')) : ?>
                <div class="visible-phone">
                  <a href="#" data-target=".create-collapse" class="btn btn-large btn-info btn-wide dropdown-toggle" data-toggle="collapse">
                    <?php echo JText::_('TPL_GOGGLES_CREATE');?>
                    <span aria-hidden="true" class="icon-pencil"></span>
                  </a>
                  <div class="create-collapse collapse">
                  	<br />
                  	<ul class="nav nav-tabs nav-stacked">
                  		<?php
                  			if($user->authorise('core.create', 'com_pfprojects')) :
                  		?>
                  	  	<li><a href="<?php echo JRoute::_($link_projects . '&task=form.add');?>"><i class="icon-briefcase"></i> <?php echo JText::_('TPL_GOGGLES_NEW_PROJECT');?></a></li>
                  	  <?php
                  	  	endif;
                  	  	if($user->authorise('core.create', 'com_pfmilestones')) :
                  	  ?>
                  	  	<li><a href="<?php echo JRoute::_($link_ms . '&task=form.add');?>"><i class="icon-flag"></i> <?php echo JText::_('TPL_GOGGLES_NEW_MILESTONE');?></a></li>
                  	  <?php
                  	  	endif;
                  	  	if($user->authorise('core.create', 'com_pftasks')) :
                  	  ?>
                  	  	<li><a href="<?php echo JRoute::_($link_tasks . '&task=tasklistform.add');?>"><i class="icon-list-view"></i> <?php echo JText::_('TPL_GOGGLES_NEW_TASKLIST');?></a></li>
                  	  <?php
                  	  	endif;
                  	  	if($user->authorise('core.create', 'com_pftasks')) :
                  	  ?>
                  	  	<li><a href="<?php echo JRoute::_($link_tasks . '&task=taskform.add');?>"><i class="icon-checkbox"></i> <?php echo JText::_('TPL_GOGGLES_NEW_TASK');?></a></li>
                  	  <?php
                  	  	endif;
                  	  	if($user->authorise('core.create', 'com_pftime')) :
                  	  ?>
                  	  	<li><a href="<?php echo JRoute::_($link_time . '&task=form.add');?>"><i class="icon-clock"></i> <?php echo JText::_('TPL_GOGGLES_NEW_TIME');?></a></li>
                  	  <?php
                  	  	endif;
                  	  	if($user->authorise('core.create', 'com_pfforum')) :
                  	  ?>
                  	  	<li><a href="<?php echo JRoute::_($link_forum . '&task=topicform.add');?>"><i class="icon-comments-2"></i> <?php echo JText::_('TPL_GOGGLES_NEW_TOPIC');?></a></li>
                  	  <?php
                  	  	endif;
                  	  	if($user->authorise('core.create', 'com_pfrepo') && $app->getUserState('com_projectfork.project.active.id')) :
                  	  ?>
                  	  	<li><a href="<?php echo JRoute::_($link_repo . '&task=fileform.add');?>"><i class="icon-upload"></i> <?php echo JText::_('TPL_GOGGLES_NEW_FILE');?></a></li>
                  	  <?php
                  	  	endif;
                  	  ?>
                  	</ul>
                  </div>
                  <hr />
                </div>
                <?php endif; ?>

				<div class="sidebar-nav">
					<a class="btn btn-large btn-wide btn-sidebar-collapse" data-toggle="collapse" data-target=".sidebar-collapse"> <?php echo JText::_('TPL_GOGGLES_MENU');?> <span aria-hidden="true" class="icon-list-view"></span></a>
					<div class="sidebar-collapse">
						<jdoc:include type="modules" name="position-7" style="xhtml" />
						<jdoc:include type="modules" name="left" style="xhtml" />
					</div>
					<hr class="visible-phone" />
	            </div>
	        	<!-- End Sidebar -->
			</div>
			<?php endif; ?>
			<div id="content" class="<?php echo $span;?>">
				<!-- Begin Content -->
				<jdoc:include type="modules" name="top" style="xhtml" />
				<jdoc:include type="message" />
				<jdoc:include type="component" />
				<jdoc:include type="modules" name="bottom" style="xhtml" />
				<!-- End Content -->
			</div>
			<?php if ($this->countModules('right')) : ?>
			<div id="aside" class="span4">
				<!-- Begin Right Sidebar -->
				<jdoc:include type="modules" name="right" style="xhtml" />
				<!-- End Right Sidebar -->
			</div>
			<?php endif; ?>
		</div>
		<hr />
		<div class="footer">
			<p>&copy; <?php echo $sitename; ?> <?php echo date('Y');?></p>
		</div>
	</div>
	<jdoc:include type="modules" name="debug" style="none" />
</body>

</html>
