# Changelog

All notable changes to `laravel-uppy-s3-multipart-upload` will be documented in this file.

## 0.1 - 2021-02-11

- Initial release

## 0.2 - 2021-02-25

- Add configuration file
- Fix variable name
- Allow CSRF
- Update README

## 0.3 - 2021-02-26

- Add S3 transfer acceleration configuration
- Update README

## 0.3.1 - 2021-02-27

- Move S3 transfer acceleration config to the package config file

## 0.3.2 - 2021-02-28

- Update the way credentials are passed to use the Credentials class

## 0.3.3 - 2021-03-03

- Remove passing credentials, assuming that AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY are set in the .env based on the AWS SDK suggestions

## 0.3.4 - 2021-03-18

- Use Flysystem AWS client

## 0.3.5 - 2021-09-15

- Add support for multiple Uppy instances
- Update README

## 0.4 - 2021-09-20

- Upgrade Uppy from 1.x to 2.0
- Add support for pre-sign URLs in batches
- Update README
- Add upgrade guide
