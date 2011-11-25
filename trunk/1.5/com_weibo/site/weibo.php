<?php

/**
 *  $Id$
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_COMPONENT . DS . 'controller.php');

// Get an instance of the controller prefixed by HelloWorld
$controller = new WeiboController();

$task = JRequest::getCmd('task');
$return = JRequest::getCmd('rid');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
?>