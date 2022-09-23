<?php
//ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
//error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки
require_once 'lib.php';

loginfo('UPDATE-SETTINGS', "Update info message: $infoMessage, store: $store");
$accountId = $_POST['accountId'];
$app = AppInstance::loadApp($accountId);

$notify = $app->status != AppInstance::ACTIVATED;
$app->status = AppInstance::ACTIVATED;

vendorApi()->updateAppStatus(cfg()->appId, $accountId, $app->getStatusName());

$app->persist();


if($_POST['stocks'] == 'allStocks'){
    $filter = 'filter=archived=true;archived=false';
    $allStores = JsonApi()->allStores($filter);
//    debug($allStores);
}
if($_POST['stocks'] == 'archiveStocks'){
    $filter = 'filter=archived=true';
    $allStores = JsonApi()->allStores($filter);
//    debug($allStores);
}
if($_POST['stocks'] == 'commonStocks'){
   $allStores = JsonApi()->allStores();
//   debug($allStores);   
}

$stocksByStore = [];
foreach ($allStores as $store){
    $stocksByStore[$store->name] = JsonApi()->getStocksByStore($store->meta->href);
}
//debug($stocksByStore);

$storeSum = [];
foreach ($stocksByStore as $store => $value){
    foreach ($value as $item){
        $storeSum[$store] += abs($item->stock) * abs($item->price);
//        $storeSum[$store] += abs($item->quantity) * abs($item->price); // на случай если нужен все-таки quantity
    }
}
//debug($storeSum);

$storesValues = [];
$i=0;
foreach($allStores as $store){
    $storesValues[$i]['name'] = $store->name;
    $storesValues[$i]['id'] = $store->id;
    if(isset($store->parent)){
        $storesValues[$i]['parent_name'] = $store->parent->meta->href;
    }
    if($store->archived == 1){
        $storesValues[$i]['archived'] = $store->archived;
    }
    $i++;
}
//debug($storesValues);


for($i=0;$i<count($storesValues);$i++){
     if(isset($storesValues[$i]['parent_name'])){
        $result = JsonApi()->stores($storesValues[$i]['parent_name']);
        $storesValues[$i]['parent_name'] = $result->name;
        $storesValues[$i]['parent_id'] = $result->id;        
     }else{
        $storesValues[$i]['parent_id'] = 0;        
     }
}
//debug($storesValues);

foreach($storesValues as $key => &$value){
    foreach($storeSum as $k => $v){
        if($value['name'] == $k ){
            $storesValues[$key]['summ'] = $v;
        }
    }
}

$res = JsonApi()->buildTree($storesValues);

$successMessage = 'Остатки успешно загружены';

require('iframe.html');