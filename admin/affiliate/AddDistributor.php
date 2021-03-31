<table>
    <theader>
        <th>نام فروشگاه</th>
        <th>توضیحات فروشگاه</th>
        <th>آدرس فروشگاه</th>
        <th>لوگو فروشگاه</th>
        <th>درصد بازاریابی</th>
        <th>اعتبار(روز)</th>
        <th>فروشگاه خصوصی</th>
    </theader>
    <tbody>
        <td><?php echo bloginfo('name'); ?></td>
        <td><?php echo bloginfo('description'); ?></td>
        <td><?php echo get_site_url(); ?></td>
        <td><img src="https://google.com/logo.png" alt="لوگو"></td>
        <td>10٪</td>
        <td>20روز</td>
        <td>هست</td>
    </tbody>
</table>
<p>شما می‌توانید فروشگاه خود را از طریق فرم زیر بروزرسانی کنید.</p>
<?php
require_once( dirname( __FILE__ ) . '/affiliate/CreateStore.html.php');