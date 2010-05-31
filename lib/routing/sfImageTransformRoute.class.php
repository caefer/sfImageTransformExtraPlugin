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
 * sfImageTransformRoute represents a route that is bound to a generated (transformed) image resource.
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage routing
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageTransformRoute extends sfRequestRoute
{
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
    return urldecode(parent::generate($this->convertObjectToArray($params), $context, $absolute));
  }

  /**
   * Reads parameters from a passed object and assignes values to type, path and sf_format parameters if necessary
   *
   * @param  array Parameters as passed to the current route
   * @return array
   */
  protected function convertObjectToArray($object)
  {
    if (!$this->compiled)
    {
      $this->compile();
    }

    if(!array_key_exists('sf_format', $object) && array_key_exists('format', $object))
    {
      $formats = sfConfig::get('thumbnailing_formats');
      $object['sf_format'] = $this->get_suffix_for_mime_type($formats[$object['format']]['mime_type']);
    }

    if (is_array($object))
    {
      if (!isset($object['sf_subject']))
      {
        return $object;
      }

      $parameters = $object;
      $object = $parameters['sf_subject'];
      unset($parameters['sf_subject']);
    }
    else
    {
      $parameters = array();
    }

    if(array_key_exists('type', $this->variables))
    {
      $parameters['type'] = get_class($object instanceof sfOutputEscaper ? $object->getRawValue() : $object);
    }

    $parameters = array_merge($parameters, $this->doConvertObjectToArray($object));

    if(array_key_exists('path', $this->variables) && !array_key_exists('path', $parameters) && array_key_exists('id', $parameters))
    {
      $parameters['path'] = implode('/', array_reverse(str_split(str_pad($parameters['id'], 6, '0', STR_PAD_LEFT) , 2)));
    }

    return $parameters;
  }

  /**
   * Attempts to read all variables for the current route from object attributes
   *
   * @param  object $object Object that was passed as sf_subject
   * @return array
   */
  protected function doConvertObjectToArray($object)
  {
    $parameters = array();
    foreach($this->variables as $variable => $token)
    {
      try
      {
        $parameter = $object->get($variable);
        $parameters[$variable] = $parameter;
      }
      catch(Exception $e){/* do nothing */}
    }
    return $parameters;
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

  /**
   * Returns the sfImageSource class name for the currently requested URL
   *
   * @return string
   */
  public function getImageSourceStreamWrapper()
  {
    $className = 'sfImageSource'.$this->options['image_source'];
    if(!class_exists($className))
    {
      throw new sfImageTransformRouteException('Image source class "'.$className.'" does not exist!');
    }
    return $className;
  }

  /**
   * Returns the sfImageSource:// URI for the currently requested URL
   *
   * @return string
   */
  public function getImageSourceURI()
  {
    return call_user_func(array($this->getImageSourceStreamWrapper(), 'buildURIfromParameters'), $this->parameters);
  }

  /**
   * Preassembles pattern with passed parameters
   *
   * This is used to limit matches when removing generated images
   *
   * @param  array $params Parameters to be encoded in the pattern
   * @return void
   */
  public function preassemblePattern($params = array())
  {
    foreach($params as $key => $value)
    {
      $this->pattern = str_replace(':'.$key, $value, $this->pattern);
    }

    $this->compiled = false;
  }
}
