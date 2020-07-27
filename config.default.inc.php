<?php

    /**
     * Namespace
     * 
     */
    namespace Plugin\Redirect;

    /**
     * Plugin Config Data
     * 
     */
    $pluginConfigData = array(
        'allowedHosts' => array(
            'local.example.com'
        ),
        'fallbackHost' => 'local.example.com'
    );

    /**
     * Storage
     * 
     */
    $key = 'TurtlePHP-RedirectPlugin';
    \Plugin\Config::add($key, $pluginConfigData);
