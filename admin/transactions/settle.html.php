<?php
if( isset( $_POST['settleDone'] ) &&  isset( $_POST['SettleAmount'] ) ){
	$amount = intval( $_POST['SettleAmount'] );
	
    if( 1000 <= $amount &&  50000000 >= $amount ){
        $parent = new PayPingAPIS();
        $response = $parent->withdraw( $amount );
		$code = wp_remote_retrieve_response_code( $response );
		if( $code === 200 ){
			$Details = json_decode( wp_remote_retrieve_body( $response ), true );
			$code = $Details['code'];
			if( isset( $code ) ){
				echo 'درخواست تسویه با موفقیت ثبت شد. شماره پیگیری: '.$code;
			}else{
				echo 'خطایی رخ داده، لطفا مجددا تلاش کنید.';
			}
		}
    }else{
		echo 'مبلغ باید بیشتر از 1000 تومان و کمتر از 50،000،000 تومان باشد.';
	}
}
?>
<div class="wp-filter" style="padding: 10px">
    <form method="post" action="<?php echo admin_url('admin.php?page=payping-transactions&action=settle'); ?>">
        <table class="form-table">
            <tr valign="top">
            <th scope="row">مبلغ تسویه</th>
            <td><input class="regular-text" type="text" name="SettleAmount"  value="<?php global $price; echo $price; ?>"/> تومان</td>
            </tr>
        </table>
        <?php submit_button( __( 'تسویه حساب', 'textdomain' ), 'secondary', 'settleDone'); ?>
    </form>
</div>