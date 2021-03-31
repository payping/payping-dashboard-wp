<?php
$_POST['isEnabled'] = true;
$_POST['isPrivate'] = boolval( $_POST['isPrivate'] );
$_POST['wage'] = intval( $_POST['wage'] );
$_POST['defaultExpireDays'] = intval( $_POST['defaultExpireDays'] );
$params = array(
    'name'              => "فروشگاه تست",
    'description'       => "توضیحات فروشگاه تست",
    'pic'               => "37b739ea-33ad-49e6-bf44-d670fcf6d964.png",
    'url'               => get_site_url(),
    'isEnabled'         => true,
    'isPrivate'         => true,
    'wage'              => 10,
    'defaultExpireDays' => 20
);
$parent = new PayPingAPIS( $params );
$response = $parent->CreateStore();
$code = wp_remote_retrieve_response_code( $response );
if( $code === 200 ){
    $CreateStore = json_decode( wp_remote_retrieve_body( $response ), true );
    echo $CreateStore['code'];
//            require_once( 'transactions/report.php' );
}else{
    echo 'Error Connect';
}
?>