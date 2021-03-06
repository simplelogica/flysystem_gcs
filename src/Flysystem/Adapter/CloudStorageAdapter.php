<?php

namespace Drupal\flysystem_gcs\Flysystem\Adapter;

use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

/**
 * Overrides methods so it works with Drupal.
 *
 * Based on methods of S3Adapter class (contrib module flysystem_s3)
 */
class CloudStorageAdapter extends GoogleStorageAdapter {

  /**
   * {@inheritdoc}
   */
  public function has($path) {
    $path = $this->applyPathPrefix($path);

    if ($this->getObject($path)->exists()) {
      return TRUE;
    }

    // Check for directory existance.
    return $this->getObject($path . '/')->exists();
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata($path) {
    // IMPORTANT: For gcs parent metadata always contains info because the path is an object,
    // meanwhile in s3 this same path is a folder. So it is necesary to return in gcs that the type is a dir,
    // in order everythings works fine.
    // @TODO: find a better way to return the necessary metadata

    return [
      'type' => 'dir',
      'path' => $path,
      'timestamp' => REQUEST_TIME,
      'visibility' => AdapterInterface::VISIBILITY_PUBLIC,
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function upload($path, $contents, Config $config) {
    $path = $this->applyPathPrefix($path);
    
    $options = $this->getOptionsFromConfig($config);
    $options['name'] = $path;

    $object = $this->bucket->upload($contents, $options);

    return $this->normaliseObject($object);
  }

}
