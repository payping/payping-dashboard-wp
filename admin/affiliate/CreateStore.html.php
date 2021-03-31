<form method="post">
    <table class="form-table">
        <tr valign="top">
            <th scope="row">نام فروشگاه: </th>
            <td>
               <input class="regular-text" name="name" type="text" value="<?php echo bloginfo('name'); ?>">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">توضیحات فروشگاه: </th>
            <td>
               <input class="regular-text" name="description" type="text" value="<?php echo bloginfo('description'); ?>">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">آدرس فروشگاه: </th>
            <td>
                <input class="regular-text" name="url" type="text" value="<?php echo get_site_url(); ?>" disabled>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">لوگوی فروشگاه: </th>
            <td>
                <input class="regular-text" name="pic" type="file">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">درصد بازاریاب: </th>
            <td>
                <input class="regular-text" name="wage" type="number">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">اعتبار(روز): </th>
            <td>
                <input class="regular-text" name="defaultExpireDays" type="number">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">فروشگاه خصوصی: </th>
            <td>
                <input class="regular-text" name="isPrivate" type="checkbox" checked="checked">
            </td>
        </tr>
    </table>
    <?php submit_button( __( 'ذخیره تغییرات', 'textdomain' ), 'secondary', 'createstor' ); ?>
</form>