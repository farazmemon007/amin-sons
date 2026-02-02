<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all pending notifications due today or earlier
     * Called on page load to show badge count
     * Includes both booking reminders and product stock alerts
     */
    public function getPendingNotifications()
    {
        try {
            $notifications = Notification::where('status', 'pending')
                ->whereDate('notification_date', '<=', Carbon::today())
                ->with(['booking', 'customer', 'product', 'warehouse'])
                ->orderBy('notification_date', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'count' => $notifications->count(),
                'notifications' => $notifications->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'title' => $n->title,
                        'description' => $n->description,
                        'type' => $n->type,
                        'notification_date' => $n->notification_date->format('Y-m-d'),
                        'customer_name' => $n->customer?->customer_name ?? ($n->product?->item_name ?? 'Unknown'),
                        'booking_no' => $n->booking?->invoice_no ?? ($n->product?->item_code ?? 'N/A'),
                        'product_name' => $n->product?->item_name,
                        'warehouse_name' => $n->warehouse?->warehouse_name,
                        'status' => $n->status,
                        'is_read' => $n->is_read,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update([
                'is_read' => true,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
                'is_read' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as sent',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark notification as dismissed
     */
    public function dismiss($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->update(['status' => 'dismissed']);

            return response()->json([
                'success' => true,
                'message' => 'Notification dismissed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error dismissing notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get notification count for badge
     */
    public function getCount()
    {
        try {
            $count = Notification::where('status', 'pending')
                ->whereDate('notification_date', '<=', Carbon::today())
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all notifications (pending, sent, dismissed)
     * Used for full notifications page with tabs
     */
    public function getAllNotifications()
    {
        try {
            $notifications = Notification::with(['booking', 'customer', 'product', 'warehouse'])
                ->orderBy('notification_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'notifications' => $notifications->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'title' => $n->title,
                        'description' => $n->description,
                        'type' => $n->type,
                        'notification_date' => $n->notification_date->format('Y-m-d'),
                        'customer_name' => $n->customer?->customer_name ?? ($n->product?->item_name ?? 'Unknown'),
                        'booking_no' => $n->booking?->invoice_no ?? ($n->product?->item_code ?? 'N/A'),
                        'product_name' => $n->product?->item_name,
                        'warehouse_name' => $n->warehouse?->warehouse_name,
                        'status' => $n->status,
                        'is_read' => $n->is_read,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
