<?php
/**
 * BSD 3-Clause License
 * Copyright (c) Euan Torano, 0xRogu, and contributors. All rights reserved.
 * See LICENSE file in the root directory.
 */
declare(strict_types=1);

// Autoload config file for environment overrides
$prometheusConfig = __DIR__ . '/MybbStuff/Prometheus/config.php';
if (file_exists($prometheusConfig)) {
    require_once $prometheusConfig;
}

if (version_compare(PHP_VERSION, '8.3.0', '<')) {
    die('This plugin requires PHP 8.3.0 or higher.');
}

use MybbStuff\Core\ClassLoader;
use MybbStuff\Prometheus\MetricReporterRegistry;
use MybbStuff\Prometheus\MetricReporters\{AwaitingActivationMetricReporter,
	BoardStatsMetricReporter,
	MailQueueMetricReporter,
	MostOnlineMetricReporter,
	OnlineUsersMetricReporter,
	ReportedContentMetricReporter,
	VersionCodeMetricReporter};

if (!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

defined('MYBBSTUFF_CORE_PATH') || define('MYBBSTUFF_CORE_PATH', __DIR__ . '/MybbStuff/Core');
defined('PROMETHEUS_PLUGIN_PATH') || define('PROMETHEUS_PLUGIN_PATH', __DIR__ . '/MybbStuff/Prometheus');

defined('MYBBSTUFF_PLUGINS_CACHE_NAME') || define('MYBBSTUFF_PLUGINS_CACHE_NAME', 'mybbstuff_plugins');

require_once MYBBSTUFF_CORE_PATH . '/src/ClassLoader.php';

$classLoader = ClassLoader::getInstance();
$classLoader->registerNamespace(
    'MybbStuff\\Prometheus\\',
	PROMETHEUS_PLUGIN_PATH . '/src/',
);
$classLoader->register();

/**
 * Returns plugin information for MyBB admin.
 */
function prometheus_info(): array
{
    return [
        'name'          => 'Prometheus',
        'description'   => 'A MyBB plugin to expose metrics to Prometheus.',
        'website'       => 'https://www.mybbstuff.com',
        'author'        => 'Euan Torano',
        'authorsite'    => '',
        'version'       => '0.0.2',
        'compatibility' => '18*', // Compatible with all MyBB 1.8.x versions
        'codename'      => 'mybbstuff_prometheus',
        // Add admin UI link for MyBB ACP
        'extra_info'    => '<a href="index.php?module=config-plugins&action=prometheus" target="_blank">Prometheus Plugin Settings</a>',
    ];
}

/**
 * Reads a value from the MyBB cache and returns an array.
 */
function prometheus_cache_read(datacache $cache, string $name): array
{
    $cached = $cache->read($name);

    if ($cached === false) {
        $cached = [];
    }

    return $cached;
}

/**
 * Installs the Prometheus plugin.
 */
function prometheus_install(): void
{
    global $cache;

    $pluginsCache = prometheus_cache_read($cache, MYBBSTUFF_PLUGINS_CACHE_NAME);
    if (isset($pluginsCache['prometheus'])) {
        unset($pluginsCache['prometheus']);
    }
    $cache->update(MYBBSTUFF_PLUGINS_CACHE_NAME, $pluginsCache);
}

/**
 * Checks if the Prometheus plugin is installed.
 */
function prometheus_is_installed(): bool
{
    global $cache;

    $pluginsCache = prometheus_cache_read($cache, MYBBSTUFF_PLUGINS_CACHE_NAME);
    return isset($pluginsCache['prometheus']);
}

/**
 * Uninstalls the Prometheus plugin.
 */
function prometheus_uninstall(): void
{
    global $cache;

    $pluginsCache = prometheus_cache_read($cache, MYBBSTUFF_PLUGINS_CACHE_NAME);
    if (isset($pluginsCache['prometheus'])) {
        unset($pluginsCache['prometheus']);
    }
    $cache->update(MYBBSTUFF_PLUGINS_CACHE_NAME, $pluginsCache);
}

/**
 * Activates the Prometheus plugin.
 */
function prometheus_activate(): void
{
    global $cache;

    $pluginInfo = prometheus_info();
    $pluginsCache = prometheus_cache_read($cache, MYBBSTUFF_PLUGINS_CACHE_NAME);
    $pluginsCache['prometheus'] = [
        'version' => $pluginInfo['version'],
    ];
    $cache->update(MYBBSTUFF_PLUGINS_CACHE_NAME, $pluginsCache);
}

/**
 * Deactivates the Prometheus plugin.
 */
function prometheus_deactivate(): void
{

}

/**
 * Returns true if HTTP Basic Auth is required for metrics endpoint.
 */
function prometheus_needs_basic_auth(): bool
{
	return isset($_ENV['PROMETHEUS_PASSWORD']);
}

/**
 * Verifies HTTP Basic Auth credentials for metrics endpoint.
 */
function prometheus_verify_credentials(): bool
{
	$user = 'prometheus';
	if (!empty($_ENV['PROMETHEUS_USER'])) {
		$user = $_ENV['PROMETHEUS_USER'];
	}

	if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
		return false;
	}

	return hash_equals($user, $_SERVER['PHP_AUTH_USER']) &&
		hash_equals($_ENV['PROMETHEUS_PASSWORD'], $_SERVER['PHP_AUTH_PW']);
}

