<?php
/**
 * Created by PhpStorm.
 * User: huanjin
 * Date: 2018/4/22
 * Time: 21:43
 */

namespace lingyin\predis;


use yii\di\Instance;

class Cache extends \yii\caching\Cache
{

    /**
     * @var Connection|string|array the Redis [[Connection]] object or the application component ID of the Redis [[Connection]].
     * This can also be an array that is used to create a redis [[Connection]] instance in case you do not want do configure
     * redis connection as an application component.
     * After the Cache object is created, if you want to change this property, you should only assign it
     * with a Redis [[Connection]] object.
     */
    public $redis = 'redis';

    public function init()
    {
        parent::init();
        $this->redis = Instance::ensure($this->redis, Connection::class);
    }

    public function exists($key)
    {
        return (bool) $this->redis->executeCommand('EXISTS', [$this->buildKey($key)]);
    }

    protected function getValue($key)
    {
        return $this->redis->executeCommand('GET', [$key]);
    }

    protected function getValues($keys)
    {
        $response = $this->redis->executeCommand('MGET', $keys);
        $result = [];
        $i = 0;
        foreach ($keys as $key) {
            $result[$key] = $response[$i++];
        }

        return $result;
    }

    protected function setValue($key, $value, $duration)
    {
        if ($duration == 0) {
            return (bool) $this->redis->executeCommand('SET', [$key, $value]);
        } else {
            $expire = (int) ($duration * 1000);

            return (bool) $this->redis->executeCommand('SET', [$key, $value, 'PX', $expire]);
        }
    }

    protected function setValues($data, $expire)
    {
        $args = [];
        foreach ($data as $key => $value) {
            $args[] = $key;
            $args[] = $value;
        }

        $failedKeys = [];
        if ($expire == 0) {
            $this->redis->executeCommand('MSET', $args);
        } else {
            $expire = (int) ($expire * 1000);
            $this->redis->executeCommand('MULTI');
            $this->redis->executeCommand('MSET', $args);
            $index = [];
            foreach ($data as $key => $value) {
                $this->redis->executeCommand('PEXPIRE', [$key, $expire]);
                $index[] = $key;
            }
            $result = $this->redis->executeCommand('EXEC');
            array_shift($result);
            foreach ($result as $i => $r) {
                if ($r != 1) {
                    $failedKeys[] = $index[$i];
                }
            }
        }

        return $failedKeys;
    }

    protected function addValue($key, $value, $duration)
    {
        if ($duration == 0) {
            return (bool) $this->redis->executeCommand('SET', [$key, $value, 'NX']);
        } else {
            $expire = (int) ($duration * 1000);

            return (bool) $this->redis->executeCommand('SET', [$key, $value, 'PX', $expire, 'NX']);
        }
    }

    protected function deleteValue($key)
    {
        return $this->redis->executeCommand('DEL', [$key]);
    }

    protected function flushValues()
    {
        return $this->redis->executeCommand('FLUSHDB');
    }
}