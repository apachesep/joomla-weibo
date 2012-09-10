<?php

/**
 *  $Id$
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');
$path = str_replace(DS . "components" . DS . "com_weibo", "", dirname(__FILE__));
require_once($path . DS . "components" . DS . "com_weibo" . DS . "weibolib.php");

/**
 * 微博认证组件的控制层程序
 */
class WeiboController extends JController {

    /**
     *  显示或跳转至认证的连接
     * @param type $type   微博类型，参见weibolib.php的TypeCheck函数
     * @param type $ifoob  是否使用oob(twitter等，需要使用这个)
     * @return WeiboController 
     */
    function authprelogin($type, $ifoob = false) {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewLayout = JRequest::getCmd('layout', 'default');

        if (version_compare(JVERSION, '1.6.0', 'ge')) {
            // Joomla! 1.7 
            $viewName = JRequest::getCmd('view', $this->default_view);
            $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath));
        } else {
            // Joomla! 1.5
            $viewName = JRequest::getCmd('view');
            $view = $this->getView($viewName, $viewType);
        }

        $view->return = JRequest::getCmd('rid');

        $u = & JFactory::getURI();
        $p = $u->base();
        $path = $p . '/index.php?option=com_weibo&task=' . $type . 'login' . '&rid=' . $view->return;
        $view->weibourl = AuthUrlGet($type, $path);

        if ($ifoob) {
            $view->oobprelogin();
        } else {
            $view->weiboprelogin();
        }


        return $this;
    }

    /**
     *  本函数用于显示自动跳转到新浪微博认证的网页
     */
    public

    function sinaprelogin($cachable = false, $urlparams = false) {
        $sinaappkey = JRequest::getCmd('sinaappkey');
        $sinasecret = JRequest::getCmd('sinasecret');
        $_SESSION['sinaappkey'] = $sinaappkey;
        $_SESSION['sinasecret'] = $sinasecret;
        //$_SESSION['scope'] = 'get_user_info';
        return $this->authprelogin('sina');
    }

    /**
     *  本函数用于显示自动跳转到腾讯微博认证的网页
     */
    public

    function tencentprelogin($cachable = false, $urlparams = false) {
        return $this->authprelogin('tencent');
    }

    /**
     *  本函数用于显示自动跳转到网易微博认证的网页
     */
    public

    function neteaseprelogin($cachable = false, $urlparams = false) {
        return $this->authprelogin('netease');
    }

    /**
     *  本函数用于显示自动跳转到Twitter认证的网页
     */
    public

    function twitterprelogin($cachable = false, $urlparams = false) {
        return $this->authprelogin('twitter', true);
    }

    /**
     *  本函数用于显示自动跳转到QQ认证的网页
     */
    public

    function qqprelogin($cachable = false, $urlparams = false) {
        $qqappid = JRequest::getCmd('qqappid');
        $qqkey = JRequest::getCmd('qqkey');
        $_SESSION['appid'] = $qqappid;
        $_SESSION['appkey'] = $qqkey;
        $_SESSION['scope'] = 'get_user_info';
        return $this->authprelogin('qq');
    }

    function authlogin($type) {
        $zhtype = TypeCheck($type);
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewLayout = JRequest::getCmd('layout', 'default');
        if (version_compare(JVERSION, '1.6.0', 'ge')) {
            // Joomla! 1.7 
            $viewName = $this->default_view;
            $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath));
        } else {
            // Joomla! 1.5
            $viewName = JRequest::getCmd('view');
            $view = $this->getView($viewName, $viewType);
        }

        $view->return = JRequest::getCmd('rid');

        // 这里准备一套假的用户名与密码，以备后面的认证插件认证不通过
        $view->showusername = 'noname@notlogin';
        $view->showpassword = 'xxxxxx';
        $_SESSION['weibouserlogin'] = 0;

        $last_key = AuthCallback($type);

        if ($last_key) {
            // 如果认证通过，以"用户ID@xxxxxweibo" 为joomla中的用户名
            if ($type == 'qq') {
                $view->showusername = $last_key['user_id'] . '@' . $type;
            } else {
                $view->showusername = $last_key['user_id'] . '@' . $type . 'weibo';
            }
            $view->showpassword = base64_encode($view->showusername);

            // 以“用户昵称(来自某某微博)”用户昵称
            $_SESSION['weibousername'] = $last_key['user_name'] . '(来自' . $zhtype . ')';
            if (array_key_exists('user_email', $last_key)) {
                $_SESSION['weibouseremail'] = $last_key['user_email'];
            } else {
                unset($_SESSION['weibouseremail']);
            }
            $_SESSION['weibouserlogin'] = 1;
            $view->weibologin();
        } else {
            $view->weibologinerror($type, $zhtype);
        }


        return $this;
    }

    /**
     *  本函数是新浪认证后回调的处理
     */
    public function sinalogin($cachable = false, $urlparams = false) {
        return $this->authlogin('sina');
    }

    /**
     *  本函数是腾讯认证后回调的处理
     */
    public function tencentlogin($cachable = false, $urlparams = false) {
        return $this->authlogin('tencent');
    }

    /**
     *  本函数是网易认证后回调的处理
     */
    public function neteaselogin($cachable = false, $urlparams = false) {
        return $this->authlogin('netease');
    }

    /**
     *  本函数是网易认证后回调的处理
     */
    public function twitterlogin($cachable = false, $urlparams = false) {
        $_SESSION['tw_oobpin'] = JRequest::getCmd('oobpin');
        return $this->authlogin('twitter');
    }

    /**
     *  本函数是QQ认证后回调的处理
     */
    public function qqlogin($cachable = false, $urlparams = false) {
        return $this->authlogin('qq');
    }

}
