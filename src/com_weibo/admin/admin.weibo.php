<?php

/**
 * $Id$
 */
defined('_JEXEC') or die('Restricted access');

$path = str_replace(DS . "components" . DS . "com_weibo", "", dirname(__FILE__));
require_once($path . DS . "components" . DS . "com_weibo" . DS . "weibolib.php");
require_once($path . DS . "components" . DS . "com_weibo" . DS . "admin.weibo.html.php");

// 本程序是com_weibo的主程序，用于处理腾讯微博的授权认证
$task = JRequest::getString('task');

// 程序处理以下几种请求，详见下面的说明
switch ($task) {
    case 'tencentauth': // 当task=tencentauth时，将页面转向腾讯的授权页面
        HTML_weibo::showTencentAuth();
        break;
    case 'tencentcallback': // 当腾讯授权正常完成时，将转到task=callback回调
        tencentCallback();
        break;
    case 'sinaauth': // 当task=sinaauth时，页面将提示用户输入Appkey和Appsecret
        HTML_weibo::showSinaAuth();
        break;
    case 'sinaauth2': // 当task=sinaauth2时，将页面转向新浪的授权页面
        HTML_weibo::showSinaAuth2();
        break;
    case 'sinacallback': // 当新浪授权正常完成时，将转到task=callback回调
        sinaCallback();
        break;
    case 'neteaseauth': // 当task=neteaseauth时，将页面转向网易的授权页面
        HTML_weibo::showNeteaseAuth();
        break;
    case 'neteasecallback': // 当网易授权正常完成时，将转到task=callback回调
        neteaseCallback();
        break;
    case 'twitterauth': // 当task=neteaseauth时，将页面转向网易的授权页面
        HTML_weibo::showTwitterAuth();
        break;
    case 'twittercallback': // 当网易授权正常完成时，将转到task=callback回调
        twitterCallback();
        break;
    default:
        break;
}

function weiboCallback($type, $lastkey) {
    if ($lastkey) {
        $last_key = $lastkey;
    } else {
        $last_key = AuthCallback($type);
    }
    if ($last_key) {
        // 如果成功取得last_key
        $db = & JFactory::getDBO();

        // 先将数据库中原有数据无论有无均删除
        $sql = "DELETE FROM #__weibo_auth WHERE type='" . $type . "'";
        $db->setQuery($sql);
        $db->Query();

        // 将取得的last_key写入数据库中
        $sql = "INSERT INTO #__weibo_auth(oauth_token,oauth_token_secret,name,type ) VALUES ('$last_key[oauth_token]','$last_key[oauth_token_secret]','$last_key[user_id]','$type') ";
        $db->setQuery($sql);
        $db->Query();

        // 显示已经成功获得授权的页面
        if (!$lastkey) {
            HTML_weibo::finishedWeiboAuth($last_key, $type);
        }
    } else {
        // 如果未成功取得last_key，显示出错的页面
        HTML_weibo::errorWeiboAuth($last_key, $type);
    }
}

/**
 * 当腾讯授权正常完成时，将转到task=tencentcallback回调，这时调用这个函数
 */
function tencentCallback() {
    weiboCallback('tencent', false);
}

/**
 * 当新浪授权正常完成时，将转到task=sinacallback回调，这时调用这个函数
 */
function sinaCallback() {
    weiboCallback('sina', false);
    $last_key['oauth_token'] = $_SESSION['sinaappkey'];
    $last_key['oauth_token_secret'] = $_SESSION['sinasecret'];
    $last_key['user_id'] = '';
    weiboCallback('sinakey', $last_key);
}

/**
 * 当网易授权正常完成时，将转到task=neteasecallback回调，这时调用这个函数
 */
function neteaseCallback() {
    weiboCallback('netease', false);
}

/**
 * 当Twitter授权正常完成时，将转到task=twittercallback回调，这时调用这个函数
 */
function twittercallback() {
    weiboCallback('twitter', false);
}

?>
