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
 * Maps sfImageSource:// URLs to files indicated by Doctrine Models
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceDoctrine implements sfImageSourceInterface
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
   * Returns an sfImageSource:// URL pointing to a file which path is stored on a Doctrine object
   *
   * @param  array  $parameters Current request parameters (expected: type, attribute, id)
   * @return string sfImageSource:// URI
   * @throws InvalidArgumentException
   */
  public static function buildURIfromParameters(array $parameters)
  {
    // all params must be given
    if ($diff = array_diff(array('type', 'attribute', 'id'), array_keys($parameters)))
    {
      throw new InvalidArgumentException(sprintf('The sf_image for image_source "Doctrine" route has some missing mandatory parameters (%s).', implode(', ', $diff)));
    }

    return sprintf('sfImageSource://%s/%s#%s', $parameters['type'], $parameters['attribute'], $parameters['id']);
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

    $url = parse_url($path);
    if(!($table = Doctrine::getTable($url['host'])))
    {
      throw new sfError404Exception('Could not find Doctrine table "'.$url['host'].'"');
    }
    
    if(!($obj = $table->find($url['fragment'])))
    {
      throw new sfError404Exception('Could not find "'.$url['host'].'" #'.$url['fragment'].'!');
    }
    $attribute = ltrim($url['path'], '/');
    $attribute = $obj->$attribute;
    $pos  = strrpos($attribute, '/');
    $path = substr($attribute, 0, $pos);
    $file = substr($attribute, $pos+1);
    $files = sfFinder::type('file')->name($file.'*')->in(sfConfig::get('sf_upload_dir').$path);
    return $files[0];
  }
}
