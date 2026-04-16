<div class="panel article">
    <div class="panel-body">
        <h1>Aktivering</h1>
    </div>
    <div class="panel-body">


        <div class="alert alert-success">
            <p>Du bör få ett mail med en länk som gör att du kan aktivera ditt konto. Kontrollera din Epost och att inte
                mailet fastnat i ditt skräppost-filter om du inte ser e-post efter
                ett par minuter. Alternativt kan du aktivera ditt medlemsskap genom att fylla i formuläret nedan om du
                har din aktiveringskod tillgänglig.</p>
        </div>

        <?php if (!empty($error_string)): ?>
            <div class="error-box">
                <?php echo $error_string ?>
            </div>
        <?php endif; ?>

        <?php echo form_open('users/activate', 'id="activate-user"') ?>
        <div class="form-group">
            <label for="email"><?php echo lang('global:email') ?></label>
            <?php echo form_input('email', isset($_user['email']) ? $_user['email'] : '', 'class="form-control"'); ?>
        </div>

        <div class="form-group">
            <label for="activation_code"><?php echo lang('user:activation_code') ?></label>
            <?php echo form_input('activation_code', '', 'class="form-control"'); ?>
        </div>

        <?php echo form_submit('btnSubmit', lang('user:activate_btn'), 'class="btn btn-default"') ?>

        <?php echo form_close() ?>

    </div>
</div>
