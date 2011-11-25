<?php

/**
 * @version         $Id$
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * com_weibo 的安装程序
 */
global $mainframe;
define("COM_NAME", 'com_weibo');

jimport('joomla.application.component.model');
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


/**
 *  卸载程序
 */
$db = & JFactory::getDBO();
$sql = 'SELECT id FROM `#__plugins` WHERE `name` = "Content - Weibo" ;';
$db->setQuery($sql);
$result = $db->loadResult();
if ($result) {
    $installer = new JInstaller();
    $rtn = $installer->uninstall('plugin', $result);
    if ($rtn) {
        echo '<p>' . JText::_('Uninstall plg_content_weibo OK') . '</p>';
    } else {
        echo '<p>' . JText::_('Uninstall plg_content_weibo ERROR') . '</p>';
    }
} else {
    echo '<p>' . JText::_('Uninstall plg_content_weibo ERROR 2') . '</p>';
}
$sql = 'SELECT id FROM `#__modules` WHERE `title` = "mod_weibologin"';
$db->setQuery($sql);
$result = $db->loadResult();
if ($result) {
    $installer = new JInstaller();
    $rtn = $installer->uninstall('module', $result);
    if ($rtn) {
        echo '<p>' . JText::_('Uninstall mod_weibologin OK') . '</p>';
    } else {
        echo '<p>' . JText::_('Uninstall mod_weibologin ERROR') . '</p>';
    }
} else {
    echo '<p>' . JText::_('Uninstall mod_weibologin ERROR 2') . '</p>';
}
$sql = 'SELECT id FROM `#__plugins` WHERE `name` = "Authentication - weibo" ;';
$db->setQuery($sql);
$result = $db->loadResult();
if ($result) {
    $installer = new JInstaller();
    $rtn = $installer->uninstall('plugin', $result);
    if ($rtn) {
        echo '<p>' . JText::_('Uninstall plg_authentication_weibo OK') . '</p>';
    } else {
        echo '<p>' . JText::_('Uninstall plg_authentication_weibo ERROR') . '</p>';
    }
} else {
    echo '<p>' . JText::_('Uninstall plg_authentication_weibo ERROR 2') . '</p>';
}

        
