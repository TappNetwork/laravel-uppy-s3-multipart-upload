# Upgrade Guide

## Upgrading from 1.x to 2.0.0

### The upload endpoints now require a logged-in user

The `/s3/multipart` routes used to be registered without any middleware, so anyone could start
uploads to your bucket. They now use the `routes.middleware` config value, `['web', 'auth']` by default.

- If your uploads already happen on pages for logged-in users, nothing else is needed.
- To use another guard, set `routes.middleware` in `config/uppy-s3-multipart-upload.php`
  (e.g. `['web', 'auth:admin']` or `['api', 'auth:sanctum']`).
- Only if guests must be able to upload, set it to `['web']`.

The `<x-input.uppy />` component now sends the CSRF token itself. If you published the view,
publish it again or replace `window.csrfToken` with `'{{ csrf_token() }}'`.

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
