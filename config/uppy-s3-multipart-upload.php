<?php

return [
    's3' => [
        'bucket' => [
            /*
             * Folder on bucket to save the file
             */
            'folder' => '',
        ],
        'presigned_url' => [
            /*
             * Expiration time of the presigned URLs
             */
            'expiry_time' => '+1 hour',
        ],
    ],
    'routes' => [
        /*
         * Middleware for the /s3/multipart routes. Whoever can reach them can
         * upload files to your bucket, so a logged-in user is required by default.
         * E.g. ['web', 'auth:admin'], or ['api', 'auth:sanctum'] for an API client.
         * Use ['web'] only if guests really must be able to upload.
         */
        'middleware' => ['web', 'auth'],
    ],
];
