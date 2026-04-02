<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>New Join Request</title>
    <style>
        body { margin:0; padding:0; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
        table { border-collapse:collapse; }
        a { color: inherit; text-decoration: none; }
        /* Fallback for clients that support embedded styles */
        @media only screen and (max-width:600px) {
            .container { width:100% !important; padding:16px !important; }
            .stack { display:block !important; width:100% !important; box-sizing:border-box; }
            .title { font-size:20px !important; }
        }
    </style>
</head>
<body style="background-color:#262626; margin:0; padding:24px; font-family:Segoe UI, Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased;">
    <center style="width:100%; table-layout:fixed;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" align="center">
            <tr>
                <td align="center">
                    <!-- Container -->
                    <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 6px 18px rgba(0,0,0,0.08);">
                        <!-- Top accent bar -->
                        <tr>
                            <td style="background:#007f88; padding:18px 24px;">
                                <h1 class="title" style="margin:0; font-size:22px; line-height:1.2; color:#ffffff; font-weight:700; text-align:left;">
                                    Nieuw lidmaatschapsverzoek
                                </h1>
                            </td>
                        </tr>

                        <!-- Body -->
                        <tr>
                            <td style="padding:20px 24px 8px 24px; color:#333333;">
                                <p style="margin:0 0 12px 0; font-size:14px; color:#4b5563;">
                                    Je hebt een nieuw lidmaatschapsverzoek ontvangen. Hieronder vind je de details.
                                </p>
                            </td>
                        </tr>

                        <!-- Segmented fields -->
                        <tr>
                            <td style="padding:0 24px 0 24px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-radius:8px; overflow:hidden; border:1px solid #e6e6e6;">
                                    <!-- Organization -->
                                    <tr>
                                        <td style="padding:16px; background:#ffffff;">
                                            <strong style="display:block; font-size:13px; color:#111827;">Organisatie</strong>
                                            <div style="margin-top:6px; font-size:15px; color:#374151;">{{ $organisation ?? '-' }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height:1px; background:#f3f4f6;"></td>
                                    </tr>
                                    <!-- Name -->
                                    <tr>
                                        <td style="padding:16px; background:#ffffff;">
                                            <strong style="display:block; font-size:13px; color:#111827;">Naam</strong>
                                            <div style="margin-top:6px; font-size:15px; color:#374151;">{{ $fullName ?? '-' }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height:1px; background:#f3f4f6;"></td>
                                    </tr>

                                    <!-- Email -->
                                    <tr>
                                        <td style="padding:16px; background:#ffffff;">
                                            <strong style="display:block; font-size:13px; color:#111827;">E-mail</strong>
                                            <div style="margin-top:6px; font-size:15px; color:#374151;">
                                                <a href="mailto:{{ $emailAddress }}" style="color:#007f88;">{{ $emailAddress ?? '-' }}</a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height:1px; background:#f3f4f6;"></td>
                                    </tr>

                                    <!-- Heard From -->
                                    <tr>
                                        <td style="padding:16px; background:#ffffff;">
                                            <strong style="display:block; font-size:13px; color:#111827;">Waar ze van ons gehoord hebben</strong>
                                            <div style="margin-top:6px; font-size:15px; color:#374151;">{{ $heardFrom ?? '—' }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="height:1px; background:#f3f4f6;"></td>
                                    </tr>

                                    <!-- Message -->
                                    <tr>
                                        <td style="padding:16px; background:#ffffff;">
                                            <strong style="display:block; font-size:13px; color:#111827;">Waarom ze willen aansluiten</strong>
                                            <div style="margin-top:8px; font-size:15px; color:#374151; white-space:pre-wrap;">{{ $joinUs ?? '—' }}</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style="padding:0 24px 20px 24px; border-top:1px solid #f3f4f6; text-align:left;">
                                <p style="margin:12px 0 0 0; font-size:12px; color:#9ca3af;">Dit bericht werd verstuurd door InteresseTest.</p>
                            </td>
                        </tr>
                    </table>
                    <!-- End container -->
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
