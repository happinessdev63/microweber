<?php


namespace Microweber\Providers\Shop;
use DB;

class OrderManager {


    /** @var \Microweber\Application */
    public $app;


    public $table = 'cart_orders';

    function __construct($app = null) {
        if (is_object($app)){
            $this->app = $app;
        } else {
            $this->app = mw();
        }
    }


    public function get($params = false) {

        $params2 = array();
        if ($params==false){
            $params = array();
        }
        if (is_string($params)){
            $params = parse_str($params, $params2);
            $params = $params2;
        }
        if (defined('MW_API_CALL') and $this->app->user_manager->is_admin()==false){
            if (!isset($params['payment_verify_token'])){
                $params['session_id'] = mw()->user_manager->session_id();
            }
        }
        if (isset($params['keyword'])){
            $params['search_in_fields'] = array('first_name', 'last_name', 'email', 'city', 'state', 'zip', 'address', 'address2', 'phone', 'promo_code');
        }
        $table = $table = $this->table;
        $params['table'] = $table;
         return $this->app->database_manager->get($params);

    }

    public function get_by_id($id = false) {


        $table = $this->table;
        $params['table'] = $table;
        $params['one'] = true;

        $params['id'] = intval($id);

        $item = $this->app->database_manager->get($params);

        if (is_array($item) and isset($item['custom_fields_data']) and $item['custom_fields_data']!=''){
            $item = $this->app->format->render_item_custom_fields_data($item);
        }

        return $item;

    }

    public function get_items($order_id = false) {
        return $this->app->cart_manager->get_by_order_id($order_id);
    }


    public function delete_order($data) {
        // this function also handles ajax requests from admin

        $adm = $this->app->user_manager->is_admin();

        if (defined('MW_API_CALL') and $adm==false){
            return $this->app->error('Not logged in as admin.' . __FILE__ . __LINE__);
        }
        $table = $table = $this->table;
        if (!is_array($data)){
            $data = array('id' => intval($data));
        }
        if (isset($data['is_cart']) and trim($data['is_cart'])!='false' and isset($data['id'])){
            $this->app->cart_manager->delete_cart('session_id=' . $data['id']);

            return $data['id'];
        } else if (isset($data['id'])){
            $c_id = intval($data['id']);
            $this->app->database_manager->delete_by_id($table, $c_id);
            $this->app->event_manager->trigger('mw.cart.delete_order', $c_id);
            $this->app->cart_manager->delete_cart('order_id=' . $data['id']);

            return $c_id;
        }

    }
    
    

    public function place_order($place_order) {

        $sid = mw()->user_manager->session_id();
        if ($sid==false){
            return $sid;
        }

        $ord = $this->app->database_manager->save($this->table, $place_order);
        $place_order['id'] = $ord;

        DB::transaction(function () use ($sid, $ord, $place_order) {
            DB::table($this->app->cart_manager->table_name())->whereOrderCompleted(0)->whereSessionId($sid)->update(['order_id' => $ord]);

            if (isset($place_order['order_completed']) and $place_order['order_completed']==1){
                DB::table($this->app->cart_manager->table_name())->whereOrderCompleted(0)->whereSessionId($sid)->update(['order_id' => $ord, 'order_completed' => 1]);

                if (isset($place_order['is_paid']) and $place_order['is_paid']==1){
                    DB::table($this->table)->whereOrderCompleted(0)->whereSessionId($sid)->whereId($ord)->update(['order_completed' => 1]);
                }
                $this->app->cache_manager->delete('cart');
                $this->app->cache_manager->delete('cart_orders');
                if (isset($place_order['is_paid']) and $place_order['is_paid']==1){
                    $this->app->shop_manager->update_quantities($ord);
                }
                $this->app->shop_manager->after_checkout($ord);
            }
        });

        mw()->user_manager->session_set('order_id', $ord);

        return $ord;
    }


    /**
     * update_order
     *
     * updates order by parameters
     *
     * @package        modules
     * @subpackage     shop
     * @subpackage     shop\orders
     * @category       shop module api
     */
    public function save($params = false) {

        $params2 = array();
        if ($params==false){
            $params = array();
        }
        if (is_string($params)){
            $params = parse_params($params);
        }

        if (isset($params['is_paid'])){
            if ($params['is_paid']=='y'){
                $params['is_paid'] = 1;
            } elseif ($params['is_paid']=='n') {
                $params['is_paid'] = 0;
            }
        }

        $table = $this->table;
        $params['table'] = $table;
        $this->app->cache_manager->delete('cart_orders');

        return $this->app->database_manager->save($table, $params);
    }

} 