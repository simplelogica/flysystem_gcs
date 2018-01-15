<?php

namespace Drupal\flysystem_gcs\Flysystem;

use Google\Cloud\Storage\StorageClient;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\flysystem\Plugin\FlysystemPluginInterface;
use Drupal\flysystem\Plugin\FlysystemUrlTrait;
use Drupal\flysystem\Plugin\ImageStyleGenerationTrait;
use Drupal\flysystem_gcs\Flysystem\Adapter\CloudStorageAdapter;
use League\Flysystem\Config;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal plugin for the "Cloud Storage" Flysystem adapter.
 *
 * @Adapter(id = "CloudStorage")
 */
class CloudStorage implements FlysystemPluginInterface, ContainerFactoryPluginInterface {

  use ImageStyleGenerationTrait;
  use FlysystemUrlTrait { getExternalUrl as getDownloadlUrl; }

  /**
   * The gcs bucket.
   *
   * @var Google\Cloud\Storage\Bucket
   */
  protected $bucket;

  /**
   * The gcs client.
   *
   * @var Google\Cloud\Storage\StorageClient
   */
  protected $client;

    /**
   * The URL prefix.
   *
   * @var string
   */
  protected $urlPrefix;

  /**
   * The current scheme configuration
   *
   * @var League\Flysystem\Config $config
   */
  protected $config;

  /**
   * Constructs a CloudStorage object.
   *
   * @param Google\Cloud\Storage\StorageClient $client
   *   The AWS client.
   * @param League\Flysystem\Config $config
   *   The configuration.
   */
  public function __construct(StorageClient $client, Config $config) {
    $this->client = $client;
    $this->config = $config;
    $this->bucket = $client->bucket($config->get('bucket', ''));
    $this->urlPrefix = self::calculateUrlPrefix($config);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    $protocol = $container->get('request_stack')->getCurrentRequest()->getScheme();
    $configuration += [
      'protocol' => $protocol,
    ];

    $config = new Config($configuration);

    $client = new StorageClient([
      'keyFilePath' => $config->get('keyFilePath', ''),
    ]);

    return new static($client, $config);
  }

  /**
   * {@inheritdoc}
   */
  public function getAdapter() {
    return new CloudStorageAdapter($this->client, $this->config);
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl($uri) {
    $target = $this->getTarget($uri);

    if (strpos($target, 'styles/') === 0 && !file_exists($uri)) {
      $this->generateImageStyle($target);
    }

    return $this->urlPrefix . '/' . UrlHelper::encodePath($target);
  }

  /**
   * {@inheritdoc}
   */
  public function ensure($force = FALSE) {
    // @TODO: If the bucket exists, can we write to it? Find a way to test that.
    if (!isset($this->bucket)) {
      return [[
        'severity' => RfcLogLevel::ERROR,
        'message' => 'Bucket %bucket does not exist.',
        'context' => [
          '%bucket' => $this->bucket->name(),
        ],
      ]];
    }

    return [];
  }

  /**
   * Calculates the URL prefix.
   *
   * @param \League\Flysystem\Config $config
   *   The configuration.
   *
   * @return string
   *   The URL prefix in the form protocol://cname[/bucket][/prefix].
   */
  private static function calculateUrlPrefix(Config $config) {

    $bucket = (string) $config->get('bucket', '');

    $uri = self::calculateStoreApiUri($config);

    $prefix = (string) $config->get('prefix', '');
    $prefix = $prefix === '' ? '' : '/' . UrlHelper::encodePath($prefix);

    if (self::isCnameVirtualHosted($uri, $bucket)) {
      return $uri . $prefix;
    }

    $bucket = $bucket === '' ? '' : '/' . UrlHelper::encodePath($bucket);

    return $uri . $bucket . $prefix;
  }

  /**
   * Detects whether the CNAME uses Virtual Hosted–Style Method.
   *
   * @param string $cname
   *   The CNAME.
   * @param string $bucket
   *   The bucket identifer.
   *
   * @return bool
   *   True if the CNAME uses Virtual Hosted–Style Method, false if not.
   *
   * @see http://docs.aws.amazon.com/AmazonS3/latest/dev/VirtualHosting.html
   */
  private static function isCnameVirtualHosted($uri, $bucket) {
    return $bucket === '' || strpos($uri, $bucket) === 0;
  }

  /**
   * Calculates the URL prefix.
   *
   * @param League\Flysystem\Config $config
   *   The configuration.
   *
   * @return string
   *   The uri in the form protocol://domain
   */
  public static function calculateStoreApiUri(Config $config) {
    $protocol = $config->get('protocol', 'https');

    $default_cname = 'storage.googleapis.com';
    $cname = (string) $config->get('cname');
    $cname = $cname === '' ? $default_cname : $cname;

    return $protocol . '://' . $cname;
  }

}
