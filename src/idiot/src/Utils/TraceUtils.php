<?php


namespace Idiot\Utils;


class TraceUtils
{
    public static $traceId = '';

    public static function getTraceId()
    {
        return self::$traceId;
    }

    public static function setTraceId($unionId)
    {
        self::$traceId = 'T_' . $unionId . '_' . time() . '_0';
        header('traceid:' . self::$traceId);
    }
}