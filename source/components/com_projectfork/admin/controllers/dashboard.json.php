<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controlleradmin');


/**
 * Projectfork JSON Controller
 *
 */
class ProjectforkControllerDashboard extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the class name.
     * @param     array     $config    Configuration array for model. Optional.
     *
     * @return    object
     */
    public function getModel($name = 'Dashboard', $prefix = 'ProjectforkModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Returns the total amount of projects
     *
     * @return    void
     */
    public function countProjects()
    {
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(*)')
              ->from('#__pf_projects');

        $db->setQuery($query);
        $count = (int) $db->loadResult();

        $rsp = array('total' => $count);

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        JResponse::setHeader('Content-Disposition', 'attachment;filename="dashboard.json"');
        // Output the JSON data.
        echo json_encode($rsp);

        jexit();
    }


    public function checkAssets()
    {
        $limitstart = JRequest::getUInt('chk_assets_limitstart');
        $model      = $this->getModel('CheckAsset');

        $model->setState('limitstart', $limitstart);

        $rsp = array('success' => $model->check());

        // Set the MIME type for JSON output.
        JFactory::getDocument()->setMimeEncoding('application/json');

        // Change the suggested filename.
        JResponse::setHeader('Content-Disposition', 'attachment;filename="dashboard.json"');
        // Output the JSON data.
        echo json_encode($rsp);

        jexit();
    }
}
