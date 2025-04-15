<?php
/**
 * BSD 3-Clause License
 * Copyright (c) Euan Torano and contributors. All rights reserved.
 * See LICENSE file in the root directory.
 */
declare(strict_types=1);

namespace MybbStuff\Prometheus\MetricReporters;

use MybbStuff\Prometheus\Metric;

class AwaitingActivationMetricReporter extends CacheBasedMetricReporter
{
    /**
     * Get the name of the metric reporter.
     */
    public function getName(): string
    {
        return 'awaiting_activation';
    }

    /**
     * Get all of the metrics for this reporter.
     *
     * @return array<string, \MybbStuff\Prometheus\Metric>
     */
    public function getMetrics(): array
    {
        $metrics = [];

        $awaitingActivationCache = $this->readCache($this->cache, 'awaitingactivation');

        if (isset($awaitingActivationCache['users'])) {
            $metrics['mybb_awaiting_activation_users'] = (new Metric('mybb_awaiting_activation_users', Metric::TYPE_GAUGE))
                ->setHelp('The number of users awaiting activation.')
                ->setValue((int) $awaitingActivationCache['users']);
        }

        return $metrics;
    }

{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'awaiting_activation';
    }

    /**
     * @inheritDoc
     */
    function getMetrics(): array
    {
        $metrics = [];

        $awaitingActivationCache = $this->readCache($this->cache, 'awaitingactivation');

        if (isset($awaitingActivationCache['users'])) {
            $metric = (new Metric('mybb_awaiting_activation_users', Metric::TYPE_GAUGE))
	            ->setHelp('The number of users awaiting activation.')
                ->setValue((int) $awaitingActivationCache['users']);

            if (isset($awaitingActivationCache['time'])) {
                $metric->setTimeStamp((int) $awaitingActivationCache['time'] * 1000);
            }

            $metrics['mybb_awaiting_activation_users'] = $metric;
        }

        return $metrics;
    }
}