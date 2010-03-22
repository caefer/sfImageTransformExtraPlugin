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
require_once dirname(__FILE__).'/../../../bootstrap/TestRecord.php';
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
    'type' => 'TestRecord',
    'attribute' => 'file',
    'id' => '1'
  );

  public function testStream_close()
  {
    $this->markTestSkipped('No Propel fixture written yet..');
    //$fh = fopen($this->testSourceUri, ..');;
    //$this->assertTrue(is_resource($fh));
    //fclose($fh);
    //$this->assertFalse(is_resource($fh));
  }

  public function testStream_eof()
  {
    $this->markTestSkipped('No Propel fixture written yet..');
    //$fh = fopen($this->testSourceUri, ..');;
    //$this->assertFalse(feof($fh));
    //while(!feof($fh))
    //{
    //  fgets($fh);
    //}
    //$this->assertTrue(feof($fh));
    //fclose($fh);
  }

  public function testStream_flush()
  {
    $this->markTestSkipped('No Propel fixture written yet..');
    //$fh = fopen($this->testSourceUri, ..');;
    //$this->assertTrue(fflush($fh));
    //fclose($fh);
  }

  public function testStream_open()
  {
    $this->markTestSkipped('No Propel fixture written yet..');
    //$fh = fopen($this->testSourceUri, ..');;
    //$this->assertTrue(is_resource($fh));
    //fclose($fh);
  }

  public function testStream_read()
  {
    $this->markTestSkipped('No Propel fixture written yet..');
    //$fh = fopen($this->testSourceUri, ..');;
    //$this->assertEquals(10, strlen(fread($fh, 10)));
    //fclose($fh);
  }

  public function testStream_stat()
  {
    $this->markTestSkipped('No Propel fixture written yet..');
    //$fh = fopen($this->testSourceUri, ..');;
    //$this->assertTrue(is_array(fstat($fh)));
    //fclose($fh);
  }

  public function testUrl_stat()
  {
    $this->markTestSkipped('No Propel fixture written yet..');
    //$this->assertTrue(is_array(stat($this->testSourceUri)));
  }

  public function testBuildURIfromParameters()
  {
    $this->markTestSkipped('No Propel fixture written yet..');
    //$this->assertEquals('sfImageSource://TestRecord/file#1', sfImageSourcePropel::buildURIfromParameters($this->testParameters));
  }

  protected function setUp()
  {
    $this->markTestSkipped('No Propel fixture written yet..');
    //$this->dbh = new Propel_Adapter_Mock('mys..');;
    //$this->conn = Propel_Manager::getInstance()->openConnection($this->dbh, 'mysql', true);

    //if(in_array('sfImageSource', stream_get_wrappers()))
    //{
    //  stream_wrapper_unregister('sfImageSour..');;
    //}
    //stream_wrapper_register('sfImageSource', 'sfImageSourceProp..'); or die('Failed to register protocol..');;

    //$this->testSourceUri = sfImageSourcePropel::buildURIfromParameters($this->testParameters);
  }
}
