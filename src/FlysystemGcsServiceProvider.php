<?php

namespace Drupal\flysystem_gcs;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\Site\Settings;

class FlysystemGcsServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {


    foreach (Settings::get('flysystem', []) as $scheme => $settings) {

      // Just some sanity checking, so things don't explode.
      if (empty($settings['driver'])) {
        continue;
      }
      
      $container
        ->getDefinition('flysystem_stream_wrapper.' . $scheme)
        ->setClass('Drupal\flysystem_gcs\CloudStorageFlysystemBridge')
        ->addTag('stream_wrapper', ['scheme' => $scheme]);

      // Override public StreamWrapper
      if ($scheme === 'public' && !empty($settings['config']['public'])) {
        $container->getDefinition('stream_wrapper.public')
          ->setClass('Drupal\flysystem_gcs\CloudStorageFlysystemBridge')
          ->addTag('stream_wrapper', ['scheme' => $scheme]);
      }
    }
  }

}