<?php
/**
 * BSD 3-Clause License
 * Copyright (c) Euan Torano and contributors. All rights reserved.
 * See LICENSE file in the root directory.
 */
declare(strict_types=1);

namespace MybbStuff\Prometheus\MetricReporters;

use MybbStuff\Prometheus\Metric;

class MailQueueMetricReporter extends CacheBasedMetricReporter
{
    /**
     * Get the name of the metric reporter.
     */
    public function getName(): string
    {
        return 'mail_queue';
    }

    /**
     * Get all of the metrics for this reporter.
     *
     * @return array<string, \MybbStuff\Prometheus\Metric>
     */
    public function getMetrics(): array
    {
        $metrics = [];

        $mailQueueCache = $this->readCache($this->cache, 'mailqueue');

        if (isset($mailQueueCache['queue_size'])) {
            $metric = (new Metric('mybb_mail_queue_size', Metric::TYPE_GAUGE))
                ->setHelp('The number of messages waiting in the mail queue.')
                ->setValue((int) $mailQueueCache['queue_size']);

            if (isset($mailQueueCache['last_run'])) {
                $metric->setTimeStamp((int) $mailQueueCache['last_run'] * 1000);
            }

            $metrics['mybb_mail_queue_size'] = $metric;
        }

        return $metrics;
    }

{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'mail_queue';
    }

    /**
     * @inheritDoc
     */
    function getMetrics(): array
    {
        $metrics = [];

        $mailQueueCache = $this->readCache($this->cache, 'mailqueue');

        if (isset($mailQueueCache['queue_size'])) {
            $metric = (new Metric('mybb_mail_queue_size', Metric::TYPE_GAUGE))
	            ->setHelp('The number of messages waiting in the mail queue.')
                ->setValue((int) $mailQueueCache['queue_size']);

            if (isset($mailQueueCache['last_run'])) {
                $metric->setTimeStamp((int) $mailQueueCache['last_run'] * 1000);
            }

            $metrics['mybb_mail_queue_size'] = $metric;
        }

        return $metrics;
    }
}