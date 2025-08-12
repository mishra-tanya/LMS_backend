@php
    $status = strtolower($data['status']);

    $icon = '';
    $statusText = '';
    $colorClass = '';

    if ($status === 'success') {
        $icon = '✅'; // Green check
        $statusText = 'Payment Successful';
        $colorClass = 'text-green-600';
    } elseif ($status === 'pending') {
        $icon = '⚠️'; // Warning sign
        $statusText = 'Payment Pending';
        $colorClass = 'text-yellow-600';
    } elseif ($status === 'failed') {
        $icon = '❌'; // Red cross
        $statusText = 'Payment Failed';
        $colorClass = 'text-red-600';
    } else {
        $icon = 'ℹ️'; // Info icon
        $statusText = ucfirst($status);
        $colorClass = 'text-gray-600';
    }
@endphp

<x-mail::message>

Hello {{ $data['name'] }},

<x-mail::panel class="{{ $colorClass }}">
    <span style="font-size: 1.25rem;">{{ $icon }} {{ $statusText }}</span>
</x-mail::panel>
This is to notify you that your payment status is: {{ ucfirst($status) }}.

**Amount:** ₹{{ number_format($data['amount'], 2) }}

**Transaction ID:** {{ $data['transactionId'] }}

Thank you for using our service. We appreciate your trust and look forward to serving you again.

Regards,  
{{ config('app.name') }}

</x-mail::message>
