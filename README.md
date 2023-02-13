# Multipart Uploads using Laravel, AWS S3, and Uppy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tapp/laravel-uppy-s3-multipart-upload.svg?style=flat-square)](https://packagist.org/packages/tapp/laravel-uppy-s3-multipart-upload)
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
        "alpinejs": "^3.11.1",
        ...
    },
    "dependencies": {
        "@uppy/aws-s3-multipart": "^3.1.2",
        "@uppy/core": "^3.0.5",
        "@uppy/drag-drop": "^3.0.1",
        "@uppy/status-bar": "^3.0.1"
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
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
```

Install the JS libraries:

for Mix:
```
npm install
npm run dev
```

for Vite:
```
npm install
npm run build
```

> You can use CDNs for [Uppy](https://uppy.io/docs/#With-a-script-tag) and [AlpineJS](https://github.com/alpinejs/alpine), if you prefer.

### Publish config file

Publish the config file with:
```bash
php artisan vendor:publish --tag=uppy-s3-multipart-upload-config
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

### Publish view file

```bash
php artisan vendor:publish --tag=uppy-s3-multipart-upload-views
```

### AWS S3 Setup

This package installs the [AWS SDK for PHP](https://github.com/aws/aws-sdk-php) and use Laravel's default `s3` disk configuration from `config/filesystems.php` file.

You just have to add your S3 keys, region, and bucket using the following env vars in your `.env` file:

```
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
```

> **Warning**
>
> The `AWS_URL` or `AWS_POST_END_POINT` env vars should only be set when using a custom, non-aws endpoint.
> For more details please refer to this issue: https://github.com/TappNetwork/laravel-uppy-s3-multipart-upload/issues/14.

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

#### Add S3 Transfer Acceleration

To use [S3 transfer acceleration](https://docs.aws.amazon.com/AmazonS3/latest/userguide/transfer-acceleration.html),
enable it by adding a `AWS_USE_ACCELERATE_ENDPOINT=true` env var on your `.env` file, 
and add `'use_accelerate_endpoint' => env('AWS_USE_ACCELERATE_ENDPOINT')` in `s3` options on your `config/filesystems.php`:

```php
       's3' => [
            ...
            'use_accelerate_endpoint' => env('AWS_USE_ACCELERATE_ENDPOINT'),
        ],
```

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
<input type="hidden" name="file" id="file" />
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

**Upload Element Class**

Use the `uploadElementClass` attribute to provide the class of the HTML element used for upload:

```php
$imageClass = 'images';
```

```html
<x-input.uppy :uploadElementClass="$imageClass" />
```

The `upload` class will be used if none is provided.

**Multiple Uppy Instances**

If you want to use multiple Uppy instances, add a different `uploadElementClass` attribute to each instance. E.g.:

```html
<!-- First Uppy instance for image uploads -->
<div>
    <input type="hidden" name="images" id="images" />
    <x-input.uppy :options="$imageOptions" :hiddenField="$imageField" :uploadElementClass="$imageClass" />
</div>


<!-- Second Uppy instance for video uploads -->
<div>
    <input type="hidden" name="videos" id="videos" />
    <x-input.uppy :options="$videoOptions" :hiddenField="$videoField" :uploadElementClass="$videoClass" />
</div>
```

**Note from Uppy docs**: _"If multiple Uppy instances are being used, for instance, on two different pages,
an id should be specified. This allows Uppy to store information in localStorage without colliding with other Uppy instances."_
[Learn more here](https://uppy.io/docs/uppy/#id-39-uppy-39).

**Extra JavaScript to onUploadSuccess**

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
php artisan view:clear
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security-related issues, please email security@tappnetwork.com.

## Credits

- [Tapp Network](https://github.com/TappNetwork)
- [All Contributors](../../contributors)

### Libraries used in this package:

- [AWS SDK for PHP](https://github.com/aws/aws-sdk-php)
- [Uppy](https://uppy.io)
- [AlpineJS](https://github.com/alpinejs/alpine)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
