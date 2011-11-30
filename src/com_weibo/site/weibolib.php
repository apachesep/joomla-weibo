<?php

/**
 * $Id$
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/*
  $path = str_replace(DS . "components" . DS . "com_weibo", "", dirname(__FILE__));
  require_once($path . DS . "components" . DS . "com_weibo" . DS . "weibo.tencent.php");
  require_once($path . DS . "components" . DS . "com_weibo" . DS . "weibo.sina.php");
  require_once($path . DS . "components" . DS . "com_weibo" . DS . "weibo.163.php");
  require_once($path . DS . "components" . DS . "com_weibo" . DS . "weibo.twitter.php");
 */
require_once("weibo.tencent.php");
require_once("weibo.sina.php");
require_once("weibo.163.php");
require_once("weibo.twitter.php");

function TypeCheck($type) {
    switch ($type) {
        case 'sina': return '新浪微博';
        case 'tencent': return '腾讯微博';
        case 'netease': return '网易微博';
        case 'twitter': return 'Twitter';
        default : die('FATAL ERROR');
    }
}

function AuthUrlGet($type, $path) {
    TypeCheck($type);
    $func = 'AuthUrlGet_' . $type;
    if (function_exists($func)) {
        return $func($path);
    } else {
        die('FATAL ERROR:AuthUrlGet');
    }
}

function AuthCallback($type) {
    TypeCheck($type);
    $func = 'AuthCallback_' . $type;
    if (function_exists($func)) {
        return $func();
    } else {
        die('FATAL ERROR:AuthCallback');
    }
}

?>
