<?php
/**
 * This file is part of the sfImageTransformExtraPlugin unit tests package.
 * (c) 2010 Christian Schaefer <caefer@ical.ly>>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @author     Christian Schaefer <caefer@ical.ly>
 * @version    SVN: $Id: sfImageTransformExtraPluginConfigurationTest.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * PHPUnit test for sfImageTransformExtraPluginConfiguration
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage config
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageTransformExtraPluginConfigurationTest extends PHPUnit_Framework_TestCase
{
  public function testInitialize()
  {
    $dispatcher = $this->projectConfiguration->getEventDispatcher();
    $change_action = count($dispatcher->getListeners('controller.change_action'));
    $changed_source = count($dispatcher->getListeners('sf_image_transform.changed_source'));

    $this->pluginConfiguration->initialize();

    $this->assertEquals($change_action + 1, count($dispatcher->getListeners('controller.change_action')));
    $this->assertEquals($changed_source + 1, count($dispatcher->getListeners('sf_image_transform.changed_source')));
  }

  public function testSetViewCache()
  {
    if(false !== sfConfig::get('sf_cache'))
    {
      $event = new sfEvent($this, 'controller.change_action', array('module' => 'sfImageTransformator', 'action' => 'index'));
      sfImageTransformExtraPluginConfiguration::setViewCache($event);
      $viewCacheManager = sfContext::getInstance(sfConfig::get('sf_app'))->getViewCacheManager();
      $this->assertType('sfRawFileCache', $viewCacheManager->getCache());
    }
    else
    {
      $this->markTestSkipped('sf_cache is false, therefor testing setting of a viewcache must be skipped.');
    }
  }

  public function testGetCache()
  {
    $this->assertType('sfRawFileCache', sfImageTransformExtraPluginConfiguration::getCache());
  }

  public function testGetRoute()
  {
    $route = sfImageTransformExtraPluginConfiguration::getRoute('sf_image');
    $this->assertType('sfImageTransformRoute', $route);
  }

  public function testGetRouteWithProjectConfiguration()
  {
    $route = sfImageTransformExtraPluginConfiguration::getRoute('sf_image', $this->projectConfiguration);
    $this->assertType('sfImageTransformRoute', $route);
  }

  /**
   * @expectedException sfImageTransformExtraPluginConfigurationException
   */
  public function testGetRouteForNonExistentRoute()
  {
    $route = sfImageTransformExtraPluginConfiguration::getRoute('sf_image_does_not_exist');
  }

  public function testRemoveOldThumbnails()
  {
    $this->markTestSkipped('Can not be tested so far as static methods can not be mocked easily');
  }

  /**
   * @expectedException sfImageTransformExtraPluginConfigurationException
   */
  public function testRemoveOldThumbnailsWithoutRoute()
  {
    $event = new sfEvent($this, 'sf_image_transform.changed_source', array());
    sfImageTransformExtraPluginConfiguration::removeOldThumbnails($event);
  }

  protected function setUp()
  {
    $this->projectConfiguration = new ProjectConfiguration(dirname(__FILE__).'/../../fixtures/project/');
    $this->pluginConfiguration = new sfImageTransformExtraPluginConfiguration($this->projectConfiguration);
    if(!sfContext::hasInstance('frontend'))
    {
      sfContext::createInstance($this->projectConfiguration->getApplicationConfiguration('frontend', 'test', true));
    }
  }
}
