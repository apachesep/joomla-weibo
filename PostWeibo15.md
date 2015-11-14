本文说明在Joomla 1.5之下如何如何发表微博



# 启用并设置微博插件 #

进入“扩展”-“插件管理”，选择“Content - Weibo”，然后进行相应的设置：

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/plg_content.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/plg_content.png)

1）启用本插件

2）发表方式有四种，（按微博的要求，仅限140字）

  1. 发表文章的全部内容
  1. 发表文章的引言
  1. 发表文章的标题
  1. 自定义发表方式

> 这时，可以在下面的“自定义微博文字”中填入自己定义的内容

> 比如“我发表了名为《%T》的文章”

> 可以使用的参数有：
    * %T  文章标题
    * %F  文章全文
    * %I  引言
    * %H  指向本网站首页的URL地址
    * %U  文章的作者
    * %L  指向本博文的地址


3）“分类”设置。在这里选择一种分类，在此分类中增加新的文章时才会在微博上发表，不会影响其他分类的文章，如果不设置这个“分类”，则所有的新增加的文章都会在微博上发表。

4）是否发表图片，如果选择了“是”，则文章中如果有图片的话，也会发表到微博上，注意，如果文章中有多个图片，只会发送第一个。

5）启用新浪，腾讯或者网易微博

在设置界面中，请点击“新浪认证”，于是弹出一个新的窗口，此窗口的页面会自动转向到新浪的认证页面：

![http://joomla-weibo.googlecode.com/svn/wiki/images/15.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15.png)

输入帐户和密码后，点击授权。这时，页面会回到joomla的控制台，并显示认证已经通过

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/16.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/16.png)

6）高级设置

对于0.8.1及以上版本，可以进行更高级的设置，详见PostWeiboAdvanced

# 发表文章并同步到微博上 #

注意：本插件仅在发表新的文章的时候同时发表到微博上，修改或者删除文章对已经发表的微博没有任何影响

下面这个例子中创建了一篇文章

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/17.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/17.png)

相应的，在新浪微博上也生成了一条微博：

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/18.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/18.png)

同时，腾讯微博上也有一条微博了

![http://joomla-weibo.googlecode.com/svn/wiki/images/15/19.png](http://joomla-weibo.googlecode.com/svn/wiki/images/15/19.png)