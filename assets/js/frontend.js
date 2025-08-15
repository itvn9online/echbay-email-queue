/**
 * Admin JavaScript for Echbay Mail Queue Manager
 */

function echbay_mail_queue_cron_send() {
	jQuery.ajax({
		url:
			window.location.origin +
			"/wp-content/plugins/echbay-email-queue/cron-send.php?active_wp_mail=1",
		type: "POST",
		data: {
			action: "emqm_cron_job",
		},
		success: function (response) {
			if (response.success) {
				console.log("Cron job executed successfully", response);

				// Lập lịch lại cron job
				setTimeout(() => {
					echbay_mail_queue_cron_send();
				}, 60 * 1000);
			} else {
				console.log("Failed to execute cron job");
			}
		},
		error: function () {
			console.log("An error occurred");
		},
	});
}

jQuery(document).ready(function () {
	// hẹn giờ nạp file cron
	setTimeout(() => {
		echbay_mail_queue_cron_send();
	}, 6000);
});
