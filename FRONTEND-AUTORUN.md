# Frontend Auto-run Cronjob - Hướng dẫn

## Tổng quan

Plugin Echbay Mail Queue Manager giờ đây có thêm tính năng **Frontend Auto-run Cronjob** để cung cấp hệ thống backup thứ ba xử lý email queue khi khách hàng truy cập trang web.

## Hệ thống cronjob ba lớp

```
1. Server Cron (Primary)
   ↓
2. Admin Auto-run (Backup Level 1)
   ↓
3. Frontend Auto-run (Backup Level 2)
   ↓
All point to: cron-send.php
```

## Cách thức hoạt động

### 1. Option `emqm_frontend_autorun`

- **Mặc định**: Tắt (giá trị 0)
- **Vị trí**: Settings → Email Queue → Frontend Auto-run Cronjob
- **Chức năng**: Tự động nhúng script cronjob vào frontend footer

### 2. Khi nào script được chạy?

- ✅ User đang ở trang frontend (không phải admin)
- ✅ Option `emqm_frontend_autorun` được bật
- ✅ File `frontend.html` tồn tại
- ❌ Không chạy ở admin pages

### 3. Script được nhúng

Script từ file `assets/frontend.html` sẽ được inject vào `wp_footer`:

```html
<!-- Echbay Email Queue Frontend Auto-run Script -->
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
<!-- End Echbay Email Queue Frontend Auto-run Script -->
```

## Khi nào nên sử dụng

### ✅ **Nên bật khi:**

1. **Server cron không hoạt động**

   - VPS/shared hosting có vấn đề cron
   - Hosting provider không hỗ trợ cron

2. **Admin không thường xuyên online**

   - Website ít có admin truy cập
   - Cần backup processing khi admin offline

3. **Website có traffic cao**
   - Nhiều visitor => nhiều cơ hội chạy cron
   - Email queue được xử lý thường xuyên

### ❌ **Không nên bật khi:**

1. **Server cron hoạt động tốt**

   - Không cần thiết backup thêm
   - Tránh overhead không cần thiết

2. **Website tốc độ quan trọng**

   - Mỗi script injection ảnh hưởng performance
   - Frontend speed optimization là ưu tiên

3. **Low traffic website**
   - Ít visitor => ít cơ hội chạy cron
   - Admin autorun đã đủ backup

## Performance Impact

### Script Loading

```javascript
// Script load async và defer
script.defer = true;
script.src =
	echbayEmailQueueBaseUrl + "assets/js/frontend.js?v=" + Math.random();
```

### Rate Limiting Protection

- Plugin có built-in rate limiting (55 giây minimum)
- Tránh multiple requests khi nhiều user cùng lúc
- File `emqm_last_cron_run.txt` bảo vệ

### Minimal Resource Usage

- Chỉ inject script nhỏ vào footer
- AJAX request chỉ khi cần thiết
- Không block page rendering

## Cấu hình

### Bật Frontend Auto-run

1. Vào **WordPress Admin → Settings → Email Queue**
2. Tìm section **"Frontend Auto-run Cronjob"**
3. Check checkbox
4. Click **Save Changes**

### Monitoring

1. **View Page Source**:

   - Kiểm tra có script injection trong footer
   - Verify URL placeholder được thay thế đúng

2. **Browser DevTools**:

   - F12 → Console: Xem cronjob execution logs
   - Network Tab: Monitor requests tới `cron-send.php`

3. **Email Queue Dashboard**:
   - Kiểm tra emails được process
   - Verify queue status changes

## So sánh với Admin Auto-run

| Feature         | Admin Auto-run     | Frontend Auto-run     |
| --------------- | ------------------ | --------------------- |
| **Default**     | Enabled            | Disabled              |
| **Target**      | Admin users only   | All visitors          |
| **Frequency**   | When admin visits  | When anyone visits    |
| **Performance** | No frontend impact | May impact page speed |
| **Use case**    | Admin backup       | Public backup         |

## Best Practices

### 1. **Hierarchical Approach**

```
Priority 1: Server Cron (always setup first)
Priority 2: Admin Auto-run (backup when server fails)
Priority 3: Frontend Auto-run (last resort backup)
```

### 2. **Conditional Enable**

```php
// Only enable frontend autorun if really needed
if (server_cron_is_unreliable() && admin_visits_are_rare()) {
    update_option('emqm_frontend_autorun', 1);
}
```

### 3. **Performance Monitoring**

- Monitor page load times after enabling
- Check Core Web Vitals impact
- Disable if performance degradation detected

### 4. **Traffic-based Decision**

- **High traffic**: Frontend autorun effective
- **Low traffic**: Admin autorun may be sufficient
- **Medium traffic**: Test both approaches

## Troubleshooting

### Script không appear trong source

1. **Check option enabled**:

   ```php
   get_option('emqm_frontend_autorun', 0) // Should return 1
   ```

2. **Check frontend context**:

   ```php
   is_admin() // Should return false on frontend
   ```

3. **Check file exists**:
   ```php
   file_exists(EMQM_PLUGIN_PATH . 'assets/frontend.html')
   ```

### Performance issues

1. **Disable frontend autorun**:

   - Keep server cron + admin autorun only
   - Monitor email queue processing

2. **Optimize script loading**:

   - Script already uses defer loading
   - Rate limiting prevents spam

3. **Check conflict với other plugins**:
   - Test with minimal plugin setup
   - Check for JavaScript errors

### Cronjob không chạy

1. **Check URL accessibility**:

   ```bash
   curl "http://yoursite.com/wp-content/plugins/echbay-email-queue/cron-send.php?active_wp_mail=1"
   ```

2. **Check rate limiting**:

   - Wait 55+ seconds between tests
   - Delete `emqm_last_cron_run.txt` if stuck

3. **Browser console errors**:
   - Check for JavaScript errors
   - Verify network requests succeed

## Code Reference

### Hook Integration

```php
// Frontend footer hook
add_action('wp_footer', array($this, 'frontend_footer_autorun_script'));
```

### Condition Checks

```php
public function frontend_footer_autorun_script()
{
    // Only frontend + option enabled
    if (is_admin() || !get_option('emqm_frontend_autorun', 0)) {
        return;
    }

    // Inject script...
}
```

### Default Settings

```php
'frontend_autorun' => 0, // Default disabled
```

## Migration Guide

### From Manual Theme Integration

Nếu bạn đã manually add script vào theme:

1. **Remove manual code** từ theme footer
2. **Enable frontend autorun** option
3. **Test functionality** không bị duplicate

### Performance Optimization

```php
// Optional: Conditional loading based on page type
if (is_home() || is_front_page()) {
    // Only enable on important pages
    add_action('wp_footer', 'your_cronjob_script');
}
```

## Security Considerations

### Rate Limiting

- Built-in 55-second minimum interval
- Prevents abuse from high traffic

### No User Authentication

- Frontend script runs for all visitors
- Rate limiting is primary protection

### Server Resource Protection

```php
// cron-send.php has built-in protection
if (($current_time - $last_run) < $min_interval) {
    exit('Rate limited');
}
```

Plugin giờ đây có **hệ thống cronjob ba lớp** hoàn chỉnh đảm bảo email queue luôn được xử lý trong mọi tình huống! 🚀

**Khuyến nghị**: Chỉ bật Frontend Auto-run khi thực sự cần thiết, ưu tiên server cron và admin autorun trước.
