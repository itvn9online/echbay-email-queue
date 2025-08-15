# Hướng dẫn cài đặt Server Cronjob

Plugin Echbay Mail Queue Manager yêu cầu sử dụng server cronjob để xử lý hàng đợi email. Plugin đã bỏ hoàn toàn WordPress cron để đảm bảo hiệu suất và độ tin cậy tốt hơn.

## Cài đặt Cronjob

### 1. Trên Linux/Unix/cPanel

Thêm dòng sau vào crontab của server:

```bash
* * * * * curl -s "http://yourdomain.com/wp-content/plugins/echbay-email-queue/cron-send.php?active_wp_mail=1" > /dev/null 2>&1
```

**Lưu ý:** Thay `http://yourdomain.com` bằng domain thực tế của bạn.

### 2. Trên cPanel

1. Đăng nhập vào cPanel
2. Tìm và click vào **Cron Jobs**
3. Trong phần **Add New Cron Job**, chọn:
   - Minute: `*`
   - Hour: `*`
   - Day: `*`
   - Month: `*`
   - Weekday: `*`
4. Command: `curl -s "http://yourdomain.com/wp-content/plugins/echbay-email-queue/cron-send.php?active_wp_mail=1" > /dev/null 2>&1`

### 3. Trên VPS/Dedicated Server

```bash
# Mở crontab editor
crontab -e

# Thêm dòng sau
* * * * * curl -s "http://yourdomain.com/wp-content/plugins/echbay-email-queue/cron-send.php?active_wp_mail=1" > /dev/null 2>&1

# Lưu và thoát
```

### 4. Kiểm tra hoạt động

Sau khi cài đặt cronjob, bạn có thể:

1. Vào trang admin của plugin để xem log
2. Gửi thử một email và kiểm tra hàng đợi
3. Truy cập trực tiếp URL cron để test: `http://yourdomain.com/wp-content/plugins/echbay-email-queue/cron-send.php?active_wp_mail=1`

## Tùy chọn thời gian chạy

- **Mỗi phút** (khuyến nghị): `* * * * *`
- **Mỗi 2 phút**: `*/2 * * * *`
- **Mỗi 5 phút**: `*/5 * * * *`

## Lưu ý quan trọng

1. **Không sử dụng WordPress cron**: Plugin đã bỏ hoàn toàn WP cron
2. **Rate limiting**: File cron-send.php có chống spam, chỉ chạy tối đa mỗi 55 giây
3. **Bảo mật**: URL cron được bảo vệ bởi rate limiting và chỉ xử lý khi có parameter `active_wp_mail=1`
4. **Hiệu suất**: Server cronjob hoạt động tốt hơn và tin cậy hơn WP cron

## Troubleshooting

### Cronjob không chạy

1. Kiểm tra URL có đúng không
2. Kiểm tra quyền thực thi của server
3. Kiểm tra log lỗi của server
4. Test trực tiếp URL trong browser

### Email không được gửi

1. Kiểm tra cài đặt SMTP
2. Kiểm tra log trong admin plugin
3. Kiểm tra hàng đợi email trong database
4. Kiểm tra cronjob có chạy đúng không

## Liên hệ hỗ trợ

Nếu gặp vấn đề, vui lòng liên hệ developer hoặc tạo issue trên repository.
