<?php

    // Namespace overhead
    namespace TurtlePHP\Plugin;

    /**
     * Redirect
     * 
     * Redirect plugin for TurtlePHP.
     * 
     * Manages redirecting requests based on a variety of conditions, including:
     * - from HTTP to HTTPS
     * - from a CDN to the configured-host (to prevent page mirroring)
     * - from an invalid (non-whitelisted) host to the configured-host
     * 
     * @author  Oliver Nassar <onassar@gmail.com>
     * @abstract
     * @extends Base
     */
    abstract class Redirect extends Base
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
         * _checkDependencies
         * 
         * @access  protected
         * @static
         * @return  void
         */
        protected static function _checkDependencies(): void
        {
            static::_checkConfigPluginDependency();
        }

        /**
         * _getFallbackHost
         * 
         * @access  protected
         * @static
         * @return  string
         */
        protected static function _getFallbackHost(): string
        {
            $configData = static::_getConfigData();
            $host = $configData['fallbackHost'];
            if (is_callable($host) === false) {
                return $host;
            }
            $params = array();
            $host = call_user_func_array($host, $params);
            return $host;
        }

        /**
         * _getFallbackURL
         * 
         * @access  protected
         * @static
         * @param   string $path (default: '/')
         * @return  string
         */
        protected static function _getFallbackURL(string $path = '/'): string
        {
            $protocol = 'https';
            $configData = static::_getConfigData();
            $host = static::_getFallbackHost();
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
         * _getHTTPVia
         * 
         * @access  protected
         * @static
         * @return  null|string
         */
        protected static function _getHTTPVia(): ?string
        {
            $httpVia = $_SERVER['HTTP_VIA'] ?? null;
            return $httpVia;
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
         * _handleCDNRedirect
         * 
         * Redirects any CDN requests that hit PHP back to the fallback host (to
         * prevent accidentally mirroring a site).
         * 
         * @access  protected
         * @static
         * @return  bool
         */
        protected static function _handleCDNRedirect(): bool
        {
            static::_handleCloudFrontRedirect();
            return false;
        }

        /**
         * _handleCloudFrontRedirect
         * 
         * Redirects any CloudFront requests that hit PHP back to the fallback
         * host (to prevent accidentally mirroring a site).
         * 
         * @access  protected
         * @static
         * @return  bool
         */
        protected static function _handleCloudFrontRedirect(): bool
        {
            if (static::_isCloudFrontRequest() === false) {
                return false;
            }
            $protocol = 'https';
            $host = static::_getFallbackHost();
            $path = static::_getRequestURI() ?? '/';
            $url = ($protocol) . '://' . ($host) . ($path);
            $permanent = true;
            static::_redirect($url, $permanent);
            return true;
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
            $path = static::_getRequestURI() ?? '/';
            $url = static::_getFallbackURL($path);
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
         * _isCloudFrontRequest
         * 
         * @access  protected
         * @static
         * @return  bool
         */
        protected static function _isCloudFrontRequest(): bool
        {
            $httpVia = static::_getHTTPVia() ?? '';
            $needle = 'CloudFront';
            $found = strpos($httpVia, $needle) !== false;
            return $found;
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
                $value = 'HTTP/1.1 301 Moved Permanently';
                static::_setHeader($value);
            }
            $value = 'Location: ' . ($uri);
            static::_setHeader($value);
            exit(0);
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
            parent::init();
            static::_handleCDNRedirect();
            static::_handleHostRedirect();
            static::_handleHTTPSRedirect();
            return true;
        }
    }

    // Config path loading
    $info = pathinfo(__DIR__);
    $parent = ($info['dirname']) . '/' . ($info['basename']);
    $configPath = ($parent) . '/config.inc.php';
    \TurtlePHP\Plugin\Redirect::setConfigPath($configPath);
