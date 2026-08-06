<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>You're Invited</title>
</head>

<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding:40px 20px;">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:10px;overflow:hidden;">

                    <tr>
                        <td style="background:#0d6efd;padding:25px;text-align:center;color:#fff;">
                            <h2 style="margin:0;">SyncWithTeam</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:40px;">

                            <h3 style="margin-top:0;">
                                Hello {{ $invitation->user->first_name }},
                            </h3>

                            <p style="font-size:15px;line-height:24px;color:#555;">
                                You have been invited to join your organization's
                                <strong>SyncWithTeam</strong> workspace.
                            </p>

                            <p style="font-size:15px;line-height:24px;color:#555;">
                                Click the button below to activate your account and
                                create your password.
                            </p>

                            <div style="text-align:center;margin:35px 0;">

                             <a href="{{ route('invitation.accept', $invitation->token) }}"
                                    style="background:#0d6efd;
                                           color:#fff;
                                           text-decoration:none;
                                           padding:14px 30px;
                                           border-radius:6px;
                                           display:inline-block;
                                           font-weight:bold;">

                                    Activate Account

                                </a>

                            </div>

                            <p style="font-size:13px;color:#777;">
                                This invitation will expire in
                                <strong>7 days</strong>.
                            </p>

                            <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">

                            <p style="font-size:12px;color:#999;">
                                If you didn't expect this invitation, you can safely ignore this email.
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f8f9fa;padding:18px;text-align:center;font-size:12px;color:#777;">
                            © {{ date('Y') }} SyncWithTeam. All rights reserved.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>