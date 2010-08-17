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
 * @version    SVN: $Id: TestTable.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Mocked Doctrine table to use in tests
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage Table
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class TestRecordTable extends Doctrine_Table
{
  public function find()
  {
    list($id) = func_get_args();
    switch ($id)
    {
      case 1:
        return new TestRecord();
      default:
        return null;
    }
  }
}
