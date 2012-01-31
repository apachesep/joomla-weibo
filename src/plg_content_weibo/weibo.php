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
function getWeiboText($row, $option, &$weibocontent) {
    unset($weibocontent['text']);
    unset($weibocontent['imgfile']);
    // 取得网站的root URI
    $u = & JFactory::getURI();
    $root = $u->root();

    // 根据微博文字的种类
    if ($option['weibotype'] == 'fulltext') {
        //  1) 全文发表
        $weibotext = $row->introtext . '<br>' . $row->fulltext;
    } else if ($option['weibotype'] == 'introtext') {
        //  2) 发表引文
        $weibotext = $row->introtext;
    } else if ($option['weibotype'] == 'title') {
        //  3）发表标题
        $weibotext = $row->title;
    } else {
        //  4) 自定义发表文字
        //$link = JRoute::_(getArticleRoute($row->id, $row->catid, $row->sectionid), false); 旧版本用这个方法
        $link = ContentHelperRoute::getArticleRoute($row->id, $row->catid);

        // 取得发表者的名字
        $user = JFactory::getUser();
        $username = $user->name;

        $weibotext = str_replace('%T', $row->title, $option['customstring']);    // %T 替换成文章的标题
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
    if ($option['picsend']) {
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
function sendSinaWeibo($row, $P) {

    // 如果没有得到本类微博的授权，不发表
    if (!$P['sinalastkey']) {
        return false;
    }
    $option = $P['typeoption']['sina'];
    if (!$option) {
        return false;
    }

    // 准备微博文字和图片
    getWeiboText($row, $option, $weibocontent);

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
function sendTencentWeibo($row, $P) {

    // 如果没有得到本类微博的授权，不发表
    if (!$P['tencentlastkey']) {
        return false;
    }
    $option = $P['typeoption']['tencent'];
    if (!$option) {
        return false;
    }
    // 准备微博文字和图片
    getWeiboText($row, $option, $weibocontent);

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
function sendNeteaseWeibo($row, $P) {

    // 如果没有得到本类微博的授权，不发表
    if (!$P['neteaselastkey']) {
        return false;
    }
    $option = $P['typeoption']['netease'];
    if (!$option) {
        return false;
    }
    
    // 准备微博文字和图片
    getWeiboText($row, $option, $weibocontent);

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
function sendTwitterWeibo($row, $P) {

    // 如果没有得到本类微博的授权，不发表
    if (!$P['neteaselastkey']) {
        return false;
    }
    $option = $P['typeoption']['twitter'];
    if (!$option) {
        return false;
    }
    
    // 准备微博文字和图片
    getWeiboText($row, $option, $weibocontent);

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

// 判别文章分类$catid是否是文章分类$testcatid
//  如果$testcatid的值是'all'，直接返回
//  joomla1.5之下如果设置的文章的分类，则检查本文是否属于这个分类，如果不是，直接返回
//  joomla1.6以后， 根据$norecursive的值
//      如果是0 判断是否是指定分类的子分类。
//      如果是1 与1.5相同
function inCat($catid, $testcatid, $norecursive=0) {

    if ($testcatid == 'all')
        return true;

    //  这里joomla1.6以上与joomla1.5有差别，
    if (version_compare(JVERSION, '1.6.0', 'ge') && !$norecursive) {
        // 这部分是joomla1.6, 1.7 的程序
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
    } else {
        // 这部分是joomla1.5的程序
        if (trim($catid) == trim($testcatid)) {
            return true;
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

        // 本微博的分类ID
        $P['row_catid'] = $row->catid;

        // 参数的设置
        $P['catoption'] = array(); // 这个是对各个分类的设定

        $catp = array();
        $catp['catid'] = $this->params->get('catid') ? $this->params->get('catid') : 'all'; // 所指定的分类

        $catp['weibotype'] = $this->params->get('weibotype', 'fulltext'); // 微博发表方式（fulltext，onlytitle，introtext或者custom）
        $P['weibotype'] = $catp['weibotype']; // 缺省时也使用这个参数
        $catp['customstring'] = $this->params->get('customstring'); // 自定义的字符串
        $P['customstring'] = $catp['customstring'];
        $catp['picsend'] = $this->params->get('picsend', true); // 将文章中的第一幅图片发布到微博上
        $P['picsend'] = $catp['picsend'];

        $P['sinaenabled'] = $this->params->get('sinaenabled', false); //是否启用新浪微博
        $catp['sina'] = $P['sinaenabled'];
        $P['tencentenabled'] = $this->params->get('tencentenabled', false); //是否启用腾讯微博
        $catp['tencent'] = $P['tencentenabled'];
        $P['neteaseenabled'] = $this->params->get('neteaseenabled', false); //是否启用网易微博
        $catp['netease'] = $P['neteaseenabled'];
        $P['twitterenabled'] = $this->params->get('twitterenabled', false); //是否启用twitter
        $catp['twitter'] = $P['twitterenabled'];
        $catp['norecursive'] = 0; // 仅对1.6及以上版本有用，缺省时分类的判断是依据分类及子分类
        $P['norecursive'] = 0;

        $P['cat_option'][] = $catp;  // 加入分类设定之中

        $P['customoption'] = $this->params->get('customoption', true); // 获取高级自定义设置
        // 这里对customoption进行分解
        $optionlines = explode("\n", $P['customoption']);
        // 对于某一个类型微博指定类型的话，可以用
        //  catid=23,sina=1,tencent=1,weibotype=fulltext,picsend=0,customstring=aaaa
        foreach ($optionlines as $optionline) {
            $temp = explode(',', $optionline);
            $temp3 = array();
            foreach ($temp as $temp1) {
                $temp2 = explode('=', $temp1);
                if ($temp2[0])
                    $temp3[$temp2[0]] = $temp2[1] ? $temp2[1] : NULL;
            }
            if ($temp3['catid']) {
                // 目前，只有含有catid设置的才被认为是有意义的设置，其他的行都忽略
                $P['cat_option'][] = $temp3;
            }
        }

        $P['typeoption'] = array();
        // 对于每一个分类的设置，看看本次微博是不是属于此分类
        foreach ($P['cat_option'] as $option) {
            if (inCat($P['row_catid'], $option['catid'], $option['norecursive'])) {
                // 如果属于这类
                if ($option['sina']) {
                    $P['typeoption']['sina'] = $option;
                }
                if ($option['twitter']) {
                    $P['typeoption']['twitter'] = $option;
                }
                if ($option['tencent']) {
                    $P['typeoption']['tencent'] = $option;
                }
                if ($option['netease']) {
                    $P['typeoption']['netease'] = $option;
                }
            }
        }

        $rtntext = '';
        // 发送新浪微博
        $rtninfo = sendSinaWeibo($row, $P);
        if ($rtninfo) {
            $rtntext .= $rtninfo . ';';
        };

        // 发送腾讯微博
        $rtninfo = sendTencentWeibo($row, $P);
        if ($rtninfo) {
            $rtntext .= $rtninfo . ';';
        };

        // 发送网易微博
        $rtninfo = sendNeteaseWeibo($row, $P);
        if ($rtninfo) {
            $rtntext .= $rtninfo . ';';
        };

        // 发送网易微博
        $rtninfo = sendTwitterWeibo($row, $P);
        if ($rtninfo) {
            $rtntext .= $rtninfo;
        };
        if ($rtntext)
            $row->fulltext .= "<!-- Donot delete this comment 请勿删除此句 {" . $rtntext . '} -->';

        return true;
    }

}

