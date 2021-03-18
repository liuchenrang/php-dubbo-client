<?php


namespace Idiot\Protocols;


use Icecave\Flax\UniversalObject;
use Idiot\Type;
use Idiot\Utils\NamingUniversalObject;

class Rest extends AbstractProtocol
{

    public static $pathToRestCallback;

    /**
     * Rest constructor.
     */
    public function __construct()
    {
        if (!isset(Rest::$pathToRestCallback)) {
            Rest::$pathToRestCallback = function ($path) {
                return $path;
            };
        }
    }

    public static function setPathToRestCallback($callback)
    {
        self::$pathToRestCallback = $callback;
    }

    public function connect($host, $port, $path, $method, $args, $group, $version, $dubboVersion, $urlInfo)
    {
        if (self::$pathToRestCallback) {
            $callback = Rest::$pathToRestCallback;
            $path = $callback($path);
        }
        $url = "http://" . $host . ":" . $port . "/$path/$method";
        if (count($args) == 1 && $args[0] instanceof Type) {
            $args = $args[0]->value;
        } elseif (count($args) == 1 && $args[0] instanceof UniversalObject) {
            $rawRags = [];
            $object = $args[0]->object();
            foreach ($object as $k => $v){
                $rawRags[$k] = $v;
            }
            $args = $rawRags;
        } elseif (count($args) > 0) {
            $rawRags = [];
            foreach ($args as $arg) {
                if ($arg instanceof Type) {
                    if (isset($arg->name)) {
                        $rawRags[$arg->name] = $arg->value;
                    } else {
                        $rawRags[] = $arg->value;
                    }
                } elseif($arg instanceof NamingUniversalObject){
                    $rawRags[$arg->getName()] = $arg->object();
                }
            }
            $args = $rawRags;
        }
        //  //curl -X POST -H "Content-Type:application/json" -H "Dubbo-Attachments: remote.application=demoProvider2" -H "host:192.168.20.93:8099" --data '1' http://127.0.0.1:8099/user/find
        $attachments = [
            'group' => $group,
            'version' => $version,
            'dubboVersion' => "",
        ];
        $data = json_encode($args);
        $curlPost = $this->curlPost($url, $data, [
            'Dubbo-Attachments:' . http_build_query($attachments),
            'Content-Type:' . 'application/json',
            'Content-Length:' . strlen($data)
        ]);
        return [1,$curlPost];
        // TODO: Implement connect() method.
    }

    public function curlPost($url, $data, $header)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);  //curl可以直接
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header); //设置响应头
//        curl_setopt($curl, CURLOPT_PROXY, "192.168.20.93:8888");
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $data = curl_exec($curl);
        curl_close($curl);
        if ($data){
            return json_decode($data);
        }
        return null;


    }
}