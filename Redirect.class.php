<?php

    // namespace
    namespace Plugin;

    // dependency check
    if (class_exists('\\Plugin\\Config') === false) {
        throw new \Exception(
            '*Config* class required. Please see ' .
            'https://github.com/onassar/TurtlePHP-ConfigPlugin'
        );
    }

    /**
     * Database
     * 
     * Redirect (http/https) plugin for TurtlePHP
     * 
     * @author   Oliver Nassar <onassar@gmail.com>
     * @abstract
     */
    abstract class Redirect
    {
        /**
         * _configPath
         *
         * @var    string
         * @access protected
         * @static
         */
        protected static $_configPath = 'config.default.inc.php';

        /**
         * _initiated
         *
         * @var    boolean
         * @access protected
         * @static
         */
        protected static $_initiated = false;

        /**
         * _httpRedirect
         * 
         * @access protected
         * @static
         * @param  string $domain
         * @return void
         */
        protected static function _httpRedirect($domain)
        {
            $current = $_SERVER['HTTP_HOST'];
            if ($current !== $domain) {
                $uri = 'http://' . ($domain) . ($_SERVER['REQUEST_URI']);
                header('Location: ' . ($uri));
                exit(0);
            }
        }

        /**
         * _httpsRedirect
         * 
         * @access protected
         * @static
         * @return void
         */
        protected static function _httpsRedirect()
        {
            // non-secure
            if (HTTPS === false) {
                $url = 'https://' . ($_SERVER['HTTP_HOST']) . ($_SERVER['REQUEST_URI']);

                // exclude for facebook (like count)
                if (strstr($_SERVER['HTTP_USER_AGENT'], 'facebook') === false) {
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: ' . ($url));
                    exit(0);
                }
            }
        }

        /**
         * init
         * 
         * @access public
         * @static
         * @return void
         */
        public static function init()
        {
            if (is_null(self::$_initiated) === false) {
                self::$_initiated = true;
                require_once self::$_configPath;
                $config = \Plugin\Config::retrieve();
                $config = $config['TurtlePHP-RedirectPlugin'];

                // redirects
                self::_httpRedirect($config['domain']);
                if ($config['https'] === true) {
                    self::_httpsRedirect();
                }
            }
        }

        /**
         * setConfigPath
         * 
         * @access public
         * @param  string $path
         * @return void
         */
        public static function setConfigPath($path)
        {
            self::$_configPath = $path;
        }
    }

    // Config
    $info = pathinfo(__DIR__);
    $parent = ($info['dirname']) . '/' . ($info['basename']);
    $configPath = ($parent) . '/config.inc.php';
    if (is_file($configPath)) {
        Redirect::setConfigPath($configPath);
    }
