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
 * PHPUnit test for sfImageSourceFile transformation
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceFileTest extends PHPUnit_Framework_TestCase
{
  private $testSourceUri = null;
  private $testParameters = array(
    'filepath' => 'testrecord/caefer.jpg'
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

  /**
   * @expectedException sfError404Exception
   */
  public function testFailedStream_open()
  {
    $fh = fopen('sfImageSource://file/does/not/exist/gif', 'r');
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
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_RESOURCE, $fh);
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
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, fstat($fh));
    fclose($fh);
  }

  public function testUrl_stat()
  {
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, stat($this->testSourceUri));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testFailedBuildURIfromParameters()
  {
    $this->assertEquals('sfImageSource://file/testrecord/caefer.jpg', sfImageSourceFile::buildURIfromParameters(array()));
  }

  public function testBuildURIfromParameters()
  {
    $this->assertEquals('sfImageSource://file/testrecord/caefer.jpg', sfImageSourceFile::buildURIfromParameters($this->testParameters));
  }

  protected function setUp()
  {
    if(in_array('sfImageSource', stream_get_wrappers()))
    {
      stream_wrapper_unregister('sfImageSource');
    }
    stream_wrapper_register('sfImageSource', 'sfImageSourceFile') or die('Failed to register protocol..');

    $this->testSourceUri = sfImageSourceFile::buildURIfromParameters($this->testParameters);
  }
}
