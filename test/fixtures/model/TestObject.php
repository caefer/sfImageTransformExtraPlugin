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

/* require Peer class */
require_once(dirname(__FILE__).'/TestObjectPeer.php');

/**
 * Mocked Propel record to use in tests
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage Object
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class TestObject
{
  public function getId()
  {
    return 1;
  }

  public function getFile()
  {
    return 'daphne.jpg';
  }
}
