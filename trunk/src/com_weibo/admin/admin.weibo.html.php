<?php
/**
 * $Id$
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 *  本程序处理与腾讯授权的com_weibo的相关页面
 *
 */
class HTML_weibo {

    /**
     * 这个数据显示一个页面，它自动会转入腾讯授权的页面
     */
    function showTencentAuth() {
        $u = & JFactory::getURI();
        $p = $u->base().'/index.php?option=com_weibo&task=tencentcallback';
        $aurl = AuthUrlGet('tencent', $p);
        ?>
        <script>
            document.location.href="<?php echo $aurl ?>"
        </script>
        <?php
    }

    /**
     * 这个数据显示一个页面，它自动会转入新浪授权的页面
     */
    function showSinaAuth() {
        $u = & JFactory::getURI();
        $p = $u->base().'/index.php?option=com_weibo&task=sinacallback';
        $aurl = AuthUrlGet('sina', $p);
        ?>
        <script>
            document.location.href="<?php echo $aurl ?>"
        </script>
        <?php
    }

    /**
     * 这个数据显示一个页面，它自动会转入网易授权的页面
     */
    function showNeteaseAuth() {
        $u = & JFactory::getURI();
        $p = $u->base().'/index.php?option=com_weibo&task=neteasecallback';
        $aurl = AuthUrlGet('netease', $p);
        ?>
        <script>
            document.location.href="<?php echo $aurl ?>"
        </script>
        <?php
    }

        /**
     * 这个数据显示一个页面，它自动会转入腾讯授权的页面
     */
    function showTwitterAuth() {
        $u = & JFactory::getURI();
        $p = $u->base().'/index.php?option=com_weibo&task=twittercallback';
        $aurl = AuthUrlGet('twitter', $p);
if ( $aurl ){
    ?>
<a href="<?php echo $aurl;?>" target="_twitter">点击这里</a>进行认证，把看到的数字填入下面的框内。<br />
<form action="<?php echo JRoute::_('index.php', true); ?>"  method="post" name="ooblogin-form">
    <input type="text" name="oobpin" />
    <input type="submit" />
    <input type="hidden" name="option" value="com_weibo" />
    <input type="hidden" name="task" value="twittercallback" />
</form>
<?php
} else {
 ?>   
无法联系上认证服务器。请返回。<br />（认证服务器可能已经停止，或者天朝内网，无法访问Twitter。）
<?php }    }

    
    /**
     * 当授权成功时，显示成功的页面
     */
    function finishedWeiboAuth($last_key, $zhtype) {
        ?>
        已经完成<?php echo $zhtype; ?>的认证，您所使用的用户名为“
        <?php echo $last_key['user_id'] ?>
        ”，你可以关闭本窗口。
        <?php
    }

    /**
     * 当授权失败时，显示失败的页面
     */
    function errorTwitterAuth($last_key,$zhtype) {
        echo $zhtype; ?>
        认证出错。
        <?php
    }

    
}
?>
