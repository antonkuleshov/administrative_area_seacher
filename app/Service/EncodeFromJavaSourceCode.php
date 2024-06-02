<?php

namespace Service;

/*
* https://www.fileformat.info/info/unicode/char/00e4/index.htm
**/
class EncodeFromJavaSourceCode
{
    public function __invoke(string $string): string
    {
        $decoded = preg_replace('/\/u([a-fA-F0-9]{4})/', '&#x\\1;', $string);
        return mb_convert_encoding($decoded, 'ISO-8859-10', 'UTF-8');
    }
}