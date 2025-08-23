# Admin Auto-run Cronjob - Hướng dẫn

## Tổng quan

Plugin Echbay Mail Queue Manager giờ đây có thêm tính năng **Admin Auto-run Cronjob** để cung cấp hệ thống backup xử lý email queue khi server cron gặp sự cố.

## Cách thức hoạt động

### 1. Option `emqm_admin_autorun`

- **Mặc định**: Bật (giá trị 1)
- **Vị trí**: Settings → Email Queue → Admin Auto-run Cronjob
- **Chức năng**: Tự động nhúng script cronjob vào admin footer

### 2. Khi nào script được chạy?

- ✅ User có quyền `manage_options` (admin)
- ✅ Đang ở trang admin WordPress
- ✅ Option `emqm_admin_autorun` được bật
- ✅ File `frontend.html` tồn tại

### 3. Script được nhúng

Script từ file `assets/frontend.html` sẽ được inject vào `admin_footer`:

```html
<script>
	const echbayEmailQueueBaseUrl =
		"http://yoursite.com/wp-content/plugins/echbay-email-queue/";
	document.addEventListener("DOMContentLoaded", function () {
		var script = document.createElement("script");
		script.src =
			echbayEmailQueueBaseUrl + "assets/js/frontend.js?v=" + Math.random();
		script.type = "text/javascript";
		script.defer = true;
		document.body.appendChild(script);
	});
</script>
```

## Lợi ích

### 1. **Backup System**

- Nếu server cron bị lỗi, admin autorun sẽ đảm bảo email vẫn được xử lý
- Hoạt động song song với server cron mà không xung đột

### 2. **Real-time Processing**

- Mỗi khi admin truy cập trang, email queue được xử lý
- Đặc biệt hữu ích cho website có admin thường xuyên online

### 3. **Zero Configuration**

- Tự động hoạt động sau khi bật
- Không cần cấu hình thêm

### 4. **Security**

- Chỉ chạy cho admin users
- Kiểm tra permissions nghiêm ngặt

## Cấu hình

### Bật/Tắt Admin Autorun

1. Vào **WordPress Admin → Settings → Email Queue**
2. Tìm section **"Admin Auto-run Cronjob"**
3. Check/uncheck checkbox
4. Click **Save Changes**

### Kiểm tra hoạt động

1. **Developer Tools**:

   - Mở F12 Console
   - Reload trang admin
   - Xem log `echbay_mail_queue_cron_send()`

2. **Network Tab**:

   - Kiểm tra request đến `cron-send.php`
   - Verify response từ cronjob

3. **Email Queue**:
   - Vào **Email Queue** tab
   - Kiểm tra emails đang được xử lý

## Tích hợp với hệ thống hiện tại

### Server Cron + Admin Autorun

```
Server Cron (Primary)     Admin Autorun (Backup)
       ↓                         ↓
   Every minute              When admin visits
       ↓                         ↓
   cron-send.php ←─────────→ cron-send.php
       ↓                         ↓
   Process emails            Process emails
```

### Rate Limiting

Plugin có cơ chế **rate limiting** tích hợp:

- Chỉ chạy tối đa mỗi 55 giây
- Tránh spam requests khi admin reload nhiều lần
- File `emqm_last_cron_run.txt` theo dõi lần chạy cuối

## Troubleshooting

### Script không chạy

1. **Kiểm tra option**:

   ```php
   get_option('emqm_admin_autorun', 1)
   ```

2. **Kiểm tra quyền user**:

   ```php
   current_user_can('manage_options')
   ```

3. **Kiểm tra file tồn tại**:
   ```php
   file_exists(EMQM_PLUGIN_PATH . 'assets/frontend.html')
   ```

### Console errors

1. **`echbayEmailQueueBaseUrl` not defined**:

   - Check URL placeholder replacement
   - Verify `EMQM_PLUGIN_URL` constant

2. **`frontend.js` không load**:

   - Check file permissions
   - Verify file path

3. **AJAX errors**:
   - Check server cron URL
   - Verify `cron-send.php` accessibility

### Rate limiting issues

1. **Too frequent requests**:

   - Script chạy mỗi 60s, rate limit là 55s
   - Normal behavior

2. **Stuck in rate limit**:
   - Delete `emqm_last_cron_run.txt`
   - Or wait 55 seconds

## Best Practices

### 1. **Server Cron ưu tiên**

- Luôn cài đặt server cron làm primary
- Admin autorun chỉ là backup

### 2. **Monitor cả hai**

- Kiểm tra server cron hoạt động
- Verify admin autorun khi cần

### 3. **Performance**

- Admin autorun không ảnh hưởng tốc độ load page
- Script load asynchronously

### 4. **Disable khi không cần**

- Tắt admin autorun nếu server cron ổn định
- Giảm thiểu requests không cần thiết

## Code Reference

### Admin Page Integration

```php
// Hook admin footer
add_action('admin_footer', array($this, 'admin_footer_autorun_script'));

// Method injection
public function admin_footer_autorun_script()
{
    if (!is_admin() || !get_option('emqm_admin_autorun', 1)) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    // Inject script...
}
```

### Settings Registration

```php
register_setting('emqm_settings', 'emqm_admin_autorun');
```

### Default Option

```php
'admin_autorun' => 1, // Default enabled
```

## Support

Nếu gặp vấn đề với Admin Auto-run:

1. Check WordPress error logs
2. Test manual cron URL: `yoursite.com/wp-content/plugins/echbay-email-queue/cron-send.php?active_wp_mail=1`
3. Verify admin permissions
4. Check console for JavaScript errors

Plugin hiện tại có **double reliability** với cả server cron và admin autorun backup system! 🚀
