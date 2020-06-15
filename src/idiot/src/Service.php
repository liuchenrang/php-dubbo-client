<?php
/**
 * Idiot
 *  - Dubbo Client in Zookeeper.
 *
 * Licensed under the Massachusetts Institute of Technology
 *
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @author   Lorne Wang < post@lorne.wang >
 * @link     https://github.com/lornewang/idiot
 */

namespace Idiot;

use Exception;
use Idiot\Registry\RegisterProtocols\NacosRegisterProtocol;
use Idiot\Registry;
use Idiot\Registry\RegisterProtocols\RedisRegisterProtocol;
use Idiot\Registry\RegisterProtocols\SlbRegisterProtocol;

class Service
{
    private $conn = '';
    private $host = '';
    private $port = '';
    private $path = '';
    private $group = '';
    private $version = '';
    private $dubboVersion = '2.8.4';
    private $protocol = 'dubbo';
    public static $registryMap = [];
    public static function addRegistryMap($scheme,$class){
        self::$registryMap[$scheme] = $class;
    }
    public function __construct($options)
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        if (empty($this->host) || empty($this->port)) {

            $this->parseURItoProps(
                $this->getRegister($this->conn)->getProvider($this->path, $this->version,$this->protocol)
            );
        }
    }

    /**
     * @param url
     * @return Register
     * @throws Exception
     */
    public function getRegister($url)
    {
        $urlInfo = parse_url($url);

        $scheme = $urlInfo["scheme"];
        if (isset(self::$registryMap[$scheme])){
            return new self::$registryMap[$scheme]['class']($urlInfo);
        }
        switch ($scheme) {
            case 'zk':
                return new Zookeeper($this->conn);
            case 'nacos':
                return new NacosRegisterProtocol($urlInfo);
            case 'redis':
                return new RedisRegisterProtocol($urlInfo);
            case 'slb':
                return new SlbRegisterProtocol($urlInfo);
            default:
                throw new Exception("not support scheme " . $scheme);
        }
    }

    /**
     * Calls to the remote interface
     *
     * @param string $method
     * @param array $args
     * @return string
     */
    public function invoke($method, $args)
    {

        $proto = Adapter::protocol($this->protocol);
        return $proto->connect(
            $this->host,
            $this->port,
            $this->path,
            $method,
            $args,
            $this->group,
            $this->version,
            $this->dubboVersion
        );
    }

    /**
     * Parse the dubbo uri to this props
     *
     * @param string $uri
     * @return void
     */
    public function parseURItoProps($uri)
    {
        $info = parse_url(urldecode($uri));
        parse_str($info['query'], $params);
        isset($info['host']) AND $this->host = $info['host'];
        isset($info['port']) AND $this->port = $info['port'];
        isset($params['version']) AND $this->version = $params['version'];
        isset($params['dubbo']) && $params['dubbo'] AND $this->dubboVersion = $params['dubbo'];
    }
}