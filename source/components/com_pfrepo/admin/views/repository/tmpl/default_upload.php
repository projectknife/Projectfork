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


$dir        = $this->items['directory'];
$browser    = JBrowser::getInstance();
$txt_upload = ($browser->getBrowser() == 'msie') ? JText::_('COM_PROJECTFORK_AJAX_UPLOAD_CLICK') : JText::_('COM_PROJECTFORK_AJAX_UPLOAD_DD');
$allowed    = PFrepoHelper::getAllowedFileExtensions();

$user         = JFactory::getUser();
$config       = JComponentHelper::getParams('com_pfrepo');
$filter_admin = $config->get('filter_ext_admin');
$is_admin     = $user->authorise('core.admin');

// Restrict file extensions?
$exts = '';

if ($is_admin && !$filter_admin) $allowed = array();

if (count($allowed)) {
    $exts = ', allowedExtensions: ' . json_encode($allowed);

    $txt_upload .= '. ' . JText::_('COM_PROJECTFORK_UPLOAD_ALLOWED_EXT') . ' ' . implode(', ', $allowed);
}

$area = array();
$area[] = '<div class="qq-uploader">';
$area[] = '<div class="qq-upload-drop-area qq-upload-button alert"><i class="icon-box-add"></i> ' . $txt_upload . '</div>';
$area[] = '</div>';
$area[] = '<div class="qq-upload-list">';

$el = array();
$el[] = '<div class="row-fluid">';
$el[] = '<div class="span3">';
$el[] = '<span class="icon-flag-2"></span> ';
$el[] = '<span class="qq-upload-file"></span>';
$el[] = '<span class="qq-upload-spinner"></span>';
$el[] = '</div>';
$el[] = '<div class="span2">';
$el[] = '<span class="qq-upload-failed-text">Failed</span>';
$el[] = '<a class="qq-upload-cancel btn btn-mini" href="#"><i class="icon-box-add"></i> Cancel</a>';
$el[] = '</div>';
$el[] = '<div class="span7">';
$el[] = '    <div class="progress progress-striped active">';
$el[] = '        <div class="bar">';
$el[] = '            <span class="qq-upload-size label pull-left"></span>';
$el[] = '        </div>';
$el[] = '    </div>';
$el[] = '</div>';
$el[] = '</div>';

// Init Ajax upload
JHtml::_('pfhtml.script.upload');
JHtml::_('stylesheet', 'com_projectfork/projectfork/ajax-upload.css', false, true, false, false, false);

$js = "
window.addEvent('domready', function createUploader()
{
    new qq.FileUploader({
        element: document.getElementById('file-uploader'),
        action: 'index.php',
        params: {
            option: 'com_pfrepo',
            task: 'file.save',
            filter_parent_id: '" . $dir->id  . "',
            format: 'json'
        },
        template: '" . implode('', $area) . "',
        fileTemplate: '" . implode('', $el) . "',
        listElement: document.getElementById('qq-upload-list'),
        debug: true
        " . $exts . "
    });
});";

JFactory::getDocument()->addScriptDeclaration($js);
?>
<div id="file-uploader" class="hidden-phone"></div>

