<?php
	/*** @copyright Copyright Pixel Praise LLC © 2012. All rights reserved. */
	// no direct access
	defined('_JEXEC') or die;

    // Include the document helper
    JLoader::register('TemplateHelperDocument', dirname(__FILE__) . '/helpers/document.php');

	$app = JFactory::getApplication();
	$doc = JFactory::getDocument();

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

        $doc->addScript($this->baseurl . '/templates/' . $this->template . '/js/application.js');
    }
?>
<!DOCTYPE html>
<html>
<head>
	<jdoc:include type="head" />
    <?php
    // Detecting Home
    $menu = & JSite::getMenu();
    if ($menu->getActive() == $menu->getDefault()) :
    $siteHome = 1;
    else:
    $siteHome = 0;
    endif;

    // Add current user information
    $user =& JFactory::getUser();

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
    $document =& JFactory::getDocument();

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
	?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/general.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/system/css/system.css" type="text/css" />
	<?php if($this->params->get('bootstrap_css', 1) && !$isset_bscss):?>
		<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template ?>/css/template.css" type="text/css" />
	<?php else: ?>
		<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template ?>/css/theme.css" type="text/css" />
	<?php endif;?>
	<?php if($this->params->get('color')):?>
		<style type="text/css">
			.navbar-inner{
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
	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container-fluid"> <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </a> <a class="brand" href="<?php echo $this->baseurl; ?>"><?php echo $sitename; ?></a>
				<div class="nav-collapse">
					<jdoc:include type="modules" name="position-1" style="none" />
					<ul class="nav pull-right">
						<?php if($user->username):?>
						<li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<?php echo $user->username; ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li class=""><a href="<?php echo JRoute::_('index.php?option=com_users&view=profile&Itemid='. $itemid);?>"><?php echo JText::_('TPL_GOGGLES_PROFILE');?></a></li>
								<li class=""><a href="<?php echo JRoute::_('index.php?option=com_projectfork&view=tasks&Itemid='. $itemid);?>"><?php echo JText::_('TPL_GOGGLES_MY_TASKS');?></a></li>
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
					<a class="logo" href="<?php echo $this->baseurl; ?>"></a>
				</div>
				<div class="span10 navbar-search">
					<jdoc:include type="modules" name="searchload" style="none" />
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
				<?php
					if($user->authorise('create', 'com_projectfork')) :
				?>
				<div class="btn-group">
				  <a href="#" class="btn btn-large btn-info btn-wide dropdown-toggle" data-toggle="dropdown">
				    <?php echo JText::_('TPL_GOGGLES_CREATE');?>
				    <span class="caret"></span>
				  </a>
				  <ul class="dropdown-menu">
				  	<?php
				  		if($user->authorise('create', 'com_projectfork.project')) :
				  	?>
				    	<li><a href="index.php?option=com_projectfork&view=projectform&layout=edit"><?php echo JText::_('TPL_GOGGLES_NEW_PROJECT');?></a></li>
				    <?php
				    	endif;
				    	if($user->authorise('create', 'com_projectfork.milestone')) :
				    ?>
				    	<li><a href="index.php?option=com_projectfork&view=milestoneform&layout=edit"><?php echo JText::_('TPL_GOGGLES_NEW_MILESTONE');?></a></li>
				    <?php
				    	endif;
				    	if($user->authorise('create', 'com_projectfork.tasklist')) :
				    ?>
				    	<li><a href="index.php?option=com_projectfork&view=tasklistform&layout=edit"><?php echo JText::_('TPL_GOGGLES_NEW_TASKLIST');?></a></li>
				    <?php
				    	endif;
				    	if($user->authorise('create', 'com_projectfork.task')) :
				    ?>
				    	<li><a href="index.php?option=com_projectfork&view=taskform&layout=edit"><?php echo JText::_('TPL_GOGGLES_NEW_TASK');?></a></li>
				    <?php
				    	endif;
				    ?>
				  </ul>
				</div>
				<?php
					endif;
				?>
				<hr />
				<div class="sidebar-nav">
					<a class="btn btn-large btn-info btn-wide btn-sidebar-collapse" data-toggle="collapse" data-target=".sidebar-collapse"> Menu <span class="caret"></span></a>
					<div class="sidebar-collapse">
						<jdoc:include type="modules" name="position-7" style="xhtml" />
						<jdoc:include type="modules" name="left" style="xhtml" />
					</div>
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
