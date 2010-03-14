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
 * @version    SVN: $Id: sfRawFileCache.class.php 63 2010-03-09 04:34:28Z caefer $
 */

/** central bootstrap for unit tests */
require_once dirname(__FILE__).'/../../bootstrap/unit.php';
/** PHPUnit Framework */
require_once 'PHPUnit/Framework.php';

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
    $load_factories = count($dispatcher->getListeners('context.load_factories'));
    $load_configuration = count($dispatcher->getListeners('routing.load_configuration'));
    $change_action = count($dispatcher->getListeners('controller.change_action'));
    $changed_source = count($dispatcher->getListeners('sf_image_transform.changed_source'));

    $this->pluginConfiguration->initialize();

    $this->assertEquals($load_factories + 1, count($dispatcher->getListeners('context.load_factories')));
    $this->assertEquals($load_configuration + 1, count($dispatcher->getListeners('routing.load_configuration')));
    $this->assertEquals($change_action + 1, count($dispatcher->getListeners('controller.change_action')));
    $this->assertEquals($changed_source + 1, count($dispatcher->getListeners('sf_image_transform.changed_source')));
  }

  public function testSetCacheKey()
  {
    $internalUri = 'sfImageTransformator/index?type=TestFile&format=original&path=00/00/00&slug=barfoo&id=1&sf_format=jpg';
    $viewCacheManager = new sfViewCacheManager(sfContext::getInstance(), new sfNoCache());
    $path = sfImageTransformExtraPluginConfiguration::setCacheKey($internalUri, '', '', '', $viewCacheManager);
    $this->assertContains('/thumbnails/TestFile/original/00/00/00/barfoo-1.jpg', $path);
  }

  public function testSetViewCache()
  {
    $this->markTestSkipped('ViewCacheManagers can not be tested from command line');
  }

  public function testGetCache()
  {
    $this->assertType('sfRawFileCache', sfImageTransformExtraPluginConfiguration::getCache());
  }

  public function testRegisterStreamWrapper()
  {
    stream_wrapper_unregister('sfImageSource');
    $event = new sfEvent($this, 'context.load_factories', array());
    sfImageTransformExtraPluginConfiguration::registerStreamWrapper($event);
    $this->assertTrue(in_array('sfImageSource', stream_get_wrappers()));
  }

  public function testPrependRoutes()
  {
    $routing = new sfPatternRouting($this->projectConfiguration->getEventDispatcher());
    $routing->clearRoutes();
    $event = new sfEvent($routing, 'routing.load_configuration', array());
    sfImageTransformExtraPluginConfiguration::prependRoutes($event);
    $this->assertTrue($routing->hasRouteName('sf_image'));
  }

  public function testRemoveOldThumbnails()
  {
    //$event = new sfEvent($this, 'sf_image_transform.changed_source', array());
    //sfImageTransformExtraPluginConfiguration::registerStreamWrapper($event);
    $this->markTestIncomplete('Removal not yet implemented');
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
