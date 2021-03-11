# php-dubbo-client
php client use dubbo Hessian protocol call remote method
包含所有依赖文件php5.x php7.x 都可以运行

# 请注意 
本库依赖Dubbo Version2.7.5,大于2.7.5由于更改参数描述过程，找不到对应方法，可以参考使用Dubbo2协议

# 本项目改自 
- lornewang/idiot
- crazyxman/hessian-parser 
- icecave/collections

```php
$options = [
    "conn" => "slb://{$ip}:{$port}?timeout=2&dubbo=2.7.5",
    "path" => 'xxx.sss.service',
    "protocol" => $protocol,
];
$options["group"] = 'user';
$options["version"] = '1.0.1';
SlbRegisterProtocol::addProtocolPort("dubbo", $port);
$service = new Service($options);
$result = $service->invoke('ping', [
]);
if (is_array($result)) {
    list($result, $resp) = $result;
    if ($result == 1 || $result == 4) {
        return $resp;
    } else {
        Log::error('error:' . $resp->detailMessage);
    }
}else{
    Log::error('error:' . $result);
    return false;
}
```