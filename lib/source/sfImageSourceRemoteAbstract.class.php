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
 * @version    SVN: $Id: sfImageSourceRemoteAbstract.class.php 29957 2010-06-24 08:24:23Z caefer $
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
 * @author     Jan Schumann <js@schumann-it.com>
 */
abstract class sfImageSourceRemoteAbstract extends sfImageSourceLocalAbstract implements sfImageSourceInterface
{
  /**
   * @var int $offset current file pointer position
   */
  protected $offset = 0;

  /**
   * @var string $content Binary contents of the remote file. Unfortunately this is necessary as remote streams are not seekable but getimagesize() tries to do that..
   */
  private $content = null;

  protected function getSize($filename)
  {
    $headers = get_headers($filename, 1);
    return $headers['Content-Length'];
  }

  /**
   * Tests for end-of-file on a file pointer
   *
   * @return bool
   */
  public function stream_eof()
  {
    return $this->offset >= $this->getSize($this->filename);
  }

  /**
   * Read from stream
   *
   * @param int $count
   * @return string
   */
  final public function stream_read($count)
  {
    if(is_null($this->content))
    {
      $this->content = stream_get_contents($this->resource);
    }

    $chunk = substr($this->content, $this->offset, $count);
    $this->offset += strlen($chunk);
    return $chunk;
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
        $this->offset = $this->getSize($this->filename) + $offset;
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
    return array(
      'mode' => 0555,
      'size' => $this->getSize($this->translatePathToFilename($path))
    );
  }
}
