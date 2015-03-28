<?php
namespace Microweber\Providers;

use Illuminate\Config\Repository;
use Symfony\Component\Finder\Finder;
use \File;

class ConfigSave extends Repository
{
    protected $beforeSave = [];
    protected $changed_keys = array();
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
        $items = (array)$app->make('config');
        $items = end($items);
        parent::__construct($items);
        $this->init();
    }

    private function init()
    {
        $this->items = array();
        $overrides = [];
        foreach (Finder::create()->files()->name('*.php')->in($this->app->configPath()) as $file) {
            $path = $file->getRelativePath();
            $key = basename($file->getRealPath(), '.php');
            if ($path && $path != $this->app->environment()) {
                continue;
            } else if ($path) {
                $overrides[$key] = $file->getRealPath();
                continue;
            }
            $this->set($key, require $file->getRealPath());
        }
        foreach ($overrides as $key => $path) {
            $this->set($key, require $path);
        }
    }

    public function set($key, $val = null)
    {
        $this->changed_keys[$key] = $val;
        return parent::set($key, $val);
    }

    public function get($key, $val = null)
    {
        if (isset($this->changed_keys[$key])) {
          //  return $this->changed_keys[$key];
        }
        return parent::get($key, $val);
    }

    public function save($allowed = array())
    {
        // Aggregating files array from changed keys
        $aggr = array();
        foreach ($this->changed_keys as $key => $value) {
            array_set($aggr, $key, $value);
        }

        // $allow_in_cli = array('database', 'microweber');
        // Preparing data
        foreach ($aggr as $file => $items) {


            $path = $this->app->configPath() . '/' . $this->app->environment() . '/';
            if (!is_dir($path)) {
                $path = $this->app->configPath() . '/';
            }


            $to_save = true;
			
            if(is_string($allowed)){
				if($file != $allowed){
					$to_save = false;
				}
			} elseif (!empty($allowed)) {
                if (!in_array($file, $allowed)) {
                    $to_save = false;
                }
            }
			
			
            if ($to_save) {
                if (!file_exists($path)) {
                    File::makeDirectory($path);
                }
                $path .= $file . '.php';
                $val = var_export($this->items[$file], true);
                $code = '<?php return ' . $val . ';';
                // Storing data
                File::put($path, $code);
            }
        }
    }

    protected function callBeforeSave($namespace, $group, $items)
    {
        $callback = $this->beforeSave[$namespace];
        return call_user_func($callback, $this, $group, $items);
    }

    public function beforeSaving($namespace, Closure $callback)
    {
        $this->beforeSave[$namespace] = $callback;
    }

    protected function parseCollection($collection)
    {
        list($namespace, $group) = explode('::', $collection);
        if ($namespace == '*') $namespace = null;
        return [$namespace, $group];
    }

    public function getBeforeSaveCallbacks()
    {
        return $this->beforeSave;
    }
}
