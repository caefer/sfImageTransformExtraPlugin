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

  public function testRemovePatternForDoctrineSources()
  {
    $route = $this->getRoute('sf_image_doctrine');
    $route->preassemblePattern(array('type' => 'testrecord'));
    $finder = new sfFinder();
    $starting_count = count($finder->type('file')->in($this->cache->getOption('cache_dir')));
    $this->cache->removePattern($route);
    $files = $finder->type('file')->in($this->cache->getOption('cache_dir'));
    $this->assertNotContains($this->cache->getOption('cache_dir').'/thumbnails/testrecord/default/01/00/00/test-record-1.gif', $files);
    $this->assertNotContains($this->cache->getOption('cache_dir').'/thumbnails/testrecord/original/01/00/00/test-record-1.jpg', $files);
    $this->assertNotContains($this->cache->getOption('cache_dir').'/thumbnails/testrecord/default/02/00/00/test-record-2.gif', $files);
    $this->assertNotContains($this->cache->getOption('cache_dir').'/thumbnails/testrecord/original/02/00/00/test-record-2.jpg', $files);
    $this->assertEquals($starting_count - 4, count($files));
  }

  public function testRemovePatternForFileSources()
  {
    $route = $this->getRoute('sf_image_file');
    $route->preassemblePattern(array('filepath' => 'path/to/file/filename'));
    $finder = new sfFinder();
    $starting_count = count($finder->type('file')->in($this->cache->getOption('cache_dir')));
    $this->cache->removePattern($route);
    $files = $finder->type('file')->in($this->cache->getOption('cache_dir'));
    $this->assertNotContains($this->cache->getOption('cache_dir').'/thumbnails/default/path/to/file/filename.gif', $files);
    $this->assertNotContains($this->cache->getOption('cache_dir').'/thumbnails/original/path/to/file/filename.jpg', $files);
    $this->assertEquals($starting_count - 2, count($files));
  }

  public function testRemovePatternForHTTPSources()
  {
    $route = $this->getRoute('sf_image_http');
    $route->preassemblePattern(array('format' => 'default'));
    $finder = new sfFinder();
    $starting_count = count($finder->type('file')->in($this->cache->getOption('cache_dir')));
    $this->cache->removePattern($route);
    $files = $finder->type('file')->in($this->cache->getOption('cache_dir'));
    $this->assertNotContains($this->cache->getOption('cache_dir').'/thumbnails/site/default/path/to/file/filename.gif', $files);
    $this->assertContains($this->cache->getOption('cache_dir').'/thumbnails/site/original/path/to/file/filename.jpg', $files);
    $this->assertEquals($starting_count - 1, count($files));
  }

  public function testRemovePatternForPropelSources()
  {
    $this->markTestSkipped('Propel is not tested yet');
  }

  public function testRemovePatternForMockSources()
  {
    $route = $this->getRoute('sf_image_mock');
    $route->preassemblePattern(array());
    $finder = new sfFinder();
    $starting_count = count($finder->type('file')->in($this->cache->getOption('cache_dir')));
    $this->cache->removePattern($route);
    $files = $finder->type('file')->in($this->cache->getOption('cache_dir'));
    $this->assertNotContains($this->cache->getOption('cache_dir').'/thumbnails/default.gif', $files);
    $this->assertNotContains($this->cache->getOption('cache_dir').'/thumbnails/original.jpg', $files);
    $this->assertEquals($starting_count - 2, count($files));
  }

  public function testGetLastModified()
  {
    $this->assertEquals(0, $this->cache->getLastModified('any_key'));
  }

  public function testGetTimeout()
  {
    $this->assertEquals(0, $this->cache->getTimeout('any_key'));
  }

  public function testSetCacheKey()
  {
    $internalUri = 'sfImageTransformator/index?format=original&filepath=TestFile&sf_format=jpg';
    $viewCacheManager = new sfViewCacheManager(sfContext::getInstance(), new sfNoCache());
    $path = sfRawFileCache::setCacheKey($internalUri, '', '', '', $viewCacheManager);
    $this->assertContains('/thumbnails/original/TestFile.jpg', $path);
  }

  private function getCache($dir)
  {
    return new sfRawFileCache(array(
      'automatic_cleaning_factor' => 0,
      'cache_dir' => $dir,
    ));
  }

  private function getRoute($routeName)
  {
    switch($routeName)
    {
      case 'sf_image_doctrine':
        return new sfImageTransformRoute(
          '/thumbnails/:type/:format/:path/:slug-:id.:sf_format',
          array(
            'module' => 'sfImageTransformator',
            'action' => 'index',
            'attribute' => 'file'
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
            'image_source' => 'Doctrine',
            'segment_separators' => array('/', '.', '-')
          )
        );
      case 'sf_image_mock':
        return new sfImageTransformRoute(
          '/thumbnails/:format.:sf_format',
          array(
            'module' => 'sfImageTransformator',
            'action' => 'index'
          ),
          array(
            'format' => '[\\w_-]+(?:,[\\w_-]+(?:,[\\w_-]+)?)?',
            'sf_format' => 'gif|png|jpg',
            'sf_method' => array('get')
          ),
          array(
            'image_source' => 'Mock'
          )
        );
      case 'sf_image_file':
        return new sfImageTransformRoute(
          '/thumbnails/:format/:filepath.:sf_format',
          array(
            'module' => 'sfImageTransformator',
            'action' => 'index'
          ),
          array(
            'format' => '[\\w_-]+(?:,[\\w_-]+(?:,[\\w_-]+)?)?',
            'filepath' => '[\w/.]+',
            'sf_format' => 'gif|png|jpg',
            'sf_method' => array('get')
          ),
          array(
            'image_source' => 'File'
          )
        );
      case 'sf_image_http':
        return new sfImageTransformRoute(
          '/thumbnails/:site/:format/:filepath.:sf_format',
          array(
            'module' => 'sfImageTransformator',
            'action' => 'index',
            'protocol' => 'http',
            'domain' => 'localhost'
          ),
          array(
            'format' => '[\\w_-]+(?:,[\\w_-]+(?:,[\\w_-]+)?)?',
            'filepath' => '[\w/.]+',
            'protocol' => 'http|https',
            'domain' => '[\w-_.]+',
            'sf_format' => 'gif|png|jpg',
            'sf_method' => array('get')
          ),
          array(
            'image_source' => 'Doctrine'
          )
        );
    }
  }

  protected function setUp()
  {
    $appConfig = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true);
    sfContext::createInstance($appConfig);
    $this->cache = $this->getCache(sfConfig::get('sf_cache_dir').'/sfImageTransformExtraPluginUnitTests');
    $fs = new sfFilesystem();
    $src_dir = dirname(__FILE__).'/../../../fixtures/thumbnails';
    $dst_dir = $this->cache->getOption('cache_dir').'/thumbnails';
    $fs->mirror($src_dir, $dst_dir, new sfFinder(), array('override'=>true));
  }

  protected function tearDown()
  {
    exec('rm -rf '.sfConfig::get('sf_cache_dir').'/sfImageTransformExtraPluginUnitTests');
  }
}

