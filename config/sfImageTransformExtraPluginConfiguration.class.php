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
 * @version    SVN: $Id: sfImageTransformExtraPluginConfiguration.class.php 30357 2010-07-22 05:31:34Z caefer $
 */

/**
 * sfImageTransformExtraPlugin configuration.
 * 
 * @package     sfImageTransformExtraPlugin
 * @subpackage  config
 * @author      Christian Schaefer <caefer@ical.ly>
 * @version     SVN: $Id: sfImageTransformExtraPluginConfiguration.class.php 30357 2010-07-22 05:31:34Z caefer $
 */
class sfImageTransformExtraPluginConfiguration extends sfPluginConfiguration
{
  const VERSION = '1.0.12';

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    if($this->configuration instanceof sfApplicationConfiguration)
    {
      require_once($this->configuration->getConfigCache()->checkConfig('config/thumbnailing.yml'));
    }

    $this->dispatcher->connect('controller.change_action', array('sfImageTransformExtraPluginConfiguration', 'setViewCache'));
    $this->dispatcher->connect('sf_image_transform.changed_source', array('sfImageTransformExtraPluginConfiguration', 'removeOldThumbnails'));
  }

  /**
   * Set a custom view cache class that just dumps the raw file with no expire time stuff
   *
   * @static
   * @param  sfEvent $event Event object as passed by symfony event system
   *
   * @return void
   */
  public static function setViewCache(sfEvent $event)
  {
    $params = $event->getParameters();

    if(sfConfig::get('sf_cache') && 'sfImageTransformator' == $params['module'] && 'index' == $params['action'])
    {
      $config = sfConfig::get('sf_thumbnail_cache');
      if(array_key_exists('namespace_callback', $config))
      {
        sfConfig::set('sf_cache_namespace_callable', $config['namespace_callback']);
      }

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
  public static function getCache()
  {
    extract(sfConfig::get('sf_thumbnail_cache'));
    return new $class($param);
  }

  /**
   * Returns a route instance for a given route name.
   *
   * @static
   * @param  string                 $routeName     Name of the route
   * @param  sfProjectConfiguration $configuration Optionally pass a project configuration (i.e. when called from a task)
   *
   * @return sfRoute
   */
  public static function getRoute($routeName, sfProjectConfiguration $configuration = null)
  {
    if(is_null($configuration))
    {
      $configuration = sfProjectConfiguration::getActive();
    }

    $routing = sfRoutingConfigHandler::getConfiguration($configuration->getConfigPaths('config/routing.yml'));
    if(!array_key_exists($routeName, $routing))
    {
      throw new sfImageTransformExtraPluginConfigurationException('Route "'.$routeName.'" could not be found!');
    }
    $route = $routing[$routeName];
    return new $route['class']($route['url'], $route['param'], $route['requirements'], $route['options']);
  }

  /**
   * Removes all generated thumbnails for given asset when a new contentAsset is published
   *
   * @static
   * @param  sfEvent $event Event object as passed by symfony event system
   *
   * @return void
   */
  public static function removeOldThumbnails(sfEvent $event)
  {
    $options = $event->getParameters();
    if(!array_key_exists('route', $options))
    {
      throw new sfImageTransformExtraPluginConfigurationException('Coul\'d not read the "route" parameter from event!');
    }
    $routeName = $options['route'];
    unset($options['route']);

    $route = self::getRoute($routeName);
    $route->preassemblePattern($options);
    self::getCache()->removePattern($route);
  }
}
