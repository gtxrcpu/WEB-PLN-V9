<?php

namespace {
    /**
     * Laravel helper functions for IDE autocomplete
     */

    /**
     * Generate a url for the application
     * @param  string  $path
     * @param  mixed   $parameters
     * @param  bool    $secure
     * @return string
     */
    function route($name, $parameters = [], $absolute = true)
    {
    }

    /**
     * Get / set the specified session value
     * @param  string|array  $key
     * @param  mixed  $default
     * @return mixed
     */
    function session($key = null, $default = null)
    {
    }

    /**
     * Create a collection from the given value
     * @param  mixed  $value
     * @return \Illuminate\Support\Collection
     */
    function collect($value = null)
    {
    }

    /**
     * Retrieve old input or return default
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function old($key = null, $default = null)
    {
    }

    /**
     * Get the configuration value
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
    }

    /**
     * Get the authenticated user
     * @param  string|null  $guard
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    function auth($guard = null)
    {
    }

    /**
     * Generate asset path
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    function asset($path, $secure = null)
    {
    }

    /**
     * Abort the application
     * @param  int     $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     */
    function abort($code, $message = '', array $headers = [])
    {
    }

    /**
     * Get view instance
     * @param  string|null  $view
     * @param  array   $data
     * @param  array   $mergeData
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    function view($view = null, $data = [], $mergeData = [])
    {
    }

    /**
     * Create redirect response
     * @param  string  $to
     * @param  int     $status
     * @param  array   $headers
     * @param  bool    $secure
     * @return \Illuminate\Http\RedirectResponse
     */
    function redirect($to = null, $status = 302, $headers = [], $secure = null)
    {
    }

    /**
     * Get request instance
     * @param  array|string  $key
     * @param  mixed  $default
     * @return \Illuminate\Http\Request|string|array
     */
    function request($key = null, $default = null)
    {
    }

    /**
     * Translate the given message
     * @param  string  $key
     * @param  array   $replace
     * @param  string  $locale
     * @return string
     */
    function __($key = null, $replace = [], $locale = null)
    {
    }
}
