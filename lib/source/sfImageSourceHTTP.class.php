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
 * @version    SVN: $Id: sfImageSourceHTTP.class.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Maps sfImageSource:// URLs to a file available via HTTP
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceHTTP extends sfImageSourceRemoteAbstract implements sfImageSourceInterface
{
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
      throw new InvalidArgumentException(sprintf('The sf_image for image_source "HTTP" route has some missing mandatory parameters (%s).', implode(', ', $diff)));
    }

    return sprintf('sfImageSource://%s/%s#%s', $parameters['protocol'], $parameters['domain'], $parameters['filepath']);
  }

  /**
   * Translates the given stream URL to the abolute path of the source image
   *
   * @param  string $path The given stream URL
   * @return string
   */
  protected function translatePathToFilename($path)
  {
    $options = sfConfig::get('thumbnailing_source_image_stream_param');
    $path = parse_url($path);
    return sprintf('%s:/%s/%s', $path['host'], $path['path'], $path['fragment']);
  }
}
