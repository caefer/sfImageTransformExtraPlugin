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
require_once dirname(__FILE__).'/../../../bootstrap/TestRecord.php';
/** PHPUnit Framework */
require_once 'PHPUnit/Framework.php';

/**
 * PHPUnit test for sfImageSourceTemplate
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage template
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceTemplateTest extends PHPUnit_Framework_TestCase
{
  public function testRetrieveFilenameForAttribute($attribute = '0')
  {
    $this->assertEquals('test-me', $this->template->retrieveFilenameForAttribute());
  }

  public function testGetPath()
  {
    $this->assertEquals('00/00/00', $this->template->getPath());
  }

  public function testGetType()
  {
    $this->assertEquals('TestRecord', $this->template->getType());
  }

  protected function setUp()
  {
    $this->dbh = new Doctrine_Adapter_Mock('mysql');
    $this->conn = Doctrine_Manager::getInstance()->openConnection($this->dbh, 'mysql', true);

    $this->template = new sfImageSourceTemplate(array('fields' => array('test_attribute')));
    $this->template->setInvoker(new TestRecord());
  }
}
