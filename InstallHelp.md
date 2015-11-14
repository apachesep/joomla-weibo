本文说明在Joomla 1.7及以上版本之下如何安装卸载本程序

(本程序没有在Joomla 1.6之下测试过，但是相信Joomla 1.6之下也可以使用)



# 说明 #
实际上本插件包含以下多个扩展：

  * com\_weibo 微博组件
  * mod\_weibologin  微博登录用模块
  * plg\_content\_weibo 发表内容同时发表微博所用到的插件
  * plg\_authentication\_weibo 用于微博登录用

以上四个程序，在安装本插件时，会自动安装进去。
删除时，请删除com\_weibo，则也会自动删除全部
如果手动删除其它三个中一个，则程序的工作会不正常

由于上述原因，安装插件时显示以下信息属于正常

![http://joomla-weibo.googlecode.com/svn/wiki/images/2.png](http://joomla-weibo.googlecode.com/svn/wiki/images/2.png)

删除本插件时，也会显示以下界面属于正常

![http://joomla-weibo.googlecode.com/svn/wiki/images/3.png](http://joomla-weibo.googlecode.com/svn/wiki/images/3.png)

# 安装本程序 #

进入管理菜单，选择“扩展-扩展管理”，选择本程序的ZIP文件。

然后，点击“上传文件&安装”

![http://joomla-weibo.googlecode.com/svn/wiki/images/17/install1.png](http://joomla-weibo.googlecode.com/svn/wiki/images/17/install1.png)

如果显示以下信息，表示所有的组件/模块/插件均安装成功

![http://joomla-weibo.googlecode.com/svn/wiki/images/2.png](http://joomla-weibo.googlecode.com/svn/wiki/images/2.png)

# 卸载本程序 #

进入管理菜单，选择“扩展-扩展管理”，选择“管理”

找到Weibo组件，然后点击“卸载”

![http://joomla-weibo.googlecode.com/svn/wiki/images/17/install2.png](http://joomla-weibo.googlecode.com/svn/wiki/images/17/install2.png)

如果显示以下信息，表示所有的组件/模块/插件均卸载成功

![http://joomla-weibo.googlecode.com/svn/wiki/images/3.png](http://joomla-weibo.googlecode.com/svn/wiki/images/3.png)

# 自动更新本程序 #

可以选择“扩展-扩展管理”，选择“更新”，点击“查找更新”，可以查看本插件是否更新的版本。

如果有更新的版本，使用“更新”进行更新