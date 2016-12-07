Flysystem Google Cloud Storage
============

This module is a plugin for Flysystem library, and allows to store and retrieve data from GCS.

It has been created based on Flysystem_s3, the ones that is contrib in the Community to connect Flysystem and S3.

Flysystem library of The PHP League is required. GoogleStorageAdapter adapter of Superbalist is required. Both are installed via composer.

For setup instructions see the Flysystem README.md.

## CONFIGURATION ##

Example configuration:

```php
/**
 * Flysystem schemes.
 *
 * Scheme key name must be in lowercase
 * Driver value must be the name of the class that implements this adapter
 */
$schemes = [
  // Google cloud storage scheme (credentials and settings)
  'cloudstorage' => [
    'driver' => 'CloudStorage',
    'config' => [
      'keyFilePath' => 'path/to/json', // Required. Path where service account credentials is located.
      'bucket' => 'BUCKET', // Required. Buckt name.
      'prefix' => 'an/optional/prefix', // Optional. Directory prefix for all uploaded/viewed files.
      'protocol' => '', // Optional (http|https). Default: https. Autodetected based on the current request if not provided.
      'cname' => 'static.example.com', // Optional. A CNAME that resolves to your bucket. Used for URL generation.
    ],
  ],
];
```
