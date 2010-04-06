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
 * Mocked Propel table to use in tests
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage Peer
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class TestPeer
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
