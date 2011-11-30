<?php

define("TW_AKEY", 'TBbso69EKLPrI066LK7Ig');
define("TW_SKEY", '2F0yIv34VXlxIrbqlJTWMmWrlJz3GkMeXJlc0nN5hs');

/**
 * 此函数，返回一个供认证转移的URL
 */
function AuthUrlGet_twitter($path) {
    $tmhOAuth = new tmhOAuth(array(
                'consumer_key' => TW_AKEY,
                'consumer_secret' => TW_SKEY,
            ));
    $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/request_token', ''), array(
        'oauth_callback' => 'oob',
            ));
    if ($code == 200) {
        $oauth_creds = $tmhOAuth->extract_params($tmhOAuth->response['response']);
        // update with the temporary token and secret
        $tmhOAuth->config['user_token'] = $oauth_creds['oauth_token'];
        $tmhOAuth->config['user_secret'] = $oauth_creds['oauth_token_secret'];
        $_SESSION['tw_user_token'] = $oauth_creds['oauth_token'];
        $_SESSION['tw_user_secret'] = $oauth_creds['oauth_token_secret'];

        $aurl = $tmhOAuth->url('oauth/authorize', '') . "?oauth_token={$oauth_creds['oauth_token']}";
    } else {
        return false;
    }
    return $aurl;
}

/**
 * 此函数，供Callback处调用，如果返回false，认证失败，否则返回以下哈希表：
 *   last_key  ->  callback得到的last_key
 *   oauth_token ->  上述lastkey中的oauth_token
 *   oauth_token_secret -> 上述lastkey中的oauth_token_secret
 *   user_id -> 用户ID
 *   user_name ->  用户昵称
 *   user_email -> 暂不提供
 */
function AuthCallback_twitter() {

    $tmhOAuth = new tmhOAuth(array(
                'consumer_key' => TW_AKEY,
                'consumer_secret' => TW_SKEY,
            ));
    $tmhOAuth->config['user_token'] = $_SESSION['tw_user_token'];
    $tmhOAuth->config['user_secret'] = $_SESSION['tw_user_secret'];
    unset($_SESSION['tw_user_token']);
    unset($_SESSION['tw_user_secret']);
    $pin = JRequest::getCmd('oobpin');
    $code = $tmhOAuth->request('POST', $tmhOAuth->url('oauth/access_token', ''), array(
        'oauth_verifier' => trim($pin)
            ));

    if ($code == 200) {
        $oauth_creds = $tmhOAuth->extract_params($tmhOAuth->response['response']);

        $tmhOAuth->config['user_token'] = $oauth_creds['oauth_token'];
        $tmhOAuth->config['user_secret'] = $oauth_creds['oauth_token_secret'];

            $rtn = array();
            $rtn['last_key'] = $oauth_creds;
            $rtn['oauth_token'] = $oauth_creds['oauth_token'];
            $rtn['oauth_token_secret'] = $oauth_creds['oauth_token_secret'];
            $rtn['user_id'] = $oauth_creds['screen_name'];
            $rtn['user_name'] = $oauth_creds['screen_name'];
            return $rtn;
    } else {

        return false;
    }
}

// 以下程序是源自https://github.com/themattharris/tmhOAuth
// 由Matt Harris 所编写的tmhOAuth.php
/**
 * tmhOAuth
 *
 * An OAuth 1.0A library written in PHP.
 * The library supports file uploading using multipart/form as well as general
 * REST requests. OAuth authentication is sent using the an Authorization Header.
 *
 * @author themattharris
 * @version 0.56
 *
 * 29 September 2011
 */
class tmhOAuth {
    const VERSION = 0.56;

