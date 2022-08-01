<?php

/**
 * @return \Predis\Client
 */
function get_redis(){
    $client = new Predis\Client([
        'scheme' => 'tcp',
        'host' => '192.168.31.100',
        'port' => 6379,
    ]);

    return $client;
}

function json($code = 200,$msg = '',$data= []){
    $ret = [];
    $ret['code'] = $code;
    $ret['msg'] = $msg;
    $ret['data'] = $data;

    exit(json_encode($ret));
}