<?php
/**
 * @package      pkg_projectfork
 * @subpackage   lib_projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


/**
 * Version information class for the Projectfork package.
 *
 */
final class PFVersion
{
    /** @var  string  Product name. */
    public $PRODUCT = 'Projectfork';

    /** @var  string  Release version. */
    public $RELEASE = '4.1';

    /** @var  string  Maintenance version. */
    public $DEV_LEVEL = '0';

    /** @var  string  Development status. */
    public $DEV_STATUS = 'dev';

    /** @var  string  Build number. */
    public $BUILD = '1';

    /** @var  string  Code name. */
    public $CODENAME = 'Intermezzo';

    /** @var  string  Release date. */
    public $RELDATE = '6-April-2013';

    /** @var  string  Release time. */
    public $RELTIME = '01:00';

    /** @var  string  Release timezone. */
    public $RELTZ = 'CET';

    /** @var  string  Copyright Notice. */
    public $COPYRIGHT = 'Copyright (C) 2006 - 2013 Tobias Kuhn and Kyle Ledbetter. All rights reserved.';

    /** @var  string  Link text. */
    public $URL = '<a href="http://www.projectfork.net">Projectfork</a> is Free Software released under the GNU General Public License.';


    /**
     * Compares two a "PHP standardized" version number against the current Projectfork version.
     *
     * @param     string    $minimum    The minimum version of Projectfork which is compatible.
     *
     * @return    bool                  True if the version is compatible.
     */
    public function isCompatible($minimum)
    {
        return version_compare(PFVERSION, $minimum, 'ge');
    }


    /**
     * Gets a "PHP standardized" version string for the current Projectfork.
     *
     * @return    string    Version string.
     */
    public function getShortVersion()
    {
        return $this->RELEASE . '.' . $this->DEV_LEVEL;
    }


    /**
     * Gets a version string for the current Projectfork with all release information.
     *
     * @return    string    Complete version string.
     */
    public function getLongVersion()
    {
        return $this->PRODUCT . ' ' . $this->RELEASE . '.' . $this->DEV_LEVEL . ' '
                . $this->DEV_STATUS . ' [ ' . $this->CODENAME . ' ] ' . $this->RELDATE . ' '
                . $this->RELTIME . ' ' . $this->RELTZ;
    }
}
