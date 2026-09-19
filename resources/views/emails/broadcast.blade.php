<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subjectLine ?? 'Message' }}</title>
</head>
<body style="margin: 0; background: #f1f5f9; color: #1e293b; font-family: Arial, sans-serif; line-height: 1.6;">
    <div style="padding: 32px 16px;">
        <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px;">
            <div style="color: #4f46e5; font-size: 13px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase;">CRM System</div>
            <div style="margin-top: 24px; font-size: 16px;">{!! nl2br(e($bodyText)) !!}</div>
            <div style="margin-top: 32px; border-top: 1px solid #e2e8f0; padding-top: 16px; color: #64748b; font-size: 12px;">
                You are receiving this message because you are connected with our team. To stop receiving campaign emails, use the unsubscribe option provided by the sender.
            </div>
        </div>
    </div>
</body>
</html>