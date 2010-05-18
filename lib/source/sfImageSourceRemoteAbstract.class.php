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
   * @var int $offset current file pointer position
   */
  protected $offset = 0;

  /**
   * Tests for end-of-file on a file pointer
   *
   * @return bool
   */
  public function stream_eof()
  {
    $headers = get_headers($this->filename, 1);
    return $this->offset >= $headers['Content-Length'];
  }

  /**
   * Read from stream
   *
   * @param int $count
   * @return string
   */
  public function stream_read($count)
  {
    $content = stream_get_contents($this->resource, $count);
    $this->offset += strlen($content);
    return $content;
  }

  /**
   * Seeks to specific location in a stream
   *
   * @param int  $offset
   * @param int  $whence
   * @return bool
   */
  public function stream_seek($offset, $whence = SEEK_SET)
  {
    switch($whence)
    {
      case SEEK_SET:
        $this->offset = $offset;
        break;
      case SEEK_CUR:
        $this->offset += $offset;
        break;
      case SEEK_END:
        $headers = get_headers($this->filename, 1);
        $this->offset = $headers['Content-Length'] + $offset;
        break;
    }
    return true;
  }

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
   * Retrieve the current position of a stream
   * 
   * @return int 
   */ 
  public function stream_tell()
  {
    return $this->offset;
  }

  /**
   * Retrieve information about a file
   *
   * ATTENTION! stat() does not work with http streams but is only needed because
   * it is called internally by file_exists() which is used by sfImage. Returning
   * an empty array is sufficient to this call.
   * This one is also called by is_readable() which checks the inode protection mode.
   *
   * @param string $path
   * @param int $flags
   * @return array
   */
  public function url_stat($path , $flags)
  {
    $headers = get_headers($this->translatePathToFilename($path), 1);
    return array(
      'mode' => 0555,
      'size' => $headers['Content-Length']
    );
  }
}
