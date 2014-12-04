<?php


namespace Microweber\rating;


class Model
{

    public $app;
    protected $table = 'rating';
    protected $fields = array(
        'rel_type' => 'text',
        'rel_id' => 'text',
        'session_id' => 'text',
        'rating' => 'int',
        'created_at' => 'datetime',
        'user_id' => 'int',
        'user_ip' => 'text'
    );

    public function __construct($app)
    {
        if (!is_object($this->app)) {
            if (is_object($app)) {
                $this->app = $app;
            } else {
                $this->app = mw();
            }
        }

        $this->app->database_manager->build_table($this->table, $this->fields);
    }

    public function save($data)
    {
        return $this->app->database->save($this->table, $data);

    }

    public function get($params)
    {
        $params['table'] = $this->table;
        return $this->app->database->get($params);

    }
}