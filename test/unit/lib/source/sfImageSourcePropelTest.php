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
/** Propel test record for mocking */
require_once dirname(__FILE__).'/../../../fixtures/model/TestObject.php';
/** PHPUnit Framework */
require_once 'PHPUnit/Framework.php';

/**
 * PHPUnit test for sfImageSourcePropel transformation
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourcePropelTest extends PHPUnit_Framework_TestCase
{
  private $testSourceUri = null;
  private $testParameters = array(
    'type' => 'TestObject',
    'attribute' => 'file',
    'id' => '1'
  );

  public function testStream_close()
  {
    $fh = fopen($this->testSourceUri, 'r');
    $this->assertTrue(is_resource($fh));
    fclose($fh);
    $this->assertFalse(is_resource($fh));
  }

  public function testStream_eof()
  {
    $fh = fopen($this->testSourceUri, 'r');
    $this->assertFalse(feof($fh));
    while(!feof($fh))
    {
      fgets($fh);
    }
    $this->assertTrue(feof($fh));
    fclose($fh);
  }

  public function testStream_flush()
  {
    $fh = fopen($this->testSourceUri, 'r');
    $this->assertTrue(fflush($fh));
    fclose($fh);
  }

  public function testStream_open()
  {
    $fh = fopen($this->testSourceUri, 'r');
    $this->assertTrue(is_resource($fh));
    fclose($fh);
  }

  public function testStream_read()
  {
    $fh = fopen($this->testSourceUri, 'r');
    $this->assertEquals(10, strlen(fread($fh, 10)));
    fclose($fh);
  }

  public function testStream_stat()
  {
    $fh = fopen($this->testSourceUri, 'r');
    $this->assertTrue(is_array(fstat($fh)));
    fclose($fh);
  }

  public function testUrl_stat()
  {
    $this->assertTrue(is_array(stat($this->testSourceUri)));
  }

  public function testBuildURIfromParameters()
  {
    $this->assertEquals('sfImageSource://TestObject/file#1', sfImageSourcePropel::buildURIfromParameters($this->testParameters));
  }

  protected function setUp()
  {
    if(in_array('sfImageSource', stream_get_wrappers()))
    {
      stream_wrapper_unregister('sfImageSource');
    }
    stream_wrapper_register('sfImageSource', 'sfImageSourcePropel') or die('Failed to register protocol..');

    $this->testSourceUri = sfImageSourcePropel::buildURIfromParameters($this->testParameters);
  }
}
