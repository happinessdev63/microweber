<?php

namespace Weber\Utils;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class Database
{
    public $cache_minutes = 60;


    public function build_table($table_name, $fields_to_add)
    {

        $key = 'mw_build_table';
        $hash = $table_name . crc32(serialize($fields_to_add));
        $value = Cache::get($key);

        if (!isset($value[$hash])) {
            $val = $fields_to_add;
            $minutes = $this->cache_minutes;
            $expiresAt = Carbon::now()->addMinutes($minutes);
            $value[$hash] = 1;
            $cache = Cache::put($key, $value, $expiresAt);
            $this->_exec_table_builder($table_name, $fields_to_add);
        }
    }


    private function _exec_table_builder($table_name, $fields_to_add)
    {
        $table_name = $this->assoc_table_name($table_name);
        if (!Schema::hasTable($table_name)) {
            Schema::create($table_name, function ($table) {
                $table->increments('id');
            });
        }
        if (is_array($fields_to_add)) {
            foreach ($fields_to_add as $name => $type) {

                if (is_array($type)) {
                    $name = array_shift($type);
                    $type = array_shift($type);
                }

                if (!Schema::hasColumn($table_name, $name)) {
                    Schema::table($table_name, function ($table) use ($name, $type) {
                        $table->$type($name);
                    });
                }
            }
        }
    }


    public function assoc_table_name($assoc_name)
    {

        if ($this->table_prefix == false) {
            $this->table_prefix = Config::get('database.connections.mysql.prefix');
        }
        $assoc_name_o = $assoc_name;
        $assoc_name = str_ireplace($this->table_prefix, '', $assoc_name);
        return $assoc_name;
    }

    public $table_prefix;

    public function real_table_name($assoc_name)
    {

        $assoc_name_new = $assoc_name;
        static $contig_pref = false;

        if (!$contig_pref) {
            $contig_pref = Config::get('database.connections.mysql.prefix');
        }

        if ($this->table_prefix == false) {
            $this->table_prefix = $contig_pref;
        }


        if ($this->table_prefix != false) {
            $assoc_name_new = str_ireplace('table_', $this->table_prefix, $assoc_name_new);
        }

        $assoc_name_new = str_ireplace('table_', $this->table_prefix, $assoc_name_new);
        $assoc_name_new = str_ireplace($this->table_prefix . $this->table_prefix, $this->table_prefix, $assoc_name_new);

        if ($this->table_prefix and $this->table_prefix != '' and stristr($assoc_name_new, $this->table_prefix) == false) {
            $assoc_name_new = $this->table_prefix . $assoc_name_new;
        }

        return $assoc_name_new;
    }

    public $default_limit = 30;