    /**
     * Creates a new tmhOAuth object
     *
     * @param string $config, the configuration to use for this request
     */
    function __construct($config) {
        $this->params = array();
        $this->headers = array();
        $this->auto_fixed_time = false;
        $this->buffer = null;

        // default configuration options
        $this->config = array_merge(
                array(
            'user_agent' => 'tmhOAuth ' . self::VERSION . ' - //github.com/themattharris/tmhOAuth',
            'consumer_key' => '',
            'consumer_secret' => '',
            'user_token' => '',
            'user_secret' => '',
            'use_ssl' => true,
            'host' => 'api.twitter.com',
            'debug' => false,
            'force_nonce' => false,
            'nonce' => false, // used for checking signatures. leave as false for auto
            'force_timestamp' => false,
            'timestamp' => false, // used for checking signatures. leave as false for auto
            'oauth_version' => '1.0',
            // you probably don't want to change any of these curl values
            'curl_connecttimeout' => 30,
            'curl_timeout' => 10,
            // for security you may want to set this to TRUE. If you do you need
            // to install the servers certificate in your local certificate store.
            'curl_ssl_verifypeer' => false,
            'curl_followlocation' => false, // whether to follow redirects or not
            // support for proxy servers
            'curl_proxy' => false, // really you don't want to use this if you are using streaming
            'curl_proxyuserpwd' => false, // format username:password for proxy, if required
            'curl_encoding' => '', // leave blank for all supported formats, else use gzip, deflate, identity
            // streaming API
            'is_streaming' => false,
            'streaming_eol' => "\r\n",
            'streaming_metrics_interval' => 60,
            // header or querystring. You should always use header
            // this is just to help me debug other developers
            // implementations
            'as_header' => true,
                ), $config
        );
    }

    /**
     * Generates a random OAuth nonce.
     * If 'force_nonce' is true a nonce is not generated and the value in the configuration will be retained.
     *
     * @param string $length how many characters the nonce should be before MD5 hashing. default 12
     * @param string $include_time whether to include time at the beginning of the nonce. default true
     * @return void
     */
    private function create_nonce($length=12, $include_time=true) {
        if ($this->config['force_nonce'] == false) {
            $sequence = array_merge(range(0, 9), range('A', 'Z'), range('a', 'z'));
            $length = $length > count($sequence) ? count($sequence) : $length;
            shuffle($sequence);

            $prefix = $include_time ? microtime() : '';
            $this->config['nonce'] = md5(substr($prefix . implode($sequence), 0, $length));
        }
    }

    /**
     * Generates a timestamp.
     * If 'force_timestamp' is true a nonce is not generated and the value in the configuration will be retained.
     *
     * @return void
     */
    private function create_timestamp() {
        $this->config['timestamp'] = ($this->config['force_timestamp'] == false ? time() : $this->config['timestamp']);
    }

    /**
     * Encodes the string or array passed in a way compatible with OAuth.
     * If an array is passed each array value will will be encoded.
     *
     * @param mixed $data the scalar or array to encode
     * @return $data encoded in a way compatible with OAuth
     */
    private function safe_encode($data) {
        if (is_array($data)) {
            return array_map(array($this, 'safe_encode'), $data);
        } else if (is_scalar($data)) {
            return str_ireplace(
                            array('+', '%7E'), array(' ', '~'), rawurlencode($data)
            );
        } else {
            return '';
        }
    }

    /**
     * Decodes the string or array from it's URL encoded form
     * If an array is passed each array value will will be decoded.
     *
     * @param mixed $data the scalar or array to decode
     * @return $data decoded from the URL encoded form
     */
    private function safe_decode($data) {
        if (is_array($data)) {
            return array_map(array($this, 'safe_decode'), $data);
        } else if (is_scalar($data)) {
            return rawurldecode($data);
        } else {
            return '';
        }
    }

    /**
     * Returns an array of the standard OAuth parameters.
     *
     * @return array all required OAuth parameters, safely encoded
     */
    private function get_defaults() {
        $defaults = array(
            'oauth_version' => $this->config['oauth_version'],
            'oauth_nonce' => $this->config['nonce'],
            'oauth_timestamp' => $this->config['timestamp'],
            'oauth_consumer_key' => $this->config['consumer_key'],
            'oauth_signature_method' => 'HMAC-SHA1',
        );

        // include the user token if it exists
        if ($this->config['user_token'])
            $defaults['oauth_token'] = $this->config['user_token'];

        // safely encode
        foreach ($defaults as $k => $v) {
            $_defaults[$this->safe_encode($k)] = $this->safe_encode($v);
        }

        return $_defaults;
    }

    /**
     * Extracts and decodes OAuth parameters from the passed string
     *
     * @param string $body the response body from an OAuth flow method
     * @return array the response body safely decoded to an array of key => values
     */
    function extract_params($body) {
        $kvs = explode('&', $body);
        $decoded = array();
        foreach ($kvs as $kv) {
            $kv = explode('=', $kv, 2);
            $kv[0] = $this->safe_decode($kv[0]);
            $kv[1] = $this->safe_decode($kv[1]);
            $decoded[$kv[0]] = $kv[1];
        }
        return $decoded;
    }

