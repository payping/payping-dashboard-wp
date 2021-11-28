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
		<input type="text" name="amount" placeholder="مبلغ به تومان" dir="ltr" class="regular-text"> 
		<input type="text" name="code" placeholder="کد پرداخت" dir="ltr" class="regular-text">
		<input type="text" name="payerName" placeholder="نام پرداخت کننده" class="regular-text">
	</p>
	<p>
		<input type="text" name="payerPhoneNumber" placeholder="شماره تلفن پرداخت کننده" dir="ltr" class="regular-text">
		<input type="text" name="cardNumber" placeholder="شماره کارت پرداخت کننده" dir="ltr" class="regular-text">
		<input type="submit" value="جستجو" class="button button-primary" name="searchtrsnatn">
	</p>
</form>
<div class="button-group" style="margin-bottom: 15px;">
	<a href="" style="font-size:0;" id="ppd_download_file" target="_blank">دانلود کنید</a>
	<a href="javascript:;" data-type="excel" title="دانلود گزارش بصورت excel" class="button button-secondary ppd_download_reports">Excel</a>
	<a href="javascript:;" data-type="pdf" title="دانلود گزارش بصورت PDF" class="button button-secondary ppd_download_reports">PDF</a>
	<span id="ppd_spinner" class="spinner"></span>
</div>
<table class="wp-list-table widefat fixed striped posts" style="margin-bottom: 15px;">
	<thead>
		<tr>
			<td id="cb" class="manage-column column-cb check-column">
				<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
				<input id="cb-select-all-1" type="checkbox">
			</td>
			<th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
				<span>پرداخت کننده</span>
			</th>
			<th scope="col" id="author" class="manage-column column-author">کد پرداخت</th>
			<th scope="col" id="categories" class="manage-column column-categories">تاریخ</th>
			<th scope="col" id="tags" class="manage-column column-tags">وضعیت</th>
			<th scope="col" id="tags" class="manage-column column-tags">مبلغ</th>
		</tr>
	</thead>
	<tbody id="the-list">
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
		<tr id="" class="iedit author-self level-0 type-post status-publish format-standard hentry" data-href='<?php echo admin_url("admin.php?page=payping-transactions&code=".$Transaction["code"]); ?>'>
			<th scope="row" class="check-column">
				<label class="screen-reader-text" for="cb-select-1">Select Post #1</label>
				<input id="cb-select-1" type="checkbox" name="post[]" value="1">
				<div class="locked-indicator">
					<span class="locked-indicator-icon" aria-hidden="true"></span>
					<span class="screen-reader-text">
						“Post #1” is locked
					</span>
				</div>
			</th>
			<td class="author column-author" data-colname="Author">
				<a href='<?php echo admin_url("admin.php?page=payping-transactions&code=".$Transaction["code"]); ?>' title="مشاهده جزئیات پرداخت" target="_self" style="text-decoration: none;">
                   		<?php echo $Transaction['name']; ?>
				</a>
			</td>
			<td class="categories column-categories" data-colname="Categories">
				<?php echo $Transaction['code']; ?>
			</td>
			<td class="tags column-tags" data-colname="Tags">
				<?php echo date_i18n('Y-m-d ساعت: H:i:s', strtotime( $Transaction['payDate'])); ?>
			</td>
			<td class="comments column-comments" data-colname="Comments">		
				<?php echo $status; ?>
			</td>
			<td class="date column-date" data-colname="Date">
				<?php echo number_format($Transaction['amount'], 0, "،", "،"); ?> تومان
			</td>
		</tr>					
		<?php endforeach; ?>									
</tbody>
	<tfoot>
		<tr>
			<td id="cb" class="manage-column column-cb check-column">
				<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
				<input id="cb-select-all-1" type="checkbox">
			</td>
			<th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
				<span>پرداخت کننده</span>
			</th>
			<th scope="col" id="author" class="manage-column column-author">کد پرداخت</th>
			<th scope="col" id="categories" class="manage-column column-categories">تاریخ</th>
			<th scope="col" id="tags" class="manage-column column-tags">وضعیت</th>
			<th scope="col" id="tags" class="manage-column column-tags">مبلغ</th>
		</tr>
</tfoot>
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
                    <span class="tablenav-paging-text"><?= $this->page ?> از 
				    	<span class="total-pages"><?= $pageCount ?></span>
               		</span>
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