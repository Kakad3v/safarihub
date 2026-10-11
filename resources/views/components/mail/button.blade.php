@props(['url'])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td align="center" class="btn" bgcolor="#0A7C79"
                        style="background-color:#0A7C79;border-radius:8px;">
                        <a href="{{ $url }}"
                            style="display:inline-block;padding:15px 32px;font-family:'Figtree',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:16px;line-height:20px;font-weight:600;color:#FFFFFF;text-decoration:none;border-radius:8px;">{{ $slot }}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
