<?php

namespace Gracious\Interconnect\Support\Text;


abstract class Inflector
{
    /**
     * @param $text
     * @return mixed
     */
    public static function unSnakeCase($text)
    {
        if ($text === null) {
            return null;
        }

        return preg_replace('/_/', ' ', $text);
    }
}