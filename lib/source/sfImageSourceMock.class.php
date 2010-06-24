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
 * @version    SVN: $Id: sfImageSourceMock.class.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Maps sfImageSource:// URLs to an example image file
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceMock extends sfImageSourceLocalAbstract implements sfImageSourceInterface
{
  /**
   * Returns an sfImageSource:// URL pointing to a single mock file within this plugin
   *
   * @param  array  $parameters Current request parameters (expected: ~)
   * @return string sfImageSource:// URI
   * @throws InvalidArgumentException
   */
  public static function buildURIfromParameters(array $parameters)
  {
    return 'sfImageSource://mock';
  }

  /**
   * Translates the given stream URL to the abolute path of the source image
   *
   * @param  string $path The given stream URL
   * @return string
   */
  protected function translatePathToFilename($path)
  {
    return dirname(__FILE__).'/../../data/caefer.jpg';
  }
}
