<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2011 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
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

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');


/**
 * Form Field class for selecting a milestone.
 *
 */
class JFormFieldMilestone extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 */
	public $type = 'Milestone';


	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */
	protected function getInput()
	{
		// Initialize variables.
		$attr = '';


		// Initialize some field attributes.
		$attr .= $this->element['class']                         ? ' class="'.(string) $this->element['class'].'"'       : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"'                                : '';
		$attr .= $this->element['size']                          ? ' size="'.(int) $this->element['size'].'"'            : '';
		$attr .= $this->multiple                                 ? ' multiple="multiple"'                                : '';
		$attr .= $this->element['onchange']                      ? ' onchange="'.(string) $this->element['onchange'].'"' : '';


		// Get the field options and generate the list
        $options = $this->getOptions();
		$html    = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);


        return $html;
	}


    /**
	 * Method to get the field list options markup.
	 *
	 * @return    array    The list options markup.
	 */
	protected function getOptions()
	{
	    // Get custom options
        $options = parent::getOptions();


        // Get field attributes for the database query
        $query_state = ($this->element['state']) ? (int) $this->element['state'] : NULL;


        // Find the current project id
	    $project = $this->element['project'] ? (string) $this->element['project'] : NULL;
        $view    = $this->element['view']    ? (string) $this->element['view']    : (string) JRequest::getCmd('view');


        // Load milestones of a project
        if($project) {
            $project_id = (int) $this->form->getValue($project);
            $data       = JFactory::getApplication()->getUserState('com_projectfork.edit.'.$view.'.data', array());

            if(!$project_id) return $options;


            // Get the project table and load the project
            JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'projectfork'.DS.'tables');

            $table = JTable::getInstance('Project', 'JTable');

            if(!$table) return $options;
            if(!$table->load($project_id)) return $options;


            // Find all project milestones
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('a.id AS value, a.title AS text');
            $query->from('#__pf_milestones AS a');
            $query->where('a.project_id = '.(int) $project_id);
            if(!is_null($query_state)) $query->where('a.state = '.$query_state);
            $query->order('a.title');

            $db->setQuery($query->__toString());
            $list = (array) $db->loadObjectList();


            // Generate the options
            foreach($list AS $item)
            {
                // Create a new option object based on the <option /> element.
    			$tmp = JHtml::_('select.option',
                                (string) $item->value,
                                JText::alt(trim((string) $item->text),
                                preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)),
                                'value',
                                'text'
                               );

    			// Add the option object to the result set.
    			$options[] = $tmp;
            }

    		reset($options);
        }
        else {
            // No parent project configured, load all milestones
            // TODO
        }

		return $options;
	}
}