    public function build_query($orm, $params)
    {

        if (is_array($params)) {
            $groupBy = false;
            $order_by = false;
            $count = false;
            $no_limit = false;

            $limit = $this->default_limit;
            if ($limit == false) {
                $limit = 30;
            }
            $offset = false;
            $min = false;
            $max = false;
            $avg = false;
            $ids = false;
            $exclude_ids = false;
            $current_page = false;
            $count_paging = false;
            $to_search_in_fields = false;
            $to_search_keyword = false;
            $fields = false;
            $filter = false;
            $filter = false;
            $to_search_in_fields = false;
            $ids = false;
            if (isset($params['filter'])) {
                $filter = $params['filter'];
                unset($params['filter']);
            }
            if (is_array($filter)) {
                foreach ($filter as $k => $v) {
                    if (isset($params[$k]) and is_callable($v)) {
                        call_user_func($v, $orm, $params[$k], $params);
                    }
                }
            }


            if (isset($params['group_by'])) {
                $groupBy = $params['group_by'];
                unset($params['group_by']);
            }
            if (isset($params['ids'])) {
                $ids = $params['ids'];
                unset($params['ids']);
            }
            if (isset($params['exclude_ids'])) {
                $exclude_ids = $params['exclude_ids'];
                unset($params['exclude_ids']);
            }

            if (isset($params['fields'])) {
                $fields = $params['fields'];
                unset($params['fields']);
            }
            if (isset($params['order_by'])) {
                $order_by = $params['order_by'];
                unset($params['order_by']);
            }
            if (isset($params['orderby'])) {
                $order_by = $params['orderby'];
                unset($params['orderby']);
            }
            if (isset($params['count'])) {
                $count = $params['count'];
                unset($params['count']);
            }
            if (isset($params['limit']) and $params['limit'] != false) {
                $limit = $params['limit'];

                unset($params['limit']);
            }
            if (isset($params['no_limit']) and $params['no_limit'] != false) {
                $no_limit = $params['no_limit'];

                unset($params['limit']);
            }
            if (isset($params['offset'])) {
                $offset = $params['offset'];
                unset($params['offset']);
            }
            if (isset($params['min'])) {
                $min = $params['min'];
                unset($params['min']);
            }
            if (isset($params['max'])) {
                $max = $params['max'];
                unset($params['max']);
            }
            if (isset($params['avg'])) {
                $avg = $params['avg'];
                unset($params['avg']);
            }

            if (isset($params['current_page'])) {
                $current_page = $params['current_page'];
                unset($params['current_page']);
            }

            if (isset($params['paging_param'])) {
                $paging_param = $params['paging_param'];
                if (isset($params[$paging_param])) {
                    $current_page = $params[$paging_param];
                    unset($params[$paging_param]);
                }
                unset($params['paging_param']);
            }
            if (isset($params['page'])) {
                $current_page = $params['page'];
                unset($params['page']);
            }
            if (isset($params['search_in_fields'])) {
                $to_search_in_fields = $params['search_in_fields'];
            }
            if (isset($params['keyword'])) {
                $to_search_keyword = $params['keyword'];
            }

            if (isset($params['count_paging'])) {
                $count = true;
                $count_paging = true;
                unset($params['count_paging']);
            }
            if (isset($params['page_count'])) {
                $count = true;
                $count_paging = true;
                unset($params['page_count']);
            }


            // @todo build_query limit

            if (!empty($params)) {
                foreach ($params as $field_name => $field_value) {
                    if ($field_value !== false and $field_name) {

                        if (is_string($field_value) or is_int($field_value)) {

                            $field_value = trim($field_value);
                            $field_value_len = strlen($field_value);

                            $second_char = substr($field_value, 0, 2);
                            $first_char = substr($field_value, 0, 1);
                            $compare_sign = false;
                            $where_method = false;

                            if ($field_value_len > 0) {
                                if (is_string($field_value)) {
                                    if (stristr($field_value, '[lt]')) {
                                        $first_char = '<';
                                        $field_value = str_replace('[lt]', '', $field_value);
                                    } else if (stristr($field_value, '[lte]')) {
                                        $second_char = '<=';
                                        $field_value = str_replace('[lte]', '', $field_value);
                                    } else if (stristr($field_value, '[st]')) {
                                        $first_char = '<';
                                        $field_value = str_replace('[st]', '', $field_value);
                                    } else if (stristr($field_value, '[ste]')) {
                                        $second_char = '<=';
                                        $field_value = str_replace('[ste]', '', $field_value);
                                    } else if (stristr($field_value, '[gt]')) {
                                        $first_char = '>';
                                        $field_value = str_replace('[gt]', '', $field_value);
                                    } else if (stristr($field_value, '[gte]')) {
                                        $second_char = '>=';
                                        $field_value = str_replace('[gte]', '', $field_value);
                                    } else if (stristr($field_value, '[mt]')) {
                                        $first_char = '>';
                                        $field_value = str_replace('[mt]', '', $field_value);
                                    } else if (stristr($field_value, '[md]')) {
                                        $first_char = '>';
                                        $field_value = str_replace('[md]', '', $field_value);
                                    } else if (stristr($field_value, '[mte]')) {
                                        $second_char = '>=';
                                        $field_value = str_replace('[mte]', '', $field_value);
                                    } else if (stristr($field_value, '[mde]')) {
                                        $second_char = '>=';
                                        $field_value = str_replace('[mde]', '', $field_value);
                                    } else if (stristr($field_value, '[neq]')) {
                                        $second_char = '!=';
                                        $field_value = str_replace('[neq]', '', $field_value);
                                    } else if (stristr($field_value, '[eq]')) {
                                        $first_char = '=';
                                        $field_value = str_replace('[eq]', '', $field_value);
                                    } else if (stristr($field_value, '[int]')) {
                                        $field_value = str_replace('[int]', '', $field_value);
                                    } else if (stristr($field_value, '[is]')) {
                                        $first_char = '=';
                                        $field_value = str_replace('[is]', '', $field_value);
                                    } else if (stristr($field_value, '[like]')) {
                                        $second_char = '%';
                                        $field_value = str_replace('[like]', '', $field_value);
                                    } else if (stristr($field_value, '[null]')) {
                                        $field_value = 'is_null';
                                    } else if (stristr($field_value, '[not_null]')) {
                                        $field_value = 'is_not_null';
                                    } else if (stristr($field_value, '[is_not]')) {
                                        $second_char = '!%';
                                        $field_value = str_replace('[is_not]', '', $field_value);
                                    }
                                }
                                if ($field_value == 'is_null') {
                                    $where_method = 'where_null';
                                    $field_value = $field_name;
                                } elseif ($field_value == 'is_not_null') {
                                    $where_method = 'where_not_null';
                                    $field_value = $field_name;
                                } else if ($second_char == '<=' or $second_char == '=<') {
                                    $where_method = 'where_lte';
                                    $two_char_left = substr($field_value, 0, 2);
                                    if ($two_char_left === '<=' or $two_char_left === '=<') {
                                        $field_value = substr($field_value, 2, $field_value_len);
                                    }
                                } elseif ($second_char == '>=' or $second_char == '=>') {
                                    $where_method = 'where_gte';
                                    $two_char_left = substr($field_value, 0, 2);
                                    if ($two_char_left === '>=' or $two_char_left === '=>') {
                                        $field_value = substr($field_value, 2, $field_value_len);
                                    }
                                } elseif ($second_char == '!=' or $second_char == '=!') {
                                    $where_method = 'where_not_equal';
                                    $two_char_left = substr($field_value, 0, 2);
                                    if ($two_char_left === '!=' or $two_char_left === '=!') {
                                        $field_value = substr($field_value, 2, $field_value_len);
                                    }
                                } elseif ($second_char == '!%' or $second_char == '%!') {
                                    $where_method = 'where_not_like';
                                    $two_char_left = substr($field_value, 0, 2);
                                    if ($two_char_left === '!%' or $two_char_left === '%!') {
                                        $field_value = '%' . substr($field_value, 2, $field_value_len);
                                    } else {
                                        $field_value = '%' . $field_value;
                                    }
                                } elseif ($first_char == '%') {
                                    $where_method = 'where_like';
                                } elseif ($first_char == '>') {
                                    $where_method = 'where_gt';
                                    $first_char_left = substr($field_value, 0, 1);
                                    if ($first_char_left == '>') {
                                        $field_value = substr($field_value, 1, $field_value_len);
                                    }
                                } elseif ($first_char == '<') {
                                    $where_method = 'where_lt';
                                    $first_char_left = substr($field_value, 0, 1);
                                    if ($first_char_left == '<') {
                                        $field_value = substr($field_value, 1, $field_value_len);
                                    }

                                } elseif ($first_char == '=') {
                                    $where_method = 'where_equal';
                                    $first_char_left = substr($field_value, 0, 1);
                                    if ($first_char_left == '=') {
                                        $field_value = substr($field_value, 1, $field_value_len);
                                    }
                                }
                                if ($where_method == false) {
                                    $orm->where($field_name, $field_value);
                                } else {
                                    $orm->$where_method($field_name, $field_value);
                                }
                            }
                        } elseif (is_array($field_value)) {
                            $items = array();
                            foreach ($field_value as $field) {
                                $items[] = $field;
                            }
                            if (!empty($items)) {
                                if (count($items) == 1) {
                                    $orm->where($field_name, reset($items));
                                } else {
                                    $orm->whereIn($field_name, $items);
                                }
                            } else {
                                if (is_string($field_value) or is_int($field_value)) {
                                    $orm->where($field_name, $field_value);
                                }
                            }
                        }
                    }
                }
            }


            if ($to_search_in_fields != false and $to_search_keyword != false) {
                if (is_string($to_search_in_fields)) {
                    $to_search_in_fields = explode(',', $to_search_in_fields);
                }
                // $to_search_keyword = trim($to_search_keyword);
                $to_search_keyword = preg_replace("/(^\s+)|(\s+$)/us", "", $to_search_keyword);

                $to_search_keyword = strip_tags($to_search_keyword);
                $to_search_keyword = str_replace('\\', '', $to_search_keyword);
                $to_search_keyword = str_replace('*', '', $to_search_keyword);

                if ($to_search_keyword != '') {
                    $raw_search_query = false;
                    if (!empty($to_search_in_fields)) {
                        $raw_search_query = '';
                        $search_vals = array();
                        $search_qs = array();
                        foreach ($to_search_in_fields as $to_search_in_field) {
                            $search_qs[] = " `{$to_search_in_field}` REGEXP ? ";
                            $search_vals[] = $to_search_keyword;
                        }
                        if (!empty($search_qs)) {
                            $raw_search_query = implode($search_qs, ' OR ');
                            $orm->where_raw('(' . $raw_search_query . ')', $search_vals);
                        }
                    }
                }
            }


            if ($ids != false) {
                if (is_string($ids)) {
                    $ids = explode(',', $ids);
                }
                $orm->whereIn('id', ($ids));
            }
            if ($exclude_ids != false) {
                if (is_string($exclude_ids)) {
                    $exclude_ids = explode(',', $exclude_ids);
                }
                $orm->whereNotIn('id', ($exclude_ids));
            }

            if ($groupBy == false) {
                if ($count == false and $count_paging == false and $min == false and $max == false and $avg == false) {
                    $orm->groupBy('id');
                }
            } else {
                if ($count_paging == false) {
                    if (is_string($groupBy)) {
                        $groupBy = explode(',', $groupBy);
                    }
                    if (is_array($groupBy)) {
                        foreach ($groupBy as $group) {
                            $orm->groupBy($group);
                        }
                    }
                }
            }
            if ($count == false and $count_paging == false and $order_by != false) {
                $order_by = urldecode($order_by);
                $order_by = strip_tags($order_by);
                $order_by = str_replace(array('*', ';', '--'), '', $order_by);
                $orm->orderBy($order_by);
            }


            if ($count_paging == true) {
                $ret = $orm->count('*');
                $plimit = $limit;
                if ($plimit != false and $ret != false) {
                    $pages_qty = ceil($ret / $plimit);
                    return $pages_qty;
                } else {
                    return;
                }

            }


            if ($count == false) {
                if ($current_page != false and $current_page > 1) {
                    if ($limit != false) {
                        $page_start = ($current_page - 1) * $limit;
                        $page_end = ($page_start) + $limit;
                        $offset = $page_start;
                    }
                }
            }
            if ($no_limit == false) {
                if ($count == false) {
                    if ($limit != false) {
                        $orm->take(intval($limit));
                    }
                    if ($offset != false) {
                        $orm->skip(intval($offset));
                    }
                }
            }
            if ($count != false) {
                return $orm->count();
            } else if ($min != false) {
                return $orm->min($min);
            } else if ($max != false) {
                return $orm->max($max);
            } else if ($avg != false) {
                return $orm->avg($avg);
            } else {
                return $orm;
                return $orm->find_array();
                // return $orm->find_many();
            }


        }
        return $orm;

    }

