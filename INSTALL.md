# Echbay Mail Queue Manager - Hướng dẫn cài đặt

## Cài đặt Plugin

1. Upload thư mục `echbay-mail-queue` vào `/wp-content/plugins/`
2. Kích hoạt plugin trong WordPress Admin
3. Vào Settings > Email Queue để cấu hình

## Cấu hình Cron Job

### 1. Client-side Cron (Khuyến nghị)

Thêm đoạn code sau vào file `footer.php` của theme:

```html
<script>
	// Chỉ chạy cho guest users để tránh spam
	if (!document.body.classList.contains('logged-in')) {
	    fetch('<?php echo esc_url(home_url('/wp-content/plugins/echbay-mail-queue/cron-send.php')); ?>');
	}
</script>
```

### 2. Server Cron (Nâng cao)

Thêm vào crontab của server:

```bash
# Chạy mỗi 5 phút
*/5 * * * * /usr/bin/php /path/to/wp-content/plugins/echbay-mail-queue/cron-send.php > /dev/null 2>&1

# Hoặc sử dụng wget/curl
*/5 * * * * wget -q -O - http://yoursite.com/wp-content/plugins/echbay-mail-queue/cron-send.php > /dev/null 2>&1
```

## Cấu hình SMTP

Plugin hoạt động tốt với các plugin SMTP:

- WP Mail SMTP
- FluentSMTP
- Easy WP SMTP
- Post SMTP

Chỉ cần cài đặt và cấu hình SMTP như bình thường.

## Cài đặt khuyến nghị

### Cơ bản:

- **Enable Email Queue**: Bật
- **Queue for Guests Only**: Bật (khuyến nghị)
- **Batch Size**: 10-20 emails
- **Cron Interval**: 5 phút
- **Max Attempts**: 3
- **Use WP Cron**: Tắt (khuyến nghị dùng client-side cron)
- **Enable Logging**: Tắt (chỉ bật khi debug)

### High Traffic:

- **Batch Size**: 50-100 emails
- **Cron Interval**: 2-5 phút
- **Max Attempts**: 5
- **Use WP Cron**: Tắt
- **Enable Logging**: Tắt

## Troubleshooting

### Email không được gửi

1. Kiểm tra cron job có chạy không
2. Kiểm tra error log WordPress
3. Test SMTP configuration

### Performance Issues

1. Giảm batch size
2. Tăng cron interval
3. Enable proper caching

### Database Issues

1. Kiểm tra MySQL connection
2. Optimize database tables
3. Clean up old sent emails

## Monitoring

Theo dõi hoạt động qua:

1. Admin page: Statistics
2. WordPress error log
3. Server access log

## Uninstall

Plugin sẽ tự động:

- Xóa database table
- Xóa scheduled cron jobs
- Xóa plugin options

## Support

- Email: support@echbay.com
- Website: https://echbay.com
