<div class="panel article">
    <div class="panel-body">
        <h1>Logga in</h1>
    </div>

    <div class="panel-body">


        <?php if (validation_errors()): ?>
            <div class="alert alert-danger">
                <?php echo validation_errors(); ?>
            </div>
        <?php endif ?>

        <?php echo form_open('users/login', array('id' => 'login'), array('redirect_to' => $redirect_to, 'role' => 'form')) ?>

        <div class="form-group">
            <label for="email">E-postadress</label>
            <?php echo form_input('email', $this->input->post('email') ? $this->input->post('email') : '', 'class="form-control"') ?>
        </div>

        <div class="form-group">
            <label for="password">Lösenord</label>
            <input type="password" id="password" name="password" maxlength="20" class="form-control">
        </div>

        <div class="checkbox">
            <label>
                <?php echo form_checkbox('remember', '1', false) ?>
                Kom ihåg mig
            </label>
        </div>

        <input type="submit" value="Logga in" name="btnLogin" class="btn btn-default" style="margin-top:5px;">


        <p style="margin-top:15px;"><?php echo anchor('users/reset_pass', 'Glömt lösenord'); ?>
            | <?php echo anchor('blimedlem', 'Bli medlem'); ?></p>

        <?php echo form_close() ?>


    </div>
</div>
