<?php
/**
 * @package      Projectfork
 * @subpackage   Comments
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


abstract class JHtmlPFcomments
{
    public static function label($count = 0)
    {
        if (!$count) {
            return '';
        }

        return '<span class="label"><i class="icon-comment icon-white"></i> ' . intval($count) . '</span>';
    }
}