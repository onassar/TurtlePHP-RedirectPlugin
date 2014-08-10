<?php

    /**
     * Namespace
     * 
     */
    namespace Plugin\Redirect;

    /**
     * Config settings
     * 
     */
    $config = array(
        'domain' => 'local.accountdock.com',
        'https' => true
    );

    /**
     * Config storage
     * 
     */

    // Store
    \Plugin\Config::add(
        'TurtlePHP-RedirectPlugin',
        $config
    );
