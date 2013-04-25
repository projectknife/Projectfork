<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.helper');


/**
 * Projectfork Repository Component Route Helper
 *
 * @static
 */
abstract class PFrepoHelperRoute
{
    /**
     * Creates a valid repository directory path
     *
     * @param     string    $project    The project slug. Optional
     * @param     string    $path       The full directory path. Optional
     *
     * @return    string    $link       The link
     */
    public static function getRepositoryPath($project = '', $path = '')
    {
        static $paths = array();

        // Get all paths of the project
        if (!isset($paths[$project])) {
            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('id, path')
                  ->from('#__pf_repo_dirs')
                  ->where('project_id = ' . $db->quote((int) $project));

            $db->setQuery($query);
            $list = (array) $db->loadObjectList();

            $project_paths = array();

            foreach($list AS $list_item)
            {
                $id = $list_item->id;
                $p  = $list_item->path;

                $project_paths[$p] = $id;
            }

            $paths[$project] = $project_paths;
        }

        if ($path) {
            $parts    = array_reverse(explode('/', $path));
            $new_path = array();
            $looped   = array();

            while(count($parts))
            {
                $part     = array_pop($parts);
                $looped[] = $part;

                $find = implode('/', $looped);

                if (isset($paths[$project][$find])) {
                    $new_path[] = $paths[$project][$find] . ':' . $part;
                }
            }

            $path = implode('/', $new_path);
        }

        return $path;
    }


    /**
     * Creates a link a repo directory
     *
     * @param     string    $project    The project slug. Optional
     * @param     string    $dir        The directory slug. Optional
     * @param     string    $path       The full directory path. Optional
     *
     * @return    string    $link       The link
     */
    public static function getRepositoryRoute($project = '', $dir = '', $path = '')
    {
        $path  = self::getRepositoryPath($project, $path);
        $link  = 'index.php?option=com_pfrepo&view=repository';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_parent_id=' . $dir;
        $link .= '&path=' . $path;

        $needles = array('filter_project'   => array((int) $project),
                         'filter_parent_id' => array((int) $dir),
                         'path' => array($path),
                        );

        if ($item = PFApplicationHelper::itemRoute($needles, 'com_pfrepo.repository')) {
            $link .= '&Itemid=' . $item;
        }
        elseif ($item = PFApplicationHelper::itemRoute(null, 'com_pfrepo.repository')) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link a repo file
     *
     * @param     string    $file       The file slug
     * @param     string    $project    The project slug. Optional
     * @param     string    $dir        The directory slug. Optional
     * @param     string    $path       The full directory path. Optional
     * @param     string    $rev        The revision slug. Optional
     *
     *
     * @return    string    $link       The link
     */
    public static function getFileRoute($file, $project = '', $dir = '', $path = '', $rev = '')
    {
        $path  = self::getRepositoryPath($project, $path);
        $link  = 'index.php?option=com_pfrepo&view=file';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_parent_id=' . $dir;
        $link .= '&path=' . $path;
        $link .= '&id=' . $file;

        if ($rev) $link .= '&rev=' . $rev;

        $item = PFApplicationHelper::itemRoute(null, 'com_pfrepo.repository');

        if ($item) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a file revision list
     *
     * @param     string    $file       The file slug
     * @param     string    $project    The project slug. Optional
     * @param     string    $dir        The directory slug. Optional
     * @param     string    $path       The full directory path. Optional
     *
     *
     * @return    string    $link       The link
     */
    public static function getFileRevisionsRoute($file, $project = '', $dir = '', $path = '')
    {
        $path  = self::getRepositoryPath($project, $path);
        $link  = 'index.php?option=com_pfrepo&view=filerevisions';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_parent_id=' . $dir;
        $link .= '&path=' . $path;
        $link .= '&id=' . $file;

        $item = PFApplicationHelper::itemRoute(null, 'com_pfrepo.repository');

        if ($item) $link .= '&Itemid=' . $item;

        return $link;
    }


    /**
     * Creates a link to a repo note
     *
     * @param     string    $note       The note slug
     * @param     string    $project    The project slug. Optional
     * @param     string    $dir        The directory slug. Optional
     * @param     string    $path       The full directory path. Optional
     * @param     string    $rev        The revision slug. Optional
     *
     *
     * @return    string    $link       The link
     */
    public static function getNoteRoute($note, $project = '', $dir = '', $path = '', $rev = '')
    {
        $path  = self::getRepositoryPath($project, $path);
        $link  = 'index.php?option=com_pfrepo&view=note';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_parent_id=' . $dir;
        $link .= '&path=' . $path;
        $link .= '&id=' . $note;

        if ($rev) $link .= '&rev=' . $rev;

        $item = PFApplicationHelper::itemRoute(null, 'com_pfrepo.repository');

        if ($item) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }


    /**
     * Creates a link to a note revision list
     *
     * @param     string    $note       The note slug
     * @param     string    $project    The project slug. Optional
     * @param     string    $dir        The directory slug. Optional
     * @param     string    $path       The full directory path. Optional
     *
     *
     * @return    string    $link       The link
     */
    public static function getNoteRevisionsRoute($note, $project = '', $dir = '', $path = '')
    {
        $path  = self::getRepositoryPath($project, $path);
        $link  = 'index.php?option=com_pfrepo&view=noterevisions';
        $link .= '&filter_project=' . $project;
        $link .= '&filter_parent_id=' . $dir;
        $link .= '&path=' . $path;
        $link .= '&id=' . $note;

        $item = PFApplicationHelper::itemRoute(null, 'com_pfrepo.repository');

        if ($item) {
            $link .= '&Itemid=' . $item;
        }

        return $link;
    }
}
