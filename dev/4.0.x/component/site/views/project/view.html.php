<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
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


jimport('joomla.application.component.view');


class ProjectforkViewProject extends JView
{
    protected $item;
	protected $params;
    protected $user;
    protected $state;
    
	function display($tpl = null)
	{
	    // Initialise variables.
		$this->user	 = JFactory::getUser();
        $this->state = $this->get('State');
        //$this->item  = $this->get('Item');
        
        // Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}
        
        // Create a shortcut for $item.
		$item = &$this->item;
        
        // Add router helpers.
		$item->slug = $item->alias ? ($item->id.':'.$item->alias) : $item->id;
        
		parent::display($tpl);
	} 
}
?>