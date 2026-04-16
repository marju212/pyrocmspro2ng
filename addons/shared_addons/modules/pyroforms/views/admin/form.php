<section class="title">
    <?php
    if ($this->method === 'newform')
    {
        echo '<h4>' . lang('pf_frm_create_title') . '</h4>';
    }
    else
    {
        echo '<h4>' . sprintf(lang('pf_frm_edit_title'), $frm->frmName) . '</h4>';
    }
    ?>
</section>
<section class="item">
    <div class="content">
    <?php echo form_open(current_url(), 'id="pyroforms" class="crud"');?>

    <div class="tabs">
        <ul class="tab-menu">
            <li><a href="#pf-general"><span><?php echo lang('pf_tabs_general'); ?></span></a></li>
            <li><a href="#pf-response"><span><?php echo lang('pf_tabs_response'); ?></span></a></li>
            <li><a href="#pf-notify"><span><?php echo lang('pf_tabs_notify'); ?></span></a></li>
            <li><a href="#pf-layout"><span><?php echo lang('pf_tabs_layout'); ?></span></a></li>

        </ul>

        <div id="pf-general" class="form_inputs">
            <fieldset>
                <ul>
                    <li class="<?php echo alternator('', 'even'); ?>">
                        <label for="frmName"><?php echo lang('pf_frm_name_label'); ?><span class="required-icon tooltip">*</span>
                            <small><?php echo lang('pf_frm_name_info'); ?></small></label>
                        <div class="input">
                            <input name="frmName" type="text" value="<?php echo $frm->frmName; ?>" maxlength="50" />
                        </div>
                    </li>
                    <li class="<?php echo alternator('', 'even'); ?>">
                        <label for="frmSlug"><?php echo lang('pf_frm_slug_label'); ?><span class="required-icon tooltip">*</span>
                            <small><?php echo lang('pf_frm_slug_info'); ?></small></label>
                        <div class="input">
                            <input name="frmSlug" type="text" value="<?php echo $frm->frmSlug; ?>" maxlength="50" />
                        </div>
                    </li>

                    <li class="<?php echo alternator('', 'even'); ?>">
                        <label for="is_ajax">Ajax Form
                            <small>Enable ajax for the form</small></label>
                        <div class="input">
                            <label><?php echo form_checkbox(array(
                                                'name'=>'is_ajax',
                                                'value'=>'1',
                                                'checked'=> ($frm->is_ajax == 1) )) . ' &nbsp; Yes'; ?></label>
                        </div>
                    </li>
                    <?php if ($this->settings->ga_tracking): ?>
                    <li class="<?php echo alternator('', 'even'); ?>">
                        <label for="track_pages">GA Track Pages
                            <small>Google Analytics - Track form actions as Pages</small></label>
                        <div class="input">
                            <label><?php echo form_checkbox(array(
                                                'name'=>'track_pages',
                                                'value'=>'1',
                                                'checked'=> ($frm->track_pages == 1) )) . ' &nbsp; Yes'; ?></label>
                        </div>
                    </li>

                    <li class="<?php echo alternator('', 'even'); ?>">
                        <label for="track_events">GA Track Events
                            <small>Google Analytics - Track form actions as Events</small></label>
                        <div class="input">
                            <label><?php echo form_checkbox(array(
                                                'name'=>'track_events',
                                                'value'=>'1',
                                                'checked'=> ($frm->track_events == 1) )) . ' &nbsp; Yes'; ?></label>
                        </div>
                    </li>
                <?php endif; ?>
                    <li class="<?php echo alternator('', 'even'); ?>">
                        <label for="frmFileSize">Max File Size
                            <small>Max file size allowed</small></label>
                        <div class="input">
                            <input name="file_maxsize" type="text" value="<?php echo $frm->file_maxsize; ?>" maxlength="20" />
                        </div>
                    </li>

                    <li class="<?php echo alternator('', 'even'); ?>">
                        <label for="file_types">Allowed File types
                            <small>File Types allowed (examples: jpg, png, gif, zip)</small></label>
                        <div class="input">
                            <input name="file_types" type="text" value="<?php echo $frm->file_types; ?>" id="file_types" maxlength="50" />
                        </div>
                    </li>


                    <li class="<?php echo alternator('', 'even'); ?>">
                        <label for="frmInfo">
                            <?php echo lang('pf_frm_info_label'); ?>
                            <small><?php echo lang('pf_frm_info_info'); ?></small>
                        </label>
                        <br style="clear: both;" />
                        <textarea class="wysiwyg-simple" name="frmInfo" rows="10" cols="40"><?php echo $frm->frmInfo; ?></textarea>
                    </li>
                </ul>
                <?php if (isset($frm->id)) echo '<input type="hidden" name="frm_id" value="' . $frm->id . '" />'; ?>
            </fieldset>
        </div>
        <div id="pf-response" class="form_inputs">
            <fieldset>
                <ul>
                    <li class="<?php echo alternator('', 'even');?>">
                        <label for="frmResponse"><?php echo lang('pf_frm_response_template_label'); ?>
                        <small><?php echo lang('pf_frm_response_template_info'); ?></small></label>
                        <br style="clear: both;" />
                        <textarea class="wysiwyg-simple" name="frmResponse" rows="10" cols="40"><?php echo $frm->frmResponse; ?></textarea>
                    </li>
                </ul>
                <?php if (isset($frm->id)) echo '<input type="hidden" name="frm_id" value="' . $frm->id . '" />'; ?>
            </fieldset>
        </div>
        <div id="pf-notify" class="form_inputs">
            <fieldset>
                <ul>
                    <li class="<?php echo alternator('', 'even');?>">
                        <label for="send_to"><?php echo lang('pf_frm_replyto_label'); ?>
                            <small><?php echo lang('pf_frm_replyto_info'); ?></small></label>
                        <div class="input"><?php echo form_input('reply_to', $frm->reply_to)?></div>
                    </li>

                    <li class="<?php echo alternator('', 'even');?>">
                        <label for="send_to"><?php echo lang('pf_frm_sendto_label'); ?>
                            <small><?php echo lang('pf_frm_sendto_info'); ?></small></label>
                        <div class="input"><?php echo form_input('send_to', $frm->send_to, 'id="emails"')?></div>
                    </li>
                    <li class="<?php echo alternator('', 'even');?>">
                        <label for="frmChooseTemplate">Choose Template Type
                        <small><?php echo lang('pf_frm_email_template_info'); ?></small></label>
                        <b>
                            <!--<input type="radio" rel="custom-template" name="pftemplates" <?php if ($frm->frmNotifyTitle != '' || empty($frm->frmNotifyTemplate)) echo 'checked="checked" '; ?>/> Custom Template &nbsp; &nbsp;
                            <input type="radio" rel="email-template" name="pftemplates" <?php if ($frm->frmNotifyTitle == '') echo 'checked="checked" '; ?>/> System Email Template// -->
                            <?php
                            function radio_toggle($name, $items, $cur=false)
                            {
                                $str = '';

                                foreach ($items as $key => $label)
                                {
                                    $sel = ($cur == $key) ? true:false;
                                    $str .= form_radio(array(
                                                'name'=>$name,
                                                'value'=>$key,
                                                'rel'=>$key,
                                                'checked'=>$sel)) . ' ' . $label . ' &nbsp;&nbsp;';
                                }
                                return $str;
                            }

                            if (!$frm->frmNotifyTemplate || $frm->frmNotifyTitle != '') $sel = 'custom-template';
                            else $sel = 'email-template';
                            echo radio_toggle('pftemplates', array('custom-template'=>'Custom Template','email-template'=>'Email Template'), $sel);
                            ?>
                        </b>
                    </li>

                    <li class="pf-tpl email-template <?php echo alternator('', 'even');?>">
                        <label for="frmNotifyTemplate"><?php echo lang('pf_frm_email_template_label'); ?>
                            <small><?php echo lang('pf_frm_email_template_info'); ?></small></label>
                        <div class="input"><?php echo form_dropdown('frmNotifyTemplate', $templates, $frm->frmNotifyTemplate,'id="templates"')?></div>
                    </li>
                    <li class="pf-tpl custom-template <?php echo alternator('', 'even');?>">

                        <h5 style="width:200px;margin:0;"><a class="tooltip-s toggle" original-title="Toggle this element"></a>Custom Tag Reference</h5>
                        <section class="item" style="display:none; width:56%;">
                        
                         <b>Displays the label for a field (requires the field name)</b><br />
                        <pre>
                        {{ label:fieldname }}
                        </pre>
                        <br style="clear:both" />
                        <b>Displays the value for a field (requires the field name)</b><br />
                        <pre>
                        {{ value:fieldname }}
                        </pre>
                        <br style="clear:both" />
                        
                        <b>Display all the labels and fields with a loop.</b><br />
                        <pre>
                        {{ form_entry }}
                        
                        	{{ label }} : {{ value }}
                        
                        {{ /form_entry }}
                        </pre>
                        <br style="clear:both" />
                        
                        <b>Displays the form ID</b><br />
                        <pre>
                        {{ form_id }}
                        </pre>
                        <br style="clear:both" />
                        
                        <b>Displays the form name</b><br />
                        <pre>
                        {{ form_name }}
                        </pre>
                        <br style="clear:both" />
                        
                        <b>Displays the userid</b><br />
                        <pre>
                        {{ user_id }}
                        </pre>
                        <br style="clear:both" />
                        
                        <b>Displays the user name</b><br />
                        <pre>
                        {{ username }}
                        </pre>
                        <br style="clear:both" />
                        
                        <b>Display the date that the entry was submitted</b><br />
                        <pre>
                        {{ created_on }}
                        </pre>
                        <br style="clear:both" />
                        
                        <b>Displays the clients "User Agent"</b><br />
                        <pre>
                        {{ uagent }}
                        </pre>
                        <br style="clear:both" />
                        
                        <b>Displays the clients IP address</b><br />
                        <pre>
                        {{ ip }}
                        </pre>
                        <br style="clear:both" />
                        
                        <b>Displays the clients Operating System</b><br />
                        <pre>
                        {{ os }}
                        </pre><br style="clear:both" />
                        </section>
                    </li>

                    <li id="custom-template" class="pf-tpl custom-template <?php echo alternator('', 'even');?>">
                        <label for="frmNotification"><?php echo lang('pf_frm_email_title_label'); ?>
                            <small><?php echo lang('pf_frm_email_title_info'); ?></small></label>
                        <input name="frmNotifyTitle" type="text" maxlength="100" size="50" value="<?php echo $frm->frmNotifyTitle; ?>" />
                    </li>
                    <li class="pf-tpl custom-template <?php echo alternator('', 'even');?>">
                        <label for="frmNotifyBody"><?php echo lang('pf_frm_email_body_label'); ?>
                            <small><?php echo lang('pf_frm_email_body_info'); ?></small></label>
                        <br style="clear: both;" />
                        <textarea class="wysiwyg-advanced" name="frmNotifyBody" rows="10" cols="40"><?php echo $frm->frmNotifyBody; ?></textarea>
                    </li>
                </ul>
            </fieldset>
        </div>

        <div id="pf-layout" class="form_inputs">
            <fieldset>
                <ul>
                    <li class="<?php echo alternator('', 'even');?>">
                        <label for="frmWrap[form]"><?php echo lang('pf_layout_form_label'); ?>
                        <small><?php echo lang('pf_layout_form_info'); ?></small></label>
                        <div class="input">
                        <input name="frmWrap[form-open]" type="text" size="25" value="<?php echo $frm->layout['form-open']; ?>" />
                        <input name="frmWrap[form-close]" type="text" size="25" value="<?php echo $frm->layout['form-close']; ?>" /></div>
                    </li>
                    <li class="<?php echo alternator('', 'even');?>">
                        <label><?php echo lang('pf_layout_input_label'); ?>
                        <small><?php echo lang('pf_layout_input_info'); ?></small></label>
                        <div class="input">
                        <input name="frmWrap[input-open]" type="text" size="25" value="<?php echo $frm->layout['input-open']; ?>" />
                        <input name="frmWrap[input-close]" type="text" size="25" value="<?php echo $frm->layout['input-close']; ?>" /></div>
                    </li>
                    <li class="<?php echo alternator('', 'even');?>">
                        <label><?php echo lang('pf_layout_label_label'); ?>
                        <small><?php echo lang('pf_layout_label_info'); ?></small></label>
                        <div class="input">
                        <input name="frmWrap[label-open]" type="text" size="25" value="<?php echo $frm->layout['label-open']; ?>" />
                        <input name="frmWrap[label-close]" type="text" size="25" value="<?php echo $frm->layout['label-close']; ?>" /></div>
                    </li>
                    <li class="<?php echo alternator('', 'even');?>">
                        <label><?php echo lang('pf_layout_group_label'); ?>
                        <small><?php echo lang('pf_layout_group_info'); ?></small></label>
                        <div class="input">
                        <input name="frmWrap[group-open]" type="text" size="25" value="<?php echo $frm->layout['group-open']; ?>" />
                        <input name="frmWrap[group-close]" type="text" size="25" value="<?php echo $frm->layout['group-close']; ?>" /></div>
                    </li>
                    <li class="<?php echo alternator('', 'even');?>">
                        <label><?php echo lang('pf_layout_info_label'); ?>
                        <small><?php echo lang('pf_layout_info_info'); ?></small></label>
                        <div class="input">
                        <input name="frmWrap[info-open]" type="text" size="25" value="<?php echo $frm->layout['info-open']; ?>" />
                        <input name="frmWrap[info-close]" type="text" size="25" value="<?php echo $frm->layout['info-close']; ?>" /><br />
                        <?php echo form_dropdown('frmWrap[info-pos]', array('before-label'=>'Before Label','after-label'=>'After Label','before-field'=>'Before Field','after-field'=>'After Field'), $frm->layout['info-pos'])?></div>
                    </li>
                    
                    <li class="<?php echo alternator('', 'even');?>">
                        <label><?php echo lang('pf_layout_success_label'); ?>
                        <small><?php echo lang('pf_layout_success_info'); ?></small></label>
                        <div class="input">
                        <input name="frmWrap[success-open]" type="text" size="25" value="<?php echo $frm->layout['success-open']; ?>" />
                        <input name="frmWrap[success-close]" type="text" size="25" value="<?php echo $frm->layout['success-close']; ?>" />
                    </div>
                    </li>

                    <li class="<?php echo alternator('', 'even');?>">
                        <label><?php echo lang('pf_layout_error_label'); ?>
                        <small><?php echo lang('pf_layout_error_info'); ?></small></label>
                        <div class="input">
                        <input name="frmWrap[error-open]" type="text" size="25" value="<?php echo $frm->layout['error-open']; ?>" />
                        <input name="frmWrap[error-close]" type="text" size="25" value="<?php echo $frm->layout['error-close']; ?>" />
                    </div>
                    </li>

                    <li class="<?php echo alternator('', 'even');?>">
                        <label><?php echo lang('pf_layout_required_label'); ?>
                        <small><?php echo lang('pf_layout_required_info'); ?></small></label>
                        <div class="input"><input name="frmWrap[required-tag]" type="text" maxlength="50" size="50" value="<?php echo $frm->layout['required-tag']; ?>" /><br />
                        <?php echo form_dropdown('frmWrap[required-pos]', array('before-label'=>'Before Label','after-label'=>'After Label','before-field'=>'Before Field','after-field'=>'After Field'), $frm->layout['required-pos'])?></div>
                    </li>
                </ul>
                <?php if (isset($frm->id)) echo '<input type="hidden" name="frm_id" value="' . $frm->id . '" />'; ?>
            </fieldset>
        </div>

    </div>

    <div class="buttons">
        <?php $this->load->view('admin/partials/buttons', array('buttons' => array('save', 'cancel')));
        ?>
    </div>
    <?php echo form_close(); ?>
