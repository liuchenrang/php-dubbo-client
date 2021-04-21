<?php

namespace Idiot\Protocols;

use Icecave\Flax\Message\Decoder;
use Icecave\Flax\Serialization\Encoder;
use Icecave\Flax\DubboParser;
use Idiot\Languages\AbstractLanguage;
use Idiot\Protocols\Dubbo\DubboBuilder;
use Idiot\Protocols\Dubbo\DubboRequest;
use stdClass;
use Exception;
use Idiot\Adapter;
use Idiot\Type;
use Idiot\Utility;
use Icecave\Collections\Vector;
use Icecave\Chrono\DateTime;

class BaseDubbo extends AbstractProtocol
{
    /**
     * @var AbstractLanguage
     */
    protected $lang;
    private $debug=true;
    private function typeRefs(&$args)
    {
        $typeRefs = '';

        if (count($args)) {
            foreach ($args as &$arg) {
                if ($arg instanceof Type) {
                    $type = $arg->type;
                    $arg = $arg->value;
                } else {
                    $type = $this->argToType($arg);
                }
                $typeRefs .= $this->lang->typeRef($type);
            }
        }
        return $typeRefs;
    }
    public function connect($host, $port, $path, $method, $args, $group, $version, $dubboVersion = self::DEFAULT_DUBBO_VERSION,$urlInfo)
    {
        try {

            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
            socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO, array("sec" => 1, "usec" => 0));
            socket_connect($socket, $host, $port);
            $buffer = $this->buffer($path, $method, $args, $group, $version, $dubboVersion);
            if ($this->debug){
                file_put_contents("/tmp/dubbo_request.bin",$buffer);
            }
            socket_write($socket, $buffer, strlen($buffer));

            $data = '';
            $bl = 16;

            do {
                $chunk = @socket_read($socket, 1024);
                if (empty($data)) {
                    $arr = Utility::sliceToArray($chunk, 0, 16);
                    $i = 0;
                    while ($i < 3) {
                        $bl += array_pop($arr) * pow(256, $i++);
                    }
                }
                $data .= $chunk;
                if (empty($chunk) || strlen($data) >= $bl) {
                    break;
                }
            } while (true);
            if ($this->debug){
                file_put_contents("/tmp/dubbo_response.bin",$data);
            }
            socket_close($socket);
            return $this->parser($data);
        } catch (Exception $e) {
            $message = $data ? $this->rinser($data) : $e->getMessage();
            throw new Exception($message);
        }
    }

    private function rinser($data)
    {
        return substr($data, 17);
    }

    private function parser($data)
    {
        $decoder = new DubboParser();
        return $decoder->getData($data);
    }

    private function recursive($data)
    {
        if ($data instanceof Vector) {
            return $this->recursive($data->elements());
        }

        if ($data instanceof DateTime) {
            return $data->unixTime();
        }

        if ($data instanceof stdClass) {
            foreach ($data as $key => $value) {
                $data->$key = $this->recursive($value);
            }
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->recursive($value);
            }
        }

        return $data;
    }

    private function buffer($path, $method, $args, $group, $version, $dubboVersion)
    {
        $typeRefs = $this->typeRefs($args);
        $attachment = Type::object('java.util.HashMap', [
            'interface' => $path,
            'version' => $version,
            'group' => $group,
            'path' => $path,
            'timeout' => '60000'
        ]);

        $bufferBody = $this->bufferBody($path, $method, $typeRefs, $args, $attachment, $version, $dubboVersion);
        $length = strlen($bufferBody);
        $bufferHead = $this->bufferHead($length);
        return $bufferHead . $bufferBody;
    }

    private function bufferHead($length)
    {
        $head = [0xda, 0xbb, 0xc2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $i = 15;

        if ($length - 256 < 0) {
            $head[$i] = $length - 256;
        } else {
            while ($length - 256 >= 0) {
                $head[$i--] = $length % 256;
                $length = $length >> 8;
            }

            $head[$i] = $length;
        }

        return Utility::asciiArrayToString($head);
    }

    private function bufferBody($path, $method, $typeRefs, $args, $attachment, $version, $dubboVersion)
    {
        $body = '';
        $encoder = new Encoder();
        $body .= $encoder->encode($dubboVersion);
        $body .= $encoder->encode($path);
        $body .= $encoder->encode($version);
        $body .= $encoder->encode($method);
        $body .= $encoder->encode($typeRefs);
        foreach ($args as $arg) {
            $body .= $encoder->encode($arg);
        }
        $body .= $encoder->encode($attachment);
        return $body;
    }



    protected function argToType($arg)
    {
        switch (gettype($arg)) {
            case 'integer':
                return $this->numToType($arg);
            case 'boolean':
                return Type::BOOLEAN;
            case 'double':
                return Type::DOUBLE;
            case 'string':
                return Type::STRING;
            case 'object':
                return $arg->className();
            default:
                throw new Exception("Handler for type {$arg} not implemented");
        }
    }

    private function numToType($value)
    {
        if (Utility::isBetween($value, -32768, 32767)) {
            return Type::SHORT;
        } elseif (Utility::isBetween($value, -2147483648, 2147483647)) {
            return Type::INT;
        }

        return Type::LONG;
    }
}