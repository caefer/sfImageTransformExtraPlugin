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
 * Maps sfImageSource:// URLs to a file available via HTTP
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceHTTP implements sfImageSourceInterface
{
  /**
   * resource context
   *
   * @var resource
   */
  public $context = null;

  /**
   * resource handle
   *
   * @var resource
   */
  private $resource;

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
    $this->resource = fopen(self::getURL($path), $mode);
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

  /**
   * Translates the current sfImage:// URI to a http:// location
   *
   * @static
   * @param  string $path
   * @return string
   */
  public static function getURL($path)
  {
    $options = sfConfig::get('thumbnailing_source_image_stream_param');
    $path = parse_url($path);
    return sprintf('%s:/%s/%s', $path['host'], $path['path'], $path['fragment']);
  }

  /**
   * Returns an sfImageSource:// URL pointing to a file read over HTTP
   *
   * @param  array  $parameters Current request parameters (expected: protocol, domain, filepath)
   * @return string sfImageSource:// URI
   * @throws InvalidArgumentException
   */
  public static function buildURIfromParameters(array $parameters)
  {
    // all params must be given
    if ($diff = array_diff(array('protocol', 'domain', 'filepath'), array_keys($parameters)))
    {
      throw new InvalidArgumentException('The sf_image for image_source "HTTP" route has some missing mandatory parameters (url).');
    }

    return sprintf('sfImageSource://%s/%s#%s', $parameters['protocol'], $parameters['domain'], $parameters['filepath']);
  }
}
