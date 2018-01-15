Flysystem Google Cloud Storage
============

This module is a plugin for Flysystem library, and allows to store and retrieve data from GCS.

It has been created based on Flysystem_s3, the ones that is contrib in the Community to connect Flysystem and S3.

Flysystem library of The PHP League is required. GoogleStorageAdapter adapter of Superbalist is required. Both are installed via composer.

For setup instructions see the Flysystem README.md.

**IMPORTANT**: due to Drupal's new repository semantic versioning, this module is only compatible with projects using new repository.

If you are using `https://packagist.drupal-composer.org` , you must use this project in commit `56f60acaa6a148ed40b7b3041add9533a49b26e5` or before.

More information [here](https://www.drupal.org/node/2822344).

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
  'public' => [
    'driver' => 'CloudStorage',
    'config' => [
      'keyFilePath' => 'path/to/json',
      'bucket' => 'BUCKET',
      'public' => true, // Optional. If set, the scheme must be called 'public'. Uses bucket storage for all the files within web/sites/site/files instead of local storage.
    ],
  ],
];
```
