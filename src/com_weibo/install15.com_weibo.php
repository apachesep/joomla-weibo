<?php

/**
 * @version         $Id$
 */
// 本安装文件仅适用于Joomla 1.5
defined('_JEXEC') or die();

/**
 * com_weibo 的安装程序
 */
global $mainframe;

if (!version_compare(JVERSION, '1.6.0', 'ge')) {

    jimport('joomla.application.component.model');
    jimport('joomla.installer.installer');
    jimport('joomla.installer.helper');
    jimport('joomla.filesystem.folder');
    jimport('joomla.filesystem.file');
}

/**
 *  安装程序
 */
function com_install() {
    if (version_compare(JVERSION, '1.6.0', 'ge')) {
        return;
    }
    $msg = array();
    $COM_NAME = 'com_weibo';

// 从已经安装到com_weibo组件的文件中，找出zip文件
    $files = JFolder::files(JPATH_ADMINISTRATOR . DS . 'components' . DS . $COM_NAME, 'zip', true, true);
    if ($files) {
        foreach ($files as $file) { // 对于每个zip文件
            // 解压zip文件，取得安装包的相关信息
            $dest = dirname($file) . DS . JFile::stripExt(basename($file));
            JArchive::extract($file, $dest);
            $package = getPackageFromFolder($dest);

            // 使用JInstaller对象
            $installer = new JInstaller();
            $installer->setOverwrite(true);

            // 安装相应的ZIP包
            if (!$installer->install($package['dir'])) {
                // 如果有错，将错误信息加入信息列表中
                $msg[] = '<span style="color:#FF0000">' . basename($file) . ': ' . JText::sprintf('INSTALLERR', JText::_($package['type']), ' - ' . JText::_('ERROR')) . '</span>';
                $result = false;
            } else {
                // 安装成功，也将成功信息加入信息列表中
                $msg[] = basename($file) . ': ' . JText::sprintf('INSTALLOK', JText::_($package['type']), JText::_('Success'));
                $result = true;
            }
        }
    }

    $path = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_weibo' . DS . 'mod_weibologin';
    if (!JFolder::delete($path)) {
        $msg[] = JText::_('DELETETMPERROR');
    }
    $path = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_weibo' . DS . 'plg_authentication_weibo';
    if (!JFolder::delete($path)) {
        $msg[] = JText::_('DELETETMPERROR');
    }
    $path = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_weibo' . DS . 'plg_content_weibo';
    if (!JFolder::delete($path)) {
        $msg[] = JText::_('DELETETMPERROR');
    }

// 数据库的创立
    $db = & JFactory::getDBO();
    $sql = 'CREATE TABLE IF NOT EXISTS `#__weibo_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oauth_token` varchar(255) NOT NULL,
  `oauth_token_secret` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(10),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
';

    $db->setQuery($sql);
    $db->query() or die("数据库创立错误");

    // 加上这段话是因为在%VERSION%以前的版本中，这个字段没有AUTO_INCREMEN
    $sql = 'ALTER TABLE `#__weibo_auth` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ';
    $db->setQuery($sql);
    $db->query() or die("数据库创立错误");
    
// 输出结果
    if (count($msg)) {
        echo '<dt>安装结果:</dt>';
        foreach ($msg as $m) {
            echo '<dd> - ' . $m . '</dd>';
        }
        echo '</dl>';
    }
}

/**
 * 取得zip的安装包的相关信息
 * */
function getPackageFromFolder($p_dir) {
    // 如果目录不正确，出错
    if (!is_dir($p_dir)) {
        JError::raiseWarning('SOME_ERROR_CODE', JText::_('Please enter a package directory'));
        return false;
    }

    // 取得包的类型
    $type = JInstallerHelper::detectType($p_dir);

    // 如果不能取得，出错
    if (!$type) {
        JError::raiseWarning('SOME_ERROR_CODE', JText::_('Path does not have a valid package'));
        return false;
    }

    // 设置相关的包的信息
    $package['packagefile'] = null;
    $package['extractdir'] = null;
    $package['dir'] = $p_dir;
    $package['type'] = $type;

    return $package;
}

?>
