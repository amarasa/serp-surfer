@component('vendor.mail.html.message')
# New Contact Message

You have received a new message from **{{ $name }}** ({{ $email }}).

**Message:**

{{ $messageContent }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent