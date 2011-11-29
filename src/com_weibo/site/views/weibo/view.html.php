<?php

/**
 *  $Id$
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * HTML View class for the HelloWorld Component
 */
class WeiboViewWeibo extends JView
{
    
	// Overwriting JView display method
	function display($tpl = null) 
	{
		// Assign data to the view
		$this->msg = 'No Access';
                $this->auth = 'no';
		// Display the view
		parent::display($tpl);
	}
        
        function weibologin($tpl = null) 
	{
		// Assign data to the view
		$this->msg = 'No Access';
                $this->auth = 'weibologin';
		parent::display($tpl);
	}
        
        function weiboprelogin($tpl = null) 
	{
		// Assign data to the view
		$this->msg = 'No Access';
                $this->auth = 'weiboprelogin';
		parent::display($tpl);
	}
}