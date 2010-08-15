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
 * @version    SVN: $Id: BasesfImageTransformatorActions.class.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Base actions for the sfImageTransformExtraPlugin sfImageTransformator module.
 *
 * @package     sfImageTransformExtraPlugin
 * @subpackage  action
 * @author      Christian Schaefer <caefer@ical.ly>
 */
abstract class BasesfImageTransformatorActions extends sfActions
{
  /**
   * Generates a thumbnail image
   *
   * @param  sfWebRequest $request The symfony request object
   * @return string
   */
  public function executeIndex(sfWebRequest $request) 
  {
    if(in_array('sfImageSource', stream_get_wrappers()))
    {
      stream_wrapper_unregister('sfImageSource');
    }
    $streamwrapper = $this->getRoute()->getImageSourceStreamWrapper();
    stream_wrapper_register('sfImageSource', $streamwrapper) or die('Failed to register protocol..');

    $formats = sfConfig::get('thumbnailing_formats', array());
    $thumbnailer = new sfImageTransformManager($formats);
    $uri = $this->getRoute()->getImageSourceURI();
    $thumbnail = $thumbnailer->generate($uri, $request->getParameter('format', 'default'));

    $response = $this->getResponse();
    $response->setContentType($thumbnail->getMIMEType());
    $response->setContent($thumbnail->toString());

    return sfView::NONE;
  }
}
