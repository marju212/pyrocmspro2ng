<div class="panel article">
    {{ streams:cycle stream="article" namespace="pages" where="`id` = 4"}}
    <div class="panel-body">
        <h1>{{ artikelrubrik }}</h1>
    </div>
    <div class="panel-body">
        <b>{{ ingress }}</b>
    </div>
    <div class="panel-body">
        {{ artikeltext }}
    </div>
    {{ /streams:cycle }}


    <div class="panel-body">

        <?php if (!empty($error_string)): ?>
            <div class="alert alert-danger">
                <?php echo $error_string; ?>
            </div>
        <?php endif; ?>

        <?php echo form_open('register', array('id' => 'register'), 'role="form"') ?>

        <?php if (!Settings::get('auto_username')): ?>
            <div class="form-group">
                <label for="username"><?php echo lang('user:username') ?></label>
                <input type="text" name="username" maxlength="100" value="<?php echo $_user->username ?>"
                       class="form-control">
            </div>
        <?php endif ?>

        <div class="form-group">
            <label for="email"><?php echo lang('global:email') ?></label>
            <input type="text" name="email" maxlength="100" value="<?php echo $_user->email ?>" class="form-control">
            <?php echo form_input('d0ntf1llth1s1n', ' ', 'class="default-form" style="display:none"') ?>
        </div>

        <div class="form-group">
            <label for="password"><?php echo lang('global:password') ?></label>
            <input type="password" name="password" maxlength="100" class="form-control">
        </div>

        <div id="profile-fields">

            <?php
            $profile_fields_copy[] = $profile_fields[1];
            $profile_fields_copy[] = $profile_fields[0];
            $profile_fields_copy[] = $profile_fields[2];
            $profile_fields_copy[] = $profile_fields[6];
            $profile_fields_copy[] = $profile_fields[7];


            foreach ($profile_fields_copy as $field): ?>

                <?php if ($field['field_slug'] == 'company' OR ($field['required'] and $field['field_slug'] != 'display_name')): ?>
                    <div class="form-group">
                        <label
                            for="<?php echo $field['field_slug'] ?>"><?php echo (lang($field['field_name'])) ? lang($field['field_name']) : $field['field_name']; ?></label>
                        <small>&nbsp;<?php echo $field['instructions']; ?></small>
                        <?php echo str_replace("/>", 'class="form-control" />', $field['input']); ?>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>

        </div>
        <div class="form-group" style="margin-top:30px;">
            <p class="help-block">Vi kommer att behandla din medlemsansökan när vi mottagit din betalning.</p>
        </div>
        <div class="checkbox" style="margin:30px 0;">
            <label class="control-label">
                <input name="medlemsvillkor[]" value="1" class="checkbox"
                       type="checkbox"> Ja, jag har läst och godkänner
                <a data-toggle="modal" data-target=".villkor" style="text-decoration: underline;">medlemsvillkoren</a>
            </label>
        </div>
        <div id="profile-fields" style="width:75%;margin:0 auto;">
            <?php echo form_submit('btnSubmit', 'Skicka in ansökan om medlemskap', 'class="btn btn-success btn-md btn-block"') ?>
        </div>

        <?php echo form_close() ?>

    </div>

</div>

<script>
    $(function () {
        $('input[name="medlemsvillkor[]"].form-control').closest('.form-group').remove();
    });
</script>

{{ streams:cycle stream="article" namespace="pages" where="`id` = 8"}}
<div class="modal fade villkor" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="panel-body">
                <h1>{{ artikelrubrik }}</h1>
            </div>
            <div class="panel-body">
                <b>{{ ingress }}</b>
            </div>
            <div class="panel-body">
                {{ artikeltext }}
            </div>
        </div>
    </div>
</div>
{{ /streams:cycle }}
