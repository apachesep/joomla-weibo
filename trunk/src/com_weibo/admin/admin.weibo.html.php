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
     * 当授权成功时，显示成功的页面
     */
    function finishedTencentAuth($last_key) {
        ?>
        已经完成腾讯的认证，您所使用的腾讯微博用户名为“
        <?php echo $last_key['user_id'] ?>
        ”，你可以关闭本窗口。
        <?php
    }

    /**
     * 当授权失败时，显示失败的页面
     */
    function errorTencentAuth($last_key) {
        ?>
        腾讯认证出错。
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
     * 当授权成功时，显示成功的页面
     */
    function finishedSinaAuth($last_key) {
        ?>
        已经完成新浪的认证，您所使用的新浪微博用户id为“
        <?php echo $last_key['user_id']; ?>
        ”，你可以关闭本窗口。
        <?php
    }

    /**
     * 当授权失败时，显示失败的页面
     */
    function errorSinatAuth($last_key) {
        ?>
        新浪认证出错。
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
     * 当授权成功时，显示成功的页面
     */
    function finishedNeteaseAuth($last_key) {
        ?>
        已经完成网易的认证，你可以关闭本窗口。
        <?php
    }

    /**
     * 当授权失败时，显示失败的页面
     */
    function errorNeteaseAuth($last_key) {
        ?>
        网易认证出错。
        <?php
    }

}
?>
