<?php

    // namespace
    namespace Plugin;

    // dependency check
    if (class_exists('\\Plugin\\Config') === false) {
        $link = 'https://github.com/onassar/TurtlePHP-ConfigPlugin';
        $msg = '*Config* class required. Please see ' . ($link);
        throw new \Exception($msg);
    }

    /**
     * Redirect
     * 
     * Manages HTTP/HTTPS redirect for TurtlePHP requests.
     * 
     * @author  Oliver Nassar <onassar@gmail.com>
     * @abstract
     */
    abstract class Redirect
    {
        /**
         * _configPath
         *
         * @access  protected
         * @var     string (default: 'config.default.inc.php')
         * @static
         */
        protected static $_configPath = 'config.default.inc.php';

        /**
         * _initiated
         *
         * @access  protected
         * @var     bool (default: false)
         * @static
         */
        protected static $_initiated = false;

        /**
         * _getConfigData
         * 
         * @access  protected
         * @static
         * @return  array
         */
        protected static function _getConfigData(): array
        {
            $key = 'TurtlePHP-RedirectPlugin';
            $configData = \Plugin\Config::retrieve($key);
            return $configData;
        }

        /**
         * _getFallbackURL
         * 
         * @access  protected
         * @static
         * @return  string
         */
        protected static function _getFallbackURL(): string
        {
            $protocol = 'https';
            $configData = static::_getConfigData();
            $host = $configData['fallbackHost'];
            $path = '/';
            $url = ($protocol) . '://' . ($host) . ($path);
            return $url;
        }

        /**
         * _getHTTPHost
         * 
         * @access  protected
         * @static
         * @return  null|string
         */
        protected static function _getHTTPHost(): ?string
        {
            $httpHost = $_SERVER['HTTP_HOST'] ?? null;
            return $httpHost;
        }

        /**
         * _getHTTPSRedirectURL
         * 
         * @throws  \Exception
         * @access  protected
         * @static
         * @return  string
         */
        protected static function _getHTTPSRedirectURL(): string
        {
            $protocol = 'https';
            $host = static::_getHTTPHost();
            if ($host === null) {
                $msg = 'Invalid HTTP Host property';
                throw new \Exception($msg);
            }
            $path = static::_getRequestURI() ?? '/';
            $url = ($protocol) . '://' . ($host) . ($path);
            return $url;
        }

        /**
         * _getRequestURI
         * 
         * @access  protected
         * @static
         * @return  null|string
         */
        protected static function _getRequestURI(): ?string
        {
            $path = $_SERVER['REQUEST_URI'] ?? null;
            return $path;
        }

        /**
         * _handleHostRedirect
         * 
         * Handles redirecting the request in cases where the HTTP_HOST value
         * is not in the whitelist of allowed hosts.
         * 
         * @access  protected
         * @static
         * @return  bool
         */
        protected static function _handleHostRedirect(): bool
        {
            $httpHost = static::_getHTTPHost();
            if ($httpHost === null) {
                return false;
            }
            $configData = static::_getConfigData();
            $allowedHosts = $configData['allowedHosts'];
            if (in_array($httpHost, $allowedHosts) === true) {
                return false;
            }
            $url = static::_getFallbackURL();
            $permanent = false;
            static::_redirect($url, $permanent);
            return true;
        }

        /**
         * _handleHTTPSRedirect
         * 
         * Handles redirecting the request in cases where the request is being
         * made insecurely (eg. via http and not https).
         * 
         * @access  protected
         * @static
         * @return  bool
         */
        protected static function _handleHTTPSRedirect(): bool
        {
            if (HTTPS === true) {
                return false;
            }
            $url = static::_getHTTPSRedirectURL();
            $permanent = true;
            static::_redirect($url, $permanent);
            return true;
        }

        /**
         * _loadConfigPath
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _loadConfigPath(): void
        {
            require_once static::$_configPath;
        }

        /**
         * _redirect
         * 
         * @access  protected
         * @static
         * @param   string $uri
         * @param   bool $permanent
         * @return  void
         */
        protected static function _redirect(string $uri, bool $permanent): void
        {
            if ($permanent === true) {
                header('HTTP/1.1 301 Moved Permanently');
            }
            $value = 'Location: ' . ($uri);
            header($value);
            exit(0);
        }

        /**
         * _setInitiated
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _setInitiated(): void
        {
            static::$_initiated = true;
        }

        /**
         * init
         * 
         * @access  public
         * @static
         * @return  bool
         */
        public static function init(): bool
        {
            if (static::$_initiated === true) {
                return false;
            }
            static::_setInitiated();
            static::_loadConfigPath();
            static::_handleHostRedirect();
            static::_handleHTTPSRedirect();
            return true;
        }

        /**
         * setConfigPath
         * 
         * @access  public
         * @param   string $configPath
         * @return  bool
         */
        public static function setConfigPath(string $configPath): bool
        {
            if (is_file($configPath) === false) {
                return false;
            }
            static::$_configPath = $configPath;
            return true;
        }
    }

    // Config path loading
    $info = pathinfo(__DIR__);
    $parent = ($info['dirname']) . '/' . ($info['basename']);
    $configPath = ($parent) . '/config.inc.php';
    Redirect::setConfigPath($configPath);
