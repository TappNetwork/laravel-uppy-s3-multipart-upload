<?php

namespace TappNetwork\LaravelUppyS3MultipartUpload;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelUppyS3MultipartUploadServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-uppy-s3-multipart-upload')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web');
    }
}