    /**
     * Prepares the HTTP method for use in the base string by converting it to
     * uppercase.
     *
     * @param string $method an HTTP method such as GET or POST
     * @return void value is stored to a class variable
     * @author themattharris
     */
    private function prepare_method($method) {
        $this->method = strtoupper($method);
    }

    /**
     * Prepares the URL for use in the base string by ripping it apart and
     * reconstructing it.
     *
     * Ref: 3.4.1.2
     *
     * @param string $url the request URL
     * @return void value is stored to a class variable
     * @author themattharris
     */
    private function prepare_url($url) {
        $parts = parse_url($url);

        $port = isset($parts['port']) ? $parts['port'] : false;
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = isset($parts['path']) ? $parts['path'] : false;

        $port or $port = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443')
                || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }
        $this->url = strtolower("$scheme://$host$path");
    }

    /**
     * Prepares all parameters for the base string and request.
     * Multipart parameters are ignored as they are not defined in the specification,
     * all other types of parameter are encoded for compatibility with OAuth.
     *
     * @param array $params the parameters for the request
     * @return void prepared values are stored in class variables
     */
    private function prepare_params($params) {
        // do not encode multipart parameters, leave them alone
        if ($this->config['multipart']) {
            $this->request_params = $params;
            $params = array();
        }

        // signing parameters are request parameters + OAuth default parameters
        $this->signing_params = array_merge($this->get_defaults(), (array) $params);

        // Remove oauth_signature if present
        // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
        if (isset($this->signing_params['oauth_signature'])) {
            unset($this->signing_params['oauth_signature']);
        }

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($this->signing_params, 'strcmp');

        // encode. Also sort the signed parameters from the POST parameters
        foreach ($this->signing_params as $k => $v) {
            $k = $this->safe_encode($k);
            $v = $this->safe_encode($v);
            $_signing_params[$k] = $v;
            $kv[] = "{$k}={$v}";
        }

        // auth params = the default oauth params which are present in our collection of signing params
        $this->auth_params = array_intersect_key($this->get_defaults(), $_signing_params);
        if (isset($_signing_params['oauth_callback'])) {
            $this->auth_params['oauth_callback'] = $_signing_params['oauth_callback'];
            unset($_signing_params['oauth_callback']);
        }

        // request_params is already set if we're doing multipart, if not we need to set them now
        if (!$this->config['multipart'])
            $this->request_params = array_diff_key($_signing_params, $this->get_defaults());

        // create the parameter part of the base string
        $this->signing_params = implode('&', $kv);
    }

    /**
     * Prepares the OAuth signing key
     *
     * @return void prepared signing key is stored in a class variables
     */
    private function prepare_signing_key() {
        $this->signing_key = $this->safe_encode($this->config['consumer_secret']) . '&' . $this->safe_encode($this->config['user_secret']);
    }

    /**
     * Prepare the base string.
     * Ref: Spec: 9.1.3 ("Concatenate Request Elements")
     *
     * @return void prepared base string is stored in a class variables
     */
    private function prepare_base_string() {
        $base = array(
            $this->method,
            $this->url,
            $this->signing_params
        );
        $this->base_string = implode('&', $this->safe_encode($base));
    }

    /**
     * Prepares the Authorization header
     *
     * @return void prepared authorization header is stored in a class variables
     */
    private function prepare_auth_header() {
        $this->headers = array();
        uksort($this->auth_params, 'strcmp');
        if (!$this->config['as_header']) :
            $this->request_params = array_merge($this->request_params, $this->auth_params);
            return;
        endif;

        foreach ($this->auth_params as $k => $v) {
            $kv[] = "{$k}=\"{$v}\"";
        }
        $this->auth_header = 'OAuth ' . implode(', ', $kv);
        $this->headers['Authorization'] = $this->auth_header;
    }

    /**
     * Signs the request and adds the OAuth signature. This runs all the request
     * parameter preparation methods.
     *
     * @param string $method the HTTP method being used. e.g. POST, GET, HEAD etc
     * @param string $url the request URL without query string parameters
     * @param array $params the request parameters as an array of key=value pairs
     * @param string $useauth whether to use authentication when making the request.
     */
    private function sign($method, $url, $params, $useauth) {
        $this->prepare_method($method);
        $this->prepare_url($url);
        $this->prepare_params($params);

        // we don't sign anything is we're not using auth
        if ($useauth) {
            $this->prepare_base_string();
            $this->prepare_signing_key();

            $this->auth_params['oauth_signature'] = $this->safe_encode(
                    base64_encode(
                            hash_hmac(
                                    'sha1', $this->base_string, $this->signing_key, true
                            )));

            $this->prepare_auth_header();
        }
    }

    /**
     * Make an HTTP request using this library. This method doesn't return anything.
     * Instead the response should be inspected directly.
     *
     * @param string $method the HTTP method being used. e.g. POST, GET, HEAD etc
     * @param string $url the request URL without query string parameters
     * @param array $params the request parameters as an array of key=value pairs
     * @param string $useauth whether to use authentication when making the request. Default true.
     * @param string $multipart whether this request contains multipart data. Default false
     */
    function request($method, $url, $params=array(), $useauth=true, $multipart=false) {
        $this->config['multipart'] = $multipart;

        $this->create_nonce();
        $this->create_timestamp();

        $this->sign($method, $url, $params, $useauth);
        return $this->curlit();
    }

    /**
     * Make a long poll HTTP request using this library. This method is
     * different to the other request methods as it isn't supposed to disconnect
     *
     * Using this method expects a callback which will receive the streaming
     * responses.
     *
     * @param string $method the HTTP method being used. e.g. POST, GET, HEAD etc
     * @param string $url the request URL without query string parameters
     * @param array $params the request parameters as an array of key=value pairs
     * @param string $callback the callback function to stream the buffer to.
     */
    function streaming_request($method, $url, $params=array(), $callback='') {
        if (!empty($callback)) {
            if (!function_exists($callback)) {
                return false;
            }
            $this->config['streaming_callback'] = $callback;
        }
        $this->metrics['start'] = time();
        $this->metrics['interval_start'] = $this->metrics['start'];
        $this->metrics['tweets'] = 0;
        $this->metrics['last_tweets'] = 0;
        $this->metrics['bytes'] = 0;
        $this->metrics['last_bytes'] = 0;
        $this->config['is_streaming'] = true;
        $this->request($method, $url, $params);
    }

    /**
     * Handles the updating of the current Streaming API metrics.
     */
    function update_metrics() {
        $now = time();
        if (($this->metrics['interval_start'] + $this->config['streaming_metrics_interval']) > $now)
            return false;

        $this->metrics['tps'] = round(($this->metrics['tweets'] - $this->metrics['last_tweets']) / $this->config['streaming_metrics_interval'], 2);
        $this->metrics['bps'] = round(($this->metrics['bytes'] - $this->metrics['last_bytes']) / $this->config['streaming_metrics_interval'], 2);

        $this->metrics['last_bytes'] = $this->metrics['bytes'];
        $this->metrics['last_tweets'] = $this->metrics['tweets'];
        $this->metrics['interval_start'] = $now;
        return $this->metrics;
    }

    /**
     * Utility function to create the request URL in the requested format
     *
     * @param string $request the API method without extension
     * @param string $format the format of the response. Default json. Set to an empty string to exclude the format
     * @return string the concatenation of the host, API version, API method and format
     */
    function url($request, $format='json') {
        $format = strlen($format) > 0 ? ".$format" : '';
        $proto = $this->config['use_ssl'] ? 'https:/' : 'http:/';

        // backwards compatibility with v0.1
        if (isset($this->config['v']))
            $this->config['host'] = $this->config['host'] . '/' . $this->config['v'];

        return implode('/', array(
                    $proto,
                    $this->config['host'],
                    $request . $format
                ));
    }

    /**
     * Public access to the private safe decode/encode methods
     *
     * @param string $text the text to transform
     * @param string $mode the transformation mode. either encode or decode
     * @return the string as transformed by the given mode
     */
    function transformText($text, $mode='encode') {
        return $this->{"safe_$mode"}($text);
    }

    /**
     * Utility function to parse the returned curl headers and store them in the
     * class array variable.
     *
     * @param object $ch curl handle
     * @param string $header the response headers
     * @return the string length of the header
     */
    private function curlHeader($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->response['headers'][$key] = $value;
        }
        return strlen($header);
    }

    /**
     * Utility function to parse the returned curl buffer and store them until
     * an EOL is found. The buffer for curl is an undefined size so we need
     * to collect the content until an EOL is found.
     *
     * This function calls the previously defined streaming callback method.
     *
     * @param object $ch curl handle
     * @param string $data the current curl buffer
     */
    private function curlWrite($ch, $data) {
        $l = strlen($data);
        if (strpos($data, $this->config['streaming_eol']) === false) {
            $this->buffer .= $data;
            return $l;
        }

        $buffered = explode($this->config['streaming_eol'], $data);
        $content = $this->buffer . $buffered[0];

        $this->metrics['tweets']++;
        $this->metrics['bytes'] += strlen($content);

        if (!function_exists($this->config['streaming_callback']))
            return 0;

        $metrics = $this->update_metrics();
        $stop = call_user_func(
                $this->config['streaming_callback'], $content, strlen($content), $metrics
        );
        $this->buffer = $buffered[1];
        if ($stop)
            return 0;

        return $l;
    }

    /**
     * Makes a curl request. Takes no parameters as all should have been prepared
     * by the request method
     *
     * @return void response data is stored in the class variable 'response'
     */
    private function curlit() {
        // method handling
        switch ($this->method) {
            case 'POST':
                break;
            default:
                // GET, DELETE request so convert the parameters to a querystring
                if (!empty($this->request_params)) {
                    foreach ($this->request_params as $k => $v) {
                        // Multipart params haven't been encoded yet.
                        // Not sure why you would do a multipart GET but anyway, here's the support for it
                        if ($this->config['multipart']) {
                            $params[] = $this->safe_encode($k) . '=' . $this->safe_encode($v);
                        } else {
                            $params[] = $k . '=' . $v;
                        }
                    }
                    $qs = implode('&', $params);
                    $this->url = strlen($qs) > 0 ? $this->url . '?' . $qs : $this->url;
                    $this->request_params = array();
                }
                break;
        }

        // configure curl
        $c = curl_init();
        curl_setopt_array($c, array(
            CURLOPT_USERAGENT => $this->config['user_agent'],
            CURLOPT_CONNECTTIMEOUT => $this->config['curl_connecttimeout'],
            CURLOPT_TIMEOUT => $this->config['curl_timeout'],
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => $this->config['curl_ssl_verifypeer'],
            CURLOPT_FOLLOWLOCATION => $this->config['curl_followlocation'],
            CURLOPT_PROXY => $this->config['curl_proxy'],
            CURLOPT_ENCODING => $this->config['curl_encoding'],
            CURLOPT_URL => $this->url,
            // process the headers
            CURLOPT_HEADERFUNCTION => array($this, 'curlHeader'),
            CURLOPT_HEADER => FALSE,
            CURLINFO_HEADER_OUT => true,
        ));

        if ($this->config['curl_proxyuserpwd'] !== false)
            curl_setopt($c, CURLOPT_PROXYUSERPWD, $this->config['curl_proxyuserpwd']);

        if ($this->config['is_streaming']) {
            // process the body
            $this->response['content-length'] = 0;
            curl_setopt($c, CURLOPT_TIMEOUT, 0);
            curl_setopt($c, CURLOPT_WRITEFUNCTION, array($this, 'curlWrite'));
        }

        switch ($this->method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($c, CURLOPT_POST, TRUE);
                break;
            default:
                curl_setopt($c, CURLOPT_CUSTOMREQUEST, $this->method);
        }

        if (!empty($this->request_params)) {
            // if not doing multipart we need to implode the parameters
            if (!$this->config['multipart']) {
                foreach ($this->request_params as $k => $v) {
                    $ps[] = "{$k}={$v}";
                }
                $this->request_params = implode('&', $ps);
            }
            curl_setopt($c, CURLOPT_POSTFIELDS, $this->request_params);
        } else {
            // CURL will set length to -1 when there is no data, which breaks Twitter
            $this->headers['Content-Type'] = '';
            $this->headers['Content-Length'] = '';
        }

        // CURL defaults to setting this to Expect: 100-Continue which Twitter rejects
        $this->headers['Expect'] = '';

        if (!empty($this->headers)) {
            foreach ($this->headers as $k => $v) {
                $headers[] = trim($k . ': ' . $v);
            }
            curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        }

        if (isset($this->config['prevent_request']) && false == $this->config['prevent_request'])
            return;

        // do it!
        $response = curl_exec($c);
        $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($c);
        curl_close($c);

        // store the response
        $this->response['code'] = $code;
        $this->response['response'] = $response;
        $this->response['info'] = $info;
        return $code;
    }

}

