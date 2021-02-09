# Multipart Uploads using Laravel, AWS S3, and Uppy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tapp network/laravel-uppy-s3-multipart-upload.svg?style=flat-square)](https://packagist.org/packages/tapp network/laravel-uppy-s3-multipart-upload)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/tapp network/laravel-uppy-s3-multipart-upload/run-tests?label=tests)](https://github.com/tapp network/laravel-uppy-s3-multipart-upload/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/tapp network/laravel-uppy-s3-multipart-upload.svg?style=flat-square)](https://packagist.org/packages/tapp network/laravel-uppy-s3-multipart-upload)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require tappnetwork/laravel-uppy-s3-multipart-upload
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="TappNetwork\LaravelUppyS3MultipartUpload\LaravelUppyS3MultipartUploadServiceProvider" --tag="laravel-uppy-s3-multipart-upload-config"
```

This is the contents of the published config file:

```php
return [
];
```

Disable CSRF on `s3/multipart` routes by adding this in your `app/Http/Middleware/VerifyCsrfToken.php`:

```php
protected $except = [
    's3/multipart*',
];
```

Add these Uppy JS libraries on your `package.json` file:

```json
    ...
    "dependencies": {
        "@uppy/aws-s3-multipart": "^1.8.12",
        "@uppy/core": "^1.16.0",
        "@uppy/drag-drop": "^1.4.24",
        "@uppy/status-bar": "^1.9.0",
        ...
    }
    ...
```

Install the JS libraries:

```
$ npm install
$ npm run dev
```

## Usage



## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Tapp Network](https://github.com/TappNetwork)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
