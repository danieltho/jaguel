{{--
    Email footer — mirrors Figma node 1219:7315.
    Include via: @include('emails.partials.footer')

    Email-friendly: inline styles, table layout, 390px minimum width.
--}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
    style="background-color: #A1A389; color: #F8F8F9; font-family: 'Montserrat', Arial, sans-serif;">
    <tr>
        <td style="padding: 24px 60px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                {{-- Top row: brand + contact --}}
                <tr>
                    {{-- Brand + social --}}
                    <td valign="top" style="padding-bottom: 16px;">
                        <p style="margin: 0 0 24px 0; font-size: 20px; font-weight: 800; letter-spacing: 2px; line-height: 1; color: #FFFFFF;">
                            EL JAGÜEL
                        </p>
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="padding-right: 8px;">
                                    <a href="{{ $whatsappUrl ?? '#' }}"
                                        style="display: inline-block; width: 24px; height: 24px; border: 1px solid #FFFFFF; border-radius: 50%; text-align: center; line-height: 22px; text-decoration: none; color: #FFFFFF; font-size: 12px;">
                                        WA
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ $instagramUrl ?? '#' }}"
                                        style="display: inline-block; width: 24px; height: 24px; border: 1px solid #FFFFFF; border-radius: 50%; text-align: center; line-height: 22px; text-decoration: none; color: #FFFFFF; font-size: 12px;">
                                        IG
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>

                    {{-- Address + contact --}}
                    <td valign="top" align="right" style="padding-bottom: 16px; font-size: 12px; line-height: 1.4; color: #F8F8F9;">
                        <div style="margin-bottom: 16px;">
                            Calle 37 N° 1242<br>
                            Miramar, Buenos Aires
                        </div>
                        <div>
                            +54 9 223 312-3981<br>
                            eljaguelcriollo@gmail.com
                        </div>
                    </td>
                </tr>

                {{-- Copyright --}}
                <tr>
                    <td colspan="2" align="center" style="padding-top: 16px; font-size: 12px; color: #FFFFFF;">
                        &copy; {{ date('Y') }} El Jaguel. All rights reserved
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
