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
								&nbsp;<span style="color: #c00;">— <?php echo ($target - $current); ?> migration(s) pending. Hit any page on the site to trigger them.</span>
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

<div class="one_full">
	<section class="title">
		<h4>Streams &laquo;ads&raquo; diagnostics</h4>
	</section>

	<section class="item">
		<div class="content">

			<table class="table-list" cellspacing="0" style="margin-bottom: 1em;">
				<tbody>
					<tr>
						<th style="width: 30%;">SITE_REF</th>
						<td><code><?php echo htmlspecialchars($site_ref, ENT_QUOTES, 'UTF-8'); ?></code></td>
					</tr>
					<tr>
						<th>data_streams row</th>
						<td>
							<?php if ($stream): ?>
								namespace <code><?php echo htmlspecialchars($stream->stream_namespace, ENT_QUOTES, 'UTF-8'); ?></code>,
								slug <code><?php echo htmlspecialchars($stream->stream_slug, ENT_QUOTES, 'UTF-8'); ?></code>,
								prefix <code><?php echo htmlspecialchars((string) $stream->stream_prefix, ENT_QUOTES, 'UTF-8'); ?></code>
							<?php else: ?>
								<span style="color: #c00;">no stream registered with slug=ads</span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th>Resolved table name</th>
						<td>
							<code><?php echo htmlspecialchars($ads_table, ENT_QUOTES, 'UTF-8'); ?></code>
							<?php if ($ads_table_exists): ?>
								<span style="color: #080;">— exists</span>
							<?php else: ?>
								<span style="color: #c00;">— MISSING</span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th>updated column definition</th>
						<td>
							<?php if ($updated_column): ?>
								<code>
									<?php echo htmlspecialchars($updated_column->Type, ENT_QUOTES, 'UTF-8'); ?>
									Null=<?php echo htmlspecialchars($updated_column->Null, ENT_QUOTES, 'UTF-8'); ?>
									Default=<?php echo htmlspecialchars((string) $updated_column->Default, ENT_QUOTES, 'UTF-8'); ?>
									Extra=<?php echo htmlspecialchars($updated_column->Extra, ENT_QUOTES, 'UTF-8'); ?>
								</code>
								<?php
									$ok = (
										stripos($updated_column->Type, 'timestamp') !== false
										&& strtolower($updated_column->Null) === 'no'
										&& stripos((string) $updated_column->Default, 'CURRENT_TIMESTAMP') !== false
										&& stripos($updated_column->Extra, 'on update CURRENT_TIMESTAMP') !== false
									);
								?>
								<?php if ($ok): ?>
									<span style="color: #080;">— migration 132 applied</span>
								<?php else: ?>
									<span style="color: #c00;">— still on the old definition; migration 132 may have failed silently for this table</span>
								<?php endif; ?>
							<?php else: ?>
								<span style="color: #c00;">column not found</span>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>

			<?php if ($recent_ads): ?>
				<table class="table-list" cellspacing="0">
					<thead>
						<tr>
							<th style="width: 60px;">id</th>
							<th>created</th>
							<th>updated</th>
							<th>in 2-month window?</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($recent_ads as $r): ?>
						<tr>
							<td><?php echo (int) $r->id; ?></td>
							<td><code><?php echo htmlspecialchars((string) $r->created, ENT_QUOTES, 'UTF-8'); ?></code></td>
							<td><code><?php echo htmlspecialchars((string) $r->updated, ENT_QUOTES, 'UTF-8'); ?></code></td>
							<td>
								<?php if ((int) $r->in_window === 1): ?>
									<span style="color: #080;">yes</span>
								<?php else: ?>
									<span style="color: #c00;">no — frontend will hide it</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php elseif ($ads_table_exists): ?>
				<p>Table exists but is empty.</p>
			<?php endif; ?>

		</div>
	</section>
</div>
