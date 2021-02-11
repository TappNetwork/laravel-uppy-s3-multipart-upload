# Multipart Uploads using Laravel, AWS S3, and Uppy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tapp/laravel-uppy-s3-multipart-upload.svg?style=flat-square)](https://packagist.org/packages/tapp/laravel-uppy-s3-multipart-upload)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/tapp/laravel-uppy-s3-multipart-upload/run-tests?label=tests)](https://github.com/tapp/laravel-uppy-s3-multipart-upload/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/tapp/laravel-uppy-s3-multipart-upload.svg?style=flat-square)](https://packagist.org/packages/tapp/laravel-uppy-s3-multipart-upload)

Upload large files directly to [AWS S3](https://aws.amazon.com/s3/) using [Laravel](https://laravel.com/) (backend) and [Uppy](https://uppy.io/) (frontend).

## Appearance

![upload00](https://raw.githubusercontent.com/TappNetwork/laravel-uppy-s3-multipart-upload/master/docs/upload00.png)

![upload01](https://raw.githubusercontent.com/TappNetwork/laravel-uppy-s3-multipart-upload/master/docs/upload01.png)

![upload02](https://raw.githubusercontent.com/TappNetwork/laravel-uppy-s3-multipart-upload/master/docs/upload02.png)

## Installation

### Install the package via Composer

```bash
composer require tapp/laravel-uppy-s3-multipart-upload
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

Add in your `resources/js/bootstrap.js` file:

```javascript
...

require('@uppy/core/dist/style.min.css')
require('@uppy/drag-drop/dist/style.min.css')
require('@uppy/status-bar/dist/style.min.css')

import Uppy from '@uppy/core'
import DragDrop from '@uppy/drag-drop'
import StatusBar from '@uppy/status-bar'
import AwsS3Multipart from '@uppy/aws-s3-multipart'

window.Uppy = Uppy
window.DragDrop = DragDrop
window.StatusBar = StatusBar
window.AwsS3Multipart = AwsS3Multipart
```

Add in your `resources/js/app.js`:

```javascript
...
require('alpinejs');
```

Install the JS libraries:

```
$ npm install
$ npm run dev
```

> You can use CDNs for [Uppy](https://uppy.io/docs/#With-a-script-tag) and [AlpineJS](https://github.com/alpinejs/alpine), if you prefer.

### Publish config file

Publish the config file with:
```bash
php artisan vendor:publish --provider="Tapp\LaravelUppyS3MultipartUpload\LaravelUppyS3MultipartUploadServiceProvider" --tag="laravel-uppy-s3-multipart-upload-config"
```

This is the contents of the published config file:

```php
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
];
```

### AWS S3 Setup

This package installs the [AWS SDK for PHP](https://github.com/aws/aws-sdk-php) and use Laravel's default `s3` disk configuration from `filesystems.php` file.

You just have to add your S3 keys, region, and bucket using the following env vars in your `.env` file:

```
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_URL="https://s3.amazonaws.com"
AWS_POST_END_POINT="https://${AWS_BUCKET}.s3.amazonaws.com/"
```

To allow direct multipart uploads to your S3 bucket, you need to add some extra configuration on bucket's `CORS configuration`.
On your AWS S3 console, select your bucket.
Click on `"Permissions"` tab.
On `"CORS configuration"` add the following configuration:

```
[
    {
        "AllowedHeaders": [
            "Authorization",
            "x-amz-date",
            "x-amz-content-sha256",
            "content-type"
        ],
        "AllowedMethods": [
            "PUT",
            "POST",
            "DELETE",
            "GET"
        ],
        "AllowedOrigins": [
            "*"
        ],
        "ExposeHeaders": [
            "ETag"
        ]
    }
]
```

On `AllowedOrigins`:

```
"AllowedOrigins": [
    "*"
]
```

You should list the URLs allowed, e.g.:

```
"AllowedOrigins": [
    "https://example.com"
]
```

https://uppy.io/docs/aws-s3-multipart/#S3-Bucket-Configuration

https://uppy.io/docs/aws-s3/#S3-Bucket-configuration

#### Configuration

You can configure the folder to upload the files and the expiration of the presigned URLs used to upload the parts, with the `config/uppy-s3-multipart-upload.php` file:

```php
return [
    's3' => [
        'bucket' => [
            /*
             * Folder on bucket to save the file
             */
            'folder' => 'videos',
        ],
        'presigned_url' => [
            /*
             * Expiration time of the presigned URLs
             */
            'expiry_time' => '+30 minutes',
        ],
    ],
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

### Clear caches

Run:

```
php artisan optimize
```

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

### Libraries used in this package:

- [AWS SDK for PHP](https://github.com/aws/aws-sdk-php)
- [Uppy](https://uppy.io)
- [AlpineJS](https://github.com/alpinejs/alpine)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
