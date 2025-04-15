<?php
/**
 * Prometheus Plugin Admin UI (Single Page)
 *
 * Allows configuration of metrics path, allowlist, and viewing audit logs.
 * Compatible with MyBB 1.8 ACP plugin system.
 *
 * BSD 3-Clause License
 *
 * Contributor: 0xRogu
 */

if (!defined('IN_MYBB') || !defined('IN_ADMINCP')) {
    die('Direct initialization of this file is not allowed.');
}

$page->add_breadcrumb_item('Prometheus Metrics');

// Load current settings from environment or fallback
$metrics_path = $_ENV['PROMETHEUS_METRICS_PATH'] ?? 'prometheus_metrics';
$allowlist = $_ENV['PROMETHEUS_ALLOWLIST'] ?? '';

// Handle form submission
if ($mybb->request_method === 'post') {
    // Save settings to inc/plugins/MybbStuff/Prometheus/config.php
    $config_file = MYBB_ROOT . 'inc/plugins/MybbStuff/Prometheus/config.php';
    $config = [
        'PROMETHEUS_METRICS_PATH' => $mybb->get_input('metrics_path'),
        'PROMETHEUS_ALLOWLIST' => $mybb->get_input('allowlist'),
    ];
    $config_php = "<?php\n";
    foreach ($config as $key => $value) {
        $config_php .= "putenv(\"$key=" . addslashes($value) . "\");\n";
    }
    file_put_contents($config_file, $config_php);
    flash_message('Prometheus plugin settings updated. Please reload your web server if using PHP-FPM.', 'success');
    admin_redirect('index.php?module=config-plugins&action=prometheus');
}

// Audit log preview
$log_file = MYBB_ROOT . 'inc/plugins/MybbStuff/Prometheus/logs/metrics_audit.log';
$audit_log = '';
if (file_exists($log_file)) {
    $lines = file($log_file);
    $audit_log = implode("<br>", array_slice($lines, -20)); // Show last 20 entries
}

$form = new Form('index.php?module=config-plugins&action=prometheus', 'post');

$page->output_header('Prometheus Metrics Configuration');

$table = new Table;
$table->construct_header('Setting', ['width' => '30%']);
$table->construct_header('Value');
$table->construct_row([
    'Metrics Path',
    $form->generate_text_box('metrics_path', $metrics_path, ['style' => 'width: 300px;'])
]);
$table->construct_row([
    'IP Allowlist',
    $form->generate_text_box('allowlist', $allowlist, ['style' => 'width: 300px;'])
]);

$table->output('Prometheus Metrics Plugin Settings');

$form->output_submit_wrapper(['Update Settings']);
$form->end();

// Audit log display
if ($audit_log) {
    echo '<h3>Recent Metrics Endpoint Access Log</h3>';
    echo '<div style="max-height:200px;overflow:auto;border:1px solid #ccc;padding:8px;font-family:monospace;font-size:12px;">' . $audit_log . '</div>';
}

$page->output_footer();
