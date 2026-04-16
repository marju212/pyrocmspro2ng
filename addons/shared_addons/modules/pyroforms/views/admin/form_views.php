<section class="title">
	<h4>View Fields</h4>
</section>
<section class="item">
	<p>Select the fields below that will display in your log views.</p>
    <?php echo form_open(current_url(), 'id="frm-viewbuilder" class="crud"'); ?>
	<table border="0" class="table-list frm-list">
        <thead>
                <tr>
                    <th><?php echo form_checkbox(array('name' => 'action_to_all', 'class' => 'check-all'));?></th>
                    <th>Field Name</th>
                </tr>
        </thead>
        <tbody>
            <?php foreach($fields as $e): ?>
            <tr>
                <td class="action-to"><?php echo form_checkbox('fields_v[]', $e->fldName, (in_array($e->fldName, $selected))) ?></td>
                <td><?php echo $e->fldLabel; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
	</table>
    <div class="buttons">
        <?php $this->load->view('admin/partials/buttons', array('buttons' => array('save', 'cancel')));
        ?>
    </div>
	<?php echo form_close(); ?>
</section>