<?php 
class PAYPING_DASHBOARD_AJAX{
  // Put all your add_action, add_shortcode, add_filter functions in __construct()
  // For the callback name, use this: array($this,'<function name>')
  // <function name> is the name of the function within this class, so need not be globally unique
  // Some sample commonly used functions are included below
    public function __construct(){
        // Add Javascript and CSS for front-end display
        add_action('admin_enqueue_scripts', array($this,'enqueue'));

        // Add ajax function that will receive the call back for logged in users
        add_action( 'wp_ajax_ppd_reports_action', array( $this, 'action_callback') );
        // Add ajax function that will receive the call back for guest or not logged in users
        add_action( 'wp_ajax_nopriv_ppd_reports_action', array( $this, 'action_callback') );
    }

    // This is an example of enqueuing a JavaScript file and a CSS file for use on the front end display
    public function enqueue(){
        // Actual enqueues, note the files are in the js and css folders
        // For scripts, make sure you are including the relevant dependencies (jquery in this case)
		wp_register_script('ppd_report_detail_wp_admin', PPD_GPPDIRU.'assets/js/payping_script.js', array('jquery'), false, true);
		wp_enqueue_script('ppd_report_detail_wp_admin');
		
		// Sometimes you want to have access to data on the front end in your Javascript file
        // Getting that requires this call. Always go ahead and include ajaxurl. Any other variables,
        // add to the array.
		wp_localize_script('ppd_report_detail_wp_admin', 'payping_wp_dashboard', array( 'ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('_ppd_nonce')));
    }

     /**
     * Callback function for the my_action used in the form.
     *
     * Processses the data recieved from the form, and you can do whatever you want with it.
     *
     * @return    echo   response string about the completion of the ajax call.
     */
    public function action_callback(){
        // echo wp_die('<pre>' . print_r($_REQUEST) . "<pre>");
        check_ajax_referer( '_ppd_nonce', 'security');
        if( ! empty( $_POST )){
            if( isset( $_POST['type'] ) ){
                $type = sanitize_text_field( $_POST['type'] );
				if( $type == 'pdf' ){
					$requrl = 'https://api.payping.ir/v2/upload/PaymentsFullExport';
				}else{
			    	$requrl = 'https://api.payping.ir/v1/upload/PaymentsExcelExport';
				}
            }else{
				$err = "مشکلی در ارسال درخواست وجود دارد! مجددا تلاش کنید.";
			}

			$TokenCode = get_option('PayPing_TokenCode');
			$ClientsInfo = array( 'ClientId' => "", 'ClientRefId' => "" );

			$body = array(
				'ClientsInfo'     => $ClientsInfo,
				'Filter'          => null,
				'limit'           => 10,
				'offset'          => 0,
				'transactionType' => 6
			);
			$args = array(
            'timeout'      => 30,
            'redirection'  => '5',
            'httpsversion' => '1.0',
            'blocking'     => true,
            'headers'      => array(
                                  'Authorization' => 'Bearer '.$TokenCode,
                                  'Content-Type'  => 'application/json',
                                  'Accept'        => 'application/json'
                              ),
            'cookies'      => array(),
			'body'         => json_encode( $body, true )
         	);
			$response = wp_remote_post($requrl, $args);
			$code = wp_remote_retrieve_response_code( $response );
			if( $code == 200 ){
				$body = json_decode(wp_remote_retrieve_body($response), true);
				echo $body;
			}else{
				$err = 'عدم دریافت کد 200!';
			}
            ///////////////////////////////////////////
            // do stuff with values
            // example : validate and save in database
            //          process and output
            /////////////////////////////////////////// 

            //this will send data back to the js function:
        }else{
            echo $err;
        }
        wp_die(); // required to terminate the call so, otherwise wordpress initiates the termination and outputs weird '0' at the end.
    }
}
// Create an instance of our class to kick off the whole thing
new PAYPING_DASHBOARD_AJAX();