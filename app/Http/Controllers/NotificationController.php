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
            $query = Notification::where('status', 'pending')
                ->whereDate('notification_date', '<=', Carbon::today());

            if (!auth()->user()->hasRole('super admin')) {
                $branchId = auth()->user()->branch_id;
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            }

            $notifications = $query->with(['booking', 'customer', 'product', 'warehouse'])
                ->orderBy('notification_date', 'asc')
                ->get();

            return response()
                ->json([
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
                            'warehouse_order_id' => $n->warehouse_order_id,
                            'status' => $n->status,
                            'is_read' => $n->is_read,
                        ];
                    }),
                ])
                ->header('Content-Type', 'application/json')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        } catch (\Exception $e) {
            \Log::error('Notification fetch error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()
                ->json([
                    'success' => false,
                    'message' => 'Error fetching notifications',
                ], 500)
                ->header('Content-Type', 'application/json');
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

            return response()
                ->json([
                    'success' => true,
                    'message' => 'Notification marked as read',
                ])
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            \Log::error('Mark notification as read error: ' . $e->getMessage());

            return response()
                ->json([
                    'success' => false,
                    'message' => 'Error marking notification',
                ], 500)
                ->header('Content-Type', 'application/json');
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

            return response()
                ->json([
                    'success' => true,
                    'message' => 'Notification marked as sent',
                ])
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            \Log::error('Mark notification as sent error: ' . $e->getMessage());

            return response()
                ->json([
                    'success' => false,
                    'message' => 'Error updating notification',
                ], 500)
                ->header('Content-Type', 'application/json');
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

            return response()
                ->json([
                    'success' => true,
                    'message' => 'Notification dismissed',
                ])
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            \Log::error('Dismiss notification error: ' . $e->getMessage());

            return response()
                ->json([
                    'success' => false,
                    'message' => 'Error dismissing notification',
                ], 500)
                ->header('Content-Type', 'application/json');
        }
    }

    /**
     * Get notification count for badge
     */
    public function getCount()
    {
        try {
            $query = Notification::where('status', 'pending')
                ->whereDate('notification_date', '<=', Carbon::today());

            if (!auth()->user()->hasRole('super admin')) {
                $branchId = auth()->user()->branch_id;
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            }

            $count = $query->count();

            return response()
                ->json([
                    'success' => true,
                    'count' => $count,
                ])
                ->header('Content-Type', 'application/json')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        } catch (\Exception $e) {
            \Log::error('Notification count error: ' . $e->getMessage());

            return response()
                ->json([
                    'success' => false,
                    'count' => 0,
                ], 500)
                ->header('Content-Type', 'application/json');
        }
    }

    /**
     * Get all notifications (pending, sent, dismissed)
     * Used for full notifications page with tabs
     */
    public function getAllNotifications()
    {
        try {
            $query = Notification::query();

            if (!auth()->user()->hasRole('super admin')) {
                $branchId = auth()->user()->branch_id;
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            }

            $notifications = $query->with(['booking', 'customer', 'product', 'warehouse'])
                ->orderBy('notification_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()
                ->json([
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
                            'warehouse_order_id' => $n->warehouse_order_id,
                            'status' => $n->status,
                            'is_read' => $n->is_read,
                        ];
                    }),
                ])
                ->header('Content-Type', 'application/json')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        } catch (\Exception $e) {
            \Log::error('Get all notifications error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()
                ->json([
                    'success' => false,
                    'message' => 'Error fetching notifications',
                ], 500)
                ->header('Content-Type', 'application/json');
        }
    }
}
