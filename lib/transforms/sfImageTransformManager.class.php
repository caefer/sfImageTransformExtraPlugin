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
   * @var string $adapter Holds the adapter for the current transformation
   */
  private $adapter = 'GD';

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
   * @param  array   $options Thumbnail parameters taken from the thumbnail URL referencing a format and id
   * @return sfImage
   */
  public function generate($options = array())
  {
    if (!array_key_exists($options['format'], $this->options['formats']))
    {
      sfContext::getInstance()->getLogger()->warning('{' . __CLASS__ . '} [' . __FUNCTION__ . '] Format "' . $options['format'] . '" unknown. Using "default" instead.');
      $options['format'] = 'default';
    }

    $sourceImage    = $this->getSourceImage($options);

    $settings       = $this->options['formats'][$options['format']];

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
    $this->setAdapter($sourceImage, $transformation['adapter']);

    $parameters = $this->prepareParameters($sourceImage, $transformation['transformation'], $transformation['param']);

    call_user_func_array(array($sourceImage, $transformation['transformation']), $parameters);
  }

  /**
   * Sets new adapter on the image if necessary
   *
   * @param  sfImage $sourceImage The image to transform
   * @param  array   $adapter     The adapter of the current transformation
   * @return void
   */
  private function setAdapter(sfImage $sourceImage, $adapter)
  {
    if($adapter != $this->adapter)
    {
      $newAdapter = $this->createAdapter($adapter);
      $this->adapter = $adapter;
      $sourceImage->setAdapter($newAdapter);
    }
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
    $class_adapter = 'sfImage'.ucfirst($method) . $this->adapter;

    if(method_exists($class_adapter, 'prepareParameters'))
    {
      $parameters = call_user_func(array($class_adapter, 'prepareParameters'), $sourceImage, $parameters);
    }
    else if(method_exists($class_generic, 'prepareParameters'))
    {
      $parameters = call_user_func(array($class_generic, 'prepareParameters'), $sourceImage, $parameters);
    }

    return $parameters;
  }

  /**
   * Returns the image source stream for the given options
   *
   * @param  array   $options Array of options
   * @return sfImage
   */
  private function getSourceImage($options)
  {
    $sourceImageFile = 'sfImageSource://'.$options['type'].'/'.$options['attribute'].'#'.$options['id'];
    return  new sfImage($sourceImageFile);
  }

  /**
   * Returns a adapter class of the specified type
   * @access protected
   * @param  string                          $name Name of the transformation to instantiate
   * @return sfImageTransformAdapterAbstract
   */
  private function createAdapter($name)
  {
    $adapter_class = 'sfImageTransform' . $name . 'Adapter';

    if (class_exists($adapter_class))
    {
      $adapter = new $adapter_class;
    }
    // Cannot find the adapter class so throw an exception
    else
    {
      throw new sfImageTransformException(sprintf('Unsupported adapter: %s', $adapter_class));
    }

    return $adapter;
  }
}
