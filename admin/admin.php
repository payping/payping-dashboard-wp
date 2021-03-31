<?php
class PayPingAdminPage{
    public function __construct(){
        // Wordpress Hooks In Plugin
        add_action( 'admin_bar_menu',  array( $this, 'PayPing_WP_Admin_Bar_Menu' ), 999 );
        add_action( 'admin_menu', array( $this, 'PayPing_WP_Plugin_Menu' ) );
        add_action( 'admin_init', array( $this, 'PayPing_WP_Plugin_Settings' ) );
    }
    
    /* Start Create Menu In Dashboard Wordpress */
    public function PayPing_WP_Plugin_Menu(){
        add_menu_page( __( 'پی‌پینگ' ), __( 'پی‌پینگ', '' ), 'manage_options', 'payping', array( $this, 'PayPing_WP_Plugin_Main_Page' ), PPD_GPPDIRU .'/assets/images/logo.png', 32 );
        add_submenu_page( 'payping' , __( 'تراکنش‌ها' ), __( 'تراکنش‌ها', '' ), 'manage_options', 'payping-transactions', array( $this, 'PayPing_WP_Plugin_Transactions_Page' ) );
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
    }
    /* End Settings In Wordpress */
    
    /* Start Balance In Wordpress */
    public function PayPing_WP_Admin_Bar_Menu( $wp_admin_bar ){
        if( ! current_user_can( 'manage_options' ) ){ return; }
        global $price;
        $price = 0;
//        $Balance = wp_cache_get( 'PayPingBalance', 'payping', true );
        $Balance = get_transient( 'PayPingBalance' );
        if( false === $Balance ){
            $parent = new PayPingAPIS();
            $balance = json_decode( wp_remote_retrieve_body( $parent->GetBalance() ) );
            $price = $balance->result;
			set_transient( 'PayPingBalance', $price, 1 * HOUR_IN_SECONDS );
//            wp_cache_set( 'PayPingBalance', $price, 'payping', 1 * HOUR_IN_SECONDS );
        }else{
			$price = $Balance;
		}
        $title = 'موجودی پی‌پینگ: '.number_format($price, 0, "،", "،"). ' تومان';
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
}