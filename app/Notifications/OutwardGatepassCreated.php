<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class OutwardGatepassCreated extends Notification
{
    use Queueable;

    protected $gpId;
    protected $orderId;

    public function __construct($gpId, $orderId = null)
    {
        $this->gpId = $gpId;
        $this->orderId = $orderId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Outward gate pass created (#'.$this->gpId.')',
            'gp_id' => $this->gpId,
            'order_id' => $this->orderId,
        ];
    }
}
