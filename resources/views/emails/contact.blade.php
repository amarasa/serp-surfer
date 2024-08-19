@component('mail::message')
# New Contact Message

You have received a new message from **{{ $name }}** ({{ $email }}).

---

**Message:**

{{ $messageContent }}

---

{{ config('app.name') }}
@endcomponent