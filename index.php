<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muzammal's S3 Uploader</title>
    <style>
        /* Modern CSS Variables for easy color changes */
        :root {
            --primary-color: #4F46E5; /* Indigo */
            --primary-hover: #4338CA;
            --bg-color: #F3F4F6;
            --card-bg: #FFFFFF;
            --text-main: #1F2937;
            --text-muted: #6B7280;
            --border-color: #E5E7EB;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            line-height: 1.5;
        }

        .upload-card {
            background-color: var(--card-bg);
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .file-input-wrapper {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 2rem 1rem;
            background-color: #f8fafc;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .file-input-wrapper:hover {
            border-color: var(--primary-color);
            background-color: #f1f5f9;
        }

        input[type="file"] {
            width: 100%;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Styling the default ugly choose file button */
        input[type="file"]::file-selector-button {
            background-color: var(--border-color);
            color: var(--text-main);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            margin-right: 1rem;
            transition: background-color 0.2s ease;
        }

        input[type="file"]::file-selector-button:hover {
            background-color: #d1d5db;
        }

        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .submit-btn:hover {
            background-color: var(--primary-hover);
        }

        .submit-btn:active {
            transform: scale(0.98);
        }

        .footer {
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="upload-card">
        <div class="header">
            <h1>Muzammal's S3 Uploader</h1>
            <p>Securely transfer images to your AWS environment</p>
        </div>

        <!-- The form action and method match the required backend logic exactly -->
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            
            <div class="file-input-wrapper">
                <!-- The name attribute "image_file" is required by the instructor's upload.php -->
                <input type="file" name="image_file" id="image_file" accept="image/*" required>
            </div>
            
            <button type="submit" class="submit-btn">Upload to Bucket</button>
        </form>

        <div class="footer">
            Powered by PHP & Amazon ECS
        </div>
    </div>

</body>
</html>