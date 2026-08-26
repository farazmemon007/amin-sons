<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Apply Role, Branch and Warehouse Scope on Notification Query
     */
    protected function applyScope($query)
    {
        $user = auth()->user();
        if (!$user || $user->hasRole('super admin')) {
            return $query;
        }

        $branchId = $user->branch_id;
        $assignedWhIds = $user->assignedWarehouseIds();

        return $query->where(function ($q) use ($user, $branchId, $assignedWhIds) {
            // Branch level check
            $q->where(function ($bq) use ($branchId) {
                if ($branchId) {
                    $bq->where('branch_id', $branchId)
                       ->orWhereNull('branch_id');
                } else {
                    $bq->whereNull('branch_id');
                }
            });

            // Warehouse incharge level check
            if (!empty($assignedWhIds) && !$user->hasRole('branch admin') && !$user->hasRole('admin')) {
                $q->where(function ($wq) use ($assignedWhIds) {
                    $wq->whereIn('warehouse_id', $assignedWhIds)
                       ->orWhereNull('warehouse_id');
                });
            }
        });
    }

    /**
     * Get all pending notifications due today or earlier
     * Called on page load to show badge count
     * Includes booking reminders, DC notifications and product stock alerts
     */
    public function getPendingNotifications()
    {
        try {
            $query = Notification::where('status', 'pending')
                ->whereDate('notification_date', '<=', Carbon::today());

            $query = $this->applyScope($query);

            $notifications = $query->with(['booking', 'customer', 'product', 'warehouse', 'createdBy', 'sale'])
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            return response()
                ->json([
                    'success' => true,
                    'count' => $notifications->count(),
                    'notifications' => $notifications->map(function ($n) {
                        $customerName = 'Unknown';
                        $bookingNo = 'N/A';

                        if ($n->type === 'stock_request') {
                            $creator = $n->createdBy?->name ?? 'System';
                            if (preg_match('/received from\s+(.+?)\.?$/i', $n->description, $branchMatches)) {
                                $branchName = trim($branchMatches[1]);
                                $customerName = $creator . ' (' . $branchName . ')';
                            } else {
                                $customerName = $creator;
                            }
                            
                            if (preg_match('/request #(\d+)/i', $n->description, $matches)) {
                                $bookingNo = 'Request #' . $matches[1];
                            } else {
                                $bookingNo = 'Request Details';
                            }
                        } elseif ($n->type === 'dc_created') {
                            $customerName = $n->customer?->customer_name ?? ($n->sale?->sub_customer ?? 'Customer');
                            $bookingNo = $n->title ?? 'New DC';
                        } elseif ($n->type === 'po_created') {
                            $customerName = $n->createdBy?->name ?? 'System';
                            $bookingNo = $n->title ?? 'New PO';
                        } else {
                            $customerName = $n->customer?->customer_name ?? ($n->product?->item_name ?? 'Unknown');
                            $bookingNo = $n->booking?->invoice_no ?? ($n->product?->item_code ?? 'N/A');
                        }

                        $targetUrl = $this->resolveTargetUrl($n);

                        return [
                            'id' => $n->id,
                            'title' => $n->title,
                            'description' => $n->description,
                            'type' => $n->type,
                            'notification_date' => $n->notification_date->format('Y-m-d'),
                            'customer_name' => $customerName,
                            'booking_no' => $bookingNo,
                            'product_name' => $n->product?->item_name,
                            'warehouse_name' => $n->warehouse?->warehouse_name,
                            'warehouse_order_id' => $n->warehouse_order_id,
                            'target_url' => $targetUrl,
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
     * Resolve destination URL based on notification type
     */
    private function resolveTargetUrl($n): string
    {
        if ($n->type === 'po_created') {
            return $n->sale_id ? url('/inward-gatepass/from-po/' . $n->sale_id) : url('/InwardGatepass');
        }
        if ($n->type === 'dc_created') {
            return $n->warehouse_order_id ? url('/add/OutwardGatepass/' . $n->warehouse_order_id) : url('/OutwardGatepass');
        }
        if ($n->type === 'stock_request') {
            return url('/stock_transfers');
        }
        if ($n->type === 'product_stock_alert') {
            return url('/productget');
        }
        if (str_contains((string)$n->type, 'booking')) {
            return url('/bookings');
        }
        return url('/InwardGatepass');
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

            $query = $this->applyScope($query);

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

            $query = $this->applyScope($query);

            $notifications = $query->with(['booking', 'customer', 'product', 'warehouse', 'createdBy', 'sale'])
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            return response()
                ->json([
                    'success' => true,
                    'notifications' => $notifications->map(function ($n) {
                        $customerName = 'Unknown';
                        $bookingNo = 'N/A';

                        if ($n->type === 'stock_request') {
                            $creator = $n->createdBy?->name ?? 'System';
                            if (preg_match('/received from\s+(.+?)\.?$/i', $n->description, $branchMatches)) {
                                $branchName = trim($branchMatches[1]);
                                $customerName = $creator . ' (' . $branchName . ')';
                            } else {
                                $customerName = $creator;
                            }
                            
                            if (preg_match('/request #(\d+)/i', $n->description, $matches)) {
                                $bookingNo = 'Request #' . $matches[1];
                            } else {
                                $bookingNo = 'Request Details';
                            }
                        } elseif ($n->type === 'dc_created') {
                            $customerName = $n->customer?->customer_name ?? ($n->sale?->sub_customer ?? 'Customer');
                            $bookingNo = $n->title ?? 'New DC';
                        } elseif ($n->type === 'po_created') {
                            $customerName = $n->createdBy?->name ?? 'System';
                            $bookingNo = $n->title ?? 'New PO';
                        } else {
                            $customerName = $n->customer?->customer_name ?? ($n->product?->item_name ?? 'Unknown');
                            $bookingNo = $n->booking?->invoice_no ?? ($n->product?->item_code ?? 'N/A');
                        }

                        $targetUrl = $this->resolveTargetUrl($n);

                        return [
                            'id' => $n->id,
                            'title' => $n->title,
                            'description' => $n->description,
                            'type' => $n->type,
                            'notification_date' => $n->notification_date->format('Y-m-d'),
                            'customer_name' => $customerName,
                            'booking_no' => $bookingNo,
                            'product_name' => $n->product?->item_name,
                            'warehouse_name' => $n->warehouse?->warehouse_name,
                            'warehouse_order_id' => $n->warehouse_order_id,
                            'target_url' => $targetUrl,
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
