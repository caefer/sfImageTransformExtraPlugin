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
 * @version    SVN: $Id: sfImageTransformManager.class.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Static class holding thumbnail generating and removing functionality.
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage transforms
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageTransformManager
{
  /**
   * @var array $options Holder for the options as configured in your thumbnailing.yml
   */
  private $options = array();

  /**
   *
   */
  public function __construct($formats = array())
  {
    $this->options['formats'] = $formats;

    if (empty($this->options['formats']))
    {
      throw new sfImageTransformExtraPluginConfigurationException('Please configure "thumbnailing_formats" in your thumbnailing.yml!');
    }
  }

  /**
   * Generates a thumbnail.
   *
   * The generation is actually done by the sfImageTransformPlugin. This method only collects the
   * options configured in the thumbnailing.yml and uses them to call sfImageTransformPlugins transformations.
   * Additionally the generated thumbnail can be cached.
   *
   * @param  string  $uri     Image source URI (sfImageSource://...)
   * @param  array   $options Thumbnail parameters taken from the thumbnail URL referencing a format and id
   * @return sfImage
   */
  public function generate($uri, $format)
  {
    if (!array_key_exists($format, $this->options['formats']))
    {
      throw new sfImageTransformExtraPluginConfigurationException('Unknown format "'.$format.'" in your thumbnailing.yml!');
    }

    $sourceImage = new sfImage($uri);
    $settings    = $this->options['formats'][$format];

    if(array_key_exists('mime_type', $settings))
    {
      $sourceImage->setMIMEType($settings['mime_type']);
    }

    if(is_array($settings['transformations']))
    {
      foreach($settings['transformations'] as $transformation)
      {
        $this->transform($sourceImage, $transformation);
      }
    }

    $sourceImage->setQuality($settings['quality']);

    return $sourceImage;
  }

  /**
   * Executes a transformation on the source image
   *
   * @param  sfImage $sourceImage    The image to transform
   * @param  array   $transformation The transformation settings
   * @return void
   */
  private function transform(sfImage $sourceImage, $transformation)
  {
    $parameters = $this->prepareParameters($sourceImage, $transformation['transformation'], $transformation['param']);

    call_user_func_array(array($sourceImage, $transformation['transformation']), $parameters);
  }

  /**
   * Extends current transformation parameters by autoboxing to objects
   *
   * This is needed for transformations that need certain objects in their parameters,
   * that need to be instantiated first which can not be done in a yaml file.
   *
   * If any parameter is prefixed with "className|" then the method autoboxClassName()
   * would be called passing the remaining parameter returning an instance of className.
   *
   * @param  sfImage $sourceImage The image to transform
   * @param  string  $method      The current transformation method
   * @param  array   $parameters  The current transformations parameters as set in the yaml
   * @return array   Extended parameters
   */
  private function prepareParameters(sfImage $sourceImage, $method, $parameters)
  {
    foreach($parameters as $key => $parameter)
    {
      if(2 == count($parts = explode('|', $parameter, 2)))
      {
        $methodName = "autobox".ucfirst($parts[0]);
        $parameter  = $parts[1];

        if(!method_exists($this, $methodName))
        {
          throw new InvalidArgumentException('Don\'t know how to autobox to "'.$parts[0].'"!');
        }

        $parameters[$key] = $this->$methodName($parameter);
      }
    }

    return $parameters;
  }

  /**
   * Autoboxes a filepath to an image into an instance of sfImage if the file exists
   *
   * @param string $parameter A filepath as specified in the thumbnailing.yml
   * @return sfImage
   */
  private function autoboxSfImage($parameter)
  {
    $pathinfo = pathinfo($parameter);
    if(in_array($pathinfo['extension'], array('jpg', 'jpeg', 'gif', 'png')))
    {
      $filepath = $pathinfo['dirname'];
      $filename = $pathinfo['basename'];

      $pluginDirs = ProjectConfiguration::getActive()->getAllPluginPaths();
      $pluginDir = $pluginDirs['sfImageTransformExtraPlugin'];

      $resourcePaths = array_merge(
        sfConfig::get('app_sfImageTransformExtraPlugin_additional_resource_paths', array()),
        array(
          sfConfig::get('sf_data_dir') . '/resources/',
          $pluginDir . '/data/example-resources/'
        )
      );

      array_walk($resourcePaths, array($this, 'extendResourcePaths'), $filepath);

      $files = sfFinder::type('file')
        ->name($filename)
        ->maxdepth(1)
        ->in($resourcePaths);

      if(0 == count($files))
      {
        throw new InvalidArgumentException('Could not find resource "'.$parameter.'"!');
      }

      $parameter = new sfImage($files[0]);
    }

    return $parameter;
  }

  /**
   * Extending known resource paths (from app.yml) with current filepath
   * Used as callback for array_walk in sfImageTransformManager::autoboxSfImage()
   * @see array_merge()
   *
   * @param  string &$path         One of the resource path as configured in app.yml
   * @param  string $index         Current key/index
   * @param  string $pathExtension The current filepath to look up
   * @return string
   */
  public function extendResourcePaths(&$path, $index, $pathExtension)
  {
    $path = $path.'/'.$pathExtension;
  }
}
