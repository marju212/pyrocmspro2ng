<div class="one_full">
	<section class="title">
		<h4>Database migrations</h4>
	</section>

	<section class="item">
		<div class="content">

			<table class="table-list" cellspacing="0" style="margin-bottom: 1em;">
				<tbody>
					<tr>
						<th style="width: 30%;">Current applied version</th>
						<td><strong><?php echo $current; ?></strong></td>
					</tr>
					<tr>
						<th>Target version (config)</th>
						<td>
							<strong><?php echo $target; ?></strong>
							<?php if ($target > $current): ?>
								&nbsp;<span style="color: #c00;">— <?php echo ($target - $current); ?> migration(s) pending. Run any page on the site to trigger them.</span>
							<?php elseif ($target < $current): ?>
								&nbsp;<span style="color: #c00;">— DB is ahead of code. Investigate before redeploying older code.</span>
							<?php else: ?>
								&nbsp;<span style="color: #080;">— in sync.</span>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>

			<?php if ($migrations): ?>
				<table class="table-list" cellspacing="0">
					<thead>
						<tr>
							<th style="width: 80px;">Version</th>
							<th>Name</th>
							<th>File</th>
							<th style="width: 100px;">Status</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($migrations as $m): ?>
						<tr>
							<td><?php echo $m['version']; ?></td>
							<td><?php echo htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8'); ?></td>
							<td><code><?php echo htmlspecialchars($m['file'], ENT_QUOTES, 'UTF-8'); ?></code></td>
							<td>
								<?php if ($m['applied']): ?>
									<span style="color: #080;">applied</span>
								<?php elseif ($m['pending']): ?>
									<span style="color: #c00;">pending</span>
								<?php else: ?>
									<span style="color: #888;">future</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else: ?>
				<p>No migration files found at <code><?php echo APPPATH.'migrations/'; ?></code>.</p>
			<?php endif; ?>

		</div>
	</section>
</div>
