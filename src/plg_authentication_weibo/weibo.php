<?php
/**
 * $Id$
 * 此插件用于认证微博帐户
 * 其实真正认证微博功能的不在本插件，当认证程序从微博的认证网页返回时
 * 会填入适当用户名与密码（见程序中详细说明）
 * 本插件则根据填入的用户与密码，先知joomla系统认证是否通过
 * 如果认证通过，加入joomla系统的用户列表中
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Weibo Authentication Plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Authentication.weibo
 * @since 1.5
 */
class plgAuthenticationWeibo extends JPlugin {

    /**
     * Joomla 1.5 使用以下函数作为认证的插件
     */
    function onAuthenticate($credentials, $options, & $response) {
	return $this->onUserAuthenticate($credentials, $options, $response);
    }

    /**
     * Joomla 1.6 以后，使用以下函数作为认证的插件
     */
    function onUserAuthenticate($credentials, $options, & $response) {
        $message = '';
        $success = 0;
        $suffixs = array(      // 当传入的用户名，有这些后缀时，本插件认为是微博帐户
            '@sinaweibo',
            '@tencentweibo',
            '@neteaseweibo',
            '@twitterweibo',
            '@qq',
        );
        // 本插件认为认证通过有二个条件
        //  $_SESSION['weibouserlogin']  设置成 1
        //  密码必须是帐户名的base64_encode，
        if (strlen($credentials['username']) && strlen($credentials['password'])
                && $_SESSION['weibouserlogin'] ) {
            $blacklist = explode(',', $this->params->get('user_blacklist', ''));
            // 检查帐户是否在黑名单中
            if (!in_array($credentials['username'], $blacklist)) {
                foreach ($suffixs as $suffix) {
                    if (strpos($credentials['username'], $suffix) != FALSE) {
                        // 密码必须是帐户名的base64_encode，则认为认证通过
                        if ($credentials['password'] == base64_encode($credentials['username'])) {
                            $message = JText::_('JGLOBAL_AUTH_ACCESS_GRANTED');
                            $success = 1;
                        } else {
                            $message = JText::_('JGLOBAL_AUTH_ACCESS_DENIED');
                        }
                        break;
                    }
                }
            } else {
                // 帐户在黑名单中，不给予登录
                $message = 'User is blacklisted';
            }
        } else {
            $message = JText::_('JGLOBAL_AUTH_USER_BLACKLISTED');
        }
        $response->type = 'Weibo';
        if ($success) {
            $response->status = JAUTHENTICATE_STATUS_SUCCESS;
            $response->error_message = '';
            // 对于已经认证通过的设置用户作息
            
            // 设置用户名
            $response->username = $credentials['username'];
            // 设置昵称
            if ( $_SESSION['weibousername'] ) {
                $response->fullname =  $_SESSION['weibousername'];
            } else {
                $response->fullname = $credentials['username'];
            }
            // 设置用户的邮箱
            if ( $_SESSION['weibouseremail'] ) {
                $response->email =  $_SESSION['weibouseremail'];
            } else { // 新浪微博无法取得用户邮箱
                $response->email = $credentials['username'];
            }
        } else {
            $response->status = JAUTHENTICATE_STATUS_FAILURE;
            $response->error_message = JText::sprintf('JGLOBAL_AUTH_FAILED', $message);
        }
    }

}
