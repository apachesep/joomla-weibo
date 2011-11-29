<?php
/**
 *  $Id$
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');
$path = str_replace(DS . "components" . DS . "com_weibo", "", dirname(__FILE__));
require_once($path . DS . "components" . DS . "com_weibo" . DS . "weibo.sina.php");
require_once($path . DS . "components" . DS . "com_weibo" . DS . "weibo.163.php");
require_once($path . DS . "components" . DS . "com_weibo" . DS . "weibo.tencent.php");

/**
 * 微博认证组件的控制层程序
 */
class WeiboController extends JController {

     function sinaprelogin() {
            sinaprelogin( false, false );
     }
     function tencentprelogin() {
            tencentprelogin( false, false );
     }
     function neteaseprelogin() {
            neteaseprelogin( false, false );
     }
     function sinalogin() {
            sinalogin( false, false );
     }
     function tencentlogin() {
            tencentlogin( false, false );
     }
     function neteaselogin() {
            neteaselogin( false, false );
     }


    /**
     *  本函数用于显示自动跳转到新浪微博认证的网页
     */
    public

    function sinaprelogin($cachable = false, $urlparams = false) {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewLayout = JRequest::getCmd('layout', 'default');

        if(version_compare(JVERSION,'1.6.0','ge')) {
        // Joomla! 1.7 
        $viewName = JRequest::getCmd('view', $this->default_view);
        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath));
        } else {
        // Joomla! 1.5
        $viewName = JRequest::getCmd('view');
        $view = $this->getView($viewName, $viewType);
        }

        $view->return = JRequest::getCmd('rid');

        $o = new WeiboOAuth(WB_AKEY, WB_SKEY);

        $keys = $o->getRequestToken();
        $u = & JFactory::getURI();
        $p = $u->base();
        $view->weibourl = $o->getAuthorizeURL($keys['oauth_token'], false
                , $p . '/index.php?option=com_weibo&task=sinalogin' .
                '&rid=' . $view->return
        );
        $_SESSION['keys'] = $keys;

        $view->weiboprelogin();

        return $this;
    }

    /**
     *  本函数用于显示自动跳转到腾讯微博认证的网页
     */
    public

    function tencentprelogin($cachable = false, $urlparams = false) {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewLayout = JRequest::getCmd('layout', 'default');

        if(version_compare(JVERSION,'1.6.0','ge')) {
        // Joomla! 1.7 
        $viewName = JRequest::getCmd('view', $this->default_view);
        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath));
        } else {
        // Joomla! 1.5
        $viewName = JRequest::getCmd('view');
        $view = $this->getView($viewName, $viewType);
        }

        $view->return = JRequest::getCmd('rid');

        $o = new MBOpenTOAuth(MB_AKEY, MB_SKEY);

        $u = & JFactory::getURI();
        $p = $u->base();
        $keys = $o->getRequestToken($p . '/index.php?option=com_weibo&task=tencentlogin' .
                '&rid=' . $view->return); //这是回调的URL 
        $view->weibourl = $o->getAuthorizeURL($keys['oauth_token'], false, '');
        $_SESSION['keys'] = $keys;

        $view->weiboprelogin();

        return $this;
    }

    /**
     *  本函数用于显示自动跳转到网易微博认证的网页
     */
    public

    function neteaseprelogin($cachable = false, $urlparams = false) {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewLayout = JRequest::getCmd('layout', 'default');

        if(version_compare(JVERSION,'1.6.0','ge')) {
        // Joomla! 1.7 
        $viewName = JRequest::getCmd('view', $this->default_view);
        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath));
        } else {
        // Joomla! 1.5
        $viewName = JRequest::getCmd('view');
        $view = $this->getView($viewName, $viewType);
        }
        $view->return = JRequest::getCmd('rid');

        $o = new ONAuth(CONSUMER_KEY, CONSUMER_SECRET);
        //$o = new WeiboOAuth(WB_AKEY, WB_SKEY);

        $keys = $o->getRequestToken();
        $u = & JFactory::getURI();
        $p = $u->base();
        $view->weibourl = $o->getAuthorizeURL($keys['oauth_token'], $p . '/index.php?option=com_weibo&task=neteaselogin' . '&rid=' . $view->return
        );
        $_SESSION['request_token'] = $keys;

        $view->weiboprelogin();

        return $this;
    }

    /**
     *  本函数是新浪认证后回调的处理
     */
    public function sinalogin($cachable = false, $urlparams = false) {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewLayout = JRequest::getCmd('layout', 'default');
        if(version_compare(JVERSION,'1.6.0','ge')) {
        // Joomla! 1.7 
        $viewName = $this->default_view;
        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath));
        } else {
        // Joomla! 1.5
        $viewName = JRequest::getCmd('view');
        $view = $this->getView($viewName, $viewType);
        }

        $view->return = JRequest::getCmd('rid');

        // 取得新浪Auth对象
        $o = new WeiboOAuth(WB_AKEY, WB_SKEY, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);

        // 获取last_key
        $last_key = $o->getAccessToken($_REQUEST['oauth_verifier']);
        // 这里准备一套假的用户名与密码，以备后面的认证插件认证不通过
        $view->showusername = 'noname@sinaweibo';
        $view->showpassword = 'xxxxxx';
        $_SESSION['weibouserlogin'] = 0;
        
        if ($last_key) {
            // 如果认证通过，以"新浪用户ID@sinaweibo" 为joomla中的用户名
            $view->showusername = $last_key['user_id'] . '@sinaweibo';
            $view->showpassword = base64_encode($view->showusername);

            $client = new WeiboClient(WB_AKEY, WB_SKEY, $last_key['oauth_token'], $last_key['oauth_token_secret']);
            $user = $client->verify_credentials();
            
            // 以“新浪用户昵称(来自新浪微博)”用户昵称
            $_SESSION['weibousername'] = $user['screen_name'] . '(来自新浪微博)';
            $_SESSION['weibouserlogin'] = 1;
        }

        $view->weibologin();

        return $this;
    }

    /**
     *  本函数是腾讯认证后回调的处理
     */
    public function tencentlogin($cachable = false, $urlparams = false) {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewLayout = JRequest::getCmd('layout', 'default');

        if(version_compare(JVERSION,'1.6.0','ge')) {
        // Joomla! 1.7 
        $viewName = $this->default_view;
        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath));
        } else {
        // Joomla! 1.5
        $viewName = JRequest::getCmd('view');
        $view = $this->getView($viewName, $viewType);
        }
        $view->return = JRequest::getCmd('rid');

        // 取得腾讯Auth对象
        $o = new MBOpenTOAuth(MB_AKEY, MB_SKEY, $_SESSION['keys']['oauth_token'], $_SESSION['keys']['oauth_token_secret']);

        // 获取last_key
        $last_key = $o->getAccessToken($_REQUEST['oauth_verifier']);
        
        // 这里准备一套假的用户名与密码，以备后面的认证插件认证不通过
        $view->showusername = 'noname@tencentweibo';
        $view->showpassword = 'xxxxxx';
        $_SESSION['weibouserlogin'] = 0;

        if ($last_key) {
            // 如果认证通过，以"腾讯用户名@tencentweibo" 为joomla中的用户名
            $view->showusername = $last_key['name'] . '@tencentweibo';
            $view->showpassword = base64_encode($view->showusername);

            $client = new MBApiClient(MB_AKEY, MB_SKEY, $last_key['oauth_token'], $last_key['oauth_token_secret']);
            $user = $client->getUserInfo();
            
            // 以“腾讯用户昵称(来自腾讯微博)”为用户昵称
            $_SESSION['weibousername'] = $user['data']['nick'] . '(来自腾讯微博)';
            // 设置用户邮箱
            $_SESSION['weibouseremail'] = $user['data']['email'];
            $_SESSION['weibouserlogin'] = 1;
        }


        $view->weibologin();

        return $this;
    }

    /**
     *  本函数是网易认证后回调的处理
     */
    public function neteaselogin($cachable = false, $urlparams = false) {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewLayout = JRequest::getCmd('layout', 'default');

        if(version_compare(JVERSION,'1.6.0','ge')) {
        // Joomla! 1.7 
        $viewName = $this->default_view;
        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath));
        } else {
        // Joomla! 1.5
        $viewName = JRequest::getCmd('view');
        $view = $this->getView($viewName, $viewType);
        }
        $view->return = JRequest::getCmd('rid');

        // 取得网易Auth对象
        $o = new ONAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['request_token']['oauth_token'], $_SESSION['request_token']['oauth_token_secret']);

        // 获取last_key
        $last_key = $o->getAccessToken($_REQUEST['oauth_token']);
        // 这里准备一套假的用户名与密码，以备后面的认证插件认证不通过
        $view->showusername = 'noname@163weibo';
        $view->showpassword = 'xxxxxx';
        $_SESSION['weibouserlogin'] = 0;
        if ($last_key) {
            
            // 如果认证通过
            $client = new TBlog(CONSUMER_KEY, CONSUMER_SECRET, $last_key['oauth_token'], $last_key['oauth_token_secret']);
            $user = $client->verify_credentials();

            // 以“腾讯用户昵称(来自腾讯微博)”为用户昵称
            $_SESSION['weibousername'] = $user['name'] . '(来自网易微博)';
            
// 以"网易用户ID@163weibo" 为joomla中的用户名
            $view->showusername = $user['screen_name'] . '@163weibo';
            $view->showpassword = base64_encode($view->showusername);

            // 设置用户邮箱
            if ($user['email']) {
                $_SESSION['weibouseremail'] = $user['email'];
            }
            $_SESSION['weibouserlogin'] = 1;
        }

        $view->weibologin();

        return $this;
    }

}
