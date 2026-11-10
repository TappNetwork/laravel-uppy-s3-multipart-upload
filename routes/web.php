<?php

use Illuminate\Support\Facades\Route;
use Tapp\LaravelUppyS3MultipartUpload\Http\Controllers\UppyS3MultipartController;

Route::options('/s3/multipart', [UppyS3MultipartController::class, 'createPreflightHeader']);

// These endpoints start uploads to the application's bucket and hand out presigned
// upload URLs, so they need a session and a logged-in user unless the app decides otherwise.
Route::middleware(config('uppy-s3-multipart-upload.routes.middleware', ['web', 'auth']))->group(function () {
    Route::post('/s3/multipart', [UppyS3MultipartController::class, 'createMultipartUpload']);
    Route::get('/s3/multipart/{uploadId}', [UppyS3MultipartController::class, 'getUploadedParts']);
    Route::post('/s3/multipart/{uploadId}/complete', [UppyS3MultipartController::class, 'completeMultipartUpload']);
    Route::delete('/s3/multipart/{uploadId}', [UppyS3MultipartController::class, 'abortMultipartUpload']);
    Route::get('/s3/multipart/{uploadId}/{partNumber}', [UppyS3MultipartController::class, 'signPartUpload']);
});