// 以下程序是源自https://github.com/themattharris/tmhOAuth
// 由Matt Harris 所编写的tmhUtilities.php

/**
 * tmhUtilities
 *
 * Helpful utility and Twitter formatting functions
 *
 * @author themattharris
 * @version 0.2
 *
 * 29 September 2011
 */
class tmhUtilities {

    /**
     * Entifies the tweet using the given entities element
     *
     * @param array $tweet the json converted to normalised array
     * @return the tweet text with entities replaced with hyperlinks
     */
    function entify($tweet, &$replacements=array()) {
        $encoding = mb_internal_encoding();
        mb_internal_encoding("UTF-8");

        $keys = array();
        // $replacements = array();
        $is_retweet = false;

        if (isset($tweet['retweeted_status'])) {
            $tweet = $tweet['retweeted_status'];
            $is_retweet = true;
        }

        if (!isset($tweet['entities'])) {
            return $tweet['text'];
        }

        // prepare the entities
        foreach ($tweet['entities'] as $type => $things) {
            foreach ($things as $entity => $value) {
                $tweet_link = "<a href=\"http://twitter.com/{$tweet['user']['screen_name']}/statuses/{$tweet['id']}\">{$tweet['created_at']}</a>";

                switch ($type) {
                    case 'hashtags':
                        $href = "<a href=\"http://twitter.com/search?q=%23{$value['text']}\">#{$value['text']}</a>";
                        break;
                    case 'user_mentions':
                        $href = "@<a href=\"http://twitter.com/{$value['screen_name']}\" title=\"{$value['name']}\">{$value['screen_name']}</a>";
                        break;
                    case 'urls':
                    case 'media':
                        $url = empty($value['expanded_url']) ? $value['url'] : $value['expanded_url'];
                        $display = isset($value['display_url']) ? $value['display_url'] : str_replace('http://', '', $url);
                        // Not all pages are served in UTF-8 so you may need to do this ...
                        $display = urldecode(str_replace('%E2%80%A6', '&hellip;', urlencode($display)));
                        $href = "<a href=\"{$value['url']}\">{$display}</a>";
                        break;
                }
                $keys[$value['indices']['0']] = mb_substr(
                        $tweet['text'], $value['indices']['0'], $value['indices']['1'] - $value['indices']['0']
                );
                $replacements[$value['indices']['0']] = $href;
            }
        }

        ksort($replacements);
        $replacements = array_reverse($replacements, true);
        $entified_tweet = $tweet['text'];
        foreach ($replacements as $k => $v) {
            // $entified_tweet = substr_replace($entified_tweet, $v, $k, strlen($keys[$k]));
            $entified_tweet = mb_substr($entified_tweet, 0, $k) . $v . mb_substr($entified_tweet, $k + strlen($keys[$k]));
        }
        $replacements = array(
            'replacements' => $replacements,
            'keys' => $keys
        );

        mb_internal_encoding($encoding);
        return $entified_tweet;
    }

