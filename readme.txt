=== Echbay Mail Queue Manager ===
Contributors: daoquocdai
Tags: email, queue, smtp, performance, mail
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Quản lý hàng đợi email cho WordPress, tránh làm chậm website khi gửi mail, hỗ trợ gửi theo batch qua cron.

== Description ==

Echbay Mail Queue Manager giúp cải thiện hiệu suất website bằng cách đưa email vào hàng đợi thay vì gửi ngay lập tức. Plugin đặc biệt hữu ích khi:

* Website sử dụng SMTP để gửi email
* Có nhiều email được gửi cùng lúc
* Muốn tránh timeout khi gửi email
* Cần theo dõi và quản lý email đã gửi

= Tính năng chính =

* **Hàng đợi email thông minh**: Tự động đưa email vào hàng đợi cho người dùng chưa đăng nhập
* **Gửi theo batch**: Cấu hình số lượng email gửi mỗi lần
* **Quản lý trạng thái**: Theo dõi email pending, sent, failed
* **Retry thông minh**: Tự động thử lại email lỗi với số lần configurable
* **Giao diện quản trị**: Xem danh sách, retry, xóa email dễ dàng
* **Tích hợp SMTP**: Hoạt động tốt với các plugin SMTP
* **Cron linh hoạt**: Hỗ trợ WP Cron, server cron, và client-side cron

= Cách hoạt động =

1. Plugin hook vào `wp_mail()` function
2. Email được lưu vào database thay vì gửi ngay
3. Cron job định kỳ gửi email theo batch
4. Admin có thể theo dõi và quản lý qua giao diện

= Yêu cầu =

* WordPress 5.0+
* PHP 7.4+
* MySQL 5.7+

== Installation ==

1. Upload plugin folder vào `/wp-content/plugins/` directory
2. Activate plugin qua WordPress admin
3. Vào Settings > Email Queue để cấu hình
4. Cấu hình cron job (tùy chọn)

= Cấu hình Cron =

**Client-side Cron (Khuyến nghị):**
Thêm vào footer theme:
```html
<script>
document.addEventListener('DOMContentLoaded', function() {
    var script = document.createElement('script');
    script.src = window.location.origin + '/wp-content/plugins/echbay-email-queue/assets/js/frontend.js?v=' + Math.random();
    script.type = 'text/javascript';
    script.defer = true; // hoặc script.async = true;
    document.body.appendChild(script); // chèn vào cuối body (footer)
});
</script>
```

**Server Cron:**
```bash
*/5 * * * * /usr/bin/php /path/to/wp-content/plugins/echbay-email-queue/cron-send.php
```

== Frequently Asked Questions ==

= Plugin có tương thích với SMTP plugins không? =

Có, plugin hoạt động tốt với WP Mail SMTP, FluentSMTP và các plugin SMTP khác.

= Email có bị mất không khi server down? =

Không, email được lưu trong database nên sẽ được gửi khi server hoạt động trở lại.

= Có thể gửi email ngay lập tức không? =

Có, người dùng đã đăng nhập sẽ có email gửi ngay lập tức (configurable).

= Plugin có ảnh hưởng đến hiệu suất không? =

Không, plugin giúp cải thiện hiệu suất bằng cách tránh timeout khi gửi email.

== Screenshots ==

1. Giao diện quản lý email queue
2. Trang cấu hình plugin
3. Thống kê email queue

== Changelog ==

= 1.0.0 =
* Initial release
* Email queue management
* Batch sending
* Admin interface
* Cron job support
* SMTP integration

== Upgrade Notice ==

= 1.0.0 =
First version of the plugin.
