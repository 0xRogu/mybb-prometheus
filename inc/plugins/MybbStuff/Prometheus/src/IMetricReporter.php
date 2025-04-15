<?php
/**
 * BSD 3-Clause License
 * Copyright (c) Euan Torano and contributors. All rights reserved.
 * See LICENSE file in the root directory.
 */
declare(strict_types=1);

namespace MybbStuff\Prometheus;

interface IMetricReporter
{
    /**
     * Get the name of the metric reporter.
     *
     * @return string
     */
    function getName(): string;

    /**
     * Get all of the metrics for this reporter.
     *
     * @return Metric[]
     */
        /**
     * Get all of the metrics for this reporter.
     *
     * @return array<string, \MybbStuff\Prometheus\Metric>
     */
    public function getMetrics(): array;

}