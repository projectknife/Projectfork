<?php
/**
* @author    Tobias Kuhn
* @copyright Copyright (C) 2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
**/

// No direct access
defined('_JEXEC') or die;


/**
 * Template document helper class
 *
 */
abstract class TemplateDocHelper
{
    /**
     * Searches the document head for a string
     *
     * @param     string     $string    The string to search for. Can be a regex.
     * @param     string     $type      What to look for. Can be: stylesheet, stylecode,
     *                                  script or scriptcode. Searches stylesheet and script
     *                                  by default.
     * @param     boolean    $regex     Signal whether to use $string as regex or not.
     *                                  False by default.
     *
     * @return    boolean               Returns True if the string was found. Returns False if not.
     */
    public function headContains($string, $type = null, $regex = false)
    {
        $type = strtolower($type);

        // Get the head data based on $type
        switch($type)
        {
            case 'stylesheet':
            case 'css':
                $data = array_keys( JFactory::getDocument()->get('_styleSheets') );
                break;

            case 'stylecode':
            case 'csscode':
                $data = array_values( JFactory::getDocument()->get('_style') );
                break;

            case 'script':
            case 'js':
                $data = array_keys( JFactory::getDocument()->get('_scripts') );
                break;

            case 'scriptcode':
            case 'jscode':
                $data = array_values( JFactory::getDocument()->get('_script') );
                break;

            default:
                // By default, search stylesheets and scripts only
                $data1 = array_keys( JFactory::getDocument()->get('_styleSheets') );
                $data2 = array_keys( JFactory::getDocument()->get('_scripts') );
                $data  = array_merge( $data1, $data2 );
                break;
        }

        $data   = implode('', $data);
        $result = false;

        // Search the string
        if($regex)
        {
            // As regex...
            if(preg_match($string, $data))
            {
                $result = true;
            }
        }
        else
        {
            // Or cheap stripos by default, which should suffice in most cases
            $result = ((stripos($data, $string) !== false) ? true : false);
        }

        return $result;
    }
}
