<?php
class PayPingAdminPage{
    public function __construct(){
        // Wordpress Hooks In Plugin
        add_action( 'admin_bar_menu',  array( $this, 'PayPing_WP_Admin_Bar_Menu' ), 999 );
        add_action( 'admin_menu', array( $this, 'PayPing_WP_Plugin_Menu' ) );
        add_action( 'admin_init', array( $this, 'PayPing_WP_Plugin_Settings' ) );
        add_action( 'admin_init', array( $this, 'set_total_transactions' ) );
    }
    
    /* Start Create Menu In Dashboard Wordpress */
    public function PayPing_WP_Plugin_Menu(){
        add_menu_page( __( 'پی‌پینگ' ), __( 'پی‌پینگ', '' ), 'manage_options', 'payping', array( $this, 'PayPing_WP_Plugin_Main_Page' ), PPD_GPPDIRU .'/assets/images/logo.png', 32 );
        add_submenu_page( 'payping' , __( 'تراکنش‌ها' ), __( 'تراکنش‌ها', '' ), 'manage_options', 'payping-transactions', array( $this, 'PayPing_WP_Plugin_Transactions_Page' ) );
		add_submenu_page('payping', 'مغایرت گیری', 'مغایرت گیری', 'manage_options', 'payping-deposite', array( $this, 'PayPing_WP_Plugin_Deposit_Page' ));
//        add_submenu_page( 'payping', __( 'همکاری در فروش' ), __( 'همکاری در فروش', '' ), 'manage_options', 'payping-affiliate', array( $this, 'PayPing_WP_Plugin_Affiliate_Page' ) );
        add_submenu_page( 'payping', __( 'تنظیمات' ), __( 'تنظیمات', '' ), 'manage_options', 'payping-setting', array( $this, 'PayPing_WP_Plugin_Settings_Page' ) );
        remove_submenu_page( 'payping','payping' );
    }
    /* End Create Menu In Dashboard Wordpress */

    /* Start Transactions Page */
    public function PayPing_WP_Plugin_Transactions_Page(){
        echo '<div class="wrap">';
        _e( '<h2>تراکنش‌ها</h2>', 'PayPing' );
        if( isset( $_GET['code'] ) && !empty( $_GET['code'] && $_GET['page'] === 'payping-transactions' ) ){
           require_once(PPD_GPPDIR . '/admin/transactions/report.php');
        }else{
           require_once(PPD_GPPDIR . 'admin/transactions/reports.php');
        }
        echo '</div>';
    }
    /* End Transactions Page */
	
	/* Start Transactions Page */
    public function PayPing_WP_Plugin_Deposit_Page(){
        echo '<div class="wrap">';
        _e( '<h2>مغایرت گیری</h2>', 'PayPing' );
        if( isset( $_GET['code'] ) && !empty( $_GET['code'] && $_GET['page'] === 'payping-transactions' ) ){
           require_once(PPD_GPPDIR . '/admin/deposit/deposit-single.php');
        }else{
           require_once(PPD_GPPDIR . 'admin/deposit/deposit-order.php');
        }
        echo '</div>';
    }
    /* End Transactions Page */
    
    /* Start Affiliate Page */
    public function PayPing_WP_Plugin_Affiliate_Page(){
        echo '<div class="wrap">';
        _e( '<h2>همکاری در فروش</h2>', 'PayPing' );
        if( isset( $_POST['createstor'] ) ){
            require_once( dirname( __FILE__ ) . '/affiliate/CreateStore.php');
        }
        /* Get Store == true */
        $parent = new PayPingAPIS();
        $response = json_decode( wp_remote_retrieve_body( $parent->CheckStore() ) );
        if( !empty( $response->code ) ){
            require_once( dirname( __FILE__ ) . '/affiliate/Store.html.php');
        }else{
            require_once( dirname( __FILE__ ) . '/affiliate/CreateStore.html.php');
        }
        echo '</div>';
    }
    /* End Affiliate Page */
 
