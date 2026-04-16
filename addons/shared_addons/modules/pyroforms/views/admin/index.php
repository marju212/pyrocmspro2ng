<section class="title">
    <h4><?php echo lang('pf_index_title'); ?></h4>
</section>
<section class="item">
    <div class="content">
    <?php if (!empty($frm)): ?>
        <?php echo form_open('admin/pyroforms/action', 'class="crud"') ?>
        <table cellspacing="0" border="0" class="table-list">
            <thead>
                <tr>
                    <th><?php echo form_checkbox(array('name' => 'action_to_all', 'class' => 'check-all')); ?></th>
                    <th><?php echo lang('pf_frm_name_label') ?></th>
                    <th class="width-10"><span><?php echo lang('global:actions'); ?></span></th>
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
                <?php foreach ($frm as $f): ?>
                    <tr>
                        <td class="action-to"><?php echo form_checkbox('action_to[]', $f->id) ?></td>
                        <td><?php echo $f->frmName; ?></td>
                        <td class="buttons buttons-small">
                            <?php echo anchor('admin/pyroforms/edit/' . $f->id, lang('pf_global:edit'), 'class="btn orange edit button"'); ?>
                            <?php echo anchor('admin/pyroforms/manage/' . $f->id, lang('pf_global:fields'), 'class="btn green view button"'); ?>
                            <!--<?php echo anchor('admin/pyroforms/manage_views/' . $f->id, 'View fields', 'class="btn green view button"'); ?>//-->
                            <?php echo anchor('admin/pyroforms/logs/' . $f->id, lang('pf_view_entries').'&nbsp;(' . $f->entry_count . ')', 'class="btn blue view button"'); ?>
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
            <?php echo lang('pf_no_forms'); ?>
        </div>
    <?php endif; ?>
    </div>
</section>
