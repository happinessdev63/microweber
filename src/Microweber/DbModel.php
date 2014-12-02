<?php

class DbModel extends BaseModel
{

    protected $table = '';

    // called once when Post is first used
    public static function boot()
    {
        // there is some logic in this method, so don't forget this!
        parent::boot();
    }


    function save_item($params)
    {

        $params = $this->set_params($params);
        return parent::save_item($params);
    }


    function save_data($table, $params)
    {
        $this->table = $table;

        return parent::save_item($params);
    }

    function items($params)
    {

        $params = $this->set_params($params);
        return parent::items($params);
    }


    public function set_params($params)
    {
        if (is_string($params)) {
            $params = parse_params($params);
        }
        $table = false;
        if (isset($params['table'])) {
            $table = $params['table'];
            unset($params['table']);
        }

        if (isset($params['from'])) {
            $table = $params['from'];
            unset($params['from']);
        }
        if ($table == false) {
            return;
        }

        $this->table = $table;
        return $params;
    }


}

