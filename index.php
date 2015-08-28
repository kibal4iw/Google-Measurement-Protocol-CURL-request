<?php

function gen_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function execute_request_to_google($data, $url) {
    $content = http_build_query($data);
    $content = utf8_encode($content);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
    $result = curl_exec($ch);
    curl_close($ch);

    return $ch;
}

function submit_data_to_google($order_data, $content_data, $order_source) {
    // Формирование данных для аналитики
    $gaurl = 'https://ssl.google-analytics.com/collect'; // https://developers.google.com/analytics/devguides/collection/protocol/v1/reference#transport
    $gaid = 'UA-65338473-1'; // Идентификатор GA
    $gav = '1'; // Версия GA
    $gasitename = 'i.factor.ua'; // Название сайта

    // Список параметров https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#
    $cid = gen_uuid();  // Client ID
    $uid = gen_uuid();  // User ID
    $cn = '';  // Campaign Name
    $cs = '';  // Campaign Source
    $cm = '';  // Campaign Medium


    // https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#in
    $data_transaction = array(
        'v' => $gav, // Версия GA
        'tid' => $gaid, // Идентификатор GA

        'cid' => $cid, // Client ID
        'uid' => $uid, // User ID

        'cn' => $cn, // Campaign Name
        'cs' => $cs, // Campaign Source
        'cm' => $cm, // Campaign Medium

        't' => 'transaction', // тим передаваемых данных
        'ti' => '', // идентификатор транзакции;
        'ta' => $gasitename, // название филиала или магазина;
        'tr' => '', // общая сумма транзакции;
        'ts' => '', // стоимость доставки
        'tt' => '', // доп. налоги
    );

    $data_item = array(
        'v' => $gav,
        'tid' => $gaid,

        'cid' => $cid,
        'uid' => $uid,

        'cn' => $cn,
        'cs' => $cs,
        'cm' => $cm,

        't' => 'item',
        'ti' => '', // идентификатор транзакции;
        'ic' => '', // код товара - SKU
        'in' => '', // название товара;
        'ip' => '', // стоимость товара;
        'iq' => '', // кол-во товара
        'iv' => 'tea', // категория товара.
    );

    // Выполнить запрос к GA
    $result_transaction = execute_request_to_google($data_transaction, $gaurl);
    $result_item = execute_request_to_google($data_item, $gaurl);

    // Вернуть результат данных отправленных в аналитику
    return array(
        'result_transaction' => $result_transaction,
        'result_item' => $result_item,
        'data_transaction' => $data_transaction,
        'data_item' => $data_item
    );
}

echo '<pre>';
print_r(submit_data_to_google());
echo '</pre>';

?>