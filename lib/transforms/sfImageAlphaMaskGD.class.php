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
 * Image transformation to apply a second image as an alpha mask to the first image
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage transforms
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageAlphaMaskGD extends sfImageTransformAbstract
{
  public function __construct($mask, $color = false) 
  {
    $this->mask  = $mask;
    $this->color = $color;
  }
  
  protected function transform(sfImage $image) 
  {
    $this->image    = $image;
    $this->mimeType = $image->getMIMEType();
    $resource       = $image->getAdapter()->getHolder();
    
    switch ($this->mimeType) 
    {
      case 'image/png':
        $this->transformAlpha($resource);
        break;
      case 'image/gif':
      case 'image/jpg':
      default:
        $this->transformDefault($resource);
    }
    
    return $image;
  }
  
  private function transformAlpha($resource) 
  {
    $w = imagesx($resource);
    $h = imagesy($resource);
    
    $mask   = $this->mask->getAdapter()->getHolder();
    $canvas = imagecreatetruecolor($w, $h);
    
    $color_background = imagecolorallocate($canvas, 0, 0, 0);
    imagefilledrectangle($canvas, 0, 0, $w, $h, $color_background);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    
    for ($x = 0;$x < $w;$x++) 
    {
      for ($y = 0;$y < $h;$y++) 
      {
        $RealPixel = @imagecolorsforindex($resource, @imagecolorat($resource, $x, $y));
        $MaskPixel = @imagecolorsforindex($mask, @imagecolorat($mask, $x, $y));
        $MaskAlpha = 127 - (floor($MaskPixel['red'] / 2) * (1 - ($RealPixel['alpha'] / 127)));
        
        if (false === $this->color) 
        {
          $newcolor = imagecolorallocatealpha($canvas, $RealPixel['red'], $RealPixel['green'], $RealPixel['blue'], intval($MaskAlpha));
        }
        else
        {
          $newcolorPixel    = sscanf($this->color, '#%2x%2x%2x');
          $newcolorPixel[0] = ($newcolorPixel[0] * $MaskAlpha + $RealPixel['red'] * (127 - $MaskAlpha)) / 127;
          $newcolorPixel[1] = ($newcolorPixel[1] * $MaskAlpha + $RealPixel['green'] * (127 - $MaskAlpha)) / 127;
          $newcolorPixel[2] = ($newcolorPixel[2] * $MaskAlpha + $RealPixel['blue'] * (127 - $MaskAlpha)) / 127;
          $newcolor         = imagecolorallocate($canvas, $newcolorPixel[0], $newcolorPixel[1], $newcolorPixel[2]);
        }
        imagesetpixel($canvas, $x, $y, $newcolor);
      }
    }
    imagealphablending($resource, false);
    imagesavealpha($resource, true);
    imagecopy($resource, $canvas, 0, 0, 0, 0, $w, $h);
    
    imagedestroy($mask);
    imagedestroy($canvas);
  }

  /**
   * Callback function to extend/alter parameters as given in your thumbnailing.yml.
   *
   * This callback adds the resources path to a mask image
   *
   * @param  sfImage $sourceImage The original image
   * @param  array   $parameters  Configured parameters for this transformation
   * @return array   $parameters  Extended/altered parameters
   */
  public static function prepareParameters($sourceImage, $parameters)
  {
    if (!array_key_exists('mask', $parameters))
    {
      return $parameters;
    }

    $user_resources_dir   = sfConfig::get('sf_data_dir') . '/resources';
    $plugin_paths = ProjectConfiguration::getActive()->getAllPluginPaths();
    $plugin_resources_dir = $plugin_paths['sfImageTransformExtraPlugin'].'/data/example-resources';
    if (file_exists($user_resources_dir . '/' . $parameters['mask']))
    {
      $parameters['mask'] = new sfImage($user_resources_dir . '/' . $parameters['mask']);
    }
    else if (file_exists($plugin_resources_dir . '/' . $parameters['mask']))
    {
      $parameters['mask'] = new sfImage($plugin_resources_dir . '/' . $parameters['mask']);
    }
    return $parameters;
  }
}
