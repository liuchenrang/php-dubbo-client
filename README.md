# php-dubbo-client
php client use dubbo Hessian protocol call remote method
包含所有依赖文件php5.x php7.x 都可以运行 ， 采用hessian协议

# 请注意 
本库依赖Dubbo Version2.7.(5-6),大于2.7.6由于更改参数描述过程，找不到对应方法，可以参考使用Dubbo2协议
dubbo版本是2.7.7以上版本 配置参数如下 
```php 
//config.php    
return $host = [
    'serverUser' => "slb://127.0.0.1:2100?timeout=2&dubbo_version=自己到服务端dubbo版本&version=1.0.0&port=2100&protocol=dubbo2",
];
```

```php

# 本项目改自 
- lornewang/idiot
- crazyxman/hessian-parser 
- icecave/

```php 
//config.php    
return $host = [
    'serverUser' => "slb://127.0.0.1:2100?timeout=2&dubbo_version=2.7.5&version=1.0.0&port=2100&protocol=dubbo",
];
```
```php
class DubboService
{
    public static $service = [];
    /**
         * @param string $fpath
         * @param int $timeout
         * @return mixed Dubbo
         */
        public static function getService($fpath = self::F_PATH_USER, $timeout = 0)
        {
            $conn = self::getConfigByServicePath($fpath);
            if (self::$service == null || empty(self::$service[$fpath])) {
                if (!empty($timeout)) {
                    $conn = self::reBuildQuery($conn, ['timeout' => $timeout]);
                }
                $options = [
                    "conn" => $conn,
                    "path" => $fpath
                ];
                self::$service[$fpath] = new Service($options);
            }
    
            return self::$service[$fpath];
        }
    
        /**
         * @param $url
         * @param $params (array)
         * @return string
         */
        public static function reBuildQuery($url, $params)
        {
            $get = [];
            parse_str(parse_url($url, PHP_URL_QUERY), $get);
            foreach ($params as $key => $value) {
                $get[$key] = $value;
            }
            $query = http_build_query($get);
            if (stristr($url, '?')) {
                $new_url = substr($url, 0, strrpos($url, '?'));
            } else {
                $new_url = $url;
            }
            if (empty($query)) {
                return $new_url;
            }
            return $new_url . '?' . $query;
        }
    
        public static function getConfigByServicePath($fpath)
        {
            $config = require("./config.php");
    
            if ($config[$fpath]) {
                return $config[$fpath];
            } else {
               throw new Exception("config miss");
            }
        }

}
```
```php

function callWithException($func, $name="", $args){
    try {
        $res = $func();
        $result = 0;
        $data = [];
        if (is_array($res)) {
            $result = $res[0];
            $data = $res[1];
        }//正常返回


        EsLog::info("methodRpc Success $name context " . json_encode($args) . " result " . json_encode($data));
        if ($result == 1 || $result == 4) {
            return json_decode(json_encode($data), true);
        } elseif ($result == 5 || $result == 2) {
            //业务接口返回null， 找不到相关信息，或者条件不满足情况
            return [];
        } else {
            if (is_array($res) && $res[0] == 3 ){
                $d = var_export($res, true);
                throw new Exception($d);
            }
            //接口其他情况
            $message = is_array($res) || is_object($res) ? json_encode($res) : $res;
            throw new Exception($message);
        }
    } catch (Exception $e) {
        //接口其他情况
        $message = $e->getMessage();
        $connectError = "Unexpected end of stream (state: 0).";
        EsLog::error("methodRpc $name context " . json_encode($args) . " errorMsg" . $message);
        //网络超时，连不上 都会报这个， 其他到, 读超时也会触发器这个错误信息
        if ($e->getMessage() == $connectError){
            throw  new Exception("ReConnect");
        }
        throw $e;
    }
}

function getXXXX($xxx)
{
    return self::callWithException(function () use($xxx){
        $service = self::getService("com.xxx.path");
        return $service->invoke('pingMethod', [
            Type::object("com.xx.Params", [
                "xxx" => "xx"
               
            ], "ping")
        ]);
    }, __METHOD__,   get_defined_vars());
}
```