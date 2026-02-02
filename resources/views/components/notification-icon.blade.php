<!-- Notification Icon & Panel Component -->
<style>
    /* Notification Icon Styles */
    .notification-icon-container {
        position: relative;
        display: inline-block;
        margin: 0 15px;
    }

    .notification-icon {
        cursor: pointer;
        font-size: 20px;
        color: #6c757d;
        transition: all 0.3s ease;
        position: relative;
    }

    .notification-icon:hover {
        color: #28a745;
        transform: scale(1.1);
    }

    /* Badge (Red Circle with Number) */
    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: #dc3545;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.5);
        transition: all 0.3s ease;
    }

    .notification-badge.hidden {
        display: none;
    }

    .notification-badge.bounce {
        animation: bounce 0.6s ease;
    }

    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }

    /* Notification Panel */
    .notification-panel {
        position: absolute;
        top: 40px;
        right: -20px;
        width: 380px;
        max-height: 500px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        display: none;
        flex-direction: column;
        border: 1px solid #e9ecef;
        animation: slideDown 0.3s ease;
    }

    .notification-panel.show {
        display: flex;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Panel Header */
    .notification-panel-header {
        padding: 15px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px 8px 0 0;
    }

    .notification-panel-header h5 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .notification-clear-btn {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        font-size: 14px;
        padding: 5px 10px;
        border-radius: 4px;
        transition: background 0.2s;
    }

    .notification-clear-btn:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* Panel Body */
    .notification-panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 0;
    }

    .notification-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }

    .notification-item:hover {
        background-color: #f8f9fa;
        border-left-color: #667eea;
    }

    .notification-item.unread {
        background-color: #f0f7ff;
        font-weight: 500;
    }

    .notification-item-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 5px;
    }

    .notification-item-title {
        font-size: 14px;
        color: #333;
        margin: 0;
        flex: 1;
    }

    .notification-item-date {
        font-size: 12px;
        color: #999;
        margin-left: 10px;
        white-space: nowrap;
    }

    .notification-item-customer {
        font-size: 13px;
        color: #666;
        margin: 5px 0;
    }

    .notification-item-booking {
        font-size: 12px;
        color: #999;
    }

    .notification-item-actions {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }

    .notification-action-btn {
        padding: 4px 10px;
        font-size: 11px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .notification-action-read {
        background-color: #e7f3ff;
        color: #0066cc;
    }

    .notification-action-read:hover {
        background-color: #cce5ff;
    }

    .notification-action-dismiss {
        background-color: #f0f0f0;
        color: #666;
    }

    .notification-action-dismiss:hover {
        background-color: #e0e0e0;
    }

    /* Empty State */
    .notification-empty {
        padding: 40px 20px;
        text-align: center;
        color: #999;
    }

    .notification-empty-icon {
        font-size: 40px;
        margin-bottom: 10px;
        opacity: 0.5;
    }

    .notification-empty-text {
        font-size: 14px;
    }

    /* Panel Footer */
    .notification-panel-footer {
        padding: 10px 15px;
        border-top: 1px solid #e9ecef;
        text-align: center;
        background: #f8f9fa;
        border-radius: 0 0 8px 8px;
    }

    .notification-view-all-btn {
        color: #667eea;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: color 0.2s;
    }

    .notification-view-all-btn:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    /* Loading State */
    .notification-loading {
        padding: 20px;
        text-align: center;
        color: #999;
    }

    .spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .notification-panel {
            width: 90vw;
            max-width: 350px;
            right: 10px;
        }
    }
</style>

