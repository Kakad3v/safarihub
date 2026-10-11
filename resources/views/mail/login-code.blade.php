<x-mail.layout :preheader="'Your SafariHub sign-in code is ' . $code . '. It expires in 10 minutes.'">
    <x-mail.heading>Your sign-in code</x-mail.heading>
    <x-mail.text>Enter this code on SafariHub to sign in. It works once.</x-mail.text>
    <x-mail.code note="Expires in 10 minutes">{{ $code }}</x-mail.code>
    <x-mail.text>Or skip the typing and sign in with one tap:</x-mail.text>
    <x-mail.button :url="$link">Sign in to SafariHub</x-mail.button>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px;">
        <tr>
            <td class="rule" style="border-top:1px solid #D3DFDD;font-size:0;line-height:0;height:1px;">&nbsp;</td>
        </tr>
    </table>
    <x-mail.text :small="true">Button not working? Paste this link into your browser:<br><a
            href="{{ $link }}" style="color:#0A7C79;word-break:break-all;">{{ $link }}</a></x-mail.text>
    <x-mail.text :small="true">Did not request this? You can ignore this email. Nobody can sign in without the code
        or link above, so keep them private.</x-mail.text>
</x-mail.layout>
