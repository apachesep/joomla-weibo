<?php

/**
 *  $Id$
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
if ($this->auth == 'weibologin') {
    ?>
<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="login-form" name="weibologin-form">
	<fieldset class="userdata">
	<input id="modlgn-username" type="hidden" name="username" class="inputbox"  size="18" value="<?php echo $this->showusername; ?>" />
        <input id="modlgn-passwd" type="hidden" name="password" class="inputbox" size="18"  value="<?php echo $this->showpassword;?>"/>
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGIN') ?>" />
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.login" />
	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
<script type="text/javascript">
    document.forms["weibologin-form"].submit();
</script>
<?php
} elseif ( $this->auth == 'weiboprelogin') {
?><script>
document.location.href="<?php echo $this->weibourl?>"
</script><?php
} else {
?>
<h1><?php echo $this->msg; ?></h1>
<?php
}
?>