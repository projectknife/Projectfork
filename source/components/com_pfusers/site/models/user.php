<?php
/**
 * @package      Projectfork
 * @subpackage   Users
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Base this on the backend users model
JLoader::register('UsersModelUser', JPATH_ADMINISTRATOR . '/components/com_users/models/user.php');


/**
 * Projectfork User Model
 * Extends on the backend version of com_users
 *
 */
class PFusersModelUser extends UsersModelUser
{
    /**
     * Method to find all projects a user has access to
     *
     * @param              $pk    The user id
     * @return    array           The project IDs
     */
    public function getProjects($pk = NULL)
    {
        $user  = JFactory::getUser($pk);
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $access = PFprojectsHelper::getActions();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        $query->select('id')
              ->from('#__pf_projects')
              ->where('access IN(' . $groups . ')');

        if (!$access->get('core.edit.state') && !$access->get('core.edit')) {
            $query->where('state = 1');
        }

        $db->setQuery((string) $query);
        $projects = (array) $db->loadColumn();

        return $projects;
    }


    public function deleteAvatar($pk)
    {
        $base_path = JPATH_ROOT . '/media/com_projectfork/repo/0/avatar';
        $img_path  = NULL;

        if (JFile::exists($base_path . '/' . $pk . '.jpg')) {
            $img_path = $base_path . '/' . $pk . '.jpg';
        }
        elseif (JFile::exists($base_path . '/' . $pk . '.jpeg')) {
            $img_path = $base_path . '/' . $pk . '.jpeg';
        }
        elseif (JFile::exists($base_path . '/' . $pk . '.png')) {
            $img_path = $base_path . '/' . $pk . '.png';
        }
        elseif (JFile::exists($base_path . '/' . $pk . '.gif')) {
            $img_path = $base_path . '/' . $pk . '.gif';
        }

        // No image found
        if (!$img_path) {
            return true;
        }

        if (JFile::delete($img_path) !== true) {
            return false;
        }

        return true;
    }


    public function saveAvatar($pk, $file)
    {
        if (!PFImage::isValid($file['name'], $file['tmp_name'])) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_NOT_AN_IMAGE'));
            return false;
        }

        // Delete any previous avatar
        if (!$this->deleteAvatar($pk)) {
            return false;
        }

        if ($file['error']) {
            $error = PFrepoHelper::getFileErrorMsg($file['error'], $file['name']);
            $this->setError($error);
            return false;
        }

        $uploadpath = JPATH_ROOT . '/media/com_projectfork/repo/0/avatar';
        $name = $pk . '.' . strtolower(JFile::getExt($file['name']));

        if (JFile::upload($file['tmp_name'], $uploadpath . '/' . $name) === true) {
            return true;
        }

        return false;
    }


    /**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		// Initialise variables.
		$table = $this->getTable();
		$key   = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = JRequest::getInt($key, JFactory::getUser()->get('id'));

		$this->setState($this->getName() . '.id', $pk);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
	}
}
