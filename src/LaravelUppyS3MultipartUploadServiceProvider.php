<?php

namespace Tapp\LaravelUppyS3MultipartUpload;

use Illuminate\Support\Facades\Blade;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tapp\LaravelUppyS3MultipartUpload\View\Components\Input\Uppy;

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

    public function bootingPackage()
    {
        Blade::component('input.uppy', Uppy::class);
    }
}