<div class="notification-icon-container">
    <!-- Notification Icon -->
    <div class="notification-icon" id="notificationIcon" title="Notifications">
        <i class="fas fa-bell"></i>
        <span class="notification-badge hidden" id="notificationBadge">0</span>
    </div>

    <!-- Notification Panel -->
    <div class="notification-panel" id="notificationPanel">
        <!-- Header -->
        <div class="notification-panel-header">
            <h5>Notifications</h5>
            <button class="notification-clear-btn" id="clearNotificationBtn" title="Clear All">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="notification-panel-body" id="notificationPanelBody">
            <div class="notification-loading">
                <div class="spinner"></div>
                <p>Loading...</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="notification-panel-footer">
            <a href="/notifications" class="notification-view-all-btn">View All Notifications →</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationIcon = document.getElementById('notificationIcon');
    const notificationPanel = document.getElementById('notificationPanel');
    const notificationBadge = document.getElementById('notificationBadge');
    const notificationPanelBody = document.getElementById('notificationPanelBody');
    const clearBtn = document.getElementById('clearNotificationBtn');

    let notificationsData = [];

    // Get CSRF token safely
    function getCsrfToken() {
        // Try meta tag first
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            return metaToken.getAttribute('content');
        }
        
        // Try from laravel cookie
        const token = document.querySelector('input[name="_token"]');
        if (token) {
            return token.value;
        }
        
        // Try from window object
        if (window.Laravel && window.Laravel.csrf) {
            return window.Laravel.csrf;
        }
        
        return '';
    }

    // Load notifications on page load
    loadNotifications();

    // Refresh every 30 seconds
    setInterval(loadNotifications, 30000);

    // Toggle panel on icon click
    notificationIcon.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationPanel.classList.toggle('show');
    });

    // Close panel when clicking outside
    document.addEventListener('click', function() {
        notificationPanel.classList.remove('show');
    });

    // Prevent panel from closing when clicking inside it
    notificationPanel.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Clear all notifications
    clearBtn.addEventListener('click', function() {
        if (confirm('Mark all notifications as sent?')) {
            if (notificationsData.length === 0) {
                alert('No notifications to mark as sent');
                return;
            }
            
            // Get CSRF token
            const csrfToken = getCsrfToken();
            if (!csrfToken) {
                console.error('CSRF token not found');
                alert('Security error: CSRF token not found. Please refresh the page.');
                return;
            }
            
            // Create array of promises for all mark-as-sent requests
            const promises = notificationsData.map(notif => 
                fetch(`/notifications/${notif.id}/mark-as-sent`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
            );
            
            // Wait for all requests to complete
            Promise.all(promises)
                .then(() => {
                    console.log('All notifications marked as sent');
                    notificationsData = [];
                    updateBadge(0);
                    renderNotifications([]);
                    // Close panel after success
                    notificationPanel.classList.remove('show');
                })
                .catch(error => {
                    console.error('Error marking notifications as sent:', error);
                    alert(`Error: ${error.message}`);
                    // Reload on error to sync
                    loadNotifications();
                });
        }
    });

    // Load notifications
    function loadNotifications() {
        fetch('/notifications/pending')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationsData = data.notifications;
                    updateBadge(data.count);
                    renderNotifications(data.notifications);
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                notificationPanelBody.innerHTML = '<div class="notification-empty"><div class="notification-empty-text">Error loading notifications</div></div>';
            });
    }

    // Update badge count
    function updateBadge(count) {
        if (count > 0) {
            notificationBadge.textContent = count > 99 ? '99+' : count;
            notificationBadge.classList.remove('hidden');
            notificationBadge.classList.add('bounce');
            setTimeout(() => {
                notificationBadge.classList.remove('bounce');
            }, 600);
        } else {
            notificationBadge.classList.add('hidden');
        }
    }

    // Render notifications
    function renderNotifications(notifications) {
        if (notifications.length === 0) {
            notificationPanelBody.innerHTML = `
                <div class="notification-empty">
                    <div class="notification-empty-icon">📭</div>
                    <div class="notification-empty-text">No pending notifications</div>
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(notif => {
            html += `
                <div class="notification-item ${notif.is_read ? '' : 'unread'}" data-id="${notif.id}">
                    <div class="notification-item-header">
                        <h6 class="notification-item-title">${notif.title}</h6>
                        <span class="notification-item-date">${formatDate(notif.notification_date)}</span>
                    </div>
                    <div class="notification-item-customer">
                        <i class="fas fa-user"></i> ${notif.customer_name}
                    </div>
                    <div class="notification-item-booking">
                        ${notif.booking_no ? '<i class="fas fa-file-invoice"></i> Booking: ' + notif.booking_no : (notif.product_name ? '<i class="fas fa-box"></i> Product: ' + notif.product_name : '')}
                    </div>
                    <div class="notification-item-actions">
                        <button class="notification-action-btn notification-action-read" onclick="markAsRead(${notif.id})" title="Mark as Read">
                            <i class="fas fa-check"></i> Read
                        </button>
                        <button class="notification-action-btn notification-action-dismiss" onclick="dismissNotification(${notif.id})" title="Dismiss">
                            <i class="fas fa-times"></i> Dismiss
                        </button>
                    </div>
                </div>
            `;
        });

        notificationPanelBody.innerHTML = html;
    }

    // Mark as read
    window.markAsRead = function(id) {
        console.log('Marking as read:', id);
        // Remove from local array immediately
        notificationsData = notificationsData.filter(n => n.id !== id);
        updateBadge(notificationsData.length);
        renderNotifications(notificationsData);

        // Send to server
        fetch(`/notifications/${id}/mark-as-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload after 1 second to sync
                setTimeout(loadNotifications, 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadNotifications(); // Reload on error
        });
    };

    // Mark as sent
    window.markAsSent = function(id) {
        console.log('Marking as sent:', id);
        // Remove from local array immediately
        notificationsData = notificationsData.filter(n => n.id !== id);
        updateBadge(notificationsData.length);
        renderNotifications(notificationsData);

        // Send to server
        fetch(`/notifications/${id}/mark-as-sent`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload after 1 second to sync
                setTimeout(loadNotifications, 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadNotifications(); // Reload on error
        });
    };

    // Dismiss notification
    window.dismissNotification = function(id) {
        console.log('Dismissing notification:', id);
        
        // Remove from local array immediately
        notificationsData = notificationsData.filter(n => n.id !== id);
        updateBadge(notificationsData.length);
        renderNotifications(notificationsData);

        // Send to server
        fetch(`/notifications/${id}/dismiss`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Dismiss response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Dismiss response data:', data);
            if (data.success) {
                // Reload after 1 second to sync
                setTimeout(loadNotifications, 1000);
            } else {
                alert('Error: ' + data.message);
                loadNotifications(); // Reload on error
            }
        })
        .catch(error => {
            console.error('Dismiss error:', error);
            loadNotifications(); // Reload on error
        });
    };

    // Format date
    function formatDate(dateString) {
        const date = new Date(dateString + 'T00:00:00');
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) {
            return 'Today';
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Yesterday';
        } else {
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }
    }
});
</script>
