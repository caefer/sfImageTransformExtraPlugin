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
 * @version    SVN: $Id: TestObjectPeer.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Mocked Propel table to use in tests
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage Peer
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class TestObjectPeer
{
  public static function retrieveByPK($pk)
  {
    switch ($pk)
    {
      case 1:
        return new TestObject();
      default:
        return null;
    }
  }
}
