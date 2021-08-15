<?php
if( isset( $_GET['action'] ) && $_GET['action'] === 'settle' ){
    require_once( PPD_GPPDIR . 'admin/transactions/settle.html.php');
    return;
}
$limit           = 15;
if( !isset( $_GET['p'] ) || $_GET['p'] <= 1){
    $offset = 0;
}else{
    $offset = ( $_GET['p'] - 1 )*$limit;
}
$Filter           = array();
$transactionType  = 0;
$FromDate         = null;
$ToDate           = null;
$cardNumber       = null;
$payerName        = null;
$code             = null;
$payerPhoneNumber = null;
$amount           = 0;

if( isset( $_POST['searchtrsnatn'] ) ){
	if( isset( $_POST['amount'] ) && ! empty( $_POST['amount'] ) ){
		$amount = $_POST['amount'];
	}
	
	if( isset( $_POST['code'] ) && ! empty( $_POST['code'] ) ){
		$code = $_POST['code'];
	}
	
	if( isset( $_POST['payerName'] ) && ! empty( $_POST['payerName'] ) ){
		$payerName = $_POST['payerName'];
	}
	
	if( isset( $_POST['payerPhoneNumber'] ) && ! empty( $_POST['payerPhoneNumber'] ) ){
		$payerPhoneNumber = $_POST['payerPhoneNumber'];
	}
	
	if( isset( $_POST['cardNumber'] ) && ! empty( $_POST['cardNumber'] ) ){
		$cardNumber = $_POST['cardNumber'];
	}
}

$params = array(
 	"amount"           => $amount,
    "code"             => $code,
    "fromDate"         => $FromDate,
    "toDate"           => $ToDate,
    "limit"            => $limit,
    "offset"           => $offset,
    "payerName"        => $payerName,
    "cardNumber"       => $cardNumber,
	"paymentStatus"    => $transactionType,
	"payerPhoneNumber" => $payerPhoneNumber
);