    /**
     * Returns the current URL. This is instead of PHP_SELF which is unsafe
     *
     * @param bool $dropqs whether to drop the querystring or not. Default true
     * @return string the current URL
     */
    function php_self($dropqs=true) {
        $url = sprintf('%s://%s%s', empty($_SERVER['HTTPS']) ? (@$_SERVER['SERVER_PORT'] == '443' ? 'https' : 'http') : 'http', $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']
        );

        $parts = parse_url($url);

        $port = $_SERVER['SERVER_PORT'];
        $scheme = $parts['scheme'];
        $host = $parts['host'];
        $path = @$parts['path'];
        $qs = @$parts['query'];

        $port or $port = ($scheme == 'https') ? '443' : '80';

        if (($scheme == 'https' && $port != '443')
                || ($scheme == 'http' && $port != '80')) {
            $host = "$host:$port";
        }
        $url = "$scheme://$host$path";
        if (!$dropqs)
            return "{$url}?{$qs}";
        else
            return $url;
    }

    function is_cli() {
        return (PHP_SAPI == 'cli' && empty($_SERVER['REMOTE_ADDR']));
    }

    /**
     * Debug function for printing the content of an object
     *
     * @param mixes $obj
     */
    function pr($obj) {

        if (!self::is_cli())
            echo '<pre style="word-wrap: break-word">';
        if (is_object($obj))
            print_r($obj);
        elseif (is_array($obj))
            print_r($obj);
        else
            echo $obj;
        if (!self::is_cli())
            echo '</pre>';
    }

