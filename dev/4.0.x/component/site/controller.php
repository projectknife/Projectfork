<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );


jimport('joomla.application.component.controller');

class ProjectforkController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function display($cachable = false, $urlparams = false)
	{
	    jimport( 'joomla.application.component.helper' );

        $params = JComponentHelper::getParams('com_projectfork');
		$doc    = JFactory::getDocument();
        $uri    = JFactory::getURI();


        // Load Projectfork CSS
        $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/css/com_projectfork_icons.css');
        $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/css/com_projectfork_layout.css');
        $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/css/com_projectfork_theme.css');


        // Load Projectfork JS
        $doc->addScript($uri->base(true).'/components/com_projectfork/assets/js/com_projectfork.js');


        // Load bootstrap if enabled
        if($params->get('bootstrap') == '1') {
            $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/bootstrap/css/bootstrap.min.css');
            $doc->addStyleSheet($uri->base(true).'/components/com_projectfork/assets/bootstrap/css/bootstrap-responsive.min.css');

            $doc->addScript($uri->base(true).'/components/com_projectfork/assets/bootstrap/js/bootstrap.min.js');
        }


        $cachable = true;
        $safeurlparams = array('id' => 'INT',
                               'cid' => 'ARRAY',
                               'limit' => 'INT',
                               'limitstart' => 'INT',
			                   'showall' => 'INT',
                               'return' => 'BASE64',
                               'filter' => 'STRING',
                               'filter_order' => 'CMD',
                               'filter_order_Dir' => 'CMD',
                               'filter-search' => 'STRING',
                               'filter_published' => 'CMD',
                               'print'=>'BOOLEAN',
                               'lang'=>'CMD'
                              );


		parent::display($cachable, $safeurlparams);
		return $this;
	}
}