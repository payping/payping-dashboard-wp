<?php 
if( isset( $_GET['PayCode'] ) && $_GET['action'] === 'process' ){
	/* check payment code */
	$PaymentCode = esc_sql( $_GET['PayCode'] );
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
	/* end payment code */
}
?><div class="wrap">
    <h1 class="wp-heading-inline">مغایرت گیری  تراکنش ها</h1>
    <?php do_action('admin_notices_pp'); ?>
    <table class="wp-list-table widefat fixed striped" cellspacing="0">
		<thead>
			<tr>
                <th style="text-align:center;" >تاریخ </th>
                <th style="text-align:center;" >کد پرداخت</th>
                <th style="text-align:center;" >شناسه فاکتور</th>
                <th style="text-align:center;" >مبلغ</th>
                <th style="text-align:center;">وضعیت</th>
                <th style="text-align:center;"></th>
			</tr>
		</thead>
		<tbody>
<?php
$wc_orders = wc_get_orders( array(
    'limit'          => -1,
	'meta_key'       => '_payping_payCode',
	'payment_method' => array('WC_payping', 'WC_payping_Ghesta'),
	'status'         => array( 'pending', 'on-hold', 'cancelled', 'failed' ),
) );
if( $wc_orders ){
	foreach( $wc_orders as $order ):
		$oder_id = $order->id;
		$refid = get_post_meta($oder_id, '_payping_payCode', true);
		$actionURL = admin_url( 'admin.php?page=payping-deposite&order_id='.$oder_id.'&PayCode='.$refid.'&action=process' );
		$btnDepost = '<a href="'. $actionURL .'" class="btn btn-primary">مغایرت‌گیری</a>'; ?>
				<tr class='clickable-row' data-href=''>
                    <td style="text-align:center">
                    <strong dir="ltr">
                    <?php echo date_i18n( 'Y-m-d H:i:s', strtotime( $order->get_date_created() ) ); ?>
                   </strong></td>
                    <td style="text-align:center"><span>
                    	<?php echo $refid; ?>
                    </span></td>
                    <td style="text-align:center"><span>
                    	<?php echo $oder_id; ?>
                    </span></td>
                    <td style="text-align:center"><span>
					<?php echo $order->get_total().'تومان'; ?>
                   </span></td>
                    <td style="text-align:center"><span>
						<?php echo $order->get_status(); ?>
                   </span></td>
                    <td>
                    <?php echo $btnDepost; ?>
                    </td>
                </tr>   

<?php endforeach; }else{ ?>
                <tr><td colspan=5 style="text-align:center">هیچ سفارشی یافت نشد</td></tr>
            <?php } ?>
		</tbody>

		<tfoot>
			<tr>
                <th style="text-align:center;" >تاریخ </th>
                <th style="text-align:center;" >کد پرداخت</th>
                <th style="text-align:center;" >شناسه فاکتور</th>
                <th style="text-align:center;" >مبلغ</th>
                <th style="text-align:center;">وضعیت</th>
                <th style="text-align:center;"></th>
			</tr>
		</tfoot>
	</table>
</div>