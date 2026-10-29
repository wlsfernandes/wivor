<?php

return [
    // WiVor's mailing address, printed in every email footer; CRM campaigns cannot be scheduled while it is empty.
    'postal_address' => env('CRM_POSTAL_ADDRESS', "382 Scarlet Rose Way\nHoschton, GA 30548"),

    // Sender for CRM campaigns; falls back to the application's MAIL_FROM_* identity.
    'from_address' => env('CRM_FROM_ADDRESS'),
    'from_name' => env('CRM_FROM_NAME'),
];
