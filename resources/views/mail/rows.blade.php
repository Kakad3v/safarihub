@props(['rows' => []])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="codebox" bgcolor="#E3F1F0"
    style="margin:8px 0 24px;background-color:#E3F1F0;border:1px solid #BFD3D0;border-radius:12px;">
    @foreach ($rows as [$label, $value])
        <tr>
            <td class="muted"
                style="padding:10px 16px;font-family:'Figtree',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:20px;color:#587074;">
                {{ $label }}</td>
            <td class="ink" align="right"
                style="padding:10px 16px;font-family:'Figtree',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:20px;font-weight:600;color:#0C2B2F;">
                {{ $value }}</td>
        </tr>
    @endforeach
</table>
