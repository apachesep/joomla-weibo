

# 简介 #

本文介绍如何让joomla1.5系统能够使用微博的帐号进行登录。

本文以标准的Joomla页面及缺省的安装示范数据作为例子，每个由Joomla构筑的网站多少会有所不同，但是如果您仔细阅读了本文。相信一定能够在您的网站中配置好通过本插件，使用微博登录Joomla的。

# 配置用户界面 #

要使用微博登录，首先，要启用“Authentication - Weibo”插件

## 启用插件 ##

进入菜单“扩展-插件管理”，选择“Authentication - Weibo”，然后启用本插件

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/4.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/4.png)

这时，系统已经具备了微博登录功能，但是用户界面上却没有相应的连接。您可以参照以下的方法，为您自己的页面设置微博登录的用户界面:

安装完Joomla，并且使用安装时自带的示范数据后，Joomla的首页如下。

注意其中左下部分，有一个供用户登录的表单

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/1.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/1.png)

这个表单是由joomla本身所提供的认证模块，现在让我们把我们的微博认证模块也加到其中。

## 增加微博认证用的模块 ##

首先在管理菜单中选择“扩展-模块管理”

新建一个模块：

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/5.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/5.png)

选择“微博登陆”

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/6.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/6.png)

请设置模块名称，比如“WeiboLogin”，“位置”，请选择“left”，“排序”请选择“7:Login Form”。这是系统自带的示范页面中左侧栏的位置标识。您自己的网站可能不是这个，或者你也可以把微博登录的界面安排在其他位置。

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/7.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/7.png)

其他相关选项的解释：

  * 显示标题：如果显示，那界面上会出现“WeiboLogin”这个标题
  * 前导文本：可以不设置，也可以设置成您喜欢的文字，用于提示用户使用微博登录
  * 启用新浪、腾讯、网易微博、Twitter、QQ：根据您的需要，启用或者禁用相应的登录功能，注这仅仅是用户界面上是否显示相应的图标
  * 新浪提供的AppKey、App Secret：仅在使用新浪微博登录时使用这两个设置。您应该前往新浪的网站（ http://open.weibo.com/ ）为自己的网站申请一个接入，这时，新浪会给您的接入分配一个AppKey和App Secret，把这两个填入上述相应的框内即可。注意，现在新浪是要求登录回调域名的，所以您的网站与在新浪设置的回调域名必须一致。
  * 腾讯提供的AppID、腾讯提供的Key：仅在使用QQ登录时使用这两个设置。（注意，这是QQ登录用的，与腾讯微博登录完全没有关系！）。您应该前往QQ的网站（ http://open.qq.com/ ）为自己的网站申请一个接入，这时，腾讯会给您的接入分配一个AppID和Key，把这两个填入上述相应的框内即可
  * 图标大小：显示在用户界面上的图标大小。

以上设置完毕，请先选择“保存”

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/8.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/8.png)

这时，就可以安排同样是位于“left”所标识的左侧栏中的其他模块的位置了。在这个例子里，我们把我们的登录模块安排在系统的登录表单之下。

# 使用微博登录 #

设置完成后，保存退出，这时到用户界面的首页，您可以看到，左下方的登录表单中出现了“使用微博登录”的部分，同时显示出三个按钮。用户按其中的一个，便可以使用微博登录了。

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/10.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/10.png)

下面的例子是使用新浪微博登录，这时首先会跳转到新浪微博的网页上进行认证，

![http://joomla-weibo.googlecode.com/svn/wiki/images/15.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15.png)

认证后，界面上会显示出已经登录的界面，并且，用户的昵称就是用户的“微博昵称”加上（来自某某微博）

（注意：Joomla1.5 的界面通常会显示用户ID而不是显示用户昵称，如果需要显示用户昵称，请进入系统的Login Form模块，设置中有一个“姓名/会员名”选项，设置成“姓名”即可）

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/11.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/11.png)

# 后台用户管理 #

进入管理界面中的用户管理，您可以看到登录的微博用户

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/12.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/12.png)

您可以象操作普通的Joomla用户一样，为这个用户设置权限等。请注意，在joomla系统中使用的“用户ID”

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/13.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/13.png)

当您要把这个用户设置成黑名单时，请进入“扩展-插件管理”，选择“Authentication - Weibo”，以这个用户ID填入黑名单中，多个名单请你半角逗号分隔

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/14.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/14.png)