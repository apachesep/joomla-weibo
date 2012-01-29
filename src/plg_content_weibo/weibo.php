<?php

/**
 * @version		$Id$
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access.
defined('_JEXEC') or die;


jimport('joomla.plugin.plugin');
jimport('joomla.application.component.view');

define('WEIBO_LIMIT', 140); // 限制字数
//require_once JPATH_SITE.'/components/com_content/router.php';
require_once JPATH_SITE . '/components/com_content/helpers/route.php';
require_once('weibolib.php');

/**
 * 清理文字
 */
function cleanText(&$text) {
    $text = preg_replace("'<script[^>]*>.*?</script>'si", '', $text);
    $text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text);
    $text = preg_replace('/<!--.+?-->/', '', $text);
    $text = preg_replace('/{.+?}/', '', $text);
    $text = preg_replace('/&nbsp;/', ' ', $text);
    //$text = preg_replace( '/&amp;/', ' ', $text );
    $text = preg_replace('/&quot;/', ' ', $text);
    $text = strip_tags($text);
    $text = htmlspecialchars($text);
    return $text;
}

/**
 * 准备微博文字
 */
function getWeiboText($row, $P, &$weibocontent) {
    unset($weibocontent['text']);
    unset($weibocontent['imgfile']);
    // 取得网站的root URI
    $u = & JFactory::getURI();
    $root = $u->root();

    // 根据微博文字的种类
    if ($P['weibotype'] == 'fulltext') {
        //  1) 全文发表
        $weibotext = $row->introtext . '<br>' . $row->fulltext;
    } else if ($P['weibotype'] == 'introtext') {
        //  2) 发表引文
        $weibotext = $row->introtext;
    } else if ($P['weibotype'] == 'title') {
        //  3）发表标题
        $weibotext = $row->title;
    } else {
        //  4) 自定义发表文字
        //$link = JRoute::_(getArticleRoute($row->id, $row->catid, $row->sectionid), false); 旧版本用这个方法
        $link = ContentHelperRoute::getArticleRoute($row->id, $row->catid);

        // 取得发表者的名字
        $user = JFactory::getUser();
        $username = $user->name;

        $weibotext = str_replace('%T', $row->title, $P['customstring']);    // %T 替换成文章的标题
        $weibotext = str_replace('%F', $row->introtext . '<br>' . $row->fulltext, $weibotext); // %F 替换成文章的全文
        $weibotext = str_replace('%I', $row->introtext, $weibotext);  // %I 替换为引言
        $weibotext = str_replace('%H', $root, $weibotext);   // %H 替换为网站网址
        $weibotext = str_replace('%U', $username, $weibotext); // %U 替换成发表此文章的用户名
        $weibotext = str_replace('%L', $root . $link, $weibotext); // %L （ALPAH）替换成此文章的URL，此功能尚有BUG
    }

    // 因为微博限制字数为140字，删去多出部分
    $weibotext = mb_substr($weibotext, 0, WEIBO_LIMIT, 'utf-8');

    // 检查有无图片
    $imgfile = false;
    if ($P['picsend']) {
        if (preg_match('/<img[^>]*src="([^"]+)"/is', $row->introtext . $row->fulltext, $matchs)) {
            if (strpos($matchs[1], 'images/') === 0) {
                $picurl = $root . $matchs[1];
            } else {
                $picurl = $matchs[1];
            }
            //如果有图片，取得图片数据
            $imgfile = file_get_contents($picurl);
        }
    }

    // 清理文字
    $weibotext = cleanText($weibotext);

    // 如果仅有图片，而无文字，则填充文字“无”
    if ($imgfile && !$weibotext) {
        $weibotext = '无';
    }

    // 保存返回
    $weibocontent['text'] = $weibotext;
    $weibocontent['imgfile'] = $imgfile;
    $weibocontent['imgurl'] = $picurl;
}

/**
 * 发送新浪微博
 */
function sendSinaWeibo($weibocontent, $P) {

    if (!ifEnabled('sina', $P)) {
        return false;
    }

    try {
        $c = new WeiboClient(WB_AKEY, WB_SKEY, $P['sinalastkey']['oauth_token'], $P['sinalastkey']['oauth_token_secret']);

        // 如果有图片，上传图片，发表有图片的微博
        if ($weibocontent['imgurl']) {
            $rtninfo = $c->upload($weibocontent['text'], $weibocontent['imgurl']);
        } else {
            // 发表没有图片的微博
            $rtninfo = $c->update($weibocontent['text']);
        }
    } catch (Exception $e) {
        return false;
    }
    if ($rtninfo['error_code'])
        return false;
    return ("type=sina:id={$rtninfo['id']}");
}

