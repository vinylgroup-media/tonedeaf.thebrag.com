// js/ai-chat.js
jQuery(document).ready(($) => {
	// Check if using iframe-based chat (new system)
	const usingIframeChat = $("#chat-agent-iframe").length > 0;

	if (usingIframeChat) {
		console.log("Using iframe-based chat, old chat system disabled");
	}

	// Declare these at top level so they're available everywhere
	const currentArticleId = window.currentArticleId || "";

	// Publish button functionality (needed for both old and iframe chat)
	$("#publish-article").on("click", publishArticle);

	function publishArticle() {
		// Re-query button to ensure we have fresh reference
		const publishBtn = $("#publish-article");

		const articleId = publishBtn.data("article-id");
		const publishedPostId = publishBtn.data("published-id");

		if (!articleId) {
			showPublishStatus("error", "No article ID found");
			return;
		}

		// Disable button and show loading state
		const originalText = publishBtn.text();
		publishBtn.prop("disabled", true).text("Creating Draft...");

		// Prepare data for AJAX request
		const ajaxData = {
			action: "publish_article",
			article_id: articleId,
			nonce: aiChat.publish_nonce,
		};

		// Add published post ID if this is a republish
		if (publishedPostId) {
			ajaxData.published_post_id = publishedPostId;
		}

		$.ajax({
			url: aiChat.ajax_url,
			type: "POST",
			data: ajaxData,
			dataType: "json",
			timeout: 30000,
			success: (response) => {
				if (response.success) {
					// Store the post ID globally for the unlock button
					window.lastPublishedPostId = response.data.post_id;

					showPublishedStatus(response.data);
					showUnlockButton();
				} else {
					showPublishStatus("error", response.data || "Unknown error occurred");
					publishBtn.prop("disabled", false).text(originalText);
				}
			},
			error: (xhr, status, _error) => {
				let errorMessage = "Publishing failed";
				if (status === "timeout") {
					errorMessage = "Request timed out. Please try again.";
				} else if (xhr.responseJSON?.data) {
					errorMessage = xhr.responseJSON.data;
				}

				showPublishStatus("error", errorMessage);
				publishBtn.prop("disabled", false).text(originalText);
			},
		});
	}

	function showPublishStatus(type, message) {
		const statusDiv = $("#publish-status");
		statusDiv.removeClass("success error").addClass(type).html(message).show();

		// Add CSS styling
		if (type === "success") {
			statusDiv.css({
				"background-color": "#d4edda",
				color: "#155724",
				border: "1px solid #c3e6cb",
			});
		} else {
			statusDiv.css({
				"background-color": "#f8d7da",
				color: "#721c24",
				border: "1px solid #f5c6cb",
			});
		}
	}

	function showPublishedStatus(data) {
		// Hide the publish status div
		$("#publish-status").hide();

		// Create the published status HTML similar to what's shown on page reload
		const publishedHtml = `
            <div style="padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 10px;">
                <p style="margin: 0; color: #155724;"><strong>✓ Draft Created</strong></p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #155724;">
                    <a href="${data.post_url}" target="_blank" style="color: #155724;">View Post</a> | 
                    <a href="${getEditPostUrl(data.post_id)}" target="_blank" style="color: #155724;">Edit Post</a>
                </p>
            </div>
        `;

		// Insert the published status before the publish button
		$("#publish-article").before(publishedHtml);
	}

	function showUnlockButton() {
		// Re-query publish button for fresh reference
		const publishBtn = $("#publish-article");

		// Hide the original publish button
		publishBtn.hide();

		// Show the unlock button (create it if it doesn't exist)
		let unlockBtn = $("#unlock-publish");
		if (unlockBtn.length === 0) {
			unlockBtn = $(
				'<button id="unlock-publish" class="button button-secondary" style="width: 100%;" data-warning="true">Unlock to Update Draft</button>',
			);
			publishBtn.after(unlockBtn);

			// Add click handler for the unlock button
			unlockBtn.on("click", () => {
				if (
					confirm(
						"Warning: This will delete the existing draft post and allow you to create a new one. Are you sure you want to continue?",
					)
				) {
					// Get the post ID from the response data (stored globally)
					const publishedPostId = window.lastPublishedPostId;

					if (!publishedPostId) {
						alert("Error: Post ID not found");
						return;
					}

					// Use the proper delete nonce
					const deleteNonce = aiChat.delete_nonce;

					fetch(ajaxurl, {
						method: "POST",
						headers: {
							"Content-Type": "application/x-www-form-urlencoded",
						},
						body:
							"action=delete_ai_draft&post_id=" +
							publishedPostId +
							"&nonce=" +
							deleteNonce,
					})
						.then((response) => response.json())
						.then((data) => {
							if (data.success) {
								// Refresh article content to show updated status
								refreshArticleContent();

								// Hide unlock button and show publish button
								$("#unlock-publish").hide();
								publishBtn.show();

								// Remove published status div if it exists
								publishBtn
									.siblings('div[style*="background: #d4edda"]')
									.remove();
							} else {
								alert(
									`Failed to delete draft: ${data.data || "Unknown error"}`,
								);
							}
						})
						.catch((error) => {
							console.error("Error:", error);
							alert("Failed to delete draft: Network error");
						});
				}
			});
		}
		unlockBtn.show();
	}

	function getEditPostUrl(postId) {
		// Construct the edit post URL
		const adminUrl = `${window.location.origin}/wp-admin/`;
		return `${adminUrl}post.php?post=${postId}&action=edit`;
	}

	function refreshArticleContent() {
		if (!currentArticleId) {
			console.log("No article ID available for content refresh");
			return;
		}

		$.ajax({
			url: aiChat.ajax_url,
			type: "POST",
			data: {
				action: "refresh_article_content",
				article_id: currentArticleId,
				nonce: aiChat.nonce,
			},
			dataType: "json",
			timeout: 30000,
			success: (response) => {
				if (response.success) {
					const article = response.data.article;

					// Update article headline
					$("#article-headline").text(article.post_title);

                    // Update article content (prefer rendered HTML from server)
                    if (response.data.article_html) {
                        $("#article-content").html(response.data.article_html);
                    } else {
                        $("#article-content").html(article.post_content || "");
                    }

					// Update other article fields if they exist
					if (article.post_excerpt) {
						$(".article-content")
							.find('h3:contains("Excerpt")')
							.next("p")
							.text(article.post_excerpt);
					}

					if (article.post_content_headline) {
						$(".article-content")
							.find('h3:contains("Headline")')
							.next("p")
							.text(article.post_content_headline);
					}

					if (article.post_meta_description) {
						$(".article-content")
							.find('h3:contains("Description")')
							.next("p")
							.text(article.post_meta_description);
					}

					// Update tags
					if (article.tags_input) {
						$(".article-content")
							.find('strong:contains("Tags:")')
							.next()
							.text(article.tags_input);
					}

					// Update status badge
					const statusBadge = $(".status-badge");
					if (statusBadge.length && article.status) {
						statusBadge
							.removeClass()
							.addClass(`status-badge ${getStatusClass(article.status)}`);
						statusBadge.text(
							article.status.charAt(0).toUpperCase() + article.status.slice(1),
						);
					}

					console.log("Article content refreshed successfully");
				} else {
					console.error("Failed to refresh article content:", response.data);
				}
			},
			error: (_xhr, _status, error) => {
				console.error("Error refreshing article content:", error);
			},
		});
	}

	function getStatusClass(status) {
		const statusClasses = {
			Published: "status-published",
			Draft: "status-draft",
			Review: "status-review",
			Pending: "status-pending",
		};
		return statusClasses[status] || "status-default";
	}

	function formatTimestamp(timestamp) {
		const date = new Date(timestamp);
		return date.toLocaleTimeString();
	}

	// Article Revision Functionality
	const revisionSelector = $("#revision-selector");
	const refreshButton = $("#refresh-revisions");
	const revisionStatus = $("#revision-status");
	let currentRevisionData = null; // Store current revision data
	const refreshInterval = null;

	if (revisionSelector.length && typeof currentArticleId !== "undefined") {
		console.log(
			"[AI Chat] Revision functionality initialized for article:",
			currentArticleId,
		);
		loadArticleRevisions();

		// Set up manual refresh button
		refreshButton.on("click", () => {
			console.log("[AI Chat] Manual refresh button clicked");
			refreshArticleRevisions(true);
		});

		// POLLING REMOVED - relying on postMessage from iframe for updates
		// If updates aren't working, check:
		// 1. Iframe is sending postMessage with type: 'article_updated' or 'article_saved'
		// 2. Origin matches the configured API URL
		// 3. articleId in postMessage matches current article
		console.log(
			"[AI Chat] Polling disabled - relying on iframe postMessage for updates",
		);
	}

	function loadArticleRevisions() {
		$.ajax({
			url: aiChat.ajax_url,
			type: "POST",
			data: {
				action: "get_article_revisions",
				article_id: currentArticleId,
				nonce: aiChat.nonce,
			},
			dataType: "json",
			timeout: 30000,
			success: (response) => {
				if (response.success && response.data && response.data.revisions) {
					const revisions = response.data.revisions;

					// Store current revision data for comparison
					currentRevisionData = revisions;

					// Clear and populate the selector
					revisionSelector.empty();

					// Add current version first
					revisionSelector.append(
						$("<option>", {
							value: "current",
							text: "Current Version",
							selected: true,
						}),
					);

					// Add all revisions (sorted by creation date, newest first)
					revisions.sort(
						(a, b) => new Date(b.created_at) - new Date(a.created_at),
					);

					// Add all revisions to the selector
					revisions.forEach((revision) => {
						const date = new Date(revision.created_at).toLocaleString();
						const revisionNumber =
							revision.revision ||
							revision.revision_number ||
							revision.version ||
							"Unknown";
						const label = `Revision ${revisionNumber} (${date})`;

						revisionSelector.append(
							$("<option>", {
								value: revision.id,
								text: label,
								"data-revision": JSON.stringify(revision),
							}),
						);
					});

					// Handle revision selection change
					revisionSelector.off("change").on("change", function () {
						const selectedValue = $(this).val();

						if (selectedValue === "current") {
							// Reload the page to show current version
							location.reload();
						} else {
							// Load the selected revision
							const selectedOption = $(this).find("option:selected");
							try {
								const revisionData = JSON.parse(
									selectedOption.attr("data-revision"),
								);
								loadRevisionData(revisionData);
							} catch (e) {
								console.error("Failed to parse revision data:", e);
								alert("Error loading revision. Please try again.");
							}
						}
					});
				} else {
					revisionSelector.empty();
					revisionSelector.append(
						$("<option>", {
							value: "",
							text: "No revisions available",
						}),
					);
				}
			},
			error: (_xhr, _status, error) => {
				console.error("Failed to load revisions:", error);
				revisionSelector.empty();
				revisionSelector.append(
					$("<option>", {
						value: "",
						text: "Error loading revisions",
					}),
				);
			},
		});
	}

	function refreshArticleRevisions(isManual = false) {
		const source = isManual ? "[Manual Refresh]" : "[Auto Refresh]";
		console.log("[AI Chat]", source, "Checking for revision updates...");

		if (isManual) {
			updateRevisionStatus("Checking for updates...", "info");
			refreshButton.prop("disabled", true);
			$("#refresh-icon").text("⏳");
		}

		$.ajax({
			url: aiChat.ajax_url,
			type: "POST",
			data: {
				action: "get_article_revisions",
				article_id: currentArticleId,
				nonce: aiChat.nonce,
			},
			dataType: "json",
			timeout: 30000,
			success: (response) => {
				if (response.success && response.data && response.data.revisions) {
					const newRevisions = response.data.revisions;

					// Check if revisions have changed
					if (
						JSON.stringify(currentRevisionData) !== JSON.stringify(newRevisions)
					) {
						console.log(
							"[AI Chat]",
							source,
							"✓ New revisions detected! Updating UI. Old count:",
							currentRevisionData?.length || 0,
							"New count:",
							newRevisions.length,
						);

						// Store new revision data
						currentRevisionData = newRevisions;

						// Get currently selected value
						const currentSelection = revisionSelector.val();

						// Clear and repopulate the selector
						revisionSelector.empty();

						// Add current version first
						revisionSelector.append(
							$("<option>", {
								value: "current",
								text: "Current Version",
								selected: currentSelection === "current",
							}),
						);

						// Add all revisions (sorted by creation date, newest first)
						newRevisions.sort(
							(a, b) => new Date(b.created_at) - new Date(a.created_at),
						);

						// Add all revisions to the selector
						newRevisions.forEach((revision) => {
							const date = new Date(revision.created_at).toLocaleString();
							const revisionNumber =
								revision.revision ||
								revision.revision_number ||
								revision.version ||
								"Unknown";
							const label = `Revision ${revisionNumber} (${date})`;

							revisionSelector.append(
								$("<option>", {
									value: revision.id,
									text: label,
									"data-revision": JSON.stringify(revision),
									selected: currentSelection === revision.id,
								}),
							);
						});

						// If we were viewing current version and new revisions appeared, refresh the content and show a notice
						if (currentSelection === "current" && newRevisions.length > 0) {
							// Refresh article content to show the latest changes
							refreshArticleContent();
							showRevisionUpdateNotice();
						}

						if (isManual) {
							updateRevisionStatus("Updated!", "success");
						}
					} else {
						console.log("[AI Chat]", source, "○ No revision changes detected");
						if (isManual) {
							updateRevisionStatus("No updates", "info");
						}
					}
				} else {
					if (isManual) {
						updateRevisionStatus("No revisions available", "warning");
					}
				}
			},
			error: (_xhr, _status, error) => {
				console.error("Failed to refresh revisions:", error);
				if (isManual) {
					updateRevisionStatus("Error loading revisions", "error");
				}
			},
			complete: () => {
				if (isManual) {
					refreshButton.prop("disabled", false);
					$("#refresh-icon").text("🔄");

					// Clear status after 3 seconds
					setTimeout(() => {
						updateRevisionStatus("", "");
					}, 3000);
				}
			},
		});
	}

	function updateRevisionStatus(message, type) {
		if (!message) {
			revisionStatus
				.text("")
				.removeClass("status-info status-success status-warning status-error");
			return;
		}

		revisionStatus
			.text(message)
			.removeClass("status-info status-success status-warning status-error");

		if (type) {
			revisionStatus.addClass(`status-${type}`);
		}
	}

	function showRevisionUpdateNotice() {
		// Remove any existing update notice
		$(".revision-update-notice").remove();

		const notice = $(
			'<div class="notice notice-success revision-update-notice" style="margin: 15px 0;">',
		).html(
			"<p><strong>Article updated!</strong> The content has been refreshed with the latest changes.</p>",
		);

		$(".article-content").first().before(notice);

		// Auto-hide after 10 seconds
		setTimeout(() => {
			$(".revision-update-notice").fadeOut();
		}, 10000);
	}

	function loadRevisionData(revision) {
		console.log("Loading revision data:", revision);

		// Update the article content with revision data
		$("#article-headline").text(revision.post_title || "No title");
		$("#article-description").text(
			revision.post_meta_description || "No description available",
		);
		$("#article-excerpt").text(revision.post_excerpt || "No excerpt available");
		$("#article-content-headline").text(
			revision.post_content_headline || "No headline available",
		);
		
		// Process content through markdown library via AJAX
		if (revision.post_content) {
			$.ajax({
				url: aiChat.ajax_url,
				type: "POST",
				data: {
					action: "process_revision_content",
					content: revision.post_content,
					nonce: aiChat.nonce,
				},
				dataType: "json",
				timeout: 30000,
				success: (response) => {
					if (response.success && response.data && response.data.html) {
						$("#article-content").html(response.data.html);
					} else {
						console.error("Failed to process revision content:", response.data);
						$("#article-content").text(revision.post_content);
					}
				},
				error: (_xhr, _status, error) => {
					console.error("Error processing revision content:", error);
					$("#article-content").text(revision.post_content);
				},
			});
		} else {
			$("#article-content").html("No content available");
		}

		// Handle different possible field names for revision number
		const revisionNumber =
			revision.revision ||
			revision.revision_number ||
			revision.version ||
			"Unknown";
		$("#article-revision").text(revisionNumber);

		// Handle tags - could be string or array
		const tags = Array.isArray(revision.tags_input)
			? revision.tags_input.join(", ")
			: revision.tags_input || "";
		$("#article-tags").text(tags);

		// Update time to write if available
		if (revision.start_time && (revision.end_time || revision.completed_time)) {
			const endTime = revision.completed_time || revision.end_time;
			const duration = calculateRevisionDuration(revision.start_time, endTime);
			$("#article-time").text(duration);
		}

		// Update feedback section if the function exists
		if (typeof window.updateFeedbackSection === "function") {
			window.updateFeedbackSection(revision.feedback);
		}

		// Show a notice that this is a revision view
		const existingNotice = $(".revision-notice");
		if (existingNotice.length) {
			existingNotice.remove();
		}

		const notice = $(
			'<div class="notice notice-info revision-notice" style="margin: 15px 0;">',
		).html(
			"<p><strong>Viewing Revision " +
				revisionNumber +
				'</strong> - This is a previous version of the article. Select "Current Version" to return to the latest version.</p>',
		);

		$(".article-content").first().before(notice);
	}

	function calculateRevisionDuration(startTime, endTime) {
		const start = new Date(startTime).getTime();
		const end = new Date(endTime).getTime();
		const duration = (end - start) / 1000;

		if (duration < 60) {
			return `${duration}s`;
		} else if (duration < 3600) {
			const minutes = Math.floor(duration / 60);
			const seconds = Math.floor(duration % 60);
			return `${minutes}m ${seconds}s`;
		} else {
			const hours = Math.floor(duration / 3600);
			const minutes = Math.floor((duration % 3600) / 60);
			return `${hours}h ${minutes}m`;
		}
	}

	// Expose refreshArticleRevisions globally so it can be called from article-view.php
	window.refreshArticleRevisions = refreshArticleRevisions;
	console.log("[AI Chat] refreshArticleRevisions() exposed globally");
});
