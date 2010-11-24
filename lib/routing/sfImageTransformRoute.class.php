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
 * @version    SVN: $Id: sfImageTransformRoute.class.php 29957 2010-06-24 08:24:23Z caefer $
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
   * Constructor.
   *
   * Applies a default sf_method requirements of GET or HEAD.
   *
   * @see sfRoute
   */
  public function __construct($pattern, $defaults = array(), $requirements = array(), $options = array())
  {
    if (!isset($options['max_folder_depth']))
    {
      $options['max_folder_depth'] = 10;
    }

    parent::__construct($pattern, $defaults, $requirements, $options);
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
    return urldecode(parent::generate($this->convertObjectToArray($params), $context, $absolute));
  }

  /**
   * Reads parameters from a passed object and assignes values to type, path and sf_format parameters if necessary
   *
   * @param  array Parameters as passed to the current route
   * @return array
   */
  protected function convertObjectToArray($parameters)
  {
    if (!$this->compiled)
    {
      $this->compile();
    }

    $parameters = is_array($parameters) ? $parameters : array();

    if (isset($parameters['sf_subject']))
    {
      $parameters = array_merge($parameters, $this->doConvertObjectToArray($parameters['sf_subject']));
      if(array_key_exists('type', $this->variables))
      {
        $parameters['type'] = get_class($parameters['sf_subject'] instanceof sfOutputEscaper ? $parameters['sf_subject']->getRawValue() : $parameters['sf_subject']);
      }
      unset($parameters['sf_subject']);
    }


    if(!array_key_exists('sf_format', $parameters) && array_key_exists('format', $parameters))
    {
      $formats = sfConfig::get('thumbnailing_formats');
      $parameters['sf_format'] = $this->get_suffix_for_mime_type($formats[$parameters['format']]['mime_type']);
    }

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
    $params = $this->convertObjectToArray($params);

    if(!$params['sf_format'])
    {
      $params['sf_format'] = '{jpg,gif,png}';
    }

    if(!$params['format'])
    {
      $params['format']    = '{'.implode(',', array_keys(sfConfig::get('thumbnailing_formats'))).'}';
    }

    foreach($params as $key => $value)
    {
      $this->pattern = str_replace(':'.$key, $value, $this->pattern);
    }
    $this->pattern = str_replace('//', '/', $this->pattern);

    $this->compiled = false;
  }
}
