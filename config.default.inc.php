<?php

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
    TurtlePHP\Plugin\Config::set($key, $pluginConfigData);
