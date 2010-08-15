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
 * @version    SVN: $Id: sfImageSourceLocalAbstract.class.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Maps sfImageSource:// URLs to an local image file
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
abstract class sfImageSourceLocalAbstract implements sfImageSourceInterface
{
  /**
   * resource context
   *
   * @var resource
   */
  public $context;

  /**
   * resource handle
   *
   * @var resource
   */
  protected $resource;

  /**
   * mock image absolute path
   *
   * @var string
   */
  protected $filename;

  /**
   * Close an resource
   *
   * @return void
   */
  public function stream_close()
  {
    return fclose($this->resource);
  }

  /**
   * Tests for end-of-file on a file pointer
   *
   * @return bool
   */
  public function stream_eof()
  {
    return feof($this->resource);
  }

  /**
   * Flushes the output
   *
   * @return bool
   */
  public function stream_flush()
  {
    return fflush($this->resource);
  }


  /**
   * Opens file or URL
   *
   * @param string $path
   * @param string $mode
   * @param int $options
   * @param string &$opened_path
   * @return bool
   */
  public function stream_open($path , $mode , $options , &$opened_path)
  {
    $this->filename = $this->translatePathToFilename($path);
    $this->resource = fopen($this->filename, $mode);
    return false !== $this->resource;
  }

  /**
   * Read from stream
   *
   * @param int $count
   * @return string
   */
  public function stream_read($count)
  {
    return fread($this->resource, $count);
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
    return 0 == fseek($this->resource, $offset, $whence);
  }

  /** 
   * Retrieve information about a file resource
   * 
   * @return array 
   */ 
  public function stream_stat()
  {
    return fstat($this->resource);
  }

  /** 
   * Retrieve the current position of a stream
   * 
   * @return int 
   */ 
  public function stream_tell()
  {
    return ftell($this->resource);
  }

  /**
   * Retrieve information about a file
   *
   * @param string $path
   * @param int $flags
   * @return array
   */
  public function url_stat($path , $flags)
  {
    $this->filename = $this->translatePathToFilename($path);
    return stat($this->filename);
  }
}