$parent = new PayPingAPIS();
$response = $parent->AdvancedTransactionReport( $params );
$code = wp_remote_retrieve_response_code( $response );
if( $code === 200 ){
    $Transactions = json_decode( wp_remote_retrieve_body( $response ), true );
?>
<h3>جستجوی تراکنش</h3>
<form method="post">
	<p>
		<input type="text" name="amount" placeholder="مبلغ به تومان" dir="ltr"> 
		<input type="text" name="code" placeholder="کد پرداخت" dir="ltr">
		<input type="text" name="payerName" placeholder="نام پرداخت کننده">
		<input type="text" name="payerPhoneNumber" placeholder="شماره تلفن پرداخت کننده" dir="ltr">
		<input type="text" name="cardNumber" placeholder="شماره کارت پرداخت کننده" dir="ltr">
		<input type="submit" value="جستجو" class="button" name="searchtrsnatn">
	</p>
</form>
<table class="form-table" style="border: 1px solid #fff; margin-bottom: 25px;">
    <thead>
        <tr style="text-align: center; background-color:#fff;">
            <th colspan="1" rowspan="1" style="text-align: center;">
               پرداخت کننده
            </th>
            <th colspan="1" rowspan="1" style="text-align: center;">
                کد پرداخت
            </th>
            <th colspan="1" rowspan="1" style="text-align: center;">
                تاریخ
            </th>
            <th colspan="1" rowspan="1" style="text-align: center;">
              وضعیت
            </th>
            <th colspan="1" rowspan="1" style="text-align: center;">
               مبلغ
            </th>
        </tr>
    </thead>
    <tbody style="text-align: center; background-color:#eee;">
        <?php foreach( $Transactions as $Transaction ):
			if( $Transaction['isRequest'] === true ){
				if( $Transaction['isPaid'] === true ){
					$status = '<b style="color: rgb(0, 185, 35);">پرداخت شده</b>';
				}else{
					$status = '<b style="color: yellow;">در انتظار پرداخت</b>';
				}
			}else{
				if( $Transaction['isPaid'] === true ){
					$status = '<b style="color: rgb(0, 185, 35);">پرداخت شده</b>';
				}else{
					$status = '<b style="color: red;">پرداخت نشده</b>';
				}
			}
		$isPaid = $Transaction['isPaid']; ?>
        <tr valign="top" data-href='<?php echo admin_url("admin.php?page=payping-transactions&code=".$Transaction["code"]); ?>' style="border-bottom:1px solid #fff;">
                <td style="text-align: center;">
                    <a href='<?php echo admin_url("admin.php?page=payping-transactions&code=".$Transaction["code"]); ?>' title="مشاهده جزئیات پرداخت" target="_self" style="text-decoration: none;">
                   		<?php echo $Transaction['name']; ?>
                    </a>
                </td>
                <td style="text-align: center;">
                  <?php echo $Transaction['code']; ?>
                </td>
                <td style="text-align: center;">
                    <?php echo date_i18n('Y-m-d ساعت: H:i:s', strtotime( $Transaction['payDate'])); ?>
                </td>
                <td style="text-align: center;">
                   <?php echo $status; ?>
                </td>
                <td style="text-align: center;">
                   <?php echo number_format($Transaction['amount'], 0, "،", "،"); ?> تومان
                </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfooter>
        <tr style="text-align: center; background-color:#fff;">
            <th colspan="1" rowspan="1" style="text-align: center;">
               پرداخت کننده
            </th>
            <th colspan="1" rowspan="1" style="text-align: center;">
                کد پرداخت
            </th>
            <th colspan="1" rowspan="1" style="text-align: center;">
                تاریخ
            </th>
            <th colspan="1" rowspan="1" style="text-align: center;">
              وضعیت
            </th>
            <th colspan="1" rowspan="1" style="text-align: center;">
               مبلغ
            </th>
        </tr>
    </tfooter>
</table>
<?php
$response = $parent->AdvancedTransactionReportCount( $params );
$Total = json_decode( wp_remote_retrieve_body( $response ) );
	
class pp_pagination {
	private $total = 0;
	private $per_page = 50;
	private $page = 0;

	public function __construct( $total, $per_page, $page ) {
		$this->total    = $total;
		$this->page     = $page;
		$this->per_page = $per_page;

		if($per_page == 0){
		    $this->per_page = 50;
        }

		if ( $page <= 0 ) {
			$this->page = 1;
		}
		if ( $page > ceil( $total / $per_page ) ) {
			$this->page = ceil( $total / $per_page );
		}
	}

	// determine what the current page is also, it returns the current page
	public function show() {
		$pageCount = ceil( $this->total / $this->per_page );
		$prev      = $this->page - 1;
		$next      = $this->page + 1;
		?>
        <div class="tablenav-pages">
            <span class="pagination-links">
               <a class="button prev-page" href="<?=$_SERVER['REQUEST_URI'] ?>&p=<?=$prev?>">
                   <span class="screen-reader-text">برگه قبل</span>
                   <span aria-hidden="true">‹</span>
               </a>

                <span id="table-paging" class="paging-input">
                    <span class="tablenav-paging-text"><?= $this->page ?> از <span
                                class="total-pages"><?= $pageCount ?></span></span>
                </span>
                <a class="button next-page" href="<?=$_SERVER['REQUEST_URI'] ?>&p=<?= $next ?>">
                        <span class="screen-reader-text">برگه بعد</span>
                        <span aria-hidden="true">›</span>
                </a>
            </span>
        </div>

		<?php
	}
}
if( !isset( $_GET['p'] ) || $_GET['p'] <= 1){
    $page = 1;
}else{
    $page = $_GET['p'];
}
$paginator = new pp_pagination( $Total->result, $limit, $page );
$paginator->show();
	}else{
	echo $parent->status_message( $code );
}