<section class="title">
    <h4><?php echo lang('pf_entry_details_title') ?> &nbsp; "<?php echo $entry->formname; ?>" - ID: <b><?php echo $entry->id; ?></b></h4>
</section>
<section class="item">
    <div class="content">
    <?php if (!empty($entry)): ?>
        <?php echo form_open('admin/pyroforms/delete_logs','class="crud"') ?>
        <input type="hidden" name="action_to[]" value="<?php echo $entry->id; ?>" />
        <input type="hidden" name="form_id" value="<?php echo $entry->form_id; ?>" />
        <input type="hidden" name="btnAction" value="delete" />
        <table cellspacing="0" border="0" class="table-list">
            <thead>
                <tr>
                    <th><?php echo lang('pf_frm_name_label') ?></th>
                    <th><?php echo lang('pf_username_label'); ?></th>
                    <th><?php echo lang('pf_created_label'); ?></th>
                    <th>IP</th>
                    <th><?php echo lang('pf_client_label'); ?></th>
                    <th>OS</th>
                    <th><?php echo lang('pf_frm_sendto_label'); ?></th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td><?php echo $entry->formname; ?></td>
                    <td><?php echo $entry->username; ?></td>
                    <td><?php echo format_date($entry->created_on); ?></td>
                    <td><?php echo $entry->ip; ?></td>
                    <td><?php echo $entry->uagent; ?></td>
                    <td><?php echo $entry->os; ?></td>
                    <td><?php echo $entry->send_to; ?></td>
                </tr>
            </tbody>
        </table>
<br />

        <table cellspacing="0" border="0" class="table-list">
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
    <?php
    foreach ($entry->data as $key => $val):
        if (!isset($fields[$key])) continue;
    ?>
                    <tr>
                        <td><?php echo $fields[$key]; ?></td>
                        <td><?php
        if (is_array($val))
        {
            echo implode(', ', $val);
        } else{
            if ($types[$key] == 'file')
            {
                echo $val;
            }else echo (empty($val)) ? '&nbsp;' : $val;
        }
        ?></td>
                    </tr>
    <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" name="btnAction" value="<?php echo lang('pf_global:delete'); ?>" class="btn red">
            <span><?php echo lang('pf_global:delete'); ?></span>
        </button>
        <?php //$this->load->view('admin/partials/buttons', array('buttons' => array('delete'))); ?>

            <?php echo form_close(); ?>
    <?php else: ?>
        <div class="no_data">
    <?php echo lang('pf_no_forms'); ?>
        </div>
<?php endif; ?>
</div>
</section>