    /**
     * Make an HTTP request using this library. This method is different to 'request'
     * because on a 401 error it will retry the request.
     *
     * When a 401 error is returned it is possible the timestamp of the client is
     * too different to that of the API server. In this situation it is recommended
     * the request is retried with the OAuth timestamp set to the same as the API
     * server. This method will automatically try that technique.
     *
     * This method doesn't return anything. Instead the response should be
     * inspected directly.
     *
     * @param string $method the HTTP method being used. e.g. POST, GET, HEAD etc
     * @param string $url the request URL without query string parameters
     * @param array $params the request parameters as an array of key=value pairs
     * @param string $useauth whether to use authentication when making the request. Default true.
     * @param string $multipart whether this request contains multipart data. Default false
     */
    function auto_fix_time_request($tmhOAuth, $method, $url, $params=array(), $useauth=true, $multipart=false) {
        $tmhOAuth->request($method, $url, $params, $useauth, $multipart);

        // if we're not doing auth the timestamp isn't important
        if (!$useauth)
            return;

        // some error that isn't a 401
        if ($tmhOAuth->response['code'] != 401)
            return;

        // some error that is a 401 but isn't because the OAuth token and signature are incorrect
        // TODO: this check is horrid but helps avoid requesting twice when the username and password are wrong
        if (stripos($tmhOAuth->response['response'], 'password') !== false)
            return;

        // force the timestamp to be the same as the Twitter servers, and re-request
        $tmhOAuth->auto_fixed_time = true;
        $tmhOAuth->config['force_timestamp'] = true;
        $tmhOAuth->config['timestamp'] = strtotime($tmhOAuth->response['headers']['date']);
        return $tmhOAuth->request($method, $url, $params, $useauth, $multipart);
    }

