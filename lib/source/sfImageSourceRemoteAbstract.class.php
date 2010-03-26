<?php
/**
 * This file is part of the sfImageTransformExtraPlugin package.
 * (c) 2010 Christian Schaefer <caefer@ical.ly>>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    sfImageTransformExtraPlugin
 * @author     Christian Schaefer <caefer@ical.ly>
 * @version    SVN: $Id: sfRawFileCache.class.php 63 2010-03-09 04:34:28Z caefer $
 */

/**
 * Maps sfImageSource:// URLs to a remote image file
 *
 * This class only overrides the two stat methods and returns empty arrays.
 * stat/fstat is internally used by file_exists() which is called by sfImage.
 * It doesn't rely on stat information but on a positiv return.
 * Unfortunately stat and fstat do not work with URLs other than on the local
 * filesystem therefor these methods are faked in this class.
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
abstract class sfImageSourceRemoteAbstract extends sfImageSourceLocalAbstract implements sfImageSourceInterface
{
  /** 
   * Retrieve information about a file resource
   *
   * ATTENTION! stat() does not work with http streams but is only needed because
   * it is called internally by file_exists() which is used by sfImage. Returning
   * an empty array is sufficient to this call.
   *
   * @return array 
   */ 
  public function stream_stat()
  {
    return array();
  }

  /**
   * Retrieve information about a file
   *
   * ATTENTION! stat() does not work with http streams but is only needed because
   * it is called internally by file_exists() which is used by sfImage. Returning
   * an empty array is sufficient to this call.
   *
   * @param string $path
   * @param int $flags
   * @return array
   */
  public function url_stat($path , $flags)
  {
    return array();
  }
}
