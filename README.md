# Multipart Uploads using Laravel, AWS S3, and Uppy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tappnetwork/laravel-uppy-s3-multipart-upload.svg?style=flat-square)](https://packagist.org/packages/tappnetwork/laravel-uppy-s3-multipart-upload)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/tappnetwork/laravel-uppy-s3-multipart-upload/run-tests?label=tests)](https://github.com/tappnetwork/laravel-uppy-s3-multipart-upload/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/tappnetwork/laravel-uppy-s3-multipart-upload.svg?style=flat-square)](https://packagist.org/packages/tappnetwork/laravel-uppy-s3-multipart-upload)

Upload large files directly to [AWS S3](https://aws.amazon.com/s3/) using [Laravel](https://laravel.com/) (backend) and [Uppy](https://uppy.io/) (frontend).

## Appearance

![upload00](https://raw.githubusercontent.com/TappNetwork/laravel-uppy-s3-multipart-upload/master/docs/upload00.png)

![upload01](https://raw.githubusercontent.com/TappNetwork/laravel-uppy-s3-multipart-upload/master/docs/upload01.png)

![upload02](https://raw.githubusercontent.com/TappNetwork/laravel-uppy-s3-multipart-upload/master/docs/upload02.png)

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

### Add required JS libraries

Add on your `package.json` file the Uppy JS libraries and AlpineJS library:

```
    ...
    "devDependencies": {
        "alpinejs": "^2.7.3",
        ...
    },
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

> You can use CDNs for [Uppy](https://uppy.io/docs/#With-a-script-tag) and [AlpineJS](https://github.com/alpinejs/alpine), if you prefer.


### AWS S3 Setup

Add S3 credentials, region, and bucket in your filesytems config:


To allow direct multipart uploads to your S3 bucket, you need to add some extra configuration on bucket's `CORS configuration`.
On your AWS S3 console, select your bucket.
Click on `"Permissions"` tab.
On `"CORS configuration"` add the following configuration:

```
todo
```

https://uppy.io/docs/aws-s3-multipart/#S3-Bucket-Configuration
https://uppy.io/docs/aws-s3/#S3-Bucket-configuration

### Disable CSRF

Disable CSRF on `s3/multipart` routes by adding this in your `app/Http/Middleware/VerifyCsrfToken.php`:

```php
protected $except = [
    's3/multipart*',
];
```

## Endpoints added

This package add the following routes:

```
POST    /s3/multipart
OPTIONS /s3/multipart
GET     /s3/multipart/{uploadId}
GET     /s3/multipart/{uploadId}/{partNumber}
POST    /s3/multipart/{uploadId}/complete
DELETE  /s3/multipart/{uploadId}
```

## Usage

### Add a hidden field for the uploaded file url

Add a hidden input form element on your blade template. When the upload is finished, it will receive the url of the uploaded file:

E.g.:

```html
<input type="hidden" name="image_url" id="image_url" />
```

### Add the `uppy` blade component to your blade view:

```html
<x-input.uppy />
```

### Passing data to the uppy blade component

**Hidden field name**

Use the `hiddenField` attribute to provide the name of the hidden field that will receive the url of uploaded file:

```php
$hiddenField = 'image_url';
```

```html
<x-input.uppy :hiddenField="$hiddenField" />
```

The `file` name will be used if none is provided.


**Uppy Core Options**

https://uppy.io/docs/uppy/#Options

You can pass any uppy options via `options` attribute:

```html
<x-input.uppy :options="$uppyOptions" />
```

Uppy core options are in this format:

```
$uppyOptions = "{
    debug: true,
    autoProceed: true,
    allowMultipleUploads: false,
}";
```

Default core options if none is provided:

```
{
    debug: true,
    autoProceed: true,
    allowMultipleUploads: false,
}
```

**Uppy Status Bar Options**

https://uppy.io/docs/status-bar/#Options

You can pass any uppy status bar options via `statusBarOptions` attribute:

```html
<x-input.uppy :statusBarOptions="$uppyStatusBarOptions" />
```

Uppy Status Bar options are in this format:

```
$uppyStatusBarOptions = "{
    target: '.upload .for-ProgressBar',
    hideAfterFinish: false,
}";
```

Default status bar options if none is provided:

```
{
    target: '.upload .for-ProgressBar',
    hideAfterFinish: false,
}
```

**Uppy Drag & Drop Options**

https://uppy.io/docs/drag-drop/#Options

You can pass any uppy drag & drop options via `dragDropOptions` attribute:

```html
<x-input.uppy :dragDropOptions="$uppyDragDropOptions" />
```

Uppy Drag & Drop options are in this format:

```
$uppyDragDropOptions = "{
    target: '.upload .for-DragDrop',
}";
```

Default drag & drop options if none is informed:

```
{
    target: '.upload .for-DragDrop',
}
```

**Extra JavaScript to uploadOn**

If you need to add extra JavaScript code on `onUploadSuccess` function, use the `extraJSForOnUploadSuccess` attribute:

E.g.:

```php
$extraJSForOnUploadSuccess = "
    document.getElementById('saveImageButton').removeAttribute('disabled');
    document.getElementById('saveImageButton').click();
"
```

```html
<x-input.uppy :extraJSForOnUploadSuccess="$extraJSForOnUploadSuccess" />
```

Default `extraJSForOnUploadSuccess` value is empty string.

## Complete Example

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
