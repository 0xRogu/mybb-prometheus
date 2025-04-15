<?php
/**
 * BSD 3-Clause License
 * Copyright (c) Euan Torano and contributors. All rights reserved.
 * See LICENSE file in the root directory.
 */
declare(strict_types=1);

namespace MybbStuff\Prometheus\MetricReporters;

use MybbStuff\Prometheus\Metric;

class MostOnlineMetricReporter extends CacheBasedMetricReporter
{
    /**
     * Get the name of the metric reporter.
     */
    public function getName(): string
    {
        return 'most_online';
    }

    /**
     * Get all of the metrics for this reporter.
     *
     * @return array<string, \MybbStuff\Prometheus\Metric>
     */
    public function getMetrics(): array
    {
        $metrics = [];

        $mostOnlineCache = $this->readCache($this->cache, 'mostonline');

        if (isset($mostOnlineCache['numusers'])) {
            $metric = (new Metric('mybb_most_online', Metric::TYPE_GAUGE))
                ->setHelp('The maximum number of users that have been online concurrently.')
                ->setValue((int) $mostOnlineCache['numusers']);

            if (isset($mostOnlineCache['time'])) {
                $metric->setTimeStamp((int) $mostOnlineCache['time'] * 1000);
            }

            $metrics['mybb_most_online'] = $metric;
        }

        return $metrics;
    }

{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'most_online';
    }

    /**
     * @inheritDoc
     */
    function getMetrics(): array
    {
        $metrics = [];

        $mostOnlineCache = $this->readCache($this->cache, 'mostonline');

        if (isset($mostOnlineCache['numusers'])) {
            $metric = (new Metric('mybb_most_online', Metric::TYPE_GAUGE))
	            ->setHelp('The maximum number of users that have been online concurrently.')
                ->setValue((int) $mostOnlineCache['numusers']);

            if (isset($mostOnlineCache['time'])) {
                $metric->setTimeStamp((int) $mostOnlineCache['time'] * 1000);
            }

            $metrics['mybb_most_online'] = $metric;
        }

        return $metrics;
    }
}