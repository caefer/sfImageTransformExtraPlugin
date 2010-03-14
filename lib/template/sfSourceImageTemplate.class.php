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
 * Template for models that want to be transformed
 * 
 * @package     sfImageTransformExtraPlugin
 * @subpackage  template
 * @author      Christian Schaefer <caefer@ical.ly>
 */
class sfImageSourceTemplate extends Doctrine_Template
{
  /**
   * Set table definition for Timestampable behavior
   *
   * @return void
   */
  public function retrieveFilenameForAttribute($attribute = '0')
  {
    $fields = $this->_options['fields'];
    if($attribute >= count($fields))
    {
      $attribute = '0';
    }
    $attribute = $fields[$attribute];

    return $this->getInvoker()->$attribute;
  }

  /**
   * Returns the path part for thumbnail urls
   *
   * @return string
   */
  public function getPath()
  {
    $id = $this->getInvoker()->get('id');
    return implode('/', array_reverse(str_split(str_pad($id, 6, '0', STR_PAD_LEFT) , 2)));
  }

  /**
   * Returns this models class name
   *
   * @return string
   */
  public function getType()
  {
    return get_class($this->getInvoker());
  }
}