</div>
</section>

<script>

$(document).ready(function($){
    var pf_help_data = {
        form_name: 'Name of the field (no spaces).',
        form_label: 'Labels for the field.',
        form_type: 'What type of field is this?',
        form_info: 'Information about the field.',
        form_prep: 'Would you like to format the data in any way?<br><ul><li><b><u>Trim</u></b>&nbsp; trim spaces off the beginning and end of the data.</li><li><b><u>XSS Clean</u></b>&nbsp; clean and filter input to prevent cross-site scripting attacks.</li><li><b><u>URL</u></b>&nbsp; Adds "http://" to URLs if missing.</li></ul>',
        form_validation: 'Rules used to validate the field.',
        form_group: 'Use the interface below to create a field group.'
    };
    $('.help-icon').tipsy(
    {
        html: true,
        gravity: 'w',
        live: true,
        title: function() {
            return pf_help_data[$(this).attr('data-help')];
        }
    });

    $(".toggle-body").css({display: "none"});
    $(".toggle-slide").live('click',function(){
        var $icon = $('.sprite', this);
        $(this).next(".toggle-body").slideToggle('fast');
        if ($icon.hasClass('sp-minus')) {
            $icon.removeClass('sp-minus').addClass('sp-plus');
        } else {
            $icon.removeClass('sp-plus').addClass('sp-minus');
        }
    });

    var pf_links = $('form [name="pftemplates"]');
    var _pftpl_current = $('.' + $('form [name="pftemplates"]:checked').attr('rel'));
    $('.pf-tpl').hide();
    _pftpl_current.show();
    pf_links.change(function(){
        if ($(this).not(':checked')) {
            $('.pf-tpl').hide();
            $('.'+$(this).attr('rel')).show();
        }
    });

        function string_to_slug(str) {
            str = str.replace(/^\s+|\s+$/g, ''); // trim
            str = str.toLowerCase();

            // remove accents, swap ñ for n, etc
            var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
            var to   = "aaaaeeeeiiiioooouuuunc------";
            for (var i=0, l=from.length ; i<l ; i++) {
                str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }

            str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
            .replace(/\s+/g, '-') // collapse whitespace and replace by -
            .replace(/-+/g, '-'); // collapse dashes

            return str;
        }
        $('input[name="frmName"]').live('keyup', function(){
            input_val = $(this).val();
            if ( ! input_val.length ) return;
            $('input[name="frmSlug"]').val(string_to_slug(input_val));
        });
        $.ajaxSetup({
            allowEmpty: true
        });
        function isValidEmailAddress(emailAddress) {
            var pattern = new RegExp(/^(("[\w-+\s]+")|([\w-+]+(?:\.[\w-+]+)*)|("[\w-+\s]+")([\w-+]+(?:\.[\w-+]+)*))(@((?:[\w-+]+\.)*\w[\w-+]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][\d]\.|1[\d]{2}\.|[\d]{1,2}\.))((25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\.){2}(25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\]?$)/i);
            return pattern.test(emailAddress);
        };

        $('#file_types').tagsInput({
            'defaultText':'Add extention',
            'minChars' : 2,
            'maxChars' : 0 //if not provided there is no limit
        });
        $('#emails').tagsInput({
            'defaultText':'Add email address',
            onAddTag: function(elem, elem_tags)
            {
                $('.tag span', elem_tags).each(function()
                {
                    txt = $(this).text().trim();
                    if (!isValidEmailAddress(txt)){
                        $('#emails').removeTag(txt);
                        $('#emails_tag').val(txt).css('border-color', 'red');
                        return false;
                    }
                    else {
                        $('#emails_tag').css('border-color', '#ED8E28');
                    }
                });
            },
            'minChars' : 0,
            'maxChars' : 0 //if not provided there is no limit
        });
    });
</script>