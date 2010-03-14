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
    $this->assertEquals('/thumbnails/Model/default/00/00/00/bar-foo-0.jpg', $this->route->generate(
      array(
        'format' => 'default',
        'type' => 'Model',
        'path' => '00/00/00',
        'slug' => 'bar-foo',
        'id' => '0',
        'sf_format' => 'jpg'
      )
    ));
  }

  public function testBind()
  {
    $this->route->bind(null, array());
    $this->assertTrue($this->route->isBound());
  }

  protected function setUp()
  {
    $this->route = new sfImageTransformRoute(
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
    );
  }

  protected function tearDown()
  {
    unset($this->route);
  }
}
