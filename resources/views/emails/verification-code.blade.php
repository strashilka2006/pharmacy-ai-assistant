<x-mail::message>
# Подтверждение почты

Ваш код: **{{ $code }}**

Он действует {{ config('pharmacy.email_verification.code_ttl_minutes') }} минут.
Если вы не регистрировались в аптеке — просто проигнорируйте это письмо.

<x-mail::subcopy>
Письмо отправлено автоматически, отвечать на него не нужно.
</x-mail::subcopy>
</x-mail::message>
