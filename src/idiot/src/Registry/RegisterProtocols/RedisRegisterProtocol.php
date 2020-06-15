<?php
namespace Idiot\Registry\RegisterProtocols;

use Idiot\Registry\Register;
use Redis;
use Exception;
class RedisRegisterProtocol implements Register{
    /**
     * @var Redis
     */
    private $redis;
    private $urlInfo;
    /**
     * NacosRegisterProtocol constructor.
     * @param $urlInfo
     */
    public function __construct($urlInfo)
    {
        $redis = new Redis();
        $redis->connect($urlInfo["host"],$urlInfo['port']);
        if (isset($urlInfo["query"])) {
            parse_str(isset($urlInfo["query"]),$query);
            if (isset($query['password'])){
                $redis->auth($query['password']);
            }
        }
        $this->redis = $redis;
    }


    /**
     * Get a dubbo provider uri
     *
     * @param  string $path
     * @param  string $version
     * @return string
     */
    public function getProvider($path, $version = '',$protocol="dubbo")
    {
        $providersData = @$this->redis->hGetAll("/dubbo/{$path}/providers");
        $providers = is_array($providersData)? array_keys($providersData):[];
        if (count($providers) < 1)
        {
            throw new Exception("Can not find the zoo: {$path} , please check dubbo service.");
        }

        foreach ($providers as $provider)
        {
            $info = parse_url(urldecode($provider));
            parse_str($info['query'], $args);
    
            if ($version && isset($args['version']) && $version == $args['version'])
            {
                break;
            }
        }

        return $provider;
    }
}