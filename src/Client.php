<?php

namespace Accolon\RedisDB;

use Accolon\Redis\Redis;
use Accolon\RedisDB\Exceptions\InsertFailException;
use Accolon\RedisDB\Exceptions\RecordExists;
use Accolon\RedisDB\Exceptions\RecordExistsException;
use Accolon\RedisDB\Exceptions\TypeException;

class Client
{
    private string $tableName;

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
        Redis::connect();
    }

    public function insertOne(array $data)
    {
        $id = md5(microtime(true));

        if (!isset($data['_id'])) {
            $data['_id'] = $id;
        }

        if (Redis::has("{$this->tableName}:{$id}")) {
            throw new RecordExistsException("Record already exists");
        }

        ksort($data);

        Redis::set("{$this->tableName}:{$id}", json_encode($data));
        
        if (!Redis::save()) {
            Redis::del("{$this->tableName}-{$id}");
            throw new InsertFailException("[insertOne] fail save");
        }

        return $data['_id'];
    }

    public function insertMany(array $datas)
    {
        foreach ($datas as $data) {
            $this->insertOne($data);
        }
    }

    public function updateOne(string $id, array $data)
    {
        $record = $this->find($id);

        foreach ($data as $key => $value) {
            $record->$key = $value;
        }

        Redis::set("{$this->tableName}:{$id}", json_encode($record));
        Redis::save();

        return $record;
    }

    public function deleteOne(string $id)
    {
        return Redis::del("{$this->tableName}:{$id}");
    }

    public function find(string $id)
    {
        return json_decode(Redis::get("{$this->tableName}:{$id}"));
    }

    public function all()
    {
        return array_map(
            fn($key) => $this->find(explode(":", $key)[1]),
            Redis::getKeys("{$this->tableName}:*")
        );
    }
}