    /* Start Settings Page */
    public function PayPing_WP_Plugin_Settings_Page(){
        echo '<div class="wrap">';
        _e( '<h1>پی‌پینگ</h1>', '' );
        ?>
        <form id="donate_payping" method="post" action="options.php">
        <?php settings_fields( 'PayPing_WP_Plugin_Settings' ); ?>
        <?php do_settings_sections( 'PayPing_WP_Plugin_Settings' ); ?>
           <table class="form-table" role="presentation">
               <tbody>
                   <tr>
                       <th scope="row"><label for="blogname">توکن پی‌پینگ</label></th>
                       <td>
                           <input type="text" class="regular-text" name="PayPing_TokenCode" placeholder="<?php _e('توکن پی‌پینگ', 'PayPing'); ?>" value="<?php echo get_option('PayPing_TokenCode'); ?>" style="text-align:left;">
                       </td>
                   </tr>
                    <tr>
                       <th scope="row"><label for="blogname">مغایرت‌گیری خودکار</label></th>
                       <td>
                          <select name="PayPing_Deposit" id="PayPing_Deposit">
                             <option value="deactive" <?php if( get_option('PayPing_Deposit') === 'deactive' ) echo 'selected'; ?>><?php _e('غیرفعال', 'PayPing'); ?></option>
                             <option value="active" <?php if( get_option('PayPing_Deposit') === 'active' ) echo 'selected'; ?>><?php _e('فعال', 'PayPing'); ?></option>
                          </select>
                          <p class="description" id="home-description" style="padding: 10px 0 10px 15px;color: #0080ff;">
                               <b>این مورد آزمایشی است و فقط برای 50 سفارش آخر ووکامرس عمل می‌کند! </b>    
                           </p>
                       </td>
                   </tr>
                   <tr>
                       <th scope="row"><label for="blogname">بازه مغایرت‌گیری(ساعت)</label></th>
                       <td>
                          <select name="PayPing_DepositT" id="PayPing_DepositT">
                             <option value="1" <?php if( get_option('PayPing_DepositT') === '1' ) echo 'selected'; ?>><?php _e('1 ساعت', 'PayPing'); ?></option>
                             <option value="3" <?php if( get_option('PayPing_DepositT') === '3' ) echo 'selected'; ?>><?php _e('3 ساعت', 'PayPing'); ?></option>
                             <option value="6" <?php if( get_option('PayPing_DepositT') === '6' ) echo 'selected'; ?>><?php _e('6 ساعت', 'PayPing'); ?></option>
                             <option value="12" <?php if( get_option('PayPing_DepositT') === '12' ) echo 'selected'; ?>><?php _e('12 ساعت', 'PayPing'); ?></option>
                          </select>
                       </td>
                   </tr>
                   <tr>
                       <th scope="row"><label for="blogname">حالت اشکال‌زدایی</label></th>
                       <td>
                          <select name="PayPing_DebugMode" id="PayPing_DebugMode">
                             <option value="deactive" <?php if( get_option('PayPing_DebugMode') === 'deactive' ) echo 'selected'; ?>><?php _e('غیرفعال', 'PayPing'); ?></option>
                             <option value="active" <?php if( get_option('PayPing_DebugMode') === 'active' ) echo 'selected'; ?>><?php _e('فعال', 'PayPing'); ?></option>
                          </select>
                          <p class="description" id="home-description">
                               این مورد فقط برای زمانی انتخاب شود که می‌خواهید اشکال‌زدایی کنید، در حالت عادی <strong>غیرفعال</strong> باشد.     
                           </p>
                       </td>
                   </tr>
                   <tr>
                       <th scope="row"><label for="blogname">آدرس جایگزین</label></th>
                       <td>
                           <input type="text" class="regular-text" name="PayPing_DebugUrl" placeholder="https://api.payping.ir" value="<?php echo get_option('PayPing_DebugUrl'); ?>" style="text-align:left;">
                           <p class="description" id="home-description">
                               درحالت اشکال‌زدایی این آدرس جایگزین آدرس درخواست به سرویس‌های پی‌پینگ می‌شود.     
                           </p>
                       </td>
                   </tr>
               </tbody>
           </table>
        <?php submit_button(); ?>
        </form>
  <?php echo '</div>';
    }
    /* End Settings Page */
    
