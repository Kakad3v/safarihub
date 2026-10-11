@props(['note' => null])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:8px 0 24px;">
    <tr>
        <td align="center" class="codebox" bgcolor="#E3F1F0"
            style="background-color:#E3F1F0;border:1px solid #BFD3D0;border-radius:12px;padding:22px 12px 18px;">
            <div class="code ink"
                style="padding-left:12px;font-family:'SFMono-Regular',Menlo,Consolas,'Liberation Mono',monospace;font-size:40px;line-height:44px;font-weight:700;letter-spacing:12px;color:#0C2B2F;">
                {{ $slot }}</div>
            @if ($note)
                <div class="muted"
                    style="margin-top:8px;font-family:'Figtree',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:13px;line-height:18px;color:#587074;">
                    {{ $note }}</div>
            @endif
        </td>
    </tr>
</table>
