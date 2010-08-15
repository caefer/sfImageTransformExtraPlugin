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
 * @version    SVN: $Id: sfImageSourceDoctrine.class.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Maps sfImageSource:// URLs to files indicated by Doctrine Models
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage source
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceDoctrine extends sfImageSourceLocalAbstract implements sfImageSourceInterface
{
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
  protected function translatePathToFilename($path)
  {
    if(!is_null($this->filename))
    {
      return $this->filename;
    }

    $url = parse_url($path);
    try
    {
      $table = Doctrine::getTable($url['host']);
    }
    catch(Doctrine_Exception $e)
    {
      throw new sfError404Exception('Could not find Doctrine table "'.$url['host'].'"');
    }
    
    if(!($obj = $table->find($url['fragment'])))
    {
      throw new sfError404Exception('Could not find "'.$url['host'].'" #'.$url['fragment'].'!');
    }
    $attribute = ltrim($url['path'], '/');
    $filepath = sfConfig::get('sf_upload_dir').'/'.strtolower(get_class($obj)).'/'.$obj->$attribute;
    return $filepath;
  }
}