/**
 * 发送腾讯微博
 */
function sendTencentWeibo($weibocontent, $P) {

    if (!ifEnabled('tencent', $P)) {
        return false;
    }

    // 准备微博对象
    try {
        $c = new MBApiClient(MB_AKEY, MB_SKEY, $P['tencentlastkey']['oauth_token'], $P['tencentlastkey']['oauth_token_secret']);

        // 如果有图片，上传图片，发表有图片的微博
        if ($weibocontent['imgfile']) {
            $p = array(
                'c' => $weibocontent['text'],
                'ip' => $_SERVER['REMOTE_ADDR'],
                'j' => '',
                'w' => '',
                'p' => array(null, 'pic from joomla', $weibocontent['imgfile']),
                'type' => 0
            );
        } else {
            // 发表没有图片的微博
            $p = array(
                'c' => $weibocontent['text'],
                'ip' => $_SERVER['REMOTE_ADDR'],
                'j' => '',
                'w' => ''
            );
        }
        $rtninfo = $c->postOne($p);
    } catch (Exception $e) {
        return false;
    }
    if ($rtninfo['error_code'])
        return false;
    return ("type=tencent:id={$rtninfo['data']['id']}");
}

/**
 * 发送网易微博
 */
function sendNeteaseWeibo($weibocontent, $P) {

    if (!ifEnabled('netease', $P)) {
        return false;
    }

    try {
        $c = new TBlog(CONSUMER_KEY, CONSUMER_SECRET, $P['neteaselastkey']['oauth_token'], $P['neteaselastkey']['oauth_token_secret']);
        // 如果有图片，上传图片，发表有图片的微博
        if ($weibocontent['imgurl']) {
            $rtninfo = $c->upload($weibocontent['text'], $weibocontent['imgurl']);
        } else {
            // 发表没有图片的微博
            $rtninfo = $c->update($weibocontent['text']);
        }
    } catch (Exception $e) {
        return false;
    }
    if ($rtninfo['error_code'])
        return false;
    return ("type=netease:id={$rtninfo['id']}");
}

/**
 * 发送网易微博
 */
function sendTwitterWeibo($weibocontent, $P) {

    if (!ifEnabled('twitter', $P)) {
        return false;
    }

    $tmhOAuth = new tmhOAuth(array(
                'consumer_key' => TW_AKEY,
                'consumer_secret' => TW_SKEY,
                'user_token' => $P['twitterlastkey']['oauth_token'],
                'user_secret' => $P['twitterlastkey']['oauth_token_secret'],
            ));

    $code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array(
        'status' => $weibocontent['text']
            ));
    return '';
}

