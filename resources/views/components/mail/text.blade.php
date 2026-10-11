@props(['small' => false])
<p class="{{ $small ? 'muted' : 'ink' }}"
    style="margin:0 0 16px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:{{ $small ? '13px' : '16px' }};line-height:{{ $small ? '20px' : '24px' }};color:{{ $small ? '#587074' : '#0C2B2F' }};">
    {{ $slot }}</p>
