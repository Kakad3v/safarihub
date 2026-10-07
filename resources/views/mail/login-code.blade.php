<x-mail::message>
    # Your sign-in code

    <x-mail::panel>
        {{ $code }}
    </x-mail::panel>

    The code expires in 10 minutes. You can also sign in with one tap:

    <x-mail::button :url="$link">Sign in to SafariHub</x-mail::button>

    If you did not request this, you can ignore this email.
</x-mail::message>
