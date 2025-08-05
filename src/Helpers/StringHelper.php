<?php

namespace App\Helpers;

class StringHelper
{
    public static function normalizeToAscii(string $string)
    {
        // intl extension needs to be decommented in the php.ini
        $ascii = transliterator_transliterate('NFKC; [:Nonspacing Mark:] Remove; NFKC; Any-Latin; Latin-ASCII', $string);
        if($ascii === false)
            return null;
        
        return $ascii;
    }
}
