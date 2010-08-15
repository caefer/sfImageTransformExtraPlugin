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
 * @version    SVN: $Id: sfImageSourceInterface.interface.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Interface for Image Sources
 *
 * Implement this to map sfImageSource:// URLs to real content
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
interface sfImageSourceInterface
{
  /**
   * resource context
   *
   * @var resource
   */
  //public $context;

  /**
   * Close an resource
   *
   * @return void
   */
  public function stream_close();

  /**
   * Tests for end-of-file on a file pointer
   *
   * @return bool
   */
  public function stream_eof();

  /**
   * Flushes the output
   *
   * @return bool
   */
  public function stream_flush();

  /**
   * Opens file or URL
   *
   * @param string $path
   * @param string $mode
   * @param int $options
   * @param string &$opened_path
   * @return bool
   */
  public function stream_open($path , $mode , $options , &$opened_path);

  /**
   * Read from stream
   *
   * @param int $count
   * @return string
   */
  public function stream_read($count);

  /** 
   * Retrieve information about a file resource
   * 
   * @return array 
   */ 
  public function stream_stat();

  /**
   * Retrieve information about a file
   *
   * @param string $path
   * @param int $flags
   * @return array
   */
  public function url_stat($path , $flags);

  /**
   * Returns an sfImageSource:// URL specific to the implementing stream wrapper
   *
   * @param  array  $parameters Current request parameters
   * @return string sfImageSource:// URI
   * @throws InvalidArgumentException
   */
  public static function buildURIfromParameters(array $parameters);

  /**
   * Translates the given stream URL to the abolute path of the source image
   *
   * @param  string $path The given stream URL
   * @return string
   */
  //private function translatePathToFilename($path);
}
