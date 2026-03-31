<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pressRelease->title }}</title>
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
            border-bottom: 3px solid #0066cc;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #0066cc;
            font-size: 24px;
        }
        .meta {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
        .link-btn {
            display: inline-block;
            background: #0066cc;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $pressRelease->title }}</h1>
            <div class="meta">
                <strong>Source:</strong> {{ $pressRelease->source ?? 'N/A' }} | 
                <strong>Date:</strong> {{ $pressRelease->created_at->format('F d, Y') }}
            </div>
        </div>

        <div class="content">
            @if ($pressRelease->url)
                <p>
                    <a href="{{ $pressRelease->url }}" class="link-btn">Read Full Press Release</a>
                </p>
            @endif
            
            @if ($pressRelease->description)
                <p>{{ $pressRelease->description }}</p>
            @endif
        </div>

        <div class="footer">
            <p>This is an automated notification from our agency information system.</p>
            <p>You received this because your account has email notifications enabled.</p>
        </div>
    </div>
</body>
</html>
