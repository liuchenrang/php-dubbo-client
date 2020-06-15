<?php

namespace Idiot\Registry\RegisterProtocols;

use Idiot\Registry\Register;
use Exception;
class NacosRegisterProtocol implements Register
{

    private $urlInfo;

    /**
     * NacosRegisterProtocol constructor.
     * @param $urlInfo
     */
    public function __construct($urlInfo)
    {
        $this->urlInfo = $urlInfo;
    }

    public function openUrl($url)
    {
        $curlH = curl_init();
        curl_setopt($curlH, CURLOPT_URL, $url);
        curl_setopt($curlH, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curlH);
        curl_close($curlH);
        return $result;

    }

    /**
     * Get a dubbo provider uri
     *
     * @param string $path
     * @param string $version
     * @return string
     */
    public function getProvider($path, $version = '', $protocol = "dubbo")
    {
        //127.0.0.1:8848/nacos/v1/ns/instance/list?serviceName=nacos.test.1
        $versionReq = $version;
        if ($version){
            $versionReq = "{$version}:";
        }
        $url = "http://".$this->urlInfo['host'].":".$this->urlInfo['port']."/nacos/v1/ns/instance/list?serviceName=providers:{$path}:" . $versionReq;
        $result =$this->openUrl($url);
        if ($result){
            $providersData = json_decode($result,1);
        }else{
            $providersData = [];
        }
        if (empty($providersData['hosts']) || count($providersData['hosts']) < 1) {
            throw new Exception("Can not find the zoo: {$path} , please check dubbo service.");
        }
        $providers = $providersData['hosts'];
        $url = "";
        foreach ($providers as $provider) {
            $args = $provider['metadata'];
            if ($version && isset($args['version']) && $version == $args['version'] && $args['protocol'] == $protocol) {
                $url  = "{$args['protocol']}://{$provider['ip']}:{$provider['port']}?version={$args['version']}&dubbo={$args['dubbo']}";
                break;
            }
        }

        return $url;
    }
}