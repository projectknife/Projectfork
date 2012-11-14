<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


class pkg_projectforkMemory
{
    public function check()
    {
        $usage     = $this->getUsage();
        $available = $this->getAvailable();

        if (empty($usage) || empty($available)) {
            return true;
        }

        $remaining = $available - $usage;

        // Around 6MB are needed
        $needed = 6291456;

        if (($remaining - $needed) <= 0) {
            return ($remaining - $needed);
        }

        return true;
    }


    protected function getUsage()
    {
        return JProfiler::getInstance('Application')->getMemory();
    }


    protected function getAvailable()
    {
        $mem = ini_get('memory_limit');

        if (empty($mem)) {
            return false;
        }

        $mem   = trim($mem);
        $short = strtolower($mem[strlen($mem)-1]);

        switch($short)
        {
            case 'g':
                $mem *= 1024;

            case 'm':
                $mem *= 1024;

            case 'k':
                $mem *= 1024;
                break;
        }

        return $mem;
    }
}


