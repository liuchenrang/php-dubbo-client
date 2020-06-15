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
        $args = [];
        if (isset($this->urlInfo['query'])){
            parse_str($this->urlInfo['query'],$args);
        }
        $provider = $this->urlInfo;
        $dubbo = isset($args['dubbo'])?$args['dubbo']:"";
        if (isset(self::$protocolPort[$protocol])){
            $provider['port'] = self::$protocolPort[$protocol];
        }
        $url  = "{$protocol}://{$provider['host']}:{$provider['port']}?version={$version}&dubbo={$dubbo}";
        return $url;
    }
}