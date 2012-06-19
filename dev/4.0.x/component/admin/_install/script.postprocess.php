<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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


$app = JFactory::getApplication();


// Check if the scripts folder exists
$script_folder = dirname(__FILE__).'/script_postprocess';

if(!JFolder::exists($script_folder)) {
    $app->enqueueMessage('Post-process script folder not found!');
    return false;
}


// Get all script files
$script_files = (array) JFolder::files($script_folder, '.php');
sort($script_files);


// Iterate through all scripts
foreach($script_files AS $file)
{
    // Skip disabled scripts
    if(stripos($file, 'enabled') === false || stripos($file, 'disabled')) {
        continue;
    }

    // Run the script
    if(file_exists($script_folder.'/'.$file)) {
        require_once($script_folder.'/'.$file);
    }
}
