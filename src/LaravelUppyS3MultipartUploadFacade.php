<?php

namespace Tapp\LaravelUppyS3MultipartUpload;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tapp\LaravelUppyS3MultipartUpload\LaravelUppyS3MultipartUpload
 */
class LaravelUppyS3MultipartUploadFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-uppy-s3-multipart-upload';
    }
}
