<?php


namespace Weber\Controllers;

use Weber\View;
use Weber\Utils\Installer;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

use Module;

class DefaultController extends Controller
{



    public function index()
    {


        $is_installed = Config::get('weber.is_installed');

        if (!$is_installed) {
            return $this->install();
        }



      var_dump(__FILE__.__LINE__);


//        $connection = wb()->config->get('database.connections');
//       // var_dump($connection);
//
//
////        $connection = Config::set('weber.is_installed', 1);
////        var_dump($connection);
////
////
////        $connection = Config::get('weber.is_installed');
////        var_dump($connection);
//
//        $connection = wb()->config->save();
//        var_dump($connection);
//        var_dump(__METHOD__);
//        exit;
    }

    public function install()
    {


        $view = WB_PATH . 'Views/install.php';

        $connection = Config::get('database.connections');
        $layout = new View($view);
        $is_installed = Config::get('weber.is_installed');

        if($is_installed){
            App::abort(403, 'Unauthorized action. Weber is already installed.');
        }

        $layout->assign('data', $connection);
        $layout->assign('done', $is_installed);
        $layout = $layout->__toString();

        $input = Input::all();

        if (isset($input['is_installed'])) {
            if (!isset($input['db_pass'])) {
                $input['db_pass'] = '';
            }
            if (!isset($input['table_prefix'])) {
                $input['table_prefix'] = '';
            }
            $errors = array();
            if (!isset($input['db_host'])) {
                $errors[] = 'Parameter "db_host" is required';
            } else {
                $input['db_host'] = trim($input['db_host']);
            }


            if (!isset($input['db_name'])) {
                $errors[] = 'Parameter "db_name" is required';
            } else {
                $input['db_name'] = trim($input['db_name']);
            }
            if (!isset($input['db_user'])) {
                $errors[] = 'Parameter "db_user" is required';
            }

            if (!isset($input['admin_email'])) {
                $errors[] = 'Parameter "admin_email" is required';
            }
            if (!isset($input['admin_password'])) {
                $errors[] = 'Parameter "admin_password" is required';
            }
            if (!isset($input['admin_username'])) {
                $errors[] = 'Parameter "admin_username" is required';
            }

            $mysql = Config::get('database.connections.mysql');
            if (!empty($errors)) {
                //$msg = array('message',implode("\n",$errors),'error'=>$errors);
                return implode("\n", $errors);
            }

            Config::set('database.connections.mysql.host', $input['db_host']);
            Config::set('database.connections.mysql.username', $input['db_user']);
            Config::set('database.connections.mysql.password', $input['db_pass']);
            Config::set('database.connections.mysql.database', $input['db_name']);
            Config::set('database.connections.mysql.prefix', $input['table_prefix']);
            Config::save();

            $install_finished = false;

            $mysql = Config::get('database.connections.mysql');
            try {
                DB::connection()->getDatabaseName();
            } catch (\PDOException $e) {
                return ('Error: ' . $e->getMessage() . "\n");
            } catch (\Exception $e) {
                return ('Error: ' . $e->getMessage() . "\n");
            }

            $installer = new Installer();
            $installer->run();
            $is_installed = Config::set('weber.is_installed',1);
            Config::save();
            print 'done';

        }



        return $layout;
    }

    public function admin()
    {

        $view = WB_PATH . 'Views/admin.php';
        var_dump(__FILE__.__LINE__);
        $layout = new View($view);
        $layout = $layout->__toString();
return $layout;


        //$modules=  Module::all()->where('installed',1)->take(10);
//
//        foreach($modules as $module){
//            d($module['name']);
//        }
      //  $modules=  Module::tags('my-tag')->remember(5)->get();

//        dd(antzz());
//
//        $modules = Module::has('notifications')->get();
//
//        dd($modules);

    }


    public function error($m)
    {

    }

    public function api()
    {

        var_dump(__METHOD__);
        exit;
    }
}
