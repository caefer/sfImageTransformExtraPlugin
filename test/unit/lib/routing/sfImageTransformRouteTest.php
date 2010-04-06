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
/** Doctrine test record for mocking */
require_once dirname(__FILE__).'/../../../fixtures/model/doctrine/TestRecord.php';
/** Propel test record for mocking */
require_once dirname(__FILE__).'/../../../fixtures/model/TestObject.php';
/** PHPUnit Framework */
require_once 'PHPUnit/Framework.php';

/**
 * PHPUnit test for sfImageTransformRoute
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage route
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageTransformRouteTest extends PHPUnit_Framework_TestCase
{
  public function testGenerate()
  {
    $this->assertEquals('/thumbnails/Model/default/0.jpg', $this->route->generate(
      array(
        'format' => 'default',
        'type' => 'Model',
        'id' => '0',
        'sf_format' => 'jpg'
      )
    ));
  }

  public function testGenerateFromObject()
  {
    $obj = new TestRecord();
    $this->assertEquals('/thumbnails/TestRecord/default/1.gif', $this->route->generate(
      array(
        'format' => 'default',
        'sf_subject' => $obj
      )
    ));
  }

  public function testGetImageSourceStreamWrapper()
  {
    $this->assertEquals('sfImageSourceDoctrine', $this->route->getImageSourceStreamWrapper());
  }

  public function testGetImageSourceURI()
  {
    $this->route->bind(null, array('type' => 'TestRecord', 'attribute' => 'file', 'id' => '1'));
    $this->assertEquals('sfImageSource://TestRecord/file#1', $this->route->getImageSourceURI());
  }

  protected function setUp()
  {
    $this->route = new sfImageTransformRoute(
      '/thumbnails/:type/:format/:id.:sf_format',
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
        'image_source' => 'Doctrine',
        'segment_separators' => array('/', '.', '-')
      )
    );
  }
}