$plugins->add_hook('misc_start', 'prometheus_metrics');
/**
 * Handles the Prometheus metrics endpoint.
 */
function prometheus_metrics(): void
{
    global $mybb, $cache, $db, $plugins;

    // Support configurable metrics path
    $metricsPath = $_ENV['PROMETHEUS_METRICS_PATH'] ?? 'prometheus_metrics';
    if ($mybb->get_input('action', MyBB::INPUT_STRING) !== $metricsPath) {
        return;
    }

    // Audit logging: log all requests to the metrics endpoint
    prometheus_audit_log_access($metricsPath);

    // IP allowlist: localhost, private RFC1918, or PROMETHEUS_ALLOWLIST env var (comma-separated)
    $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowlist = [
        '127.0.0.1', '::1',
        // RFC1918 IPv4 ranges
        '10.', '172.16.', '172.17.', '172.18.', '172.19.', '172.20.', '172.21.', '172.22.', '172.23.', '172.24.', '172.25.', '172.26.', '172.27.', '172.28.', '172.29.', '172.30.', '172.31.', '192.168.'
    ];
    if (!empty($_ENV['PROMETHEUS_ALLOWLIST'])) {
        $extra = array_map('trim', explode(',', $_ENV['PROMETHEUS_ALLOWLIST']));
        $allowlist = array_merge($allowlist, $extra);
    }
    $isAllowed = false;
    foreach ($allowlist as $prefix) {
        if (str_starts_with($remoteIp, $prefix)) {
            $isAllowed = true;
            break;
        }
    }

    // Only require auth if not from allowlist
    if (!$isAllowed && prometheus_needs_basic_auth() && !prometheus_verify_credentials()) {
        prometheus_audit_log_access($metricsPath, false, true); // Log failed auth
        header('WWW-Authenticate: Basic realm="Prometheus Metrics"');
        header('HTTP/1.0 401 Unauthorized');
        exit();
    }

    http_response_code(200);
    header("Content-Type: text/plain; version=0.0.4");

    $registry = prometheus_get_default_metric_registry($mybb, $cache, $db);
    // Allow other plugins to register custom metrics
    $plugins->run_hooks('prometheus_register_custom_metrics', $registry);
    $plugins->run_hooks('prometheus_metrics_start', $registry);
    $metrics = $registry->render();
    $plugins->run_hooks('prometheus_metrics_end');
    echo $metrics;
    exit();
}

/**
 * Audit logs all access to the metrics endpoint (success/failure, IP, time, user agent).
 * @param string $metricsPath
 * @param bool $success
 * @param bool $authFailure
 * @return void
 */
function prometheus_audit_log_access(string $metricsPath, bool $success = true, bool $authFailure = false): void
{
    $logDir = __DIR__ . '/MybbStuff/Prometheus/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }
    $logFile = $logDir . '/metrics_audit.log';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $time = date('c');
    $status = $success ? 'SUCCESS' : ($authFailure ? 'AUTH_FAIL' : 'FAIL');
    $line = sprintf("[%s] %s path=%s ip=%s ua=%s\n", $time, $status, $metricsPath, $ip, $userAgent);
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}


/**
 * Returns the default metric registry for Prometheus.
 */
function prometheus_get_default_metric_registry(MyBB $mybb, datacache $cache, DB_Base $db): MetricReporterRegistry
{
    $registry = MetricReporterRegistry::getInstance();

    $registry->addMetricReporter(new AwaitingActivationMetricReporter($cache));
    $registry->addMetricReporter(new BoardStatsMetricReporter($mybb, $db, $cache));
    $registry->addMetricReporter(new MailQueueMetricReporter($cache));
    $registry->addMetricReporter(new MostOnlineMetricReporter($cache));
    $registry->addMetricReporter(new OnlineUsersMetricReporter($mybb, $db, $cache));
    $registry->addMetricReporter(new ReportedContentMetricReporter($cache));
    $registry->addMetricReporter(new VersionCodeMetricReporter($cache));

    return $registry;
}