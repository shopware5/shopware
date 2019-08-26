<?php

/**
 * Copyright (c) 2011-2013, Carl Oscar Aaro
 * All rights reserved.
 *
 * New BSD License
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  * Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 *  * Neither the name of Carl Oscar Aaro nor the names of its
 *    contributors may be used to endorse or promote products derived from this
 *    software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 **/

/**
 * Redis cache backend for Zend Framework. Extends Zend_Cache_Backend
 * Supports tags and cleaning modes (except CLEANING_MODE_NOT_MATCHING_TAG)
 * Uses the PHP module phpredis by Nicolas Favre-Felix available at https://github.com/nicolasff/phpredis
 *
 * @category Zend
 *
 * @author Carl Oscar Aaro <carloscar@agigen.se>
 */
class Zend_Cache_Backend_Redis extends Zend_Cache_Backend implements Zend_Cache_Backend_ExtendedInterface
{
    /**
     * Default Values
     */
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 6379;
    const DEFAULT_PERSISTENT = true;
    const DEFAULT_DBINDEX = 0;

    protected $_options = [
        'servers' => [
            [
                'host' => self::DEFAULT_HOST,
                'port' => self::DEFAULT_PORT,
                'persistent' => self::DEFAULT_PERSISTENT,
                'dbindex' => self::DEFAULT_DBINDEX,
            ],
        ],
        'key_prefix' => '', // Is being set in the constructor
    ];

    /**
     * Redis object
     *
     * @var mixed redis object
     */
    protected $_redis = null;

    /**
     * @param array $options associative array of options
     *
     * @throws Zend_Cache_Exception
     */
    public function __construct(array $options = [])
    {
        if (!extension_loaded('redis')) {
            Zend_Cache::throwException('The redis extension must be loaded for using this backend !');
        }
        parent::__construct($options);
        $this->_redis = new Redis();

        foreach ($this->_options['servers'] as $server) {
            if (!array_key_exists('port', $server)) {
                $server['port'] = self::DEFAULT_PORT;
            }
            if (!array_key_exists('host', $server)) {
                $server['host'] = self::DEFAULT_HOST;
            }
            if (!array_key_exists('persistent', $server)) {
                $server['persistent'] = self::DEFAULT_PERSISTENT;
            }
            if (!array_key_exists('dbindex', $server)) {
                $server['dbindex'] = self::DEFAULT_DBINDEX;
            }
            if ($server['persistent']) {
                $result = $this->_redis->pconnect($server['host'], $server['port']);
            } else {
                $result = $this->_redis->connect($server['host'], $server['port']);
            }

            // SW-20299 - sw-fix: support redis auth configuration
            if (isset($server['redisAuth'])) {
                $this->_redis->auth($server['redisAuth']);
            }

            if (array_key_exists('release', $options)) {
                $this->_options['key_prefix'] = $options['release']->getRevision();
            }

            if ($result) {
                $this->_redis->select($server['dbindex']);
            } else {
                $this->_redis = null;
            }
        }
    }

    /**
     * Gets the redis instance used by the cache.
     *
     * @return Redis|null
     */
    public function getRedis()
    {
        return $this->_redis;
    }

    /**
     * Returns status on if cache backend is connected to Redis
     *
     * @return bool true if cache backend is connected to Redis server
     */
    public function isConnected()
    {
        if ($this->_redis) {
            return true;
        }

        return false;
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * @param string $id                     cache id
     * @param bool   $doNotTestCacheValidity if set to true, the cache validity won't be tested
     *
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        if (!($this->_test($id, $doNotTestCacheValidity))) {
            // The cache is not hit !
            return false;
        }

        return $this->_load($id);
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param string $id cache id
     *
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id)
    {
        return $this->_test($id, false);
    }

    /**
     * Set the Redis connection in transaction mode. All coming Redis calls will be executed until transactionEnd() is called.
     *
     * @return bool true if transaction mode is enabled and the call is successful. false on error.
     */
    public function transactionBegin()
    {
        return $this->_redis->multi();
    }

