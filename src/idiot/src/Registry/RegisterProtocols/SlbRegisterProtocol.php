<?php
namespace Idiot\Registry\RegisterProtocols;
use Idiot\Registry\Register;
use Redis;
class SlbRegisterProtocol implements Register{

    private  static $protocolPort = [

    ];
    public static function addProtocolPort($protocol,$port){
        self::$protocolPort[$protocol] = $port;
    }
    private $urlInfo;
    /**
     * NacosRegisterProtocol constructor.
     * @param $urlInfo
     */
    public function __construct($urlInfo)
    {
      $this->urlInfo = $urlInfo;
    }


    /**
     * Get a dubbo provider uri
     *
     * @param  string $path
     * @param  string $version
     * @return string
     */
    public function getProvider($path, $version = '', $protocol="dubbo")
    {
        $args = $this->urlInfo;
        $provider = parse_url($this->urlInfo['conn']);

        $dubboVersion = isset($args['dubboVersion'])?$args['dubboVersion']:"";
        if (empty($dubboVersion)){
            throw new \Exception("dubbo dubbo version missing");
        }
        if (empty($provider['port'])){
            throw new \Exception("dubbo port missing");
        }

        if ($provider['port'] != $args['port']){
            throw new \Exception("slb的服务地址和dubbo地址不一样！");
        }

        $url  = "{$protocol}://{$provider['host']}:{$provider['port']}?".$provider["query"];
        return $url;
    }
}