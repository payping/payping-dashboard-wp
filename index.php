<?php
/*
Plugin Name: PayPing dashboard
Version: 1.0.0
Description:  افزونه داشبورد پی‌پینگ در وردپرس.
Plugin URI: https://www.payping.ir/
Author: Mahdi Sarani
Author URI: https://mahdisarani.ir
*/
if (!defined('ABSPATH'))
	exit;
define( 'PPD_GPPDIR', plugin_dir_path( __FILE__ ) );
define( 'PPD_GPPDIRU', plugin_dir_url(__FILE__) );

/**
 * Register and enqueue a custom stylesheet in the WordPress admin.
 */
function ppd_enqueue_assets_admin_style() {
	wp_register_style( 'ppd_report_detail_wp_admin', PPD_GPPDIRU . 'assets/css/reposrt-detail.css', false, null );
	if( isset( $_GET['page'] ) && $_GET['page'] == 'payping-transactions' && isset( $_GET['code'] ) ){
		wp_enqueue_style( 'ppd_report_detail_wp_admin' );
	}
}
add_action( 'admin_enqueue_scripts', 'ppd_enqueue_assets_admin_style' );

include_once( PPD_GPPDIR . "includes/PayPing.php" );
include_once( PPD_GPPDIR . "admin/admin.php" );
new PayPingAdminPage();
if( get_option('PayPing_Deposit') == 'active' ){
	add_filter( 'cron_schedules', 'PayPing_check_deposit_custom_time' );
	function PayPing_check_deposit_custom_time( $schedules ){
		$time_cron = get_option('PayPing_DepositT');
		$time_cron = intval( $time_cron );
		$schedules['custom_time'] = array(
				'interval'  => $time_cron * 3600,
				'display'   => __( 'بازه مغایرت‌گیری', 'textdomain' )
		);
		return $schedules;
	}

	// Schedule an action if it's not already scheduled
	if ( ! wp_next_scheduled( 'PayPing_check_deposit_custom_time' ) ) {
		wp_schedule_event( time(), 'custom_time', 'PayPing_check_deposit_custom_time' );
	}

	// Hook into that action that'll fire every three minutes
	add_action( 'PayPing_check_deposit_custom_time', 'custom_time_event_func' );
	function custom_time_event_func(){
		pp_aouto_deposit();
	}
	
	function pp_aouto_deposit(){
		$wc_orders = wc_get_orders( array(
			'limit'          => 50,
			'meta_key'       => '_payping_payCode',
			'payment_method' => array('WC_payping', 'WC_payping_Ghesta'),
			'status'         => array( 'pending', 'on-hold', 'cancelled', 'failed' ),
		) );
		if( $wc_orders ){
			foreach( $wc_orders as $order ){
				$oder_id = $order->id;
				$PaymentCode = get_post_meta($oder_id, '_payping_payCode', true);
				$parent = new PayPingAPIS();
				$response = $parent->TransActionDetails( $PaymentCode );
				$code = wp_remote_retrieve_response_code( $response );
				if( $code === 200 ){
					$Details = json_decode( wp_remote_retrieve_body( $response ), true );
					$order_id = $Details['clientRefId'];
					$refid = $Details['invoiceNo'];
					if( $Details['isRequest'] === true ){
						return 'این پرداخت از طریق سایت انجام نشده‌است.';
					}else{
						if( $Details['isPaid'] === true ){
							$resD = DepositWoocommerce( $order_id, $refid );
							if( $resD['status'] == 'success' ){
								return 'مغایرت‌گیری انجام شد.';
							}else{
								return 'مغایرت‌گیری انجام نشد.';
							}
						}else{
							return 'این پرداخت ناموفق بوده است.';
						}
					}
				}
			}
		}
	}
}

