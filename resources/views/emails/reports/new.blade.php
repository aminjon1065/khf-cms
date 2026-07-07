<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Новая заявка о ЧС</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1a1a1a; line-height: 1.5;">
    <h2 style="margin: 0 0 4px;">Новая заявка о ЧС</h2>
    <p style="margin: 0 0 16px; color: #666;">
        Номер обращения: <strong>{{ $report->reference }}</strong>
    </p>

    <table cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
        <tr>
            <td style="color:#666;">Тип происшествия</td>
            <td><strong>{{ $report->type }}</strong></td>
        </tr>
        <tr>
            <td style="color:#666;">Регион</td>
            <td>{{ $report->region }}</td>
        </tr>
        <tr>
            <td style="color:#666;">Место</td>
            <td>{{ $report->location }}</td>
        </tr>
        <tr>
            <td style="color:#666;">Телефон</td>
            <td>{{ $report->phone }}</td>
        </tr>
        <tr>
            <td style="color:#666; vertical-align: top;">Описание</td>
            <td>{{ $report->description }}</td>
        </tr>
        <tr>
            <td style="color:#666;">Получено</td>
            <td>{{ $report->created_at?->format('d.m.Y H:i') }}</td>
        </tr>
    </table>

    <p style="margin-top: 16px; color: #666; font-size: 13px;">
        Просмотреть и обработать заявку можно в CMS (раздел «Обращения»).
    </p>
</body>
</html>
