<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $announcement->title }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #ff9800;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #ff9800;
            font-size: 24px;
        }
        .meta {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .category {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 8px;
        }
        .content {
            margin: 20px 0;
        }
        .footer {
            border-top: 1px solid #ddd;
            margin-top: 20px;
            padding-top: 15px;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $announcement->title }}</h1>
            <div class="meta">
                <strong>Date:</strong> {{ $announcement->announcement_date->format('F d, Y') }}
            </div>
            @if ($announcement->category)
                <div class="category">{{ ucfirst($announcement->category) }}</div>
            @endif
        </div>

        <div class="content">
            @if ($announcement->excerpt)
                <p><strong>{{ $announcement->excerpt }}</strong></p>
            @endif
            
            @if ($announcement->description)
                <p>{{ $announcement->description }}</p>
            @endif
        </div>

        <div class="footer">
            <p>This is an automated notification from our agency information system.</p>
            <p>You received this because your account has email notifications enabled.</p>
        </div>
    </div>
</body>
</html>
