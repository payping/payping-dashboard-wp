<table class="form-table">
    <thead>
        <tr valign="top">
            <th scope="col">نام فروشگاه</th>
            <th scope="col">توضیحات فروشگاه</th>
            <th scope="col">آدرس فروشگاه</th>
            <th scope="col">آرم فروشگاه</th>
            <th scope="col">سود بازاریاب</th>
            <th scope="col">اعتبار</th>
            <th scope="col">نوع فروشگاه</th>
        </tr>
    </thead>
    <tbody>
        <tr valign="top">
            <td><?php echo $response->name; ?></td>
            <td><?php echo $response->description; ?></td>
            <td>
                <a href="<?php echo $response->url; ?>" title="<?php echo $response->name; ?>" target="_blank">
                    <?php echo $response->url; ?>
                </a>
            </td>
            <td>
                <img src="https://cdn.payping.ir/files/168045/store/<?php echo $response->pic; ?>" alt="لوگو" width="100" height="auto">
            </td>
            <td><?php echo $response->wage; ?>٪</td>
            <td><?php echo $response->defaultExpireDays; ?> روز</td>
            <?php if( true === $response->isPrivate ){
                        echo '<td>اختصاصی</td>';
                    }else{
                        echo '<td>عمومی</td>';
                    }
            ?>
        </tr>
    </tbody>
</table>
<?php submit_button( __( 'ویرایش', 'textdomain' ), 'secondary', 'createstor' );
//require_once( dirname( __FILE__ ) . '/CreateStore.html.php');