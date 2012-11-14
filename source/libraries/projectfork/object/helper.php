<?php
/**
 * @package      Projectfork.Library
 * @subpackage   Object
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


abstract class PFObjectHelper
{
    /**
     * Method to get the property changes between two item objects
     *
     * @param     object    $old        The old object
     * @param     object    $new        The new/updated object
     * @param     array     $props      The property/comparison method pairs
     *
     * @return    array     $changes    The changed property values
     */
    public static function getDiff($old, $new, $props)
    {
        $changes   = array();
        $old_props = get_object_vars($old);
        $new_props = get_object_vars($new);

        foreach($props AS $prop)
        {
            if (!is_array($prop)) {
                $prop = array($prop, 'NE');
            }

            if (count($prop) != 2) continue;

            list($name, $cmp) = $prop;

            if (!array_key_exists($name, $new_props) || !array_key_exists($name, $old_props)) {
                continue;
            }

            switch (strtoupper($cmp))
            {
                case 'NE-SQLDATE':
                    // Not equal, not sql null date
                    if ($new->$name != $old->$name && $new->$name != JFactory::getDbo()->getNullDate()) {
                        $changes[$name] = $new->$name;
                    }
                    break;

                case 'NE':
                default:
                    // Default, not equal
                    if ($new->$name != $old->$name) {
                        $changes[$name] = $new->$name;
                    }
                    break;
            }
        }

        return $changes;
    }


    public static function toContentItem(&$item)
    {
        static $content;

        if (is_object($item)) {
            if (!$content) {
                $content_table = JTable::getInstance('Content');
                $content = $content_table->getProperties(true);
            }

            $item_props    = get_object_vars($item);
            $content_props = array_keys($content);

            foreach ($content_props AS $prop)
            {
                if (!array_key_exists($prop, $item_props)) {
                    $item->$prop = $content[$prop];
                }
            }
        }
    }
}