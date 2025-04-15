<?php
declare(strict_types=1);

namespace MybbStuff\Prometheus\MetricReporters;

use MybbStuff\Prometheus\Metric;

class VersionCodeMetricReporter extends CacheBasedMetricReporter
{
    /**
     * Get the name of the metric reporter.
     */
    public function getName(): string
    {
        return 'version_code';
    }

    /**
     * Get all of the metrics for this reporter.
     *
     * @return array<string, \MybbStuff\Prometheus\Metric>
     */
    public function getMetrics(): array
    {
        $metrics = [];

        $versionCache = $this->readCache($this->cache, 'version');

        if (isset($versionCache['version_code'])) {
            $metrics['mybb_version_code'] = (new Metric('mybb_version_code', Metric::TYPE_UNTYPED))
                ->setHelp('The version code of the currently installed MyBB version.')
                ->setValue($versionCache['version_code']);
        }

        return $metrics;
    }

{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'version_code';
    }

    /**
     * @inheritDoc
     */
    function getMetrics(): array
    {
        $metrics = [];

        $versionCache = $this->readCache($this->cache, 'version');

        if (isset($versionCache['version_code'])) {
            $metrics['mybb_version_code'] = (new Metric('mybb_version_code', Metric::TYPE_UNTYPED))
	            ->setHelp('The version code of the currently installed MyBB version.')
                ->setValue($versionCache['version_code']);
        }

        return $metrics;
    }
}