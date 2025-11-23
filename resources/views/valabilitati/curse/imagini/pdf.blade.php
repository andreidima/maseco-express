<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imagine cursă</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
        }

        .container {
            padding: 16px;
        }

        .image-wrapper {
            text-align: center;
        }

        .image-wrapper img {
            max-width: 100%;
            height: auto;
        }

        .meta {
            margin-bottom: 12px;
            font-size: 12px;
            color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="meta">
        <div><strong>Imagine:</strong> {{ $imagine->original_name ?? 'Imagine cursă' }}</div>
    </div>
    <div class="image-wrapper">
        <img src="{{ $imageDataUri }}" alt="Imagine cursă">
    </div>
</div>
</body>
</html>