    public $table_fields = array();

    /**
     * Returns an array that contains only keys that has the same names as the table fields from the database
     *
     * @param string
     * @param  array
     * @return array
     * @package Database
     * @subpackage Advanced
     * @example
     * <code>
     * $table = $this->table_prefix.'content';
     * $data = array();
     * $data['id'] = 1;
     * $data['non_ex'] = 'i do not exist and will be removed';
     * $criteria = $this->map_array_to_table($table, $array);
     * var_dump($criteria);
     * </code>
     */
    public function map_array_to_table($table, $array)
    {


        $arr_key = crc32($table) + crc32(serialize($array));
        if (isset($this->table_fields[$arr_key])) {
            return $this->table_fields[$arr_key];
        }
        $table = $this->real_table_name($table);
        if (empty($array)) {

            return false;
        }
        // $table = $this->real_table_name($table);
//        if (!Schema::hasTable($table)) {
//            return $array;
//        }
        if (isset($this->table_fields[$table])) {
            $fields = $this->table_fields[$table];
        } else {

            $fields = $this->get_fields($table);
            $this->table_fields[$table] = $fields;
        }
        if (is_array($fields)) {
            foreach ($fields as $field) {


                $field = strtolower($field);

                if (isset($array[$field])) {
                    if ($array[$field] != false) {

                        $array_to_return[$field] = $array[$field];
                    }

                    if ($array[$field] == 0) {

                        $array_to_return[$field] = $array[$field];
                    }
                }
            }
        }

        if (!isset($array_to_return)) {
            return false;
        } else {
            $this->table_fields[$arr_key] = $array_to_return;
        }
        return $array_to_return;
    }


