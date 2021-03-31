<?php
if( isset( $_POST['settleDone'] ) &&  isset( $_POST['SettleAmount'] ) ){
    $amount = intval( $_POST['SettleAmount'] );
    if( 1000 >= $amount && $amount >= 50000000 ){
        $parent = new PayPingAPIS();
        $response = $parent->withdraw( $amount );
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