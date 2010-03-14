<?php
/**
 * This file is part of the sfImageTransformExtraPlugin package.
 * (c) 2010 Christian Schaefer <caefer@ical.ly>>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    sfImageTransformExtraPluginFunctionalTests
 * @subpackage action
 * @author     Christian Schaefer <caefer@ical.ly>
 * @version    SVN: $Id: sfRawFileCache.class.php 63 2010-03-09 04:34:28Z caefer $
 */

/** include functional test bootstrap */
include dirname(__FILE__).'/../bootstrap/functional.php';

$browser = new sfTestFunctional(new sfBrowser());

$browser->
  info('1 - Request a 1x1 blank PNG in default format')->
  get('/thumbnails/News/default/01/00/00/test-1.png')->

  with('request')->begin()->
    info('1.1 - Checking for correct module/action')->
    isParameter('module', 'sfImageTransformator')->
    isParameter('action', 'index')->
  end()->
               
  with('response')->begin()->
    info('1.2 - Checking for valid response')->
    isStatusCode(200)->
    isHeader('Content-Type', 'image/gif')->
  end()->

  info('2 - Request a 1x1 blank PNG in original format')->
  get('/thumbnails/News/original/01/00/00/test-1.png')->

  with('request')->begin()->
    info('2.1 - Checking for correct module/action')->
    isParameter('module', 'sfImageTransformator')->
    isParameter('action', 'index')->
  end()->
               
  with('response')->begin()->
    info('2.2 - Checking for valid response')->
    isStatusCode(200)->
    isHeader('Content-Type', 'image/jpg')->
  end()
;
