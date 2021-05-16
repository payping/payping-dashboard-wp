<?php
class PayPingAPIS 
{
    /* Sset Variables In Class */
    protected $TokenCode;
    protected $BaseURL;
    protected $DebugMode;
    protected $DebugUrl;
    protected $VReq  = '/v1';
    protected $VPReq = '/v2';
    
	public function __construct(){
        // Get the Setting plugin
        $this->TokenCode  = get_option('PayPing_TokenCode');
        $this->BaseURL    = "https://api.payping.ir";
        $this->DebugMode  = get_option('PayPing_DebugMode');
        $this->DebugUrl   = get_option('PayPing_DebugUrl');

        // Set API URLs In Plugin
        if( $this->DebugMode === 'active' && isset( $this->DebugUrl ) && $this->DebugUrl !== '' ){
            $this->BaseURL = $this->DebugUrl;
        }
	}
    
    /* Show Debug In Console */
    public function PayPing_Debug_Log( $object = null, $label = null ){
        $message = json_encode( $object, JSON_UNESCAPED_UNICODE);
        $label = "Debug" . ( $label ? " ( $label ): " : ': ');
        echo "<script>console.log( \"$label\", $message );</script>";
        file_put_contents( WC_GPPDIR . 'LogPayPing.txt', $label ."\n" .$message . "\n\n", FILE_APPEND );
    }

    public function PayPingRequest( $Route, $method, $params = array(), $Text = 'PayPingRequest' ){
        $ReqHost = $this->BaseURL . $Route;
        $args = array(
            'timeout'      => 45,
            'redirection'  => '5',
            'httpsversion' => '1.0',
            'blocking'     => true,
            'headers'      => array(
                                  'Authorization' => 'Bearer ' . $this->TokenCode,
                                  'Content-Type'  => 'application/json',
                                  'Accept'        => 'application/json'
                              ),
            'cookies'      => array()
        );
        switch( $method ){
                case 'get':
                    $response = wp_remote_get( $ReqHost, $args );
                break;
                case 'post':
                    $args['body'] = json_encode( $params, true );
                    $response = wp_remote_post( $ReqHost, $args );
                break;
                case 'put':
                    $args['body'] = json_encode( $params, true );
                    $args['method'] = 'PUT';
                    $response = wp_remote_request( $ReqHost, $args );
                break;
                case 'delete':
                    $args['body'] = json_encode( $params, true );
                    $args['method'] = 'DELETE';
                    $response = wp_remote_request( $ReqHost, $args );
                break;
        }
        if( $this->DebugMode === 'active' ){
            $this->PayPing_Debug_Log( $response, $Text );
        }
        if( is_wp_error( $response ) ){
            return 500;
        }else{
            return $response;
        }
    }
    
    /* Create Pay */
    public function Pay( $params ){
        $PayResponse = wp_remote_post( $this->PayUrl, $pay_args );
        $ResponseXpId = wp_remote_retrieve_headers( $PayResponse )['x-paypingrequest-id'];
        /* Call Function Show Debug In Console */
        if( is_wp_error( $PayResponse ) ){
            $Message = 'خطا در ارتباط به پی‌پینگ : شرح خطا ' . $PayResponse->get_error_message() . '<br/> شماره خطای پی‌پینگ: ' . $ResponseXpId;
            return $Message;
        }else{
            $code = wp_remote_retrieve_response_code( $PayResponse );
            if( $code === 200 ){
                if ( isset( $PayResponse["body"] ) && $PayResponse["body"] != '' ) {
                    $CodePay = wp_remote_retrieve_body( $PayResponse );
                    $CodePay =  json_decode( $CodePay, true );
                    wp_redirect( sprintf( '%s/%s', $this->GoToIpgUrl, $CodePay["code"] ) );
                    exit;
                }else{
                    $Message = ' تراکنش ناموفق بود- کد خطا : ' . $ResponseXpId;
                    return $Message;
                }
            }elseif( $code == 400 ){
                $Message = wp_remote_retrieve_body( $PayResponse ) . '<br /> کد خطا: ' . $ResponseXpId;
                return $Message;
            }else{
                $Message = wp_remote_retrieve_body( $PayResponse ) . '<br /> کد خطا: ' . $ResponseXpId;
                return $Message;
            }
        }
    }
    /* End Pay */
    
