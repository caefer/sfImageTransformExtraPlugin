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
class sfImageRoundedCornersGD extends sfImageAlphaMaskGD
{
  public function __construct($radius, $color = false) 
  {
    $this->radius = $radius;
    $this->color  = $color;
  }
  
  protected function transform(sfImage $image) 
  {
    $resource       = $image->getAdapter()->getHolder();

    $w = imagesx($resource);
    $h = imagesy($resource);
    $this->mask   = $this->getMask($w, $h);
    
    return parent::transform($image);
  }
  
  private function getMask($w, $h) 
  {
    // Create a mask png image of the area you want in the circle/ellipse (a 'magicpink' image with a black shape on it, with black set to the colour of alpha transparency) - $mask
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
}
