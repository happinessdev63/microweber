<?php


namespace Microweber\Utils\Adapters\Cache;

use Closure;
use Microweber\Utils\Adapters\Cache\Storage\FileStorage;


class CacheStore {
    public $adapter;

    public function __construct($prefix = '') {
        $this->adapter = new FileStorage($prefix);
    }


    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get($key) {
        return $this->adapter->get($key);
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  int    $minutes
     *
     * @return void
     */
    public function put($key, $value, $minutes) {
        return $this->adapter->put($key, $value, $minutes);

    }

    /**
     * Tags for cache.
     *
     * @param  string $string
     *
     * @return object
     */
    public function tags($tags) {
        return $this->adapter->tags($tags);

    }


    /**
     * Get an item from the cache, or store the default value.
     *
     * @param  string        $key
     * @param  \DateTime|int $minutes
     * @param  Closure       $callback
     *
     * @return mixed
     */
    public function remember($key, $minutes, Closure $callback) {
        return $this->adapter->remember($key, $minutes, $callback);
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param  string  $key
     * @param  Closure $callback
     *
     * @return mixed
     */
    public function rememberForever($key, Closure $callback) {
        return $this->adapter->rememberForever($key, $callback);
    }


    /**
     * Increment the value of an item in the cache.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function increment($key, $value = 1) {
        return $this->adapter->increment($key, $value);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     *
     * @throws \LogicException
     */
    public function decrement($key, $value = 1) {
        return $this->adapter->decrement($key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function forever($key, $value) {
        return $this->adapter->forever($key, $value);
    }

    /**
     * Remove an item from the cache by tags.
     *
     * @param  string $string
     *
     * @return void
     */
    public function forgetTags($string) {
        return $this->adapter->forgetTags($string);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string $key
     *
     * @return void
     */
    public function forget($key) {
        return $this->adapter->forget($key);
    }

    /**
     * Remove all items from the cache.
     *
     * @param  string $tag
     *
     * @return void
     */
    public function flush($all = false) {
        return $this->adapter->flush($all);
    }


}