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
   * Extends current transformation parameters by a callback
   *
   * This is needed for transformations that need certain objects in their parameters,
   * that need to be instantiated first which can not be done in a yaml file.
   *
   * @param  sfImage $sourceImage The image to transform
   * @param  string  $method      The current transformation method
   * @param  array   $parameters  The current transformations parameters as set in the yaml
   * @return array   Extended parameters
   */
  private function prepareParameters(sfImage $sourceImage, $method, $parameters)
  {
    $class_generic = 'sfImage'.ucfirst($method) . 'Generic';
    $class_adapter = 'sfImage'.ucfirst($method) . 'GD';

    if(method_exists($class_adapter, 'prepareParameters'))
    {
      $parameters = call_user_func(array($class_adapter, 'prepareParameters'), $sourceImage, $parameters);
    }
    else if(method_exists($class_generic, 'prepareParameters'))
    {
      $parameters = call_user_func(array($class_generic, 'prepareParameters'), $sourceImage, $parameters);
    }
    else if(method_exists($this, 'prepareParametersFor'.ucfirst($method)))
    {
      $prepare = 'prepareParametersFor'.ucfirst($method);
      $parameters = $this->$prepare($sourceImage, $parameters);
    }

    return $parameters;
  }

  /**
   * Callback function to extend/alter parameters as given in your thumbnailing.yml.
   *
   * This callback adds the resources path to an overlay image
   *
   * @param  sfImage $sourceImage The original image
   * @param  array   $parameters  Configured parameters for this transformation
   * @return array   $parameters  Extended/altered parameters
   */
  private function prepareParametersForOverlay($sourceImage, $parameters)
  {
    if (!array_key_exists('overlay', $parameters))
    {
      return $parameters;
    }

    $filename = $parameters['overlay'];

    if('/' == $filename[0])
    {
      if(!file_exists($filename))
      {
        throw new InvalidArgumentException('Could not find resource "'.$parameters['overlay'].'"!');
      }
    }
    else
    {
      $filepath = '';
      if(0 <= ($pos = strrpos($filename, '/')))
      {
        $filepath = substr($filename, 0, $pos);
        $filename = substr($filename, $pos+1);
      }

      $pluginDirs = ProjectConfiguration::getActive()->getAllPluginPaths();
      $pluginDir = $pluginDirs['sfImageTransformExtraPlugin'];
      $files = sfFinder::type('file')
        ->name($filename)
        ->maxdepth(1)
        ->in(array(
          sfConfig::get('sf_data_dir') . '/resources/'.$filepath,
          $pluginDir . '/data/example-resources/'.$filepath,
        ));

      if(0 == count($files))
      {
        throw new InvalidArgumentException('Could not find resource "'.$parameters['overlay'].'"!');
      }

      $filename = $files[0];
    }


    $parameters['overlay'] = new sfImage($filename);

    return $parameters;
  }
}
