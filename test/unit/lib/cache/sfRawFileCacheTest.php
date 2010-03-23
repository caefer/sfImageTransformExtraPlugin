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
require_once dirname(__FILE__).'/../../../bootstrap/unit.php';
/** PHPUnit Framework */
require_once 'PHPUnit/Framework.php';

/**
 * PHPUnit test for sfRawFileCache
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage cache
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfRawFileCacheTest extends PHPUnit_Framework_TestCase
{
  public function testGet()
  {
    $this->assertEquals(null, $this->cache->get('any_key'));
    $this->assertEquals(true, $this->cache->get('any_key', true));
  }

  public function testHas()
  {
    $this->assertEquals(false, $this->cache->has('any_key'));
  }

  public function testWrite()
  {
    $dispatcher = new sfEventDispatcher();
    $response = new sfWebResponse($dispatcher);
    // test set as write is protected
    $this->assertEquals(true, $this->cache->set('any/key', serialize($response)));
  }

  public function testRemovePattern()
  {
    $this->markTestIncomplete('This test has not been implemented yet.');
  }

  public function testGetLastModified()
  {
    $this->assertEquals(0, $this->cache->getLastModified('any_key'));
  }

  public function testGetTimeout()
  {
    $this->assertEquals(0, $this->cache->getTimeout('any_key'));
  }

  private function getCache($dir)
  {
    return new sfRawFileCache(array(
      'automatic_cleaning_factor' => 0,
      'cache_dir' => $dir,
    ));
  }

  protected function setUp()
  {
    $this->cache = $this->getCache(sfConfig::get('sf_cache_dir').'/sfImageTransformExtraPluginUnitTests');
  }

  protected function tearDown()
  {
    exec('rm -rf '.sfConfig::get('sf_cache_dir').'/sfImageTransformExtraPluginUnitTests');
  }
}

