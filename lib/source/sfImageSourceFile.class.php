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
 * Maps sfImageSource:// URLs to an image file
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceFile implements sfImageSourceInterface
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
  private $resource;

  /**
   * mock image absolute path
   *
   * @var string
   */
  private $filename;

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
   * Retrieve information about a file resource
   * 
   * @return array 
   */ 
  public function stream_stat()
  {
    return fstat($this->resource);
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

  /**
   * Translates the given stream URL to the abolute path of the source image
   *
   * @param  string $path The given stream URL
   * @return string
   */
  private function translatePathToFilename($path)
  {
    if(!is_null($this->filename))
    {
      return $this->filename;
    }

    $url  = parse_url($path);
    $pos  = strrpos($url['path'], '/');
    $path = substr($url['path'], 0, $pos);
    $file = substr($url['path'], $pos+1);
    $files = sfFinder::type('file')->name($file.'*')->in(sfConfig::get('sf_upload_dir').$path);

    if(!count($files))
    {
      throw new sfError404Exception('Could not find image "'.$url['host'].'"');
    }

    $this->filename = $files[0];

    return $this->filename;
  }

  /**
   * Returns an sfImageSource:// URL pointing to a file on the local filesystem
   *
   * @param  array  $parameters Current request parameters (expected: filepath)
   * @return string sfImageSource:// URI
   * @throws InvalidArgumentException
   */
  public static function buildURIfromParameters(array $parameters)
  {
    // all params must be given
    if (!array_key_exists('filepath', $parameters))
    {
      throw new InvalidArgumentException('The sf_image for image_source "Doctrine" route has some missing mandatory parameters (filepath).');
    }

    return sprintf('sfImageSource://file/%s', $parameters['filepath']);
  }
}
