<section class="title">
    <h4><?php echo lang('pf_entry_list_title') ?> &nbsp; "<?php echo $form['name']; ?>" - <b>ID:<?php echo $form['id']; ?></b></h4>
</section>

<section class="item">
    <div class="content">
    <?php if(!empty($entry)): ?>
	<?php echo form_open('admin/pyroforms/logs_action', 'class="crud"') ?>
	<input type="hidden" name="form_id" id="frm_id" value="<?php echo $form['id']; ?>">
	<table cellspacing="0" border="0" class="table-list">
        <thead>
                <tr>
                    <th><?php echo form_checkbox(array('name' => 'action_to_all', 'class' => 'check-all'));?></th>
                    <th><?php echo lang('pf_username_label'); ?></th>
                    <th><?php echo lang('pf_created_label'); ?></th>
                    <th>IP</th>
                    <th><?php echo lang('pf_client_label'); ?></th>
                    <th>OS</th>
                    <th class="width-10"><span><?php echo lang('global:actions');?></span></th>
                </tr>
        </thead>
        <tfoot>
                <tr>
                        <td colspan="5">
                                <div class="inner"><?php $this->load->view('admin/partials/pagination'); ?></div>
                        </td>
                </tr>
        </tfoot>
        <tbody>
            <?php foreach($entry as $e): ?>
            <tr>
                <td class="action-to"><?php echo form_checkbox('action_to[]', $e->id) ?></td>
                <td><?php echo (!empty($e->username)) ? $e->username:'Guest'; ?></td>
                <td><?php echo format_date($e->created_on); ?></td>
                <td><?php echo $e->ip; ?></td>
                <td><?php echo $e->uagent; ?></td>
                <td><?php echo $e->os; ?></td>
                <td class="buttons buttons-small">
                    <?php echo anchor('admin/pyroforms/details/'.$e->id, 'Details', 'class="btn orange edit button"'); ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
	</table>
	<div class="table_action_buttons">
        <?php $this->load->view('admin/partials/buttons', array('buttons' => array('delete'))); ?>
	</div>
	<?php echo form_close(); ?>
<?php else: ?>
    <div class="no_data">
        No form results yet.
    </div>
<?php endif; ?>
</div>
</section>

