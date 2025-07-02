<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class SimulationSnapshotData extends Data
{
    public function __construct(
        public ?string $node_status,
        public ?array $pages_stored,
        public ?bool $evil_no_forward,
        public array $timed_metrics = [],
        public array $timeless_metrics = [],
    ) {}

    public static function rules(ValidationContext $context): array
    {
        return [
            'node_status' => [
                'string',
                'max:255',
            ],

            'pages_stored' => [
                'array',
                'max:4096',
            ],
            'pages_stored.*' => [
                'array',
                'size:2',
            ],
            'pages_stored.*.name' => [
                'string',
                'max:255',
            ],
            'pages_stored.*.id' => [
                'string',
                'max:255',
            ],

            'timed_metrics' => [
                'array',
                'max:4096',
            ],
            'timed_metrics.*' => [
                'list',
                'max:4096',
            ],
            'timed_metrics.*.0' => [
                'string',
                'max:255',
            ],
            'timed_metrics.*.1' => [
                'numeric',
            ],

            'timeless_metrics' => [
                'array',
                'max:4096',
            ],
            'timeless_metrics.*' => [
                'list',
                'max:4096',
            ],
            'timeless_metrics.*.0' => [
                'string',
                'max:255',
            ],
            'timeless_metrics.*.1' => [
                'numeric',
            ],
        ];
    }
}
