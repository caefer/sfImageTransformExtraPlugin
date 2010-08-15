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
 * @version    SVN: $Id: sfImageSourcePropel.class.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Maps sfImageSource:// URLs to files indicated by Propel Models
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourcePropel extends sfImageSourceLocalAbstract implements sfImageSourceInterface
{
  /**
   * Returns an sfImageSource:// URL pointing to a file which path is stored on a Propel object
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
      throw new InvalidArgumentException(sprintf('The sf_image for image_source "Propel" route has some missing mandatory parameters (%s).', implode(', ', $diff)));
    }

    return sprintf('sfImageSource://%s/%s#%s', $parameters['type'], $parameters['attribute'], $parameters['id']);
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

    $url = parse_url($path);
    if(!class_exists($url['host'].'Peer'))
    {
      throw new sfError404Exception('Could not find Propel Peer class for "'.$url['host'].'"');
    }
    
    if(!($obj = call_user_func(array($url['host'].'Peer', 'retrieveByPK'), $url['fragment'])))
    {
      throw new sfError404Exception('Could not find "'.$url['host'].'" #'.$url['fragment'].'!');
    }
    $attributeAccessor = 'get'.ucfirst(ltrim($url['path'], '/'));
    $files = sfFinder::type('file')->name($obj->$attributeAccessor().'*')->in(sfConfig::get('sf_upload_dir'));
    return $files[0];
  }
}