    /**
     * Gets all field names from a DB table
     *
     * @param $table string
     *            - table name
     * @param array|bool $exclude_fields array
     *            - fields to exclude
     * @return array
     * @author Peter Ivanov
     * @version 1.0
     * @since Version 1.0
     */

    public function get_fields($table, $exclude_fields = false)
    {

        static $ex_fields_static;
        if (isset($ex_fields_static[$table])) {
            return $ex_fields_static[$table];

        }
        $cache_group = 'db/fields';

        if (!$table) {

            return false;
        }

        $key = 'mw_db_get_fields';
        $hash = $table;
        $value = Cache::get($key);

        if (isset($value[$hash])) {
            return $value[$hash];
        }
        // dd(__FILE__.__LINE__);
        $fields = DB::connection()->getSchemaBuilder()->getColumnListing($table);

        // dd($fields);

        $table = $this->real_table_name($table);
        // $table = $this->escape_string($table);


        $sql = " show columns from $table ";
        $query = DB::select($sql);


        $fields = $query;


        $exisiting_fields = array();
        if ($fields == false or $fields == NULL) {
            $ex_fields_static[$table] = false;
            return false;
        }

        if (!is_array($fields)) {

            return false;
        }
        foreach ($fields as $fivesdraft) {

            $fivesdraft = (array)$fivesdraft;

            if ($fivesdraft != NULL and is_array($fivesdraft)) {
                $fivesdraft = array_change_key_case($fivesdraft, CASE_LOWER);
                if (isset($fivesdraft['name'])) {
                    $fivesdraft['field'] = $fivesdraft['name'];
                    $exisiting_fields[strtolower($fivesdraft['field'])] = true;
                } else {
                    if (isset($fivesdraft['field'])) {

                        $exisiting_fields[strtolower($fivesdraft['field'])] = true;
                    } elseif (isset($fivesdraft['Field'])) {

                        $exisiting_fields[strtolower($fivesdraft['Field'])] = true;
                    }
                }
            }
        }


        $fields = array();

        foreach ($exisiting_fields as $k => $v) {

            if (!empty($exclude_fields)) {

                if (in_array($k, $exclude_fields) == false) {

                    $fields[] = $k;
                }
            } else {

                $fields[] = $k;
            }
        }
        $ex_fields_static[$table] = $fields;


        $expiresAt = 30;
        $value[$hash] = $fields;
        $cache = Cache::put($key, $value, $expiresAt);

        return $fields;
    }

