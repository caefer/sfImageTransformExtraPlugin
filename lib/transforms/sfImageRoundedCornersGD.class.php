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
 * Image transformation to apply rounded corners to the image
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage transforms
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageRoundedCornersGD extends sfImageTransformAbstract
{
  public function __construct($radius, $color = false) 
  {
    $this->radius = $radius;
    $this->color  = $color;
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
    
    $mask   = $this->getMask($w, $h);
    $canvas = imagecreatetruecolor($w, $h);
    
    $color_background = imagecolorallocate($canvas, 0, 0, 0);
    imagefilledrectangle($canvas, 0, 0, $w, $h, $color_background);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    
    for ($x = 0;$x < $w;$x++) 
    {
      for ($y     = 0;$y < $h;$y++) 
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
  
  private function transformDefault($resource) 
  {
    $w = imagesx($resource);
    $h = imagesy($resource);
    
    imagealphablending($resource, true);
    $resource_transparent = imagecolorallocate($resource, 0, 0, 0);
    imagecolortransparent($resource, $resource_transparent);
    //$resource_transparent = $this->getTransparentColor($resource);
    $mask = $this->getMask($w, $h);
    // Copy $mask over the top of $resource maintaining the Alpha transparency
    imagecopymerge($resource, $mask, 0, 0, 0, 0, $w, $h, 100);
  }
  
  private function getMask($w, $h) 
  {
    // Create a mask png image of the area you want in the circle/ellipse (a ‘magicpink’ image with a black shape on it, with black set to the colour of alpha transparency) - $mask
    $mask = imagecreatetruecolor($w, $h);
    imagealphablending($mask, true);
    // Set the masking colours
    if (false === $this->color||'image/png' == $this->mimeType) 
    {
      $mask_black = imagecolorallocate($mask, 0, 0, 0);
    }
    else
    {
      $colorPixel = sscanf($this->color, '#%2x%2x%2x');
      $mask_black = imagecolorallocate($mask, $colorPixel[0], $colorPixel[1], $colorPixel[2]);
    }
    $mask_transparent = imagecolorallocate($mask, 255, 255, 255);
    imagecolortransparent($mask, $mask_transparent);
    imagefill($mask, 0, 0, $mask_black);
    // Draw the rounded rectangle for the mask
    $this->imagefillroundedrect($mask, 0, 0, $w, $h, $this->radius, $mask_transparent);
    return $mask;
  }
  
  private function imagefillroundedrect($im, $x, $y, $cx, $cy, $rad, $col) 
  {
    // Draw the middle cross shape of the rectangle
    imagefilledrectangle($im, $x, $y + $rad, $cx, $cy - $rad, $col);
    imagefilledrectangle($im, $x + $rad, $y, $cx - $rad, $cy, $col);
    
    $dia = $rad * 2;
    // Now fill in the rounded corners
    imagefilledellipse($im, $x + $rad, $y + $rad, $rad * 2, $dia, $col);
    imagefilledellipse($im, $x + $rad, $cy - $rad, $rad * 2, $dia, $col);
    imagefilledellipse($im, $cx - $rad, $cy - $rad, $rad * 2, $dia, $col);
    imagefilledellipse($im, $cx - $rad, $y + $rad, $rad * 2, $dia, $col);
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
  public static function prepareParameters($sourceImage, $parameters)
  {
    if (!array_key_exists('overlay', $parameters))
    {
      return $parameters;
    }

    $user_resources_dir   = sfConfig::get('sf_data_dir') . '/resources';
    $plugin_resources_dir = sfConfig::get('sf_plugins_dir') . '/sfImageTransformExtraPlugin/data/example-resources';
    if (file_exists($user_resources_dir . '/' . $parameters['overlay']))
    {
      $parameters['overlay'] = new sfImage($user_resources_dir . '/' . $parameters['overlay']);
    }
    else if (file_exists($plugin_resources_dir . '/' . $parameters['overlay']))
    {
      $parameters['overlay'] = new sfImage($plugin_resources_dir . '/' . $parameters['overlay']);
    }
    return $parameters;
  }
}
