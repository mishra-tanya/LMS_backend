<?php

namespace App\Jobs;

use App\Models\PhonePeTransactions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;  // <-- Add this
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPaymentStatusEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;  // <-- Include Dispatchable here

    protected $transaction;

    /**
     * Create a new job instance.
     *
     * @param PhonePeTransactions $transaction
     */
    public function __construct(PhonePeTransactions $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $user = $this->transaction->user;

        \Log::info('SendPaymentStatusEmailJob running for transaction ID: '.$this->transaction->id);

        if (!$user || !$user->email) {
            return; // no user or email, skip
        }

        $status = $this->transaction->status;
        $amount = $this->transaction->amount;
        $transactionId = $this->transaction->transaction_id;

        // Prepare email data (customize as needed)
        $emailData = [
            'name' => $user->name,
            'status' => ucfirst($status),
            'amount' => $amount,
            'transactionId' => $transactionId,
        ];

        // Send email using your Mailable class
        Mail::to($user->email)->send(new \App\Mail\PaymentStatusMail($emailData));
    }
}
