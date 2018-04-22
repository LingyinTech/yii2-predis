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

    protected function getValue($key)
    {
        return $this->redis->executeCommand('GET', [$key]);
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

    protected function addValue($key, $value, $duration)
    {
        // TODO: Implement addValue() method.
    }

    protected function deleteValue($key)
    {
        return $this->redis->executeCommand('DEL', [$key]);
    }

    protected function flushValues()
    {
        // TODO: Implement flushValues() method.
    }
}