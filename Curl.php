<?php
/**
 * Created on : 2018-05-24 09:21:01 星期四
 * Encoding   : UTF-8
 * Description: PHP简单CURL请求，恩，我只是想要个简单的CURL请求类库，那么重干啥
 *
 * 用法：
 * Curl::get('http://www.baidu.com', ['id' => 1], ['Header: xxx', 'Header1: 111'])
 * Curl::post('http://www.baidu.com', ['id' => 1], ['Header: xxx', 'Header1: 111'])
 *
 * @author    @qii404 <qii404.me>
 */

class Curl
{
    /**
     * 请求超时 毫秒
     */
    const REQUEST_TIMEOUT = 2000;

    /**
     * 连接超时 毫秒
     */
    const CONNECT_TIMEOUT = 500;

    /**
     * 最大重定向次数
     */
    const MAX_REDIRECT = 2;

    /**
     * @var resource curl实例
     */
    private static $curl;

    /**
     * get请求 自动追加参数到url中，$url参数有参数也无所谓，按照&追加
     *
     * @param string $url     请求地址
     * @param array  $params  get参数 ['p1' => 11, 'p2' => 22]
     * @param array  $headers 请求头 ['Content-type: text/plain', 'Content-length: 100']
     *
     * @return bool|mixed
     */
    public static function get($url, array $params = [], array $headers = [])
    {
        // 拼接get参数
        if ($params) {
            $url .= ((strpos($url, '?') === false) ? '?' : '&') . http_build_query($params);
        }

        return self::request('get', $url, [], $headers);
    }

    /**
     * post请求
     *
     * @param string $url     请求地址
     * @param array  $params  post参数 ['p1' => 11, 'p2' => 22]
     * @param array  $headers 请求头 ['Content-type: text/plain', 'Content-length: 100']
     *
     * @return bool|mixed
     */
    public static function post($url, array $params = [], array $headers = [])
    {
        return self::request('post', $url, $params, $headers);
    }

    /**
     * 直接request请求
     *
     * @param string $method  请求方法 get post等
     * @param string $url     请求地址
     * @param array  $params  body参数 ['p1' => 11, 'p2' => 22]
     * @param array  $headers 请求头 ['Content-type: text/plain', 'Content-length: 100']
     *
     * @return bool|mixed
     */
    private static function request($method, $url, array $params = [], array $headers = [])
    {
        // 获取curl实例
        $curl = self::getCurl();

        // 设置url
        curl_setopt($curl, CURLOPT_URL, $url);
        // 设置header
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // 需要填充body数据的方法处理
        switch ($method) {
            case 'post':
            case 'put':
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
        }

        // 开始请求
        try {
            $result = curl_exec($curl);
        } catch (\Exception $e) {
            // 记录日志，如果需要，替换为你自己的log实现即可
            file_put_contents('/tmp/php-curl.log', "Curl Exec Failed: Url: {$url}, Params: " . json_encode($params) . "\n");

            return false;
        }

        // http状态码
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // 200正常返回
        if ($httpCode === 200) {
            return $result;
        }

        // 非200 状态
        $msg = curl_error($curl);

        // 记录日志，如果需要，替换为你自己的log实现即可
        file_put_contents('/tmp/php-curl.log', "Curl HttpCode {$httpCode}: Msg: {$msg}, Url: {$url}, Params: " . json_encode($params) . "\n");

        return false;
    }

    /**
     * function getCurl
     *
     * @param bool $forceNew 强制使用新实例
     *
     * @return resource curl
     */
    private static function getCurl($forceNew = false)
    {
        if ($forceNew || !is_resource(self::$curl)) {
            self::$curl = self::newCurl();
        }

        // 每次请求重置curl状态
        self::resetCurl();

        return self::$curl;
    }

    /**
     * 新创建curl实例
     *
     * @return resource
     */
    private static function newCurl()
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, self::REQUEST_TIMEOUT);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, self::CONNECT_TIMEOUT);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // redirect
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_MAXREDIRS, self::MAX_REDIRECT);

        // ipv4 only
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        return $curl;
    }

    /**
     * 重置curl状态
     */
    private static function resetCurl()
    {
        // url
        curl_setopt(self::$curl, CURLOPT_URL, '');
        // header
        curl_setopt(self::$curl, CURLOPT_HTTPHEADER, []);
        // body数据
        curl_setopt(self::$curl, CURLOPT_POSTFIELDS, []);
    }
}

// var_dump(Curl::get('http://www.baidu.com'));