    /* Start Settings In Wordpress */
    public function PayPing_WP_Plugin_Settings(){
        $text_args = array(
            'type' => 'string', 
            'sanitize_callback' => 'sanitize_text_field',
            'default' => NULL,
        );
        register_setting( 'PayPing_WP_Plugin_Settings', 'PayPing_TokenCode', $text_args );
        register_setting( 'PayPing_WP_Plugin_Settings', 'PayPing_DebugMode', $text_args );
        register_setting( 'PayPing_WP_Plugin_Settings', 'PayPing_DebugUrl', $text_args );
        register_setting( 'PayPing_WP_Plugin_Settings', 'PayPing_Deposit', $text_args );
        register_setting( 'PayPing_WP_Plugin_Settings', 'PayPing_DepositT', $text_args );
    }
    /* End Settings In Wordpress */
    
    /* Start Balance In Wordpress */
    public function PayPing_WP_Admin_Bar_Menu( $wp_admin_bar ){
        if( ! current_user_can( 'manage_options' ) ){ return; }
        global $price;
        $price = 0;
//        $Balance = wp_cache_get( 'PayPingBalance', 'payping', true );
        $Balance = get_transient( 'PayPingBalance' );
        if( false == $Balance ){
            $parent = new PayPingAPIS();
            $balance = json_decode( wp_remote_retrieve_body( $parent->GetBalance() ) );
            $price = $balance->result;
			set_transient( 'PayPingBalance', $price, 1 * HOUR_IN_SECONDS );
//            wp_cache_set( 'PayPingBalance', $price, 'payping', 1 * HOUR_IN_SECONDS );
        }else{
			$price = $Balance;
		}
		if( isset( $price ) && !empty( $price ) ){
			$title = 'موجودی پی‌پینگ: '.number_format($price, 0, "،", "،"). ' تومان';
		}else{
			$title = 'عدم موجودی';
		}
        
        $argsB = array(
            'id'    => 'payping_balance',
            'title' => $title,
            'href'  => '#',
            'meta'  => array( 'class' => 'payping-balance-toolbar-page', 'title' => __( 'برای بروزرسانی مبلغ کلیک کنید', 'textdomain' ) ), //This title will show on hover )
        );
		$args = array(
            'id'    => 'payping_settle',
			'parent'=> 'payping_balance',
            'title' => 'تسویه حساب',
            'href'  => admin_url( 'admin.php?page=payping-transactions&action=settle'),
            'meta'  => array( 'class' => 'payping-settle-toolbar-page' )
        );
        $wp_admin_bar->add_menu( $argsB );
        $wp_admin_bar->add_menu( $args );
		add_action( 'admin_bar_menu', 'admin_bar_item', 500 );
    }
    /* End Balance In Wordpress */

    /** update total transactions */
    public function set_total_transactions(){
        $parent = new PayPingAPIS();
        $params = array(
            "clientsInfos"     => array(),
            "filter"           => array(),
            "transactionType"  => 6,
            "fromDate"         => null,
            "toDate"           => null
        );
       /* insert data in db */
       $response = $parent->AdvancedTransactionReportCount( $params );
       $Total = json_decode( wp_remote_retrieve_body( $response ), true );
       $Old_Total = get_option('payping_total_transactions');
       if( $Old_Total != $Total || $Old_Total === false ){
           update_option('payping_total_transactions', $Total['result']);
       }
       $this->insert_transactions_indb();
    }

    private function insert_transactions_indb(){
        if(!get_option('payping_transactions_table', false)){
            global $wpdb;
            $table_name = $wpdb->prefix.'payping_transactions';
            if($wpdb->get_var("SHOW TABLES LIKE '". $table_name ."'"  ) != $table_name ){
                $sql  = 'CREATE TABLE '.$table_name.'(
                id INT(20) AUTO_INCREMENT,
                details VARCHAR(255),
                client_id VARCHAR(255),
                type VARCHAR(255),
                time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY(id))';

                if(!function_exists('dbDelta')) {
                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                }
                dbDelta($sql);
                update_option('payping_transactions_table', true);
            }
        }
    }
}