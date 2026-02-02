@extends('admin_panel.layout.app')

@section('content')
<div class="main-content">
    <div class="main-content-inner">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h2 class="page-title">
                        <i class="fas fa-bell"></i> Notifications
                    </h2>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#pending-tab">
                                <i class="fas fa-clock"></i> Pending
                                <span class="badge badge-warning ml-2" id="pending-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#sent-tab">
                                <i class="fas fa-check"></i> Sent
                                <span class="badge badge-success ml-2" id="sent-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#dismissed-tab">
                                <i class="fas fa-times"></i> Dismissed
                                <span class="badge badge-secondary ml-2" id="dismissed-count">0</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="row">
                <div class="col-md-12">
                    <div class="tab-content">
                        <!-- Pending Notifications -->
                        <div id="pending-tab" class="tab-pane fade show active">
                            <div class="card">
                                <div class="card-body">
                                    <div id="pending-list" class="notification-list">
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sent Notifications -->
                        <div id="sent-tab" class="tab-pane fade">
                            <div class="card">
                                <div class="card-body">
                                    <div id="sent-list" class="notification-list">
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dismissed Notifications -->
                        <div id="dismissed-tab" class="tab-pane fade">
                            <div class="card">
                                <div class="card-body">
                                    <div id="dismissed-list" class="notification-list">
                                        <div class="text-center text-muted py-5">
                                            <i class="fas fa-spinner fa-spin"></i> Loading...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-title {
        font-size: 28px;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
    }

    .notification-list {
        list-style: none;
        padding: 0;
    }

    .notification-item-full {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .notification-item-full:hover {
        background-color: #f8f9fa;
        border-left: 4px solid #667eea;
        padding-left: 16px;
    }

    .notification-item-full:last-child {
        border-bottom: none;
    }

    .notification-item-header-full {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 10px;
    }

    .notification-title-full {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .notification-date-full {
        font-size: 13px;
        color: #999;
        background: #f0f0f0;
        padding: 4px 10px;
        border-radius: 20px;
    }

    .notification-content-full {
        margin: 10px 0;
        font-size: 14px;
        color: #666;
    }

    .notification-details {
        display: flex;
        gap: 20px;
        margin: 12px 0;
        flex-wrap: wrap;
        font-size: 13px;
    }

    .notification-detail {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #666;
    }

    .notification-detail i {
        color: #667eea;
        width: 16px;
    }

    .notification-actions-full {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .notification-btn {
        padding: 8px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .btn-read {
        background-color: #e7f3ff;
        color: #0066cc;
    }

    .btn-read:hover {
        background-color: #cce5ff;
    }

    .btn-dismiss {
        background-color: #f5f5f5;
        color: #666;
    }

    .btn-dismiss:hover {
        background-color: #efefef;
    }

    .btn-view {
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    .btn-view:hover {
        background-color: #c8e6c9;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.6;
    }

    .empty-text {
        font-size: 16px;
        font-weight: 500;
    }

    .badge {
        font-size: 11px;
        padding: 4px 8px;
    }

    .nav-tabs .nav-link {
        color: #666;
        border-bottom: 2px solid transparent;
        font-weight: 500;
        transition: all 0.2s;
    }

    .nav-tabs .nav-link:hover {
        color: #667eea;
        border-color: #667eea;
    }

    .nav-tabs .nav-link.active {
        color: #667eea;
        background-color: transparent;
        border-color: #667eea;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token safely
    function getCsrfToken() {
        // Try meta tag first
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            return metaToken.getAttribute('content');
        }
        
        // Try from laravel input
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

    loadAllNotifications();
    
    // Refresh every 60 seconds
    setInterval(loadAllNotifications, 60000);

    function loadAllNotifications() {
        fetch('/notifications/all')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderNotifications(data.notifications);
                }
            })
            .catch(error => console.error('Error loading notifications:', error));
    }

    function renderNotifications(notifications) {
        const pending = notifications.filter(n => n.status === 'pending');
        const sent = notifications.filter(n => n.status === 'sent');
        const dismissed = notifications.filter(n => n.status === 'dismissed');

        // Update counts
        document.getElementById('pending-count').textContent = pending.length;
        document.getElementById('sent-count').textContent = sent.length;
        document.getElementById('dismissed-count').textContent = dismissed.length;

        // Render lists
        renderList('pending-list', pending);
        renderList('sent-list', sent);
        renderList('dismissed-list', dismissed);
    }

    function renderList(elementId, notifications) {
        const element = document.getElementById(elementId);
        
        if (notifications.length === 0) {
            element.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <div class="empty-text">No notifications in this category</div>
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(notif => {
            html += `
                <div class="notification-item-full">
                    <div class="notification-item-header-full">
                        <h5 class="notification-title-full">${notif.title}</h5>
                        <span class="notification-date-full">${formatDate(notif.notification_date)}</span>
                    </div>
                    
                    <div class="notification-content-full">
                        ${notif.description || 'No description'}
                    </div>
                    
                    <div class="notification-details">
                        <div class="notification-detail">
                            <i class="fas fa-user"></i>
                            <span><strong>Customer:</strong> ${notif.customer_name}</span>
                        </div>
                        <div class="notification-detail">
                            ${notif.booking_no ? `<i class="fas fa-file-invoice"></i>
                            <span><strong>Booking:</strong> ${notif.booking_no}</span>` : (notif.product_name ? `<i class="fas fa-box"></i>
                            <span><strong>Product:</strong> ${notif.product_name}</span>` : '')}
                        </div>
                        <div class="notification-detail">
                            <i class="fas fa-tag"></i>
                            <span><strong>Type:</strong> ${notif.type}</span>
                        </div>
                    </div>
                    
                    <div class="notification-actions-full">
                        ${notif.status === 'pending' ? `
                            <button class="notification-btn btn-read" onclick="markAsRead(${notif.id})">
                                <i class="fas fa-check"></i> Mark as Read
                            </button>
                            <button class="notification-btn btn-read" onclick="markAsSent(${notif.id})">
                                <i class="fas fa-paper-plane"></i> Mark as Sent
                            </button>
                            <button class="notification-btn btn-dismiss" onclick="dismissNotification(${notif.id})">
                                <i class="fas fa-times"></i> Dismiss
                            </button>
                        ` : notif.status === 'sent' ? `
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Sent</span>
                        ` : `
                            <span class="badge badge-secondary"><i class="fas fa-ban"></i> Dismissed</span>
                        `}
                    </div>
                </div>
            `;
        });

        element.innerHTML = html;
    }

    window.markAsRead = function(id) {
        console.log('Marking as read:', id);
        fetch(`/notifications/${id}/mark-as-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Mark read response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Mark read response data:', data);
            if (data.success) {
                setTimeout(loadAllNotifications, 500);
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
                console.error('Mark read error:', data.message);
            }
        })
        .catch(error => {
            console.error('Mark read error:', error);
            alert('Error marking as read: ' + error.message);
        });
    };

    window.markAsSent = function(id) {
        console.log('Marking as sent:', id);
        fetch(`/notifications/${id}/mark-as-sent`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Mark sent response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Mark sent response data:', data);
            if (data.success) {
                setTimeout(loadAllNotifications, 500);
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
                console.error('Mark sent error:', data.message);
            }
        })
        .catch(error => {
            console.error('Mark sent error:', error);
            alert('Error marking as sent: ' + error.message);
        });
    };

    window.dismissNotification = function(id) {
        console.log('Dismissing notification:', id);
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
                setTimeout(loadAllNotifications, 500);
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
                console.error('Dismiss error:', data.message);
            }
        })
        .catch(error => {
            console.error('Dismiss error:', error);
            alert('Error dismissing: ' + error.message);
        });
    };

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
            return date.toLocaleDateString('en-US', { 
                year: 'numeric',
                month: 'short', 
                day: 'numeric' 
            });
        }
    }
});
</script>
@endsection
