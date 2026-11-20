<?php

return [
    // Sender for CRM campaigns; falls back to the application's MAIL_FROM_* identity.
    'from_address' => env('CRM_FROM_ADDRESS'),
    'from_name' => env('CRM_FROM_NAME'),
];