    public function guess_cache_group($group)
    {
        return $group;
    }


    /**
     * Escapes a string from sql injection
     *
     * @param string|array $value to escape
     * @return string|array Escaped string
     * @return mixed Es
     * @example
     * <code>
     * //escape sql string
     *  $results = $this->escape_string($_POST['email']);
     * </code>
     *
     *
     *
     * @package Database
     * @subpackage Advanced
     */
    public function escape_string($value)
    {

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->escape_string($v);
            }
            return $value;
        } else {


            if (!is_string($value)) {
                return $value;
            }
            $str_crc = 'esc' . crc32($value);
            if (isset($this->mw_escaped_strings[$str_crc])) {
                return $this->mw_escaped_strings[$str_crc];
            }

            $search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a");
            $replace = array("\\\\", "\\0", "\\n", "\\r", "\'", '\"', "\\Z");
            $new = str_replace($search, $replace, $value);
            $this->mw_escaped_strings[$str_crc] = $new;
            return $new;
        }
    }

    public function add_table_index($aIndexName, $aTable, $aOnColumns, $indexType = false)
    {
        $aTable = $this->real_table_name($aTable);
        $function_cache_id = false;

        $args = func_get_args();

        foreach ($args as $k => $v) {
            $function_cache_id = $function_cache_id . serialize($k) . serialize($v);
            $function_cache_id = 'add_table_index' . crc32($function_cache_id);
        }

        if (isset($this->add_table_index_cache[$function_cache_id])) {
            return true;
        } else {
            $this->add_table_index_cache[$function_cache_id] = true;
        }


        $table_name = $function_cache_id;
        $cache_group = 'db/' . $table_name;
        $cache_content = $this->app->cache->get($function_cache_id, $cache_group);

        if (($cache_content) != false) {

            return $cache_content;
        }


        $columns = implode(',', $aOnColumns);
        $query = $this->query("SHOW INDEX FROM {$aTable} WHERE Key_name = '{$aIndexName}';");
        if ($indexType != false) {
            $index = $indexType;
        } else {
            $index = " INDEX ";
            //FULLTEXT
        }

        if ($query == false) {
            $q = "ALTER TABLE " . $aTable . " ADD $index `" . $aIndexName . "` (" . $columns . ");";
            $this->q($q);
        }


        $this->app->cache->save('--true--', $function_cache_id, $cache_group);


    }

}