// 本函数判别是否在本类微博中发表微博
// 
function ifEnabled($type, $P) {

    // 如果没有得到本类微博的授权，不发表
    if (!$P[$type . 'lastkey']) {
        return false;
    }

    // 如果根本就没有设置过分类的ID,说明全部微博都要发布
    if (!$P['catid'] && !$P['c_' . $type . '_catid'] && $P[$type . 'enabled']) {
        return true;
    }
    $catid = $P['row_catid'];

    // 以下进行文章的类别判断
    $testcatids = array();
    if ($P['c_' . $type . '_catid']) {
        $testcatids = explode(',', $P['c_' . $type . '_catid']);
    }
    if ($P['catid'] && $P[$type . 'enabled'])
        $testcatids[] = $P['catid'];

    //  这里joomla1.6以上与joomla1.5有差别，
    //  joomla1.5之下如果设置的文章的分类，则检查本文是否属于这个分类，如果不是，直接返回
    //  joomla1.6以后，是判断是否是指定分类的子分类。
    if (version_compare(JVERSION, '1.6.0', 'ge')) {
        // 这部分是joomla1.6, 1.7 的程序
        foreach ($testcatids as $testcatid) {
            if (trim($catid) == trim($testcatid)) {
                return true;
            }
            $jcats = JCategories::getInstance('Content');
            $pcate = $jcats->get($catid)->getParent();
            while ($pcate != null) {
                if ($pcate->id == $testcatid) {
                    return true;
                }
                $pcate = $pcate->getParent();
            }
        }
    } else {
        // 这部分是joomla1.5的程序
        foreach ($testcatids as $testcatid) {
            if (trim($catid) == trim($testcatid)) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Weibo plugin.
 *
 * @package	Joomla.Plugin
 * @subpackage	Content.weibo
 */
class plgContentWeibo extends JPlugin {

    /**
     * 构造函数
     */
    public function __construct(& $subject, $config) {
        parent::__construct($subject, $config);
        $this->loadLanguage();
    }

    function plgContentWeibo(&$subject, $params) {
        parent::__construct($subject, $params);
    }

    /**
     * Joomla 1.5 发表文章自动发表微博用插件函数
     */
    function onBeforeContentSave(&$article, $isNew) {
        return $this->onContentBeforeSave(null, &$article, $isNew);
    }

    /**
     * Joomla 1.7 发表文章自动发表微博用插件函数
     */
    function onContentBeforeSave($context, &$row, $isNew) {
        global $mainframe;

        // 只处理新建的文章，所以如果不是新文章，直接返回
        if (!$isNew) {
            return true;
        }

        // 如果没有启用本插件，则直接返回
        if (!$this->params->get('enabled', 1)) {
            return true;
        }

        // 参数的设置
        $P['sinaenabled'] = $this->params->get('sinaenabled', false); //是否启用新浪微博
        $P['tencentenabled'] = $this->params->get('tencentenabled', false); //是否启用腾讯微博
        $P['neteaseenabled'] = $this->params->get('neteaseenabled', false); //是否启用网易微博
        $P['twitterenabled'] = $this->params->get('twitterenabled', false); //是否启用twitter
        //
        //取得数据库中存储的新浪微博授权码
        $db = & JFactory::getDBO();
        $sql = "SELECT  oauth_token, oauth_token_secret, name FROM #__weibo_auth WHERE type='sina'";
        $db->setQuery($sql);
        $result = $db->loadAssoc();
        $P['sinalastkey'] = $result;
        // 取得数据库中存储的腾讯微博授权码
        $sql = "SELECT  oauth_token, oauth_token_secret, name FROM #__weibo_auth WHERE type='tencent'";
        $db->setQuery($sql);
        $result = $db->loadAssoc();
        $P['tencentlastkey'] = $result;
        // 取得数据库中存储的网易微博授权码
        $sql = "SELECT  oauth_token, oauth_token_secret, name FROM #__weibo_auth WHERE type='netease'";
        $db->setQuery($sql);
        $result = $db->loadAssoc();
        $P['neteaselastkey'] = $result;
        // 取得数据库中存储的Twitter授权码
        $db = & JFactory::getDBO();
        $sql = "SELECT  oauth_token, oauth_token_secret, name FROM #__weibo_auth WHERE type='twitter'";
        $db->setQuery($sql);
        $result = $db->loadAssoc();
        $P['twitterlastkey'] = $result;

        $P['weibotype'] = $this->params->get('weibotype', 'fulltext'); // 微博发表方式（fulltext，onlytitle，introtext或者custom）
        $P['catid'] = $this->params->get('catid'); // 所指定的分类
        $P['customstring'] = $this->params->get('customstring'); // 自定义的字符串
        $P['picsend'] = $this->params->get('picsend', true); // 将文章中的第一幅图片发布到微博上
        $P['customoption'] = $this->params->get('customoption', true); // 获取详细设置
        // 这里对customoption进行分解
        $optionlines = explode("\n", $P['customoption']);
        foreach ($optionlines as $optionline) {
            $temp = explode('=', $optionline);
            $P['c_' . $temp[0]] = $temp[1];
            // 对于某一个类型微博指定类型的话，可以用
            //   sina_catid=23,12 这样的设置
            // 其他的是：tencent_catid  twitter_catid  netease_catid
        }
        $P['row_catid'] = $row->catid;

        // 准备微博文字和图片
        getWeiboText($row, $P, $weibocontent);

        $rtntext = '';
        // 发送新浪微博
        $rtninfo = sendSinaWeibo($weibocontent, $P);
        if ($rtninfo) {
            $rtntext .= $rtninfo . ';';
        };

        // 发送腾讯微博
        $rtninfo = sendTencentWeibo($weibocontent, $P);
        if ($rtninfo) {
            $rtntext .= $rtninfo . ';';
        };

        // 发送网易微博
        $rtninfo = sendNeteaseWeibo($weibocontent, $P);
        if ($rtninfo) {
            $rtntext .= $rtninfo . ';';
        };

        // 发送网易微博
        $rtninfo = sendTwitterWeibo($weibocontent, $P);
        if ($rtninfo) {
            $rtntext .= $rtninfo;
        };
        if ($rtntext)
            $row->fulltext .= "<!-- Donot delete this comment 请勿删除此句 {" . $rtntext . '} -->';

        return true;
    }

}

