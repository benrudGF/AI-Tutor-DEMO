<!DOCTYPE html>
<html>
<head>
    <title>Groq API Key Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .result {
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            word-wrap: break-word;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        h1 { color: #333; }
        pre {
            background: #e9ecef;
            padding: 12px;
            border-radius: 4px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>Groq API Key Test</h1>

    <?php
    $apiKey = getenv('GROQ_API_KEY');

    if (empty($apiKey)) {
        echo '<div class="result error"><strong>Error:</strong> GROQ_API_KEY environment variable is not set.</div>';
        exit;
    }

    echo '<div class="result info"><strong>Status:</strong> GROQ_API_KEY is present.</div>';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $url = 'https://api.groq.com/openai/v1/chat/completions';
        $data = json_encode([
            'model' => 'llama-3.1-8b-instant',
            'messages' => [
                ['role' => 'user', 'content' => 'Say "Hello! Your Groq API key is working." and nothing else.']
            ],
            'max_tokens' => 50
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 15
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            echo '<div class="result error"><strong>Connection error:</strong> ' . htmlspecialchars($curlError) . '</div>';
        } elseif ($httpCode === 200) {
            $result = json_decode($response, true);
            $message = $result['choices'][0]['message']['content'] ?? 'No response content';
            $model = $result['model'] ?? 'unknown';
            echo '<div class="result success">';
            echo '<strong>Success!</strong> API key is valid and working.<br><br>';
            echo '<strong>Model:</strong> ' . htmlspecialchars($model) . '<br>';
            echo '<strong>Response:</strong> ' . htmlspecialchars($message);
            echo '</div>';
        } else {
            $result = json_decode($response, true);
            $errorMsg = $result['error']['message'] ?? $response;
            echo '<div class="result error">';
            echo '<strong>API Error (HTTP ' . $httpCode . '):</strong><br>';
            echo '<pre>' . htmlspecialchars($errorMsg) . '</pre>';
            echo '</div>';
        }
    } else {
        echo '<form method="POST"><button type="submit" class="btn">Run API Test</button></form>';
    }
    ?>
</body>
</html>
