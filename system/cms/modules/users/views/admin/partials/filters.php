<fieldset id="filters" class="users-filters">

	<legend><?php echo lang('global:filters') ?></legend>

	<?php
		$date_presets = array(
			''          => lang('global:select-all'),
			'7days'     => lang('user:filter_date_7days'),
			'30days'    => lang('user:filter_date_30days'),
			'this_year' => lang('user:filter_date_this_year'),
		);
	?>

	<style>
		#filters.users-filters form > ul {
			display: flex;
			flex-wrap: wrap;
			gap: 12px 16px;
			margin: 8px 0;
			padding: 0;
			list-style: none;
		}
		#filters.users-filters form > ul > li {
			flex: 1 1 280px;
			max-width: 360px;
			margin: 0;
			padding: 0;
			float: none;
			display: block;
			list-style: none;
		}
		#filters.users-filters form > ul > li.filter-reset {
			flex: 0 0 auto;
			max-width: none;
			align-self: flex-end;
			margin-left: auto;
		}
		#filters.users-filters form > ul > li label {
			display: block;
			float: none;
			width: auto;
			margin: 0 0 4px 0;
			white-space: nowrap;
		}
		#filters.users-filters form > ul > li select,
		#filters.users-filters form > ul > li input[type="text"] {
			width: 100%;
			box-sizing: border-box;
			margin: 0;
		}
		/* Selects are upgraded to chosen.js which writes an inline width
		   on .chzn-container based on the original select's intrinsic width.
		   Override that so the dropdown fills its <li>. */
		#filters.users-filters form > ul > li .chzn-container {
			width: 100% !important;
		}
		#filters.users-filters form > ul > li .chzn-container .chzn-drop {
			width: 100% !important;
		}
		/* None of the filter dropdowns has enough options to warrant chosen's
		   built-in search box — hide it. */
		#filters.users-filters form > ul > li .chzn-container .chzn-search {
			display: none;
		}
		#filters.users-filters form > ul > li .chzn-container .chzn-drop {
			padding-top: 0 !important;
		}
	</style>

	<?php echo form_open('') ?>
	<?php echo form_hidden('f_module', $module_details['slug']) ?>
		<ul>
			<li>
				<?php echo lang('user:active', 'f_active') ?>
				<?php echo form_dropdown('f_active', array(0 => lang('global:select-all'), 1 => lang('global:yes'), 2 => lang('global:no')), array(0)) ?>
			</li>

			<li>
				<?php echo lang('user:group_label', 'f_group') ?>
				<?php echo form_dropdown('f_group', array(0 => lang('global:select-all')) + $groups_select) ?>
			</li>

			<li>
				<?php echo lang('user:filter_joined', 'f_joined') ?>
				<?php echo form_dropdown('f_joined', $date_presets) ?>
			</li>

			<li>
				<?php echo lang('user:filter_last_visit', 'f_last_visit') ?>
				<?php echo form_dropdown('f_last_visit', $date_presets) ?>
			</li>
		</ul>
		<ul>
			<li>
				<?php echo lang('user:filter_name', 'f_name') ?>
				<?php echo form_input('f_name', '', 'id="f_name"') ?>
			</li>

			<li>
				<?php echo lang('user:filter_email', 'f_email') ?>
				<?php echo form_input('f_email', '', 'id="f_email"') ?>
			</li>

			<li class="filter-reset"><?php echo anchor(current_url(), lang('user:filter_reset'), 'class="cancel"') ?></li>
		</ul>
	<?php echo form_close() ?>
</fieldset>
