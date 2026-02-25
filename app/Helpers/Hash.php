<?php

class Hash
{
    
    /**
     *
     * @param string $algo The algorithm (md5, sha1, whirlpool, etc)
     * @param string $data The data to encode
     * @param string $salt The salt (This should be the same throughout the system probably)
     * @return string The hashed/salted data
     */
    public static function create($algo, $data, $salt)
    {
        $context = hash_init($algo, HASH_HMAC, $salt);
        hash_update($context, $data);
        
        return hash_final($context);
        
    }

    public static function createApi(){
        $key = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 30), 6));
        return $key;
    }

    public static function formData(){
        $key = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))), 0, 15), 5));
        return $key;
    }
}