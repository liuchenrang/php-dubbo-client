<?php
require "vendor/autoload.php";

use Idiot\Service;
use Idiot\Type;
global $argv;
$protocol = $argv[1];
$options = [
//    "conn" => "redis://127.0.0.1:6379",
    "path" => "com.xxx.UserBusiness",
    "version" => "1.0.0",
    "protocol" => $protocol,
];

\Idiot\Registry\RegisterProtocols\SlbRegisterProtocol::addProtocolPort("dubbo","18099");
\Idiot\Registry\RegisterProtocols\SlbRegisterProtocol::addProtocolPort("rest","8099");
$service = new Service($options);
\Idiot\Protocols\Rest::setPathToRestCallback(function ($path) {
    //dubbo path to rest path
    return "user";
});
try {


    list($result,$data)  = $service->invoke('xxxMethod', [
         Type::object("com.*.object",[

         ],"paramName"),

    ]);

    var_dump($result);
    var_dump($data);
} catch (Exception $e) {
    echo $e->getMessage();
}
//$data = $service->invoke('findByStatus', [new Type(Type::LONG,1, "id"),new Type(Type::INTEGER,1, "isDelete")]);
//$data = $service->invoke('find', [new Type(Type::LONG,1,null)]);
