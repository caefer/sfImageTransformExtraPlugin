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
 * @version    SVN: $Id: sfImageSourceFile.class.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Maps sfImageSource:// URLs to an image file
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceFile extends sfImageSourceLocalAbstract implements sfImageSourceInterface
{
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
      throw new InvalidArgumentException('The sf_image for image_source "File" route has some missing mandatory parameters (filepath).');
    }

    return sprintf('sfImageSource://file/%s', $parameters['filepath']);
  }

  /**
   * Translates the given stream URL to the abolute path of the source image
   *
   * @param  string $path The given stream URL
   * @return string
   */
  protected function translatePathToFilename($path)
  {
    if(!is_null($this->filename))
    {
      return $this->filename;
    }

    $url  = parse_url($path);
    $path = dirname($url['path']);
    $file = basename($url['path']);
    $files = sfFinder::type('file')
      ->name(array($file.'{.jpg,.png,.gif,.jpeg,}'))
      ->maxdepth(0)
      ->in(sfConfig::get('sf_upload_dir').$path);

    if(!count($files))
    {
      throw new sfError404Exception('Could not find image "'.$url['host'].'"');
    }

    $this->filename = $files[0];

    return $this->filename;
  }
}
