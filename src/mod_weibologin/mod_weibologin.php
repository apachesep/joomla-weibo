<?php
/**
 * @version		$Id$
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$params->def('greeting', 1);

$type	= modWeiboLoginHelper::getType();
$return	= modWeiboLoginHelper::getReturnURL($params, $type);
$user	= JFactory::getUser();

require JModuleHelper::getLayoutPath('mod_weibologin', $params->get('layout', 'default'));