<?php
/**
 * BSD 3-Clause License
 * Copyright (c) Euan Torano and contributors. All rights reserved.
 * See LICENSE file in the root directory.
 */
declare(strict_types=1);

namespace MybbStuff\Prometheus\MetricReporters;

use datacache;
use MybbStuff\Prometheus\IMetricReporter;

abstract class CacheBasedMetricReporter implements IMetricReporter
{
    /**
     * @var \datacache
     */
    protected datacache $cache;

    public function __construct(datacache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Read cache and return array value.
     */
    protected function readCache(datacache $cache, string $name): array
    {
        $value = $cache->read($name);

        if ($value === false) {
            $value = [];
        }

        return $value;
    }

{
    /**
     * @var \datacache
     */
    protected $cache;

    public function __construct(datacache $cache)
    {
        $this->cache = $cache;
    }

    protected function readCache(datacache $cache, string $name): array
    {
        $value = $cache->read($name);

        if ($value === false) {
            $value = [];
        }

        return $value;
    }
}