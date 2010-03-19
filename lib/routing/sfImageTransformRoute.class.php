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
 * sfImageTransformRoutee represents a route that is bound to a generated (transformed) image resource.
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage routing
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageTransformRoute extends sfRequestRoute
{
  protected function mergeArrays($arr1, $arr2)
  {
    if(array_key_exists('sf_subject', $arr2) && is_object($arr2['sf_subject']))
    {
      $obj = $arr2['sf_subject'];
      $arr1['path'] = $obj->getPath();
      $arr1['type'] = $obj->getType();
      foreach ($this->variables as $key => $value)
      {
        if(isset($obj[$key]))
        {
          $arr1[$key] = $obj->get($key);
        }
      }

      unset($arr2['sf_subject']);
    }

    if(array_key_exists('format', $arr2))
    {
      $formats = sfConfig::get('thumbnailing_formats', array());
      $arr1['sf_format'] = $this->get_suffix_for_mime_type($formats[$arr2['format']]['mime_type']);
    }

    return parent::mergeArrays($arr1, $arr2);
  }

  /**
   * Generates a URL from the given parameters.
   *
   * @param  mixed   $params    The parameter values
   * @param  array   $context   The context
   * @param  Boolean $absolute  Whether to generate an absolute URL
   *
   * @return string The generated URL
   */
  public function generate($params, $context = array(), $absolute = false)
  {
    $url = parent:: generate($params, $context = array(), $absolute = false);
    return urldecode($url);
  }

  /**
   * Returns the associated file extension for a given mime type
   *
   * @param  string $mime_type Image mime type
   * @return string
   */
  private function get_suffix_for_mime_type($mime_type) 
  {
    switch ($mime_type) 
    {
      case 'image/gif':
        return 'gif';
      case 'image/png':
        return 'png';
      case 'image/jpg':
      case 'image/jpeg':
      default:
        return 'jpg';
    }
  }

  public function getImageSourceStreamWrapper()
  {
    return 'sfImageSource'.$this->options['image_source'];
  }

  public function getImageSourceURI()
  {
    return call_user_func(array($this->getImageSourceStreamWrapper(), 'buildURIfromParameters'), $this->parameters);
  }
}
