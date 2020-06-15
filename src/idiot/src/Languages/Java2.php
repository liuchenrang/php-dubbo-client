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
namespace Idiot\Languages;

use Exception;
use Idiot\Type;

class Java2 extends AbstractLanguage
{
    private $typeRefsMap = [
        Type::SHORT => 'Ljava/lang/Short;',
        Type::INT => 'Ljava/lang/Integer;',
        Type::LONG => 'Ljava/lang/Long;',
        Type::FLOAT => 'Ljava/lang/Float;',
        Type::DOUBLE => 'Ljava/lang/Double;',
        Type::BOOLEAN => 'Ljava/lang/Boolean;',
        Type::STRING => 'Ljava/lang/String;'
    ];

    public function typeRef($type)
    {
        return (strpos($type, '.') === FALSE ? $this->typeRefsMap[$type] : 'L' . str_replace('.', '/', $type) . ';');
    }
}