    /**
     * Execute the Redis transaction started with transactionBegin(). Also completes the transaction and puts the Redis connection
     * back into normal mode.
     *
     * @return array result set of all executed commands in the transaction
     */
    public function transactionEnd()
    {
        return $this->_redis->exec();
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param string   $data             Datas to cache
     * @param string   $id               Cache id
     * @param mixed    $tags             Array of strings, the cache record will be tagged by each string entry, if false, key
     *                                   can only be read if $doNotTestCacheValidity is true
     * @param bool|int $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     *
     * @return bool true if no problem
     */
    public function save($data, $id, $tags = [], $specificLifetime = false)
    {
        if (!$this->_redis) {
            return false;
        }

        $lifetime = $this->getLifetime($specificLifetime);

        if (!$tags || !count($tags)) {
            $tags = [''];
        }
        if (is_string($tags)) {
            $tags = [$tags];
        }

        if (!count($tags)) {
            $this->_redis->del($this->_keyFromItemTags($id));
            if ($lifetime === null) {
                $return = $this->_redis->set($this->_keyFromId($id), $data);
            } else {
                $return = $this->_redis->setex($this->_keyFromId($id), $lifetime, $data);
            }
            $this->_redis->sAdd($this->_keyFromItemTags($id), '');
            if ($lifetime !== null) {
                $this->_redis->expire($this->_keyFromItemTags($id), $lifetime);
            } else {
                $redis = $this->_redis->persist($this->_keyFromItemTags($id));
            }

            return $return;
        }

        $tagsTTL = [];
        foreach ($tags as $tag) {
            if ($tag) {
                if (!$this->_redis->exists($this->_keyFromTag($tag))) {
                    $tagsTTL[$tag] = false;
                } else {
                    $tagsTTL[$tag] = $this->_redis->ttl($this->_keyFromTag($tag));
                }
            }
        }

        $redis = $this->_redis->multi();
        $return = [];
        if (!$redis) {
            $return[] = $this->_redis->del($this->_keyFromItemTags($id));
        } else {
            $redis = $redis->del($this->_keyFromItemTags($id));
        }

        if ($lifetime === null) {
            if (!$redis) {
                $return[] = $this->_redis->set($this->_keyFromId($id), $data);
            } else {
                $redis = $redis->set($this->_keyFromId($id), $data);
            }
        } else {
            if (!$redis) {
                $return[] = $this->_redis->setex($this->_keyFromId($id), $lifetime, $data);
            } else {
                $redis = $redis->setex($this->_keyFromId($id), $lifetime, $data);
            }
        }

        $itemTags = [$this->_keyFromItemTags($id)];
        foreach ($tags as $tag) {
            $itemTags[] = $tag;
            if ($tag) {
                if (!$redis) {
                    $return[] = $this->_redis->sAdd($this->_keyFromTag($tag), $id);
                } else {
                    $redis = $redis->sAdd($this->_keyFromTag($tag), $id);
                }
            }
        }
        if (count($itemTags) > 1) {
            if (!$redis) {
                $return[] = call_user_func_array([$this->_redis, 'sAdd'], $itemTags);
            } else {
                $redis = call_user_func_array([$redis, 'sAdd'], $itemTags);
            }
        }

        if ($lifetime !== null) {
            if (!$redis) {
                $return[] = $this->_redis->expire($this->_keyFromItemTags($id), $lifetime);
            } else {
                $redis = $redis->expire($this->_keyFromItemTags($id), $lifetime);
            }
        } else {
            if (!$redis) {
                $return[] = $this->_redis->persist($this->_keyFromItemTags($id));
            } else {
                $redis = $redis->persist($this->_keyFromItemTags($id));
            }
        }

        if ($redis) {
            $return = $redis->exec();
        }
        if (!count($return)) {
            return false;
        }

        foreach ($tags as $tag) {
            if ($tag) {
                $ttl = $tagsTTL[$tag];
                if ($lifetime === null && $ttl !== false && $ttl != -1) {
                    $this->_redis->persist($this->_keyFromTag($tag));
                } elseif ($lifetime !== null && ($ttl === false || ($ttl < $lifetime && $ttl != -1))) {
                    $this->_redis->expire($this->_keyFromTag($tag), $lifetime);
                }
            }
        }

        foreach ($return as $value) {
            if ($value === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Remove a cache record
     *
     * @param string $id cache id
     *
     * @return bool true if no problem
     */
    public function remove($id, $hardReset = false)
    {
        if (!$this->_redis) {
            return false;
        }

        if (!$id) {
            return false;
        }
        if (is_string($id)) {
            $id = [$id];
        }
        if (!count($id)) {
            return false;
        }
        $deleteIds = [];
        foreach ($id as $i) {
            $deleteIds[] = $this->_keyFromItemTags($i);
            if ($hardReset) {
                $deleteIds[] = $this->_keyFromId($i);
            }
        }
        $this->_redis->del($deleteIds);

        return true;
    }

    /**
     * Remove a cache tag record
     *
     * @param string $tag cache tag
     *
     * @return bool true if no problem
     */
    public function removeTag($tag)
    {
        if (!$this->_redis) {
            return false;
        }

        if (!$tag) {
            return false;
        }
        if (is_string($tag)) {
            $id = [$tag];
        }
        if (!count($tag)) {
            return false;
        }
        $deleteTags = [];
        foreach ($tag as $t) {
            $deleteTags[] = $this->_keyFromTag($t);
        }
        if ($deleteTags && count($deleteTags)) {
            $this->_redis->del($deleteTags);
        }

        return true;
    }

    /**
     * Returns wether a specific member key exists in the Redis set
     *
     * @param string $member
     * @param string $set
     *
     * @return bool true or false
     */
    public function existsInSet($member, $set)
    {
        if (!$this->_redis) {
            return null;
        }

        if (!$this->_redis->sIsMember($this->_keyFromId($set), $member)) {
            return false;
        }

        return true;
    }

    /**
     * Adds a key to a set
     *
     * @param mixed  $member           key(s) to add
     * @param string $set
     * @param string $specificLifetime lifetime, null for persistant
     *
     * @return bool result of the add
     */
    public function addToSet($member, $set, $specificLifetime = false)
    {
        if (!$this->_redis) {
            return null;
        }

        $lifetime = $this->getLifetime($specificLifetime);

        if (is_array($member)) {
            $redis = $this->_redis;
            $return = call_user_func_array([$redis, 'sAdd'], array_merge([$this->_keyFromId($set)], $member));
        } else {
            $return = $this->_redis->sAdd($this->_keyFromId($set), $member);
        }
        if ($lifetime !== null) {
            $this->_redis->expire($this->_keyFromId($set), $lifetime);
        }

        return $return;
    }

    /**
     * Removes a key from a redis set.
     *
     * @param mixed  $member key(s) to remove
     * @param string $set
     *
     * @return bool result of removal
     */
    public function removeFromSet($member, $set)
    {
        if (!$this->_redis) {
            return null;
        }

        if (is_array($member)) {
            if (!count($member)) {
                return true;
            }
            $redis = $this->_redis;
            $return = call_user_func_array([$redis, 'sRem'], array_merge([$this->_keyFromId($set)], $member));
        } else {
            $return = $this->_redis->sRem($this->_keyFromId($set), $member);
        }

        return $return;
    }

    /**
     * Returns all keys in a Redis set
     *
     * @param string $set
     *
     * @return array member keys of set
     */
    public function membersInSet($set)
    {
        if (!$this->_redis) {
            return null;
        }

        return $this->_redis->sMembers($this->_keyFromId($set));
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     *
     * Zend_Cache::CLEANING_MODE_ALL (default)    => remove all cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_OLD              => remove too old cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_MATCHING_TAG     => remove cache entries matching all given tags
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG => remove cache entries not {matching one of the given tags}
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG => remove cache entries matching any given tags
     *                                               ($tags can be an array of strings or a single string)
     *
     * @param string     $mode clean mode
     * @param tags array $tags array of tags
     *
     * @return bool true if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = [])
    {
        return $this->_clean($mode, $tags);
    }

    /**
     * Return an array of stored cache ids. Not implemented for Redis cache
     *
     * @throws Zend_Cache_Exception
     */
    public function getIds()
    {
        Zend_Cache::throwException('Not possible to get available IDs on Redis cache');
    }

    /**
     * Return an array of stored tags. Not implemented for Redis cache
     *
     * @throws Zend_Cache_Exception
     */
    public function getTags()
    {
        Zend_Cache::throwException('Not possible to get available tags on Redis cache');
    }

    /**
     * Return an array of stored cache ids which match given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param array $tags array of tags
     *
     * @return array array of matching cache ids (string)
     */
    public function getIdsMatchingTags($tags = [])
    {
        if (!$this->_redis) {
            return [];
        }

        if (!$tags) {
            return [];
        }
        if ($tags && is_string($tags)) {
            $tags = [$tags];
        }

        $matchTags = [];
        foreach ($tags as $tag) {
            $matchTags[] = $this->_keyFromTag($tag);
        }
        if (count($matchTags) == 1) {
            return $this->_redis->sMembers($matchTags[0]);
        }

        return $this->_redis->sInter($matchTags);
    }

    /**
     * Return an array of stored cache ids which don't match given tags. Not implemented for Redis cache
     *
     * In case of multiple tags, a logical OR is made between tags
     *
     * @param array $tags array of tags
     *
     * @throws Zend_Cache_Exception
     */
    public function getIdsNotMatchingTags($tags = [])
    {
        Zend_Cache::throwException('Not possible to get IDs not matching tags on Redis cache');
    }

    /**
     * Return an array of stored cache ids which match any given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param array $tags array of tags
     *
     * @return array array of any matching cache ids (string)
     */
    public function getIdsMatchingAnyTags($tags = [])
    {
        if (!$this->_redis) {
            return [];
        }

        if (!$tags) {
            return [];
        }
        if ($tags && is_string($tags)) {
            $tags = [$tags];
        }

        $return = [];
        foreach ($tags as $tag) {
            foreach ($this->_redis->sMembers($this->_keyFromTag($tag)) as $id) {
                $return[] = $id;
            }
        }

        return $return;
    }

    /**
     * Return the filling percentage of the backend storage. Not implemented on Redis cache
     *
     * @throws Zend_Cache_Exception
     */
    public function getFillingPercentage()
    {
        Zend_Cache::throwException('getFillingPercentage not implemented on Redis cache');
    }

    /**
     * Return an array of metadatas for the given cache id. Not implemented on Redis cache
     *
     * @param string $id cache id
     *
     * @throws Zend_Cache_Exception
     */
    public function getMetadatas($id)
    {
        Zend_Cache::throwException('Metadata not implemented on Redis cache');
    }

    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id            cache id
     * @param int    $extraLifetime
     *
     * @return bool true if ok
     */
    public function touch($id, $extraLifetime)
    {
        if (!$this->_redis) {
            return false;
        }

        $tags = $this->_redis->sMembers($this->_keyFromItemTags($id));

        $lifetime = $this->getLifetime($extraLifetime);
        $return = false;
        if ($lifetime !== null) {
            $this->_redis->expire($this->_keyFromItemTags($id), $lifetime);
            $return = $this->_redis->expire($this->_keyFromId($id), $lifetime);
        } else {
            $this->_redis->persist($this->_keyFromItemTags($id));
            $return = $this->_redis->persist($this->_keyFromId($id));
        }

        if ($tags) {
            foreach ($tags as $tag) {
                if ($tag) {
                    $ttl = $this->_redis->ttl($this->_keyFromTag($tag));
                    if ($ttl !== false && $ttl !== -1 && $ttl < $lifetime && $lifetime !== null) {
                        $this->_redis->expire($this->_keyFromTag($tag), $lifetime);
                    } elseif ($ttl !== false && $ttl !== -1 && $lifetime === null) {
                        $this->_redis->persist($this->_keyFromTag($tag));
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Return an associative array of capabilities (booleans) of the backend
     *
     * The array must include these keys :
     * - automatic_cleaning (is automating cleaning necessary)
     * - tags (are tags supported)
     * - expired_read (is it possible to read expired cache records
     *                 (for doNotTestCacheValidity option for example))
     * - priority does the backend deal with priority when saving
     * - infinite_lifetime (is infinite lifetime can work with this backend)
     * - get_list (is it possible to get the list of cache ids and the complete list of tags)
     *
     * @return array associative of with capabilities
     */
    public function getCapabilities()
    {
        return [
            'automatic_cleaning' => true,
            'tags' => true,
            'expired_read' => false,
            'priority' => false,
            'infinite_lifetime' => true,
            'get_list' => false,
        ];
    }

    /**
     * Save some string datas into a cache record. Only the specific key will be stored and no tags.
     * Can only be read by load() if $doNotTestCacheValidity is true
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param string $data             Datas to cache
     * @param string $id               Cache id
     * @param int    $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     *
     * @return bool true if no problem
     */
    protected function _storeKey($data, $id, $specificLifetime = false)
    {
        if (!$this->_redis) {
            return false;
        }

        $lifetime = $this->getLifetime($specificLifetime);

        if ($lifetime === null) {
            return $this->_redis->set($this->_keyFromId($id), $data);
        }

        return $this->_redis->setex($this->_keyFromId($id), $lifetime, $data);
    }

    /**
     * Prefixes key ID
     *
     * @param string $id cache key
     *
     * @return string prefixed id
     */
    protected function _keyFromId($id)
    {
        return $this->_options['key_prefix'] . 'item__' . $id;
    }

    /**
     * Prefixes tag ID
     *
     * @param string $id tag key
     *
     * @return string prefixed tag
     */
    protected function _keyFromTag($id)
    {
        return $this->_options['key_prefix'] . 'tag__' . $id;
    }

    /**
     * Prefixes item tag ID
     *
     * @param string $id item tag key
     *
     * @return string prefixed item tag
     */
    protected function _keyFromItemTags($id)
    {
        return $this->_options['key_prefix'] . 'item_tags__' . $id;
    }

    /**
     * Clean some cache records (protected method used for recursive stuff)
     *
     * Available modes are :
     * Zend_Cache::CLEANING_MODE_ALL (default)    => remove all cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_OLD              => remove too old cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_MATCHING_TAG     => remove cache entries matching all given tags
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG => remove cache entries not {matching one of the given tags}
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG => remove cache entries matching any given tags
     *                                               ($tags can be an array of strings or a single string)
     *
     * @param string $dir  Directory to clean
     * @param string $mode Clean mode
     * @param array  $tags Array of tags
     *
     * @throws Zend_Cache_Exception
     *
     * @return bool True if no problem
     */
    protected function _clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = [])
    {
        if (!$this->_redis) {
            return false;
        }

        $result = true;
        $all = [];

        if ($mode == Zend_Cache::CLEANING_MODE_ALL) {
            return $this->_redis->flushDb();
        }

        if ($mode == Zend_Cache::CLEANING_MODE_OLD) {
            return true;
        } /* Redis takes care of expire */

        if ($mode == Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG && $tags && (is_string($tags) || count($tags))) {
            return $this->removeTag($tags);
        }

        if ($mode == Zend_Cache::CLEANING_MODE_MATCHING_TAG && $tags && (is_string($tags) || count($tags) == 1)) {
            return $this->removeTag($tags);
        }

        if ($mode == Zend_Cache::CLEANING_MODE_MATCHING_TAG && $tags && count($tags)) {
            return $this->remove($this->getIdsMatchingTags($tags));
        }

        if ($mode == Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG) {
            Zend_Cache::throwException('CLEANING_MODE_NOT_MATCHING_TAG not implemented for Redis cache');
        }

        Zend_Cache::throwException('Invalid mode for clean() method');
    }

    /**
     * Test if the given cache id is available (and still valid as a cache record)
     *
     * @param string $id                     Cache id
     * @param bool   $doNotTestCacheValidity If set to true, the cache validity won't be tested
     *
     * @return bool|mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    protected function _test($id, $doNotTestCacheValidity)
    {
        if (!$this->_redis) {
            return false;
        }

        if ($doNotTestCacheValidity) {
            return true;
        }
        $tags = $this->_redis->sMembers($this->_keyFromItemTags($id));
        if (!$tags || !count($tags)) {
            return false;
        }
        foreach ($tags as $tag) {
            if ($tag && !$this->_redis->sIsMember($this->_keyFromTag($tag), $id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return cached id
     *
     * @param string $id cache id
     *
     * @return string cached datas
     */
    protected function _load($id)
    {
        if (!$this->_redis) {
            return false;
        }

        return $this->_redis->get($this->_keyFromId($id));
    }

    /**
     * Give (if possible) an extra lifetime to the given cache id (and only that key, no tags are updated)
     *
     * @param string $id            cache id
     * @param int    $extraLifetime
     *
     * @return bool true if ok
     */
    protected function _touchKey($id, $extraLifetime)
    {
        if (!$this->_redis) {
            return false;
        }

        $data = $this->load($id, true);
        if ($data === false) {
            return false;
        }

        return $this->storeKey($data, $id, $extraLifetime);
    }
}
