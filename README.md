TurtlePHP-RedirectPlugin
======================

### Sample plugin loading:
``` php
require_once APP . '/plugins/TurtlePHP-BasePlugin/Base.class.php';
require_once APP . '/plugins/TurtlePHP-RedirectPlugin/Redirect.class.php';
$path = APP . '/config/plugins/redirect.inc.php';
TurtlePHP\Plugin\Redirect::setRedirectPath($path);
TurtlePHP\Plugin\Redirect::init();
```
