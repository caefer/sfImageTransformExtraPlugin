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
 * PHPUnit test for sfImageAlphaMaskGD transformation
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage transformation
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageAlphaMaskGDTest extends PHPUnit_Framework_TestCase
{
  /**
   * Testing the constructor
   *
   * @see sfImageAlphaMaskGD::__construct()
   * @return void
   */
  public function test__construct() 
  {
    $mask = new sfImage(dirname(__FILE__).'/../../../../data/example-resources/masks/pattern.gif');
    $transformation = new sfImageAlphaMaskGD($mask, false); 
    $this->assertType('sfImageAlphaMaskGD', $transformation);
  }

  public function testTransform() 
  {
    $mask = new sfImage(dirname(__FILE__).'/../../../../data/example-resources/masks/pattern.gif');
    $this->assertType('sfImage', $this->img->alphaMask($mask));
  }

  public function testTransformWithColor() 
  {
    $mask = new sfImage(dirname(__FILE__).'/../../../../data/example-resources/masks/pattern.gif');
    $this->assertType('sfImage', $this->img->alphaMask($mask, '#0000FF'));
  }

  public function testNonPngTransform() 
  {
    $mask = new sfImage(dirname(__FILE__).'/../../../../data/caefer.jpg');
    $this->assertType('sfImage', $this->img->alphaMask($mask));
  }
  protected function setUp()
  {
    $this->img = new sfImage(dirname(__FILE__).'/../../../../data/example-resources/overlays/logo.png');
  }
}
