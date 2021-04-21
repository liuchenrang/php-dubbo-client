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
namespace Idiot\Protocols;

use Icecave\Flax\Message\Decoder;
use Icecave\Flax\Serialization\Encoder;
use Icecave\Flax\DubboParser ;
use Idiot\Protocols\Dubbo\DubboBuilder;
use Idiot\Protocols\Dubbo\DubboRequest;
use stdClass;
use Exception;
use Idiot\Adapter;
use Idiot\Type;
use Idiot\Utility;
use Icecave\Collections\Vector;
use Icecave\Chrono\DateTime;

class Dubbo2 extends BaseDubbo
{
    const DEFAULT_LANGUAGE = 'Java2';
    /**
     * Dubbo2 constructor.
     */
    public function __construct()
    {
         $lang = Adapter::language(self::DEFAULT_LANGUAGE);
    }



}