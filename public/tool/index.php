<?php
error_reporting(0);
//ini_set('display_errors','on');
//error_reporting(E_ALL);

$act = !empty($_GET['act'])? $_GET['act']: '';
if(!empty($act) && $act == 'ajaxinterface'){
    $data = '';
    $txt_url = addslashes(trim(filter_input(INPUT_POST, 'txt_url', FILTER_SANITIZE_STRING)));
    $txt_parm = stripslashes(trim($_POST['txt_parm']));
    $method = addslashes(trim(filter_input(INPUT_POST, 'method', FILTER_SANITIZE_STRING)));
    $method = in_array($method, array('GET','POST'))? $method: 'GET';

    if(empty($txt_url) || preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $txt_url)){
        //这个写法有报错：preg_match('/^(http|https|ftp)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?/i', $txt_url)
        $data .= '无效的 URI: 此 URI 为空。';
    }
    $tmparr=parse_url($txt_url);
    // print_r($tmparr);exit;
    $port = !empty($tmparr['port'])? ':'.$tmparr['port']:'';
    $rstr=empty($tmparr['scheme'])?'http://':$tmparr['scheme'].'://';
    $rstr.=$tmparr['host'].$port.$tmparr['path'];
    $txt_url = $rstr.'?';
    $tool = new ToolBase();
    $json = false;
    if ($tool->isJson($txt_parm)) {
        $params = json_decode($txt_parm, true);
        $json = true;
        $params = http_build_query($params);
    } else {
        parse_str($txt_parm, $params);
    }
    if ($method == 'GET') {
        if ($json) { $txt_parm = http_build_query($params);}
        $txt_url = $txt_url.'&'.$txt_parm;
    }

    //print_r($params);
    //exit;
    $data = $tool->http($txt_url, $method, $params);
    if(empty($data)){
        $data = '无效的 URI 或 参数错误。';
    }
    //$data=preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $data);
    $data=preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function( '$matches', 'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'), $data);
    echo $data;
}else{
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>*{font-size: 12px;}</style>
<script src="js/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function () {
    $("#btnSubmit").live("click",function(){
        var txt_url = $("#txt_url").val();
        var txt_parm = $("#txt_parm").val();
        var method = $("#method").val();
        $.post("/tool/index.php?act=ajaxinterface",{txt_url:txt_url,txt_parm:txt_parm,method:method},function(data){
				$("#result").val(data);
		},"data");
    });
});
</script>
</head>
<body>
    <div style="width: 800px; min-height: 400px; border: 10px solid #f3f3f3; margin: 30px auto 0px auto;
        padding: 10px 8px; font-size: 13px; font-family: " 微软雅黑"">
        <p>
接口地址:<br>
            <input name="txt_url" id="txt_url" style="width: 99%" type="text" value="http://lumen.widoli.com">
        </p>
        <p>
数据参数:<br>
            <textarea name="txt_parm" id="txt_parm" style="width: 99%; height: 100px; color: blue; border: 1px solid #ccc" replaceholder="123"></textarea>
        </p>
        <p>
            <select name="method" id="method">
				<option value="POST">POST</option>
				<option selected="selected" value="GET">GET</option>

			</select>
            &nbsp; &nbsp;
            <input name="btnSubmit" value="Submit" id="btnSubmit" style="border: 1px solid green; background-color: #fff;
                width: 80px; height: 23px;" type="submit">
        </p>
        <p>
返回结果（Json）:
            <br>
            <textarea name="result" id="result" style="width: 99%; height: 200px; color: red;border: 1px solid #ccc"></textarea>
        </p>
    </div>

    <div id="prompt" style="width: 800px; border: 10px solid rgb(243, 243, 243); margin: 5px auto; padding: 10px 8px; font-size: 13px; font-family: &quot; 微软雅黑&quot;;">
        <b>接口地址：</b>
        <br>
        <p>
如：http://www.test.com/api.php 或 https://www.test.com/api.php
        </p>
        <b>数据参数：</b>
        <br>
        <p>
如：api=get_phone_code&key=&args={"phone":13761343721}</p>
        <p>或
            {"api":"user_login","username":"huang","password":"111111"}</p>
            <p>或
[{"api":"user_login","username":"huang1","password":"111111"},{"api":"user_login","username":"huang2","password":"111111"}]
        </p>
        <b>返回结果为Json格式 </b>
        <br>

    </div>

</body></html>';
}

//http://t.dgc.com/api.php?api=show_article



/**
 * @ignore
 */
class OAuthException extends Exception {
    // pass
}

class ToolBase
{
    /**
     * Contains the last HTTP status code returned.
     *
     * @ignore
     */
    public $http_code;
    /**
     * Contains the last API call.
     *
     * @ignore
     */
    public $url;
    /**
     * Set timeout default.
     *
     * @ignore
     */
    public $timeout = 30;
    /**
     * Set connect timeout.
     *
     * @ignore
     */
    public $connecttimeout = 30;
    /**
     * Verify SSL Cert.
     *
     * @ignore
     */
    public $ssl_verifypeer = FALSE;

    /**
     * Contains the last HTTP headers returned.
     *
     * @ignore
     */
    public $http_info;
    /**
     * Set the useragnet.
     *
     * @ignore
     */
    public $useragent = 'OAuth2 v1.0';

    /**
     * print the debug info
     *
     * @ignore
     */
    public $debug = FALSE;

    /**
     * boundary of multipart
     * @ignore
     */
    public static $boundary = '';

    /**
     * params of file
     * @ignore
     */

    public static $params_file = array(
        'pic',
        'image',
        'attachment',
        'attachment1',
        'attachment2',
        'attachment3',
        'attachment4',
    );

    /**
     * construct OAuth object
     */
    public function __construct()
    {
    }

    public function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Make an HTTP request
     *
     * @return string API results
     * @ignore
     */
    public function http($url, $method, $postfields = NULL, $headers = array())
    {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_ENCODING, "");
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);

        curl_setopt($ci, CURLOPT_COOKIESESSION, true);
        //curl_setopt($ci, CURLOPT_COOKIEFILE, $cookie_jar);
        //curl_setopt($ci, CURLOPT_COOKIEJAR, $cookie_jar);

        if (version_compare(phpversion(), '5.4.0', '<')) {
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 1);
        } else {
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        if (!empty($this->remote_ip)) {
            $headers[] = "API-RemoteIP: " . $this->remote_ip;
        } else {
            $headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);

        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;

        if ($this->debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);

            echo "=====headers======\r\n";
            print_r($headers);

            echo '=====request info=====' . "\r\n";
            print_r(curl_getinfo($ci));

            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return $response;
    }

    /**
     * Get the header info to store.
     *
     * @return int
     * @ignore
     */
    public function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }

}
?>