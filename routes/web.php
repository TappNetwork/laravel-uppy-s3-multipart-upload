<?php

use Tapp\LaravelUppyS3MultipartUpload\Http\Controllers\UppyS3MultipartController;
use Illuminate\Support\Facades\Route;

Route::post('/s3/multipart', [UppyS3MultipartController::class, 'createMultipartUpload']);

Route::options('/s3/multipart', [UppyS3MultipartController::class, 'createMultipartUploadOptions']);

Route::get('/s3/multipart/{uploadId}', [UppyS3MultipartController::class, 'getUploadedParts']);

Route::get('/s3/multipart/{uploadId}/{partNumber}', [UppyS3MultipartController::class, 'prepareUploadPart']);

Route::post('/s3/multipart/{uploadId}/complete', [UppyS3MultipartController::class, 'completeMultipartUpload']);

Route::delete('/s3/multipart/{uploadId}', [UppyS3MultipartController::class, 'abortMultipartUpload']);
