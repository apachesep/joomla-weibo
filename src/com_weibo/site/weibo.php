<?php
/**
 *  $Id$
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
if(version_compare(JVERSION,'1.6.0','ge')) {
   // Joomla 1.7 
jimport('joomla.application.component.controller');
$controller = JController::getInstance('Weibo');
} else {
   // Joomla 1.5
require_once (JPATH_COMPONENT . DS . 'controller.php');
$controller = new WeiboController();
}

$task = JRequest::getCmd('task');
$return = JRequest::getCmd('rid');

$controller->execute(JRequest::getCmd('task'));
 
$controller->redirect();
?>
