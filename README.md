# mybb-prometheus (Modernized Fork)

A modernized fork of the original MyBB Prometheus plugin to expose metrics to Prometheus, updated for PHP 8.3+ and all MyBB 1.8.x versions.

## About this Fork

This fork modernizes the original plugin for current PHP and MyBB standards, improves security, and adds new configuration options. It is maintained by 0xRogu and contributors.

## Supported metrics

This plugin currently reports the following set of metrics:

- Number of users awaiting activation
- Number of messages waiting in the mail queue
- Maximum number of concurrent online users
- Number of unread reports
- Total number of reports
- MyBB version code number
- Number of threads in all forums
- Number of unapproved threads in all forums
- Number of deleted threads in all forums
- Number of posts in all forums
- Number of unapproved threads in all forums
- Number of deleted posts in all forums
- Number of registered users
- ID of the last registered user
- Total number of online users
- Number of online members
- Number of online bots
- Number of online guests
- Posts per day
- Threads per day
- Members per day
- Posts per member
- Threads per member
- Replies per thread
- Percentage of users who have posted

## Features and Improvements

- **PHP 8.3+ support** (strict types, type hints, modern syntax)
- **MyBB 1.8.x compatibility** (all versions)
- **Configurable metrics endpoint path** (via environment variable and admin UI)
- **Admin UI for configuration** (single page in ACP, with audit log viewing)
- **Audit logging** of all metrics endpoint access (success/failure, IP, user agent)
- **Custom metrics registration** via `prometheus_register_custom_metrics` plugin hook
- **Metrics endpoint security**:
  - Requests from localhost and private IPs are allowed without authentication
  - IP allowlist via `PROMETHEUS_ALLOWLIST` environment variable
  - HTTP Basic Auth only required for external IPs
- **Environment-based configuration** (including auto-loaded config file)
- **Refactored and type-safe codebase**
- **Ready for future enhancements and community contributions**

## Configuring the Plugin

### Using the Admin UI (Recommended)

- Go to the MyBB ACP > Plugins page and click the "Prometheus Plugin Settings" link under the Prometheus plugin entry.
- Configure the metrics endpoint path and IP allowlist directly from the UI.
- View the last 20 audit log entries for metrics endpoint access.
- Settings are saved to `inc/plugins/MybbStuff/Prometheus/config.php` and auto-loaded on plugin initialization.

### Manual Environment Variables (Advanced)

- `PROMETHEUS_USER`: Username for accessing Prometheus metrics (default: `prometheus`)
- `PROMETHEUS_PASSWORD`: Password for accessing Prometheus metrics (required for external access)
- `PROMETHEUS_ALLOWLIST`: (Optional) Comma-separated list of additional IP addresses or prefixes allowed to access metrics without authentication
- `PROMETHEUS_METRICS_PATH`: (Optional) Custom path for metrics endpoint (default: `prometheus_metrics`)

These settings can be set in your MyBB web server environment or via the config file.

## Configuring Prometheus

You must configure Prometheus to add a new scrape config. Below is an example scrape configuration to scrape metrics:

```yaml
scrape_configs:
  - job_name: 'mybb'
    metrics_path: '/misc.php'
    scrape_interval: '5s'
    basic_auth:
      username: 'prometheus'
      password: 'change_me-123'
    params:
      action: ['prometheus_metrics'] # Use your configured path here if changed
    static_configs:
      - targets:
        - 'mybb.dev'
```

If you change the metrics path (e.g., to `my_metrics`), update the `action` param accordingly:

```yaml
params:
  action: ['my_metrics']
```

## Custom Metrics Registration (for Developers)

Other plugins can register their own Prometheus metrics by hooking into the `prometheus_register_custom_metrics` event:

```php
$plugins->add_hook('prometheus_register_custom_metrics', function(&$registry) {
    // $registry is the MetricReporterRegistry instance
    $registry->addMetricReporter(new MyCustomMetricReporter(...));
});
```

Obviously, you should change the target to your actual IP address/hostname and the username and password to those set as per the above section [`Configuring the plugin`](#configuring-the-plugin).

## License

This project is licensed under the BSD 3-Clause License.

- Original author: Euan Torano
- Modernized fork maintained by 0xRogu and contributors

See the [LICENSE](LICENSE) file for the full license text and attribution details.
