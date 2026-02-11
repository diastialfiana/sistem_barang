<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusChanged extends Notification
{
    use Queueable;

    protected $request;
    protected $role;
    protected $reason;

    public function __construct($request, $role, $reason)
    {
        $this->request = $request;
        $this->role = $role;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'request_code' => $this->request->code,
            'status' => $this->request->status,
            'rejected_by' => $this->role,
            'reason' => $this->reason,
            'message' => "Request {$this->request->code} has been rejected by {$this->role}: {$this->reason}",
        ];
    }
}
