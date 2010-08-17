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
 * @version    SVN: $Id: sfImageSourceHTTPTest.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * PHPUnit test for sfImageSourceHTTP transformation
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceHTTPTest extends PHPUnit_Framework_TestCase
{
  private $testSourceUri = null;
  private $testParameters = array(
    'protocol' => 'http',
    'domain' => 'www.google.com',
    'filepath' => 'intl/en_com/images/srpr/logo1w.png'
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
    $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_RESOURCE, $fh);
    fclose($fh);
  }

  public function testStream_read()
  {
    $fh = fopen($this->testSourceUri, 'r');
    $this->assertEquals(10, strlen(fread($fh, 10)));
    fclose($fh);
  }

  public function testStream_seek()
  {
    $fh = fopen($this->testSourceUri, 'r');
    $this->assertEquals(0, fseek($fh, 1));
    $this->assertEquals(1, ftell($fh));
    fseek($fh, 1, SEEK_CUR);
    $this->assertEquals(2, ftell($fh));
    fseek($fh, 1, SEEK_SET);
    $this->assertEquals(1, ftell($fh));
    fseek($fh, -1, SEEK_END);
    $this->assertEquals(filesize($this->testSourceUri)-1, ftell($fh));
    rewind($fh);
    $this->assertEquals(0, ftell($fh));
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
    $this->assertEquals('sfImageSource://http/www.google.com#intl/en_com/images/srpr/logo1w.png', sfImageSourceHTTP::buildURIfromParameters(array()));
  }

  public function testBuildURIfromParameters()
  {
    $this->assertEquals('sfImageSource://http/www.google.com#intl/en_com/images/srpr/logo1w.png', sfImageSourceHTTP::buildURIfromParameters($this->testParameters));
  }

  protected function setUp()
  {
    if(in_array('sfImageSource', stream_get_wrappers()))
    {
      stream_wrapper_unregister('sfImageSource');
    }
    sfConfig::set('thumbnailing_source_image_stream_param', array('url_schema' => 'http://localhost/%type/%attribute/%id'));
    stream_wrapper_register('sfImageSource', 'sfImageSourceHTTP') or die('Failed to register protocol..');

    $this->testSourceUri = sfImageSourceHTTP::buildURIfromParameters($this->testParameters);
  }
}
