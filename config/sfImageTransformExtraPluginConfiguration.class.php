<?php
/**
 * This file is part of the sfImageTransformExtraPlugin package.
 * (c) 2010 Christian Schaefer <caefer@ical.ly>>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    sfImageTransformExtraPlugin
 * @author     Christian Schaefer <caefer@ical.ly>
 * @version    SVN: $Id: sfRawFileCache.class.php 63 2010-03-09 04:34:28Z caefer $
 */

/**
 * sfImageTransformExtraPlugin configuration.
 * 
 * @package     sfImageTransformExtraPlugin
 * @subpackage  config
 * @author      Christian Schaefer <caefer@ical.ly>
 * @version     SVN: $Id: sfImageTransformExtraPluginConfiguration.class.php 63 2010-03-09 04:34:28Z caefer $
 */
class sfImageTransformExtraPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '1.0.0-DEV';

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    if($this->configuration instanceof sfApplicationConfiguration)
    {
      require_once($this->configuration->getConfigCache()->checkConfig('config/thumbnailing.yml'));
    }

    $this->dispatcher->connect('context.load_factories', array('sfImageTransformExtraPluginConfiguration', 'registerStreamWrapper'));
    $this->dispatcher->connect('routing.load_configuration', array('sfImageTransformExtraPluginConfiguration', 'prependRoutes'));
    $this->dispatcher->connect('controller.change_action', array('sfImageTransformExtraPluginConfiguration', 'setViewCache'));
    $this->dispatcher->connect('sf_image_transform.changed_source', array('sfImageTransformExtraPluginConfiguration', 'removeOldThumbnails'));
  }

  /**
   * Callback to set a custom cache key
   *
   * This sets the cache key to the same value as the current image url.
   * set in settings.yml / cache_namespace_callable
   *
   * @static
   * @param  sfEvent $event Event object as passed by symfony event system
   *
   * @return void
   */
  static public function setCacheKey($internalUri, $hostName = '', $vary = '', $contextualPrefix = '', $sfViewCacheManager)
  {
    return sfContext::getInstance()->getController()->genUrl($internalUri, false);
  }

  /**
   * Set a custom view cache class that just dumps the raw file with no expire time stuff
   *
   * @static
   * @param  sfEvent $event Event object as passed by symfony event system
   *
   * @return void
   */
  static public function setViewCache(sfEvent $event)
  {
    $params = $event->getParameters();

    if(sfConfig::get('sf_cache') && 'sfImageTransformator' == $params['module'] && 'index' == $params['action'])
    {
      $viewCacheManager = sfContext::getInstance(sfConfig::get('sf_app'))->getViewCacheManager();
      $viewCacheManager->initialize(
        $viewCacheManager->getContext(),
        self::getCache()
      );
    }
  }

  /**
   * Returns a cache instance that is used by the view cache manager and the removal task.
   *
   * @static
   *
   * @return sfCache
   */
  static public function getCache()
  {
    return new sfRawFileCache(array(
      'automatic_cleaning_factor' => 0,
      'cache_dir' => sfConfig::get('sf_web_dir')
    ));
  }

  /**
   * Registers the sfImageSource:// protocol stream wrapper
   *
   * @static
   * @param  sfEvent $event Event object as passed by symfony event system
   *
   * @return void
   */
  static public function registerStreamWrapper(sfEvent $event)
  {
    if(in_array('sfImageSource', stream_get_wrappers()))
    {
      stream_wrapper_unregister('sfImageSource');
    }
    $streamwrapper = sfConfig::get('thumbnailing_source_image_stream_class', 'sfImageSourceMock');
    stream_wrapper_register('sfImageSource', $streamwrapper) or die('Failed to register protocol..');
  }

  /**
   * Prepends default route for generated images
   *
   * @static
   * @param  sfEvent $event Event object as passed by symfony event system
   *
   * @return void
   */
  static public function prependRoutes(sfEvent $event)
  {
    $routing = $event->getSubject();
    $routes  = $routing->getRoutes();

    if(!array_key_exists('sf_image', $routes))
    {
      $routing->prependRoute('sf_image',
        new sfImageTransformRoute(
          '/thumbnails/:type/:format/:path/:slug-:id.:sf_format',
          array(
            'module' => 'sfImageTransformator',
            'action' => 'index'
          ),
          array(
            'format' => '[\\w_-]+(?:,[\\w_-]+(?:,[\\w_-]+)?)?',
            'path' => '[\\w/]+',
            'slug' => '[\\w_-]+',
            'id' => '\d+(?:,\d+)?',
            'sf_format' => 'gif|png|jpg',
            'sf_method' => array('get')
          ),
          array(
            'segment_separators' => array('/', '.', '-')
          )
        )
      );
    }
  }

  /**
   * Removes all generated thumbnails for given asset when a new contentAsset is published
   *
   * @todo
   * @static
   * @param  sfEvent $event Event object as passed by symfony event system
   *
   * @return void
   */
  static public function removeOldThumbnails(sfEvent $event)
  {
    $options = $event->getParameters();
    $pattern = sprintf('*:%s:*:**:*-%s.*',
      $options['type'],
      $options['id']
    );

    self::getCache()->removePattern($pattern);
  }
}
