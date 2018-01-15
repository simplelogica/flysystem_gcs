<?php

namespace Drupal\flysystem_gcs;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Drupal\flysystem\FlysystemBridge;
use Symfony\Component\HttpFoundation\Request;

class CloudStorageFlysystemBridge extends FlysystemBridge {

  /**
   * {@inheritdoc}
   */
  public function getDirectoryPath() {
    return static::basePath();
  }

  public static function basePath(\SplString $site_path = NULL) {
    if ($site_path === NULL) {
      // Find the site path. Kernel service is not always available at this
      // point, but is preferred, when available.
      if (\Drupal::hasService('kernel')) {
        $site_path = \Drupal::service('site.path');
      }
      else {
        // If there is no kernel available yet, we call the static
        // findSitePath().
        $site_path = DrupalKernel::findSitePath(Request::createFromGlobals());
      }
    }
    return Settings::get('file_public_path', $site_path . '/files');
  }
}