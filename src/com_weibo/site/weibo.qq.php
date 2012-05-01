<?php

/**
 * 此函数，返回一个供认证转移的URL
 */
function AuthUrlGet_qq($path) {
    $_SESSION['state'] = md5(uniqid(rand(), TRUE));
    $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
            . $_SESSION['appid'] . "&redirect_uri=" . urlencode($path)
            . "&state=" . $_SESSION['state']
            . "&scope=" . $_SESSION['scope'];
    $_SESSION["callback"] = $path;

    return $login_url;
}

/**
 * 此函数，供Callback处调用，如果返回false，认证失败，否则返回以下哈希表：
 *   last_key  ->  callback得到的last_key
 *   oauth_token ->  上述lastkey中的oauth_token
 *   oauth_token_secret -> 上述lastkey中的oauth_token_secret
 *   user_id -> 用户ID(注意不是QQ号，是QQ的openid)
 *   user_name ->  用户昵称
 *   user_email -> 暂不提供
 */
function AuthCallback_qq() {

    if ($_REQUEST['state'] == $_SESSION['state']) { //csrf
        $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
                . "client_id=" . $_SESSION["appid"] . "&redirect_uri=" . urlencode($_SESSION["callback"])
                . "&client_secret=" . $_SESSION["appkey"] . "&code=" . $_REQUEST["code"];

        $response = get_url_contents($token_url);
        if (strpos($response, "callback") !== false) {
            /* $lpos = strpos($response, "(");
              $rpos = strrpos($response, ")");
              $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
              $msg = json_decode($response);
              if (isset($msg->error)) {
              echo "<h3>error:</h3>" . $msg->error;
              echo "<h3>msg  :</h3>" . $msg->error_description;
              exit;
              } */
            return null;
        }
    }

    $params = array();
    parse_str($response, $params);

    //debug
    //print_r($params);
    //set access token to session
    $_SESSION["access_token"] = $params["access_token"];

    $graph_url = "https://graph.qq.com/oauth2.0/me?access_token="
            . $_SESSION['access_token'];

    $str = get_url_contents($graph_url);
    if (strpos($str, "callback") !== false) {
        $lpos = strpos($str, "(");
        $rpos = strrpos($str, ")");
        $str = substr($str, $lpos + 1, $rpos - $lpos - 1);
    }

    $user = json_decode($str);
    if (isset($user->error)) {
        return null;
    }

    //debug
    //echo("Hello " . $user->openid);
    //set openid to session
    $_SESSION["openid"] = $user->openid;

    $rtn = array();
    $get_user_info = "https://graph.qq.com/user/get_user_info?"
            . "access_token=" . $_SESSION['access_token']
            . "&oauth_consumer_key=" . $_SESSION["appid"]
            . "&openid=" . $_SESSION["openid"]
            . "&format=json";

    $info = get_url_contents($get_user_info);
    $arr = json_decode($info, true);
    $rtn['user_id'] = $user->openid;
    $rtn['user_name'] = $arr["nickname"];
    //$rtn['user_email'] = $rtn['user_id'] . '@openid_qq';
    //$rtn['user_email'] = 'qqqqq'. '@openid_qq';
    //$rtn['user_id'] = 'aaaaaaa';
    return $rtn;
}

function get_url_contents($url) {
    if (ini_get("allow_url_fopen") == "1")
        return file_get_contents($url);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}