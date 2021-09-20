# Upgrade Guide

## Upgrading from 0.3.5 to 0.4.0

### To upgrade Uppy from 1.x to 2.0

Update the following dependencies on your `package.json`:

```json
"dependencies": {
    ...
    "@uppy/aws-s3-multipart": "^2.0.2",
    "@uppy/core": "^2.0.2",
    "@uppy/drag-drop": "^2.0.1",
    "@uppy/status-bar": "^2.0.1"
}
```

Install the dependencies and compile assets:

```bash
npm install
npm run dev
```

Clear caches:

```bash
php artisan optimize
php artisan view:clear
```
