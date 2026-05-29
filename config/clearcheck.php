<?php

/**
 * ClearCheck Service Identity Configuration
 * DEORIS Ecosystem — Clearance Validation Module
 */
return [

    // ── Service Identity ──────────────────────────────────────────────────
    'service_name'      => env('CLEARCHECK_SERVICE_NAME', 'ClearCheck'),
    'service_key'       => env('CLEARCHECK_SERVICE_KEY', 'clearcheck-service'),
    'service_url'       => env('APP_URL', 'https://clearcheck.deoris.test'),
    'api_version'       => env('CLEARCHECK_API_VERSION', 'v1'),
    'trusted_portal_url'=> env('APP_PORTAL_URL', 'https://deoris.test'),

    // ── Event Hub ─────────────────────────────────────────────────────────
    'event_hub_url'     => env('EVENT_HUB_URL', 'https://deoris.test/api/events/ingest'),
    'event_secret'      => env('CLEARCHECK_EVENT_SECRET', ''),
    'event_schema_version' => '1.0',

    // ── Redis Channels ────────────────────────────────────────────────────
    'redis_channels' => [
        'events'        => env('REDIS_CHANNEL_EVENTS',        'clearance.events'),
        'notifications' => env('REDIS_CHANNEL_NOTIFICATIONS', 'clearance.notifications'),
        'updates'       => env('REDIS_CHANNEL_UPDATES',       'clearance.updates'),
    ],

    // ── Queue Names ───────────────────────────────────────────────────────
    'queues' => [
        'clearance'     => env('QUEUE_CLEARANCE',     'clearance'),
        'validations'   => env('QUEUE_VALIDATIONS',   'validations'),
        'notifications' => env('QUEUE_NOTIFICATIONS', 'notifications'),
        'events'        => env('QUEUE_EVENTS',        'events'),
    ],

    // ── External Module API Endpoints ─────────────────────────────────────
    'modules' => [
        'enrollease' => [
            'name'    => 'EnrollEase',
            'url'     => env('ENROLLEASE_URL', 'https://enrollease.deoris.test'),
            'api_key' => env('ENROLLEASE_API_KEY', ''),
            'timeout' => 10,
        ],
        'assesspay' => [
            'name'    => 'AssessPay',
            'url'     => env('ASSESSPAY_URL', 'https://assesspay.deoris.test'),
            'api_key' => env('ASSESSPAY_API_KEY', ''),
            'timeout' => 10,
        ],
        'librarysys' => [
            'name'    => 'LibrarySys',
            'url'     => env('LIBRARYSYS_URL', 'https://librarysys.deoris.test'),
            'api_key' => env('LIBRARYSYS_API_KEY', ''),
            'timeout' => 10,
        ],
        'gradetrack' => [
            'name'    => 'GradeTrack',
            'url'     => env('GRADETRACK_URL', 'https://gradetrack.deoris.test'),
            'api_key' => env('GRADETRACK_API_KEY', ''),
            'timeout' => 10,
        ],
    ],

    // ── Clearance Logic ───────────────────────────────────────────────────
    'required_modules'  => ['enrollease', 'assesspay', 'librarysys', 'gradetrack'],

    // Cache TTL for module validation responses (seconds)
    'validation_cache_ttl' => env('CLEARCHECK_VALIDATION_CACHE_TTL', 300),

    // Replay attack window (seconds) — reject events older than this
    'event_replay_window' => env('CLEARCHECK_EVENT_REPLAY_WINDOW', 300),
];
