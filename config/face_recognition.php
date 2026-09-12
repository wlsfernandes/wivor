<?php

return [
    'enabled' => (bool) env('FACE_RECOGNITION_ENABLED', false),

    // Temporary MVP threshold. Calibrate against representative event photography before public production launch.
    'similarity_threshold' => 90,
    'max_results' => 50,
];
