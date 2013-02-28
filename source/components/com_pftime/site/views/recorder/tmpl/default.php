<?php
/**
 * @package      Projectfork
 * @subpackage   Timetracking
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

JHtml::_('pfhtml.script.form');
JHtml::_('pfhtml.script.listform');
JHtml::_('pfhtml.script.timerec');

$last_time = time() - $this->time;
if ($last_time > 60) $last_time = 0;

$pcfg = PFApplicationHelper::getProjectParams();
$currency_sign = $pcfg->get('currency_sign');
$currency_del  = $pcfg->get('decimal_delimiter');
$currency_pos  = $pcfg->get('currency_position');
?>
<script type="text/javascript">
jQuery(document).ready(function() {
   PFtimerec.setForm('adminForm');
   PFtimerec.setTicker('ticker', 'ticker-progress');

   setInterval(PFtimerec.tick, 1000);

   PFform.radio2btngroup();
});

function setRateFieldValue(i)
{
    var v1 = jQuery('#rec-rate0-' + i, PFtimerec.fe).val();
    var v2 = jQuery('#rec-rate1-' + i, PFtimerec.fe).val();

    jQuery('#rec-rate-' + i, PFtimerec.fe).val(v1 + '.' + v2);
}
</script>
<div id="projectfork" class="view-recorder">

    <div>&nbsp;</div>
    <h3><?php echo JText::_('COM_PROJECTFORK_TIME_RECORDER_TITLE'); ?></h3>

    <form name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_pftime&view=recorder'); ?>"
        method="post" class="form-inline" autocomplete="off"
        >

        <table id="recordings" class="table table-striped">
            <thead>
                <tr>
                    <th width="1" class="nowrap">
                        <div class="btn-group">
                            <a class="btn btn-mini hasTip" onclick="PFtimerec.pauseAll();"
                                title="<?php echo addslashes(JText::_('COM_PROJECTFORK_TIME_REC_TT_PAUSE_ALL')); ?>"
                                >
                                <i class="icon-checkbox-partial"></i>
                            </a>
                        </div>
                    </th>
                    <th width="1" class="nowrap">
                        <div class="btn-group">
                            <a class="btn btn-mini hasTip" onclick="PFtimerec.startAll();"
                                title="<?php echo addslashes(JText::_('COM_PROJECTFORK_TIME_REC_TT_RESUME_ALL')); ?>"
                            >
                                <i class="icon-play-2"></i>
                            </a>
                        </div>
                    </th>
                    <th>
                        <div class="btn-group">
                            <a class="btn btn-mini hasTip" onclick="PFtimerec.removeAll(1);"
                                title="<?php echo addslashes(JText::_('COM_PROJECTFORK_TIME_REC_TT_REMOVE_COMPLETE_ALL')); ?>"
                                >
                                <i class="icon-checkmark"></i>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a class="btn btn-mini hasTip" onclick="PFtimerec.removeAll(0);"
                                title="<?php echo addslashes(JText::_('COM_PROJECTFORK_TIME_REC_TT_REMOVE_ALL')); ?>"
                                >
                                <i class="icon-remove"></i>
                            </a>
                        </div>
                    </th>
                    <th width="1%" class="nowrap">
                        <div class="progress progress-striped active" id="ticker-progress" style="margin-bottom: 0px;">
                            <div class="bar" style="width: 0%;"></div>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php
                $txt_desc_lbl = JText::_('COM_PROJECTFORK_FIELD_DESCRIPTION_LABEL');
                $txt_rate_lbl = JText::_('COM_PROJECTFORK_FIELD_RATE_LABEL');
                $txt_blb_lbl  = JText::_('COM_PROJECTFORK_FIELD_BILLABLE_LABEL');
                $txt_no       = JText::_('JNO');
                $txt_yes      = JText::_('JYES');
                $txt_save     = JText::_('JSAVE');
                $txt_close    = JText::_('JLIB_HTML_BEHAVIOR_CLOSE');
                $txt_rm       = JText::_('COM_PROJECTFORK_REMOVE');
                $txt_rmc      = JText::_('COM_PROJECTFORK_TIME_REC_REMOVE_COMPLETE');

                foreach ($this->items AS $i => $item) :
                    $id    = (int) $item['id'];
                    $pause = (int) $item['pause'];
                    $time  = (int) $item['time'];
                    $data  = $item['data'];

                    if ($time == 1) $time = 60;

                    // Prepare rate field values
                    list($rate_0, $rate_1) = explode('.', $data->rate);
                    $rate = intval($rate_0) . '.' . intval($rate_1);

                    // Prepare the Recording state button
                    $btn_id    = 'btn-rec-state-' . $i;
                    $btn_class = 'btn btn-mini btn-rec-state' . ($pause ? '' : ' btn-success active');
                    $btn_js    = "PFtimerec.togglePause(" . $i . ");";

                    $btn_pause = '<a href="javascript:void(0);" onclick="' . $btn_js . '" class="' . $btn_class . '" id="' . $btn_id . '">'
                               . '    <i class="icon-play-2"></i>'
                               . '</a>';
                ?>
                <tr class="row<?php echo $i % 2; ?> recording" id="rec-<?php echo $i; ?>">
                    <td class="nowrap">
                        <div class="btn-group">
                            <a class="btn btn-mini dropdown-toggle" data-toggle="dropdown">
                                <i class="caret"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="javascript:void(0);" onclick="PFtimerec.remove(<?php echo $i; ?>, 0);">
                                        <?php echo $txt_rm; ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" onclick="PFtimerec.remove(<?php echo $i; ?>, 1);">
                                        <?php echo $txt_rmc; ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                    <td class="nowrap">
                        <div class="btn-group">
                            <?php echo $btn_pause; ?>
                        </div>
                    </td>
                    <td>
                        <a data-toggle="collapse" href="#rec-edit-<?php echo $i; ?>">
                            <strong><?php echo $this->escape($data->task_title); ?></strong>
                        </a>

                        <!-- Start Edit Container -->
                        <div class="collapse" id="rec-edit-<?php echo $i; ?>">
                            <hr class="hr-condensed"/>
                            <ul class="unstyled">
                                <li>
                                    <!-- Start Description -->
                                    <label for="rec-desc-<?php echo $i; ?>"><?php echo $txt_desc_lbl; ?></label>
                                    <fieldset>
                                        <input type="text" id="rec-desc-<?php echo $i; ?>" class="input-medium"
                                            name="description[<?php echo $i; ?>]" value="<?php echo $this->escape($data->description); ?>"
                                        />
                                    </fieldset>
                                    <!-- End Description -->
                                </li>
                                <li>
                                    <!-- Start Rate -->
                                    <label for="rec-rate0-<?php echo $i; ?>"><?php echo $txt_rate_lbl; ?></label>
                                    <fieldset>
                                        <?php if ($currency_pos == '0') : ?>
                                            <label><?php echo $currency_sign; ?></label>
                                        <?php endif; ?>

                                        <input type="text" class="input-mini" name="rate0[<?php echo $i; ?>]" id="rec-rate0-<?php echo $i; ?>"
                                            value="<?php echo (int) $rate_0; ?>" onkeyup="setRateFieldValue(<?php echo $i; ?>)" maxlength="5" size="10"
                                        />
                                        <label><?php echo $currency_del; ?></label>
                                        <input type="text" class="input-mini" name="rate1[<?php echo $i; ?>]" id="rec-rate1-<?php echo $i; ?>"
                                            value="<?php echo (int) $rate_1; ?>" onkeyup="setRateFieldValue(<?php echo $i; ?>)" maxlength="2" size="5"
                                        />
                                        <input type="hidden" name="rate[<?php echo $i; ?>]" id="rec-rate-<?php echo $i; ?>"
                                            value="<?php echo $rate; ?>"/>

                                        <?php if ($currency_pos == '1') : ?>
                                            <label><?php echo $currency_sign; ?></label>
                                        <?php endif; ?>

                                    </fieldset>
                                    <!-- End Rate -->
                                </li>
                                <li>
                                    <!-- Start Billable -->
                                    <label><?php echo $txt_blb_lbl; ?></label>
                                    <div class="clearfix"></div>
                                    <fieldset class="radio inputbox btn-group">
                                        <input type="radio" class="inputbox" name="billable[<?php echo $i; ?>]"
                                            value="0" id="rec-blb0-<?php echo $i; ?>"
                                            <?php echo (!$data->billable ? 'checked=checked' : ''); ?>
                                        />
                                        <label for="rec-blb0-<?php echo $i; ?>"><?php echo $txt_no; ?></label>
                                        <input type="radio" class="inputbox" name="billable[<?php echo $i; ?>]"
                                            value="1" id="rec-blb1-<?php echo $i; ?>"
                                            <?php echo ($data->billable ? 'checked=checked' : ''); ?>
                                        />
                                        <label for="rec-blb1-<?php echo $i; ?>"><?php echo $txt_yes; ?></label>
                                    </fieldset>
                                    <!-- End Billable -->
                                </li>
                                <li>
                                    <!-- Start Buttons -->
                                    <hr class="hr-condensed"/>
                                    <div class="btn-toolbar pull-right">
                                        <a class="btn btn-small btn-primary" onclick="PFtimerec.save(<?php echo $i; ?>);" id="btn-rec-save-<?php echo $i; ?>">
                                            <?php echo $txt_save; ?>
                                        </a>
                                        <a class="btn btn-small" onclick="PFtimerec.closeEdit(<?php echo $i; ?>);">
                                            <?php echo $txt_close; ?>
                                        </a>
                                    </div>
                                    <div class="clearfix"></div>
                                    <!-- End Buttons -->
                                </li>
                            </ul>
                        </div>
                        <!-- End Edit Container -->
                    </td>
                    <td class="nowrap">
                        <span class="label pull-right" id="rec-time-<?php echo $id; ?>">
                            <?php echo JHtml::_('time.format', $time); ?>
                        </span>
                        <div class="clearfix"></div>
                        <input type="hidden" name="pause[<?php echo $i; ?>]" id="rec-state-<?php echo $i; ?>" value="<?php echo $pause; ?>"/>
                        <div style="display: none;"><?php echo JHtml::_('pf.html.id', $i, $id); ?></div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <input type="hidden" id="boxchecked" name="boxchecked" value="0"/>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="complete" value="0" />
        <input type="hidden" name="ticker" id="ticker" value="<?php echo ($this->time == 0 ? 0 : $last_time); ?>"/>
        <?php echo JHtml::_('form.token'); ?>
    </form>

    <?php if (count($this->items)) : ?>
        <div class="alert">
            <?php echo JText::_('COM_PROJECTFORK_TIME_REC_NOTICE'); ?>
        </div>
    <?php else : ?>
        <div class="alert">
            <?php echo JText::_('COM_PROJECTFORK_TIME_REC_NOTICE_EMPTY'); ?>
        </div>
    <?php endif; ?>
</div>