    /**
     * Asks the user for input and returns the line they enter
     *
     * @param string $prompt the text to display to the user
     * @return the text entered by the user
     */
    function read_input($prompt) {
        echo $prompt;
        $handle = fopen("php://stdin", "r");
        $data = fgets($handle);
        return trim($data);
    }

    /**
     * Get a password from the shell.
     *
     * This function works on *nix systems only and requires shell_exec and stty.
     *
     * @param  boolean $stars Wether or not to output stars for given characters
     * @return string
     * @url http://www.dasprids.de/blog/2%VERSION%8/22/getting-a-password-hidden-from-stdin-with-php-cli
     */
    function read_password($prompt, $stars=false) {
        echo $prompt;
        $style = shell_exec('stty -g');

        if ($stars === false) {
            shell_exec('stty -echo');
            $password = rtrim(fgets(STDIN), "\n");
        } else {
            shell_exec('stty -icanon -echo min 1 time 0');
            $password = '';
            while (true) :
                $char = fgetc(STDIN);
                if ($char === "\n") :
                    break;
                elseif (ord($char) === 127) :
                    if (strlen($password) > 0) {
                        fwrite(STDOUT, "\x08 \x08");
                        $password = substr($password, 0, -1);
                    }
                    else
                        fwrite(STDOUT, "*");
                    $password .= $char;
                endif;
            endwhile;
        }

        // Reset
        shell_exec('stty ' . $style);
        echo PHP_EOL;
        return $password;
    }

    /**
     * Check if one string ends with another
     *
     * @param string $haystack the string to check inside of
     * @param string $needle the string to check $haystack ends with
     * @return true if $haystack ends with $needle, false otherwise
     */
    function endswith($haystack, $needle) {
        $haylen = strlen($haystack);
        $needlelen = strlen($needle);
        if ($needlelen > $haylen)
            return false;

        return substr_compare($haystack, $needle, -$needlelen) === 0;
    }

}
?>
