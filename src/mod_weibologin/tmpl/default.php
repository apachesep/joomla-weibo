<?php
/**
 * @version		$Id$
 */
// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>
<?php
switch ($params->get('logosize')) {
    case 1:
        $sinalogo = '<img src="components/com_weibo/images/logosina_24.png" />';
        $tencentlogo = '<img src="components/com_weibo/images/logotencent_24.png" />';
        $neteaselogo = '<img src="components/com_weibo/images/logo163_24.png" />';
        $twitterlogo = '<img src="components/com_weibo/images/logotwitter_24.png" />';
        break;
    case 2:
        $sinalogo = '<img src="components/com_weibo/images/logosina_16.png" />';
        $tencentlogo = '<img src="components/com_weibo/images/logotencent_16.png" />';
        $neteaselogo = '<img src="components/com_weibo/images/logo163_16.png" />';
        $twitterlogo = '<img src="components/com_weibo/images/logotwitter_16.png" />';
        break;
    default:
        $sinalogo = '<img src="components/com_weibo/images/logosina_32.png" />';
        $tencentlogo = '<img src="components/com_weibo/images/logotencent_32.png" />';
        $neteaselogo = '<img src="components/com_weibo/images/logo163_32.png" />';
        $twitterlogo = '<img src="components/com_weibo/images/logotwitter_32.png" />';
        break;
}

if ($type == 'logout') :
//微博插件不显示“登出”
else :
    ?>
    <?php if ($params->get('pretext')): ?>
        <div class="pretext">
            <p><?php echo $params->get('pretext'); ?></p>
        </div>
    <?php endif; ?>
    <?php if ($params->get('sinaenabled')) { ?>
        <a href=index.php?option=com_weibo&task=sinaprelogin&rid=<?php echo $return; ?>>
            <?php echo $sinalogo; ?>
        </a>
    <?php } ?>
    <?php if ($params->get('tencentenabled')) { ?>
        <a href=index.php?option=com_weibo&task=tencentprelogin&rid=<?php echo $return; ?>>
            <?php echo $tencentlogo; ?>
        </a>
    <?php } ?>
    <?php if ($params->get('neteaseenabled')) { ?>
        <a href=index.php?option=com_weibo&task=neteaseprelogin&rid=<?php echo $return; ?>>
            <?php echo $neteaselogo; ?>
        </a>
    <?php } ?>
    <?php if ($params->get('twitterenabled')) { ?>
        <a href=index.php?option=com_weibo&task=twitterprelogin&rid=<?php echo $return; ?>>
            <?php echo $twitterlogo; ?>
        </a>
    <?php } ?>
<?php endif; ?>