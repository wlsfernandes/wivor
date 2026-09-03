<?php

return [
    'disk' => env('WIVOR_MEDIA_DISK', 's3'),
    'max_file_bytes' => (int) env('PHOTO_MAX_FILE_BYTES', 40 * 1024 * 1024),
    'min_longest_side' => (int) env('PHOTO_MIN_LONGEST_SIDE', 2400),
    'max_side' => (int) env('PHOTO_MAX_SIDE', 12000),
    'max_batch_size' => (int) env('PHOTO_MAX_BATCH_SIZE', 500),
    'max_event_photos' => (int) env('PHOTO_MAX_EVENT_PHOTOS', 5000),
    'upload_deadline_hours' => (int) env('PHOTO_UPLOAD_DEADLINE_HOURS', 72),
    'sales_window_days' => (int) env('PHOTO_SALES_WINDOW_DAYS', 60),
    'sold_original_days' => (int) env('PHOTO_SOLD_ORIGINAL_DAYS', 90),
    'upload_url_minutes' => (int) env('PHOTO_UPLOAD_URL_MINUTES', 15),
    'delivery_url_minutes' => (int) env('PHOTO_DELIVERY_URL_MINUTES', 5),
    'preview_max_side' => 1600,
    'preview_quality' => 82,
    'thumbnail_max_side' => 480,
    'thumbnail_quality' => 78,
];
