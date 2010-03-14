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
 * Base actions for the sfImageTransformExtraPlugin sfImageTransformator module.
 *
 * @package     sfImageTransformExtraPlugin
 * @subpackage  action
 * @author      Christian Schaefer <caefer@ical.ly>
 */
abstract class BasesfImageTransformatorActions extends sfActions
{
  private $options = array();

  /**
   * Generates a thumbnail image
   *
   * @param  sfWebRequest $request The symfony request object
   * @return string
   */
  public function executeIndex(sfWebRequest $request) 
  {
    $options = $this->prepareOptions($request);

    $response = $this->getResponse();
    $formats = sfConfig::get('thumbnailing_formats', array());
    $thumbnailer = new sfImageTransformManager($formats);
    $thumbnail = $thumbnailer->generate($options);
    $response->setContentType($thumbnail->getMIMEType());
    $response->setContent($thumbnail->toString());

    return sfView::NONE;
  }

  private function prepareOptions(sfWebRequest $request) 
  {
    $options = array();
    $formats = explode(',', $request->getParameter('format', false));
    $options['format'] = array_pop($formats);
    $options['type'] = $request->getParameter('type', false);
    $options['path'] = $request->getParameter('path', false);
    $options['slug'] = $request->getParameter('slug', false);
    $options['id'] = $request->getParameter('id', false);
    $options['attribute'] = $request->getParameter('attribute', '0');
    $options['sf_format'] = $request->getParameter('sf_format', false);

    foreach ($options as $key => $value) 
    {
      if (false === $value) 
      {
        throw new sfError404Exception('[sfImageTransformExtraPlugin] URL parameter "' . $key . '" not set!');
      }
    }

    return $options;
  }
}
