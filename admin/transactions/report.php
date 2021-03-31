<?php
$PaymentCode = $_GET['code'];
$parent = new PayPingAPIS();
$response = $parent->TransActionDetails( $PaymentCode );
$code = wp_remote_retrieve_response_code( $response );
if( $code === 200 ){
    $Details = json_decode( wp_remote_retrieve_body( $response ), true );
	if( $Details['isRequest'] === true ){
		if( $Details['isPaid'] === true ){
			$status = '<button class="success-paid">پرداخت شده</button>';
		}else{
			$status = '<button class="faild-paid">در انتظار پرداخت</button>';
		}
	}else{
		if( $Details['isPaid'] === true ){
			$status = '<button class="success-paid">پرداخت شده</button>';
		}else{
			$status = '<button class="faild-paid">پرداخت نشده</button>';
		}
	}
}
?>
<div class="payping-detail-content">
	<?php echo $status; ?>
	<div class="hr"></div><div style="clear: both;"></div>
	<h2 class="title-detail-pay">مشخصات درخواست</h2>
	<div class="ppd-row-input-place">
		<div class="ppd-col-input-place">
			<span class="ppd-title-input-place">کد پرداخت</span><span class="ppd-value-input-place"><?php echo $_GET['code']; ?></span>
		</div>
		<div class="ppd-col-input-place">
			<span class="ppd-title-input-place">پرداخت کننده</span><span class="ppd-value-input-place"><?php echo $Details['name']; ?></span>
		</div>
		<div class="ppd-col-input-place">
			<span class="ppd-title-input-place">شماره کارت</span><span class="ppd-value-input-place"><?php echo $Details['rrn']; ?></span>
		</div>
		<div class="ppd-col-input-place">
			<span class="ppd-title-input-place">نوع پرداخت</span><span class="ppd-value-input-place">-</span>
		</div>
	</div>
	<div class="ppd-desc-details">
		<h2>توضیح</h2>
		<p>
			<?php echo $Details['description']; ?>
		</p>
	</div>
	<h2 class="title-detail-pay">مشخصات پرداخت</h2>
	<div class="ppd-row-input-place">
		<div class="ppd-col-input-place">
			<span class="ppd-title-input-place">مبلغ</span><span class="ppd-value-input-place"><?php echo $Details['amount']; ?></span>
		</div>
		<div class="ppd-col-input-place">
			<span class="ppd-title-input-place">تاریخ</span><span class="ppd-value-input-place"><?php echo date_i18n('Y-m-d H:i', strtotime( $Details['payDate'])); ?></span>
		</div>
		<div class="ppd-col-input-place">
			<span class="ppd-title-input-place">شناسه پرداخت</span><span class="ppd-value-input-place"><?php echo $Details['clientRefId']; ?></span>
		</div>
		<div class="ppd-col-input-place">
			<span class="ppd-title-input-place">کارمزد تراکنش</span><span class="ppd-value-input-place" dir="rtl"><?php echo $Details['wage']; ?></span>
		</div>
	</div>
</div>
<?php else{
    echo $parent->status_message( $code );
}