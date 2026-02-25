<?php

class Session
{

    public static function init()
    {
        @session_start();
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        if (isset($_SESSION[$key]))
            return $_SESSION[$key];
    }

    public static function destroy()
    {
        //unset($_SESSION);
        session_destroy();
    }

    /*
     * Set a value or create a child under parent session.
     */
    public static function pSet($parent, $key, $value)
    {
        $_SESSION[$parent][$key] = $value;
    }


    /*
     * Unset Just  Child
     */
    public static function cUnset($parent, $key)
    {
        unset($_SESSION[$parent][$key]);
    }


    /*
     * Unset Parent and everything under it
     */
    public static function pUnset($key)
    {
        unset($_SESSION[$key]);
    }


    /**
     *
     * @param parameter $key
     * @return Session
     */
    public static function pGet($parent, $key)
    {
        if (isset($_SESSION[$parent][$key]))
            return $_SESSION[$parent][$key];
    }
}