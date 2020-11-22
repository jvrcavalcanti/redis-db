<?php

use Accolon\RedisDB\Client;

require "./vendor/autoload.php";

$client = new Client('test');

$id = $client->insertOne([
    'value' => 'Kappa'
]);

$client->updateOne($id, [
    'value' => 'KEKW',
    'key' => 'one'
]);

var_dump($client->find($id));

$client->deleteOne($id);

var_dump($client->find($id));

var_dump(count($client->all()));