    /* Create Verify */
    public function Verify( $params ){
        $VarifyData = array( 'refId' => $params['refId'], 'amount' => $params['amount'] );
        $VarifyArgs = array(
            'body' => json_encode( $VarifyData ),
            'timeout' => '45',
            'redirection' => '5',
            'httpsversion' => '1.0',
            'blocking' => true,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->TokenCode,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'cookies' => array()
        );
        $VerifyResponse = wp_remote_post( $this->VerifyUrl, $VarifyArgs );
        $ResponseXpId = wp_remote_retrieve_headers( $VerifyResponse )['x-paypingrequest-id'];
        /* Call Function Show Debug In Console */
        self::PayPing_Debug_Log( $this->DebugMode, $VerifyResponse, "Verify" );
        if( is_wp_error( $VerifyResponse ) ){
            $Status = 'failed';
            $Fault = $VerifyResponse->get_error_message();
            $Message = 'خطا در ارتباط به پی‌پینگ : شرح خطا '.$VerifyResponse->get_error_message();
            return array( 'status' => $Status, 'message' => $Message, 'fault' => $Fault );
        }else{
            $code = wp_remote_retrieve_response_code( $VerifyResponse );
            if( $code === 200 ){
                if( isset( $_POST["refid"] ) and $_POST["refid"] != '' ){
                    $Status = 'completed';
                    $Transaction_ID = $_POST["refid"];
                    $Fault = 'پرداخت موفق';
                    $Message = 'تراکنش موفق';
                    return array( 'status' => $Status, 'transaction_id' => $Transaction_ID, 'message' => $Message, 'fault' => $Fault );
                }else{
                    $Status = 'failed';
                    $Transaction_ID = $_POST['refid'];
                    $Message = 'متافسانه سامانه قادر به دریافت کد پیگیری نمی باشد!<br/>نتیجه درخواست: ' .wp_remote_retrieve_body( $response ) . '<br/> شماره خطا: ' . $ResponseXpId;
                    $Fault = $Message;
                    return array( 'status' => $Status, 'transaction_id' => $Transaction_ID, 'message' => $Message, 'fault' => $Fault );
                }
            }elseif( $code == 1 ){
                $Status = 'canceled';
                $Transaction_ID = $_POST['refid'];
                $Message = wp_remote_retrieve_body( $VerifyResponse );
                $Fault = $Message;
                return array( 'status' => $Status, 'transaction_id' => $Transaction_ID, 'message' => $Message, 'fault' => $Fault );
            }elseif( $code == 400 ){
                $Status = 'failed';
                $Transaction_ID = $_POST['refid'];
                $Message = wp_remote_retrieve_body( $VerifyResponse ) . '<br /> شماره خطا: ' . $ResponseXpId;
                $Fault = $Message;
                return array( 'status' => $Status, 'transaction_id' => $Transaction_ID, 'message' => $Message, 'fault' => $Fault );
            }else{
                $Status = 'failed';
                $Transaction_ID = $_POST['refid'];
                $Message = wp_remote_retrieve_body( $VerifyResponse ) . '<br /> شماره خطا: ' . $ResponseXpId;
                $Fault = $Message;
                return array( 'status' => $Status, 'transaction_id' => $Transaction_ID, 'message' => $Message, 'fault' => $Fault );
            }
        }
    }
    /* End Verify */
    
    /* Start Store List Count */
    public function StoreListCount( $params ){
        $Response = self::PayPingRequest( '/v1/affiliate/store/create', 'get', array(), 'StoreListCount' );
        return $Response;
    }
    /* End Store List Count */
    
    /* Start CheckStore */
    public function CheckStore(){
        $Response = self::PayPingRequest( '/v1/affiliate/store/checkstore', 'get', array(), 'CheckStore' );
        return $Response;
    }
    /* End CheckStore */
    
    /* Start withdraw */
    public function withdraw( $amount ){
        $Response = self::PayPingRequest( '/v1/withdraw/' . $amount, 'post', array(), 'withdraw' );
        return $Response;
    }
    /* End withdraw */
    
    /* Start CreateStore */
    public function CreateStore( $params ){
        $Response = self::PayPingRequest( '/v1/affiliate/store/create', 'post', $params, 'CreateStore' );
        return $Response;
    }
    /* End CreateStore */
    
    /* Start TransactionReportCount */
    public function TransactionReportCount( $params ){
        $Response = self::PayPingRequest( '/v1/report/TransactionReportCount', 'post', $params, 'TransactionReportCount' );
        return $Response;
    }
    /* End TransactionReportCount */
    
    /* Start TransactionReport */
    public function TransactionReport( $params ){
        $Response = self::PayPingRequest( '/v1/report/TransactionReport', 'post', $params, 'TransactionsList' );
        return $Response;
    }
    /* End TransactionReport */
    
    /* Start TransActionDetails */
    public function TransActionDetails( $PaymentCode ){
        $Response = self::PayPingRequest( '/v1/report/' . $PaymentCode, 'get', array(), 'GetTransaction' );
        return $Response;
    }
    /* End TransActionDetails */
    
    /* Start Balance */
    public function GetBalance(){
        $Response = self::PayPingRequest( '/v1/report/Balance', 'get', array(), 'GetBalance' );
        return $Response;
    }
    /* End Balance */
	
	public function status_message($code){
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
    
	/* Start UnVerifiedPayment */
    public function UnVerifiedPayment( $params ){
        $Response = self::PayPingRequest( '/v1/pay/UnVerifiedPayment', 'get', $params, 'UnVerifiedPayment' );
        return $Response;
    }
    /* End UnVerifiedPayment */
	
}