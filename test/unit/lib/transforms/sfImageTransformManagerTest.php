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
 * PHPUnit test for sfImageTransformManager
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage transforms
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageTransformManagerTest extends PHPUnit_Framework_TestCase
{
  private $dummy_formats = array(
      'original' => array(
        'quality' => 100,
        'mime_type' => 'image/png',
        'transformations' => array(
          0 => array(
            'adapter' => 'GD',
            'transformation' => 'overlay',
            'param' => array(
              'overlay' => 'overlays/logo.png',
            )
          ),
        ),
      )
    );

  private $dummy_failing_formats = array(
      'original' => array(
        'quality' => 100,
        'mime_type' => 'image/png',
        'transformations' => array(
          0 => array(
            'adapter' => 'GD',
            'transformation' => 'overlay',
            'param' => array(
              'overlay' => 'overlays/doesnotexist.png',
            )
          ),
        ),
      )
    );

  public function test__constructException()
  {
    try
    {
      $manager = new sfImageTransformManager();
    }
    catch(sfImageTransformExtraPluginConfigurationException $e)
    {
      $this->assertTrue(true);
    }
  }

  public function test__construct()
  {
    $manager = new sfImageTransformManager($this->dummy_formats);
    $this->assertType('sfImageTransformManager', $manager);
  }

  /**
   * @expectedException sfImageTransformExtraPluginConfigurationException
   */
  public function testGenerateWrongFormat()
  {
    $manager = new sfImageTransformManager($this->dummy_formats);
    $this->assertType('sfImage', $manager->generate('sfImageSource://mock', 'doesnotexist'));
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testGenerateIncompleteFormat()
  {
    $manager = new sfImageTransformManager($this->dummy_failing_formats);
    $this->assertType('sfImage', $manager->generate('sfImageSource://mock', 'original'));
  }

  public function testGenerate()
  {
    $manager = new sfImageTransformManager($this->dummy_formats);
    $this->assertType('sfImage', $manager->generate('sfImageSource://mock', 'original'));
  }

  protected function setUp()
  {
    if(!sfContext::hasInstance('frontend'))
    {
      $configuration = new ProjectConfiguration(dirname(__FILE__).'/../../fixtures/project');
      sfContext::createInstance($configuration->getApplicationConfiguration('frontend', 'test', true));
    }

    if(in_array('sfImageSource', stream_get_wrappers()))
    {
      stream_wrapper_unregister('sfImageSource');
    }
    stream_wrapper_register('sfImageSource', 'sfImageSourceMock') or die('Failed to register protocol..');
  }
}
