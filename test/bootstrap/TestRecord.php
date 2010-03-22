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

/**
 * Mocked Doctrine record to use in tests
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage Record
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class TestRecord extends Doctrine_Record
{
  public function getTestAttribute()
  {
    return 'test-me';
  }

  public function getFile()
  {
    return '/testrecord/daphne.jpg';
  }
}

/**
 * Mocked Doctrine table to use in tests
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage Table
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class TestRecordTable extends Doctrine_Table
{
  public function find($id)
  {
    return new TestRecord();
  }
}
