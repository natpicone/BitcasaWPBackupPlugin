<?php
class BACKUP_Extension_Manager
{
    const API_VERSION = 0;
    const API_KEY = '7121664d208a603de9d93e564adfcd0a';

    private
        $objectCache = array(),
        $extensionsCache,
        $new_site = true
        ;

    public static function construct()
    {
        return new self();
    }

    public function __construct()
    {
        $extensions = get_option('wpb2b-premium-extensions');

        if (is_array($extensions)) {
            foreach ($extensions as $name => $file) {
                if (file_exists(EXTENSIONS_DIR . $file)) {

                    //Support for pre PHP 5.3
                    if (!function_exists('spl_autoload_register')) {
                        require_once $file;
                    }

                    $this->get_instance($name);
                }
            }
        }
    }

    public function get_url($api = false)
    {
        if (defined('BACKUP_URL')) {
            $url =  BACKUP_URL;
        } else {
            $url = 'http://extendy.com';
        }

        if ($api) {
            $url .= '/' . self::API_VERSION;
        }

        return $url;
    }

    public function get_install_url()
    {
        return 'admin.php?page=backup-to-bitcasa-premium';
    }

    public function get_buy_url()
    {
        return $this->get_url() . '/buy';
    }

    public function get_extensions()
    {
        if (!$this->extensionsCache) {
            $params = array(
                'apikey' => self::API_KEY,
                'site' => get_site_url(),
            );

            $response = wp_remote_get("{$this->get_url(true)}/products?" . http_build_query($params));

            if (is_wp_error($response)) {
                throw new Exception(__('There was an error getting the list of premium extensions', 'wpbtd'));
            }

            $this->extensionsCache = json_decode($response['body'], true);
        }

        return $this->extensionsCache;
    }

    public function is_installed($name)
    {
        return $this->get_instance($name);
    }

    public function install($name)
    {
        if (!defined('FS_METHOD')) {
            define('FS_METHOD', 'direct');
        }

        WP_Filesystem();

        $params = array(
            'apikey' => self::API_KEY,
            'name' => $name,
            'site' => get_site_url(),
            'version' => BACKUP_TO_BITCASA_VERSION,
        );

        $download_file = download_url("{$this->get_url(true)}/download?" . http_build_query($params));

        if (is_wp_error($download_file)) {
            $errorMsg = strtolower($download_file->get_error_message());
            if ($errorMsg == 'Forbidden') {
                $errorMsg = __('access is deined, this could be because your payment has expired.', 'wpbtd');
            } elseif (!$errorMsg) {
                $errorMsg = __('you have exceeded your download limit for this extension on this site.', 'wpbtd');
            }

            throw new Exception(__('There was an error downloading your premium extension because', 'wpbtd') . " $errorMsg");
        }

        $result = unzip_file($download_file, EXTENSIONS_DIR);
        if (is_wp_error($result)) {
            $errorCode = $result->get_error_code();
            $errorMsg = strtolower($result->get_error_message());

            if (in_array($errorCode, array('copy_failed', 'incompatible_archive'))) {
                $errorMsg = sprintf(__("'%s' is not writeable.", 'wpbtd'), EXTENSIONS_DIR);
            }

            throw new Exception(__('There was an error installing your premium extension because', 'wpbtd') . " $errorMsg");
        }

        unlink($download_file);

        $extensions = get_option('wpb2b-premium-extensions');

        $extensions[$name] = str_replace(' ', '', ucwords($name)) . '.php';

        update_option('wpb2b-premium-extensions', $extensions);

        //Support for pre PHP 5.3
        if (!function_exists('spl_autoload_register')) {
            require_once EXTENSIONS_DIR . $extensions[$name];
        }

        return $this->get_instance($name);
    }

    public function get_output()
    {
        foreach ($this->objectCache as $obj) {
            if ($obj && $obj->get_type() == BACKUP_Extension_Base::TYPE_OUTPUT && $obj->is_enabled()) {
                return $obj;
            }
        }

        return $this->get_instance('DefaultOutput');
    }

    public function add_menu_items()
    {
        foreach ($this->objectCache as $obj) {
            $title = $obj->get_menu();
            $slug = $this->get_menu_slug($obj);
            $func = $this->get_menu_func($obj);

            add_submenu_page('backup-to-bitcasa', $title, $title, 'activate_plugins', $slug, $func);
        }
    }

    public function complete()
    {
        $this->call('complete');
    }

    public function failure()
    {
        $this->call('failure');
    }

    public function get_menu_slug($obj)
    {
        return str_replace('_', '-', strtolower(get_class($obj)));
    }

    public function get_menu_func($obj)
    {
        return strtolower(get_class($obj));
    }

    private function call($func)
    {
        foreach ($this->objectCache as $obj) {
            if ($obj && $obj->is_enabled()) {
                $obj->$func();
            }
        }
    }

    private function get_instance($name)
    {
        $class = 'BACKUP_Extension_' . str_replace(' ', '', ucwords($name));

        if (!isset($this->objectCache[$class])) {
            if (!class_exists($class)) {
                return false;
            }

            $this->objectCache[$class] = new $class();
        }

        return $this->objectCache[$class];
    }
}
