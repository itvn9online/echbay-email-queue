/**
 * Admin JavaScript for Echbay Mail Queue Manager
 */

jQuery(document).ready(function ($) {
	// Retry email
	$(".retry-email").on("click", function (e) {
		e.preventDefault();

		var $button = $(this);
		var emailId = $button.data("email-id");

		$button.prop("disabled", true).text("Retrying...");

		$.ajax({
			url: emqm_ajax.ajaxurl,
			type: "POST",
			data: {
				action: "emqm_retry_email",
				email_id: emailId,
				nonce: emqm_ajax.nonce,
			},
			success: function (response) {
				if (response.success) {
					$button
						.closest("tr")
						.find(".badge")
						.removeClass("badge-danger")
						.addClass("badge-warning")
						.text("Pending");
					$button.remove();
					showNotice(response.data, "success");
				} else {
					showNotice("Failed to retry email", "error");
				}
			},
			error: function () {
				showNotice("An error occurred", "error");
			},
			complete: function () {
				$button.prop("disabled", false).text("Retry");
			},
		});
	});

	// Delete email
	$(".delete-email").on("click", function (e) {
		e.preventDefault();

		if (!confirm("Are you sure you want to delete this email?")) {
			return;
		}

		var $button = $(this);
		var emailId = $button.data("email-id");
		var $row = $button.closest("tr");

		$button.prop("disabled", true).text("Deleting...");

		$.ajax({
			url: emqm_ajax.ajaxurl,
			type: "POST",
			data: {
				action: "emqm_delete_email",
				email_id: emailId,
				nonce: emqm_ajax.nonce,
			},
			success: function (response) {
				if (response.success) {
					$row.fadeOut(function () {
						$row.remove();
						// Also remove details row if exists
						$("#details-" + emailId).remove();
					});
					showNotice(response.data, "success");
				} else {
					showNotice("Failed to delete email", "error");
				}
			},
			error: function () {
				showNotice("An error occurred", "error");
			},
			complete: function () {
				$button.prop("disabled", false).text("Delete");
			},
		});
	});

	// View email details
	$(".view-details").on("click", function (e) {
		e.preventDefault();

		var emailId = $(this).data("email-id");
		var $detailsRow = $("#details-" + emailId);

		if ($detailsRow.is(":visible")) {
			$detailsRow.hide();
			$(this).text("View");
		} else {
			// Hide all other details first
			$(".email-details").hide();
			$(".view-details").text("View");

			$detailsRow.show();
			$(this).text("Hide");
		}
	});

	// Auto-refresh functionality
	var autoRefreshEnabled = false;
	var autoRefreshInterval;

	// Add auto-refresh toggle button
	if ($(".emqm-filters form").length) {
		$(".emqm-filters form").append(
			'<button type="button" class="button auto-refresh-toggle" data-enabled="false">' +
				"Enable Auto-refresh" +
				"</button>"
		);
	}

	$(".auto-refresh-toggle").on("click", function () {
		var $button = $(this);
		autoRefreshEnabled = !autoRefreshEnabled;

		if (autoRefreshEnabled) {
			$button.text("Disable Auto-refresh").addClass("button-primary");
			autoRefreshInterval = setInterval(function () {
				location.reload();
			}, 30000); // Refresh every 30 seconds
		} else {
			$button.text("Enable Auto-refresh").removeClass("button-primary");
			clearInterval(autoRefreshInterval);
		}
	});

	// Process queue manually
	if ($("#process-queue-manually").length == 0) {
		$(".emqm-filters form").append(
			'<button type="button" id="process-queue-manually" class="button button-secondary">' +
				"Process Queue Now" +
				"</button>"
		);
	}

	$("#process-queue-manually").on("click", function (e) {
		e.preventDefault();

		var $button = $(this);
		$button.prop("disabled", true).text("Processing...");

		$.ajax({
			url: emqm_ajax.ajaxurl,
			type: "POST",
			data: {
				action: "emqm_process_queue_manually",
				nonce: emqm_ajax.nonce,
			},
			success: function (response) {
				if (response.success) {
					showNotice("Queue processed successfully", "success");
					setTimeout(function () {
						location.reload();
					}, 1000);
				} else {
					showNotice("Failed to process queue", "error");
				}
			},
			error: function () {
				showNotice("An error occurred", "error");
			},
			complete: function () {
				$button.prop("disabled", false).text("Process Queue Now");
			},
		});
	});

	// Show notice function
	function showNotice(message, type) {
		var noticeClass = type === "success" ? "notice-success" : "notice-error";
		var $notice = $(
			'<div class="notice ' +
				noticeClass +
				' is-dismissible"><p>' +
				message +
				"</p></div>"
		);

		$(".wrap h1").after($notice);

		// Auto-dismiss after 3 seconds
		setTimeout(function () {
			$notice.fadeOut();
		}, 3000);
	}

	// Bulk actions (future enhancement)
	$('.check-column input[type="checkbox"]').on("change", function () {
		var checkedCount = $('.check-column input[type="checkbox"]:checked').length;
		if (checkedCount > 0) {
			if ($(".bulk-actions").length == 0) {
				$(".emqm-filters").append(
					'<div class="bulk-actions" style="margin-top: 10px;">' +
						'<select name="bulk-action">' +
						'<option value="">Bulk Actions</option>' +
						'<option value="delete">Delete Selected</option>' +
						'<option value="retry">Retry Selected</option>' +
						"</select> " +
						'<button type="button" class="button apply-bulk-action">Apply</button>' +
						"</div>"
				);
			}
		} else {
			$(".bulk-actions").remove();
		}
	});
});
