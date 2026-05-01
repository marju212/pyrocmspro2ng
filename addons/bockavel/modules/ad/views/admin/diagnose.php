<?php
// Temporary diagnostic page. Delete with the matching admin->diagnose()
// method when the ad-image / ad-listing bug is resolved.
$pp = function ($v) {
    return '<pre style="background:#f7f7f7;border:1px solid #ddd;padding:8px;'
         . 'overflow:auto;font-size:12px;line-height:1.4;">'
         . htmlspecialchars(print_r($v, true), ENT_QUOTES, 'UTF-8')
         . '</pre>';
};
?>
<section class="title"><h4>Ad diagnostics</h4></section>
<section class="item">
    <div class="content">
        <p style="color:#a00;"><strong>Temporary page.</strong> Remove
        <code>diagnose()</code> in
        <code>addons/bockavel/modules/ad/controllers/admin.php</code> and
        flip <code>backend</code> back to <code>FALSE</code> in
        <code>details.php</code> when done.</p>

        <h3>Environment</h3>
        <?= $pp($diag['env']) ?>

        <h3>Tables matching <code>%_ads</code></h3>
        <?= $pp($diag['ads_tables']) ?>

        <h3>Per-table detail</h3>
        <?php foreach ($diag['per_table'] as $tbl => $info): ?>
            <h4><code><?= htmlspecialchars($tbl) ?></code></h4>
            <p>
                Has <code>updated</code> column:
                <strong><?= $info['has_updated'] ? 'yes' : 'NO' ?></strong>
                &middot; Files table: <code><?= htmlspecialchars($info['files_table']) ?></code>
                (exists: <strong><?= $info['files_table_exists'] ? 'yes' : 'no' ?></strong>)
            </p>

            <h5>Columns</h5>
            <?= $pp($info['columns']) ?>

            <h5>Last 5 rows</h5>
            <?= $pp($info['recent']) ?>

            <h5>Rows where ad_title = 'öö'</h5>
            <?= $pp($info['oo_match']) ?>

            <?php if (isset($info['latest_image_lookup'])): ?>
                <h5>Latest row's image slots resolved against
                    <code><?= htmlspecialchars($info['files_table']) ?></code>
                    + disk</h5>
                <?= $pp($info['latest_image_lookup']) ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <h3>Registered event listeners</h3>
        <?= $pp($diag['event_listeners']) ?>

        <h3>Ad module row in <code><?= htmlspecialchars(SITE_REF) ?>_modules</code></h3>
        <?= $pp($diag['ad_module_row']) ?>
    </div>
</section>