function DepositWoocommerce( $order_id, $refid ){
	global $woocommerce;
	if($order_id){
		//Get PayCode
		$paypingpayCode = get_post_meta($order_id, '_payping_payCode', true);
		// Get Order id
		$order = new WC_Order($order_id);
		// Get Currency Order
		$currency = $order->get_currency();

		if( $order->status != 'completed' ){
			// Get Amount
			$Amount = intval($order->order_total);

			/* check currency and set amount */
			if( strtolower( $currency ) == strtolower('IRT') || strtolower( $currency ) == strtolower('TOMAN') || strtolower( $currency ) == strtolower('Iran TOMAN') || strtolower( $currency ) == strtolower('Iranian TOMAN') || strtolower( $currency ) == strtolower('Iran-TOMAN') || strtolower( $currency ) == strtolower('Iranian-TOMAN') || strtolower( $currency ) == strtolower('Iran_TOMAN') || strtolower( $currency ) == strtolower('Iranian_TOMAN') || strtolower( $currency ) == strtolower('تومان') || strtolower( $currency ) == strtolower('تومان ایران') ){
				$Amount = $Amount * 1;
			}elseif(strtolower($currency) == strtolower('IRHT')){
				$Amount = $Amount * 1000;
			}elseif( strtolower( $currency ) == strtolower('IRHR') ){
				$Amount = $Amount * 100;					
			}elseif( strtolower( $currency ) == strtolower('IRR') ){
				$Amount = $Amount / 10;
			}

			//Set Data 
			$data = array('refId' => $refid, 'amount' => $Amount);
			$args = array(
				'body' => json_encode($data),
				'timeout' => '30',
				'redirection' => '5',
				'httpsversion' => '1.0',
				'blocking' => true,
				'headers' => array(
				'Authorization' => 'Bearer ' . get_option('PayPing_TokenCode'),
				'Content-Type'  => 'application/json',
				'Accept' => 'application/json'
				),
			 'cookies' => array()
			);

		//response
		$response = wp_remote_post('https://api.payping.ir/v2/pay/verify', $args);
		$body = wp_remote_retrieve_body( $response );
		$XPP_ID = $response["headers"]["x-paypingrequest-id"];

			$code = wp_remote_retrieve_response_code( $response );
			$txtmsg = status_message( $code );
			if( $code === 200 ){
				if( isset( $refid ) and $refid != '' ){
					$Status = 'completed';
					$Transaction_ID = $refid;
				}else{
					$Status = 'failed';
					$Transaction_ID = $refid;
					$Message = 'متاسفانه سامانه قادر به دریافت کد پیگیری نمی باشد! نتیجه درخواست : ' . $body .'<br /> شماره خطا: '.$XPP_ID;
					$Fault = $Message;
				}
			}elseif( $code == 400){
				$rbody = json_decode( $body, true );
				if( array_key_exists('15', $rbody) ){
					$Status = 'completed';
					$Transaction_ID = $refid;
				}elseif( array_key_exists( '1', $rbody) ){
					$Status = 'failed';
					$Transaction_ID = $refid;
					$Message = "کاربر در صفحه بانک از پرداخت انصراف داده است.<br>کد پرداخت: $paypingpayCode <br> شماره خطا: $XPP_ID";
					$Fault = 'تراكنش توسط شما لغو شد.';
				}else{
					$Status = 'failed';
					$Transaction_ID = $refid;
					$Message = $txtmsg."<br>کد پرداخت: $paypingpayCode <br> شماره خطا: $XPP_ID";
					$Fault = 'خطایی رخ داده است، با مدیریت سایت تماس بگیرید.';
				}
			}else{
				$Status = 'failed';
				$Transaction_ID = $refid;
				$Message = $txtmsg.'<br> شماره خطا: '.$XPP_ID;
				$Fault = $Message;
			}

			if($Status == 'completed' && isset( $Transaction_ID ) && $Transaction_ID != 0){
				update_post_meta($order_id, '_transaction_id', $Transaction_ID);
				$order->payment_complete($Transaction_ID);
				
				$Note = sprintf(__('%s .<br/> شماره سفارش: %s', 'woocommerce'), 'پرداخت شده(مغایرت‌گیری)', $Transaction_ID);
				$Note = apply_filters('WC_payping_Return_from_Gateway_Success_Note', $Note, $order_id, $Transaction_ID);
				if( $Note ){ $order->add_order_note($Note, 1); }
				
				return array( 'status' => 'success', 'message' => 'مغایرت‌گیری با موفقیت انجام شد.');
				exit;
			}else{
				$tr_id = ($Transaction_ID && $Transaction_ID != 0) ? ('<br/>کد پیگیری : ' . $Transaction_ID) : '';
				do_action('WC_payping_Return_from_Gateway_Failed', $order_id, $Transaction_ID, $Fault);
				return array( 'status' => 'faild', 'message' => 'مغایرت‌گیری انجام نشد.');
				exit;
			}
		}else{
			$Transaction_ID = get_post_meta($order_id, '_transaction_id', true);
			do_action('WC_payping_Return_from_Gateway_ReSuccess', $order_id, $Transaction_ID);
			return array( 'status' => 'success', 'message' => 'مغایرت‌گیری با موفقیت انجام شد.');
			exit;
		}
	}else{
		$Fault = __('شماره سفارش وجود ندارد .', 'woocommerce');
		do_action('WC_payping_Return_from_Gateway_No_Order_ID', $order_id, $Transaction_ID, $Fault);
		return array( 'status' => 'faild', 'message' => 'مغایرت‌گیری انجام نشد.');
		exit;
	}
}

 function status_message($code){
	switch ($code){
		case 200 :
			return 'عملیات با موفقیت انجام شد';
			break ;
		case 400 :
			return 'مشکلی در ارسال درخواست وجود دارد';
			break ;
		case 500 :
			return 'مشکلی در سرور رخ داده است';
			break;
		case 503 :
			return 'سرور در حال حاضر قادر به پاسخگویی نمی‌باشد';
			break;
		case 401 :
			return 'عدم دسترسی';
			break;
		case 403 :
			return 'دسترسی غیر مجاز';
			break;
		case 404 :
			return 'آیتم درخواستی مورد نظر موجود نمی‌باشد';
			break;
	}
}