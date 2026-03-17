<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

header('Content-Type: application/json');

function logPromptRequest($pdo, $params) {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO prompt_requests
             (user_id, session_id, ip_address, grade_range, subject, voice,
              user_prompt, groq_response, model_used, tokens_sent,
              tokens_received, response_time_ms, status, error_message)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $params['user_id'],
            $params['session_id'],
            $params['ip_address'],
            $params['grade_range'],
            $params['subject'],
            $params['voice'],
            $params['user_prompt'],
            $params['groq_response'],
            $params['model_used'],
            $params['tokens_sent'],
            $params['tokens_received'],
            $params['response_time_ms'],
            $params['status'],
            $params['error_message'],
        ]);
    } catch (PDOException $e) {
        error_log('Failed to log prompt request: ' . $e->getMessage());
    }
}

$input = json_decode(file_get_contents('php://input'), true);
$gradeRange = trim($input['gradeRange'] ?? '');
$subject = trim($input['subject'] ?? '');
$voice = trim($input['voice'] ?? '');
$userMessage = trim($input['message'] ?? '');

if ($userMessage === '' || $subject === '' || $gradeRange === '' || $voice === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Message, subject, grade range, and voice are required']);
    exit();
}

$apiKey = getenv('GROQ_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'GROQ_API_KEY is not configured']);
    exit();
}

$modelUsed = 'llama-3.1-8b-instant';

$logParams = [
    'user_id'     => $_SESSION['user_id'],
    'session_id'  => session_id(),
    'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
    'grade_range' => $gradeRange,
    'subject'     => $subject,
    'voice'       => $voice,
    'user_prompt' => $userMessage,
    'model_used'  => $modelUsed,
];

$systemPrompt = 'You are a helpful AI tutor specializing in ' . $subject . ' for ' . $gradeRange . ' school students. Answer all questions in the voice of: ' . $voice . '. You must respond in valid JSON with exactly these fields: {"answer": "Your full tutoring response", "topic": "The specific topic addressed (e.g. fractions, photosynthesis)", "difficulty": "beginner | intermediate | advanced", "key_concepts": ["concept1", "concept2", "concept3"], "summary": "One-line summary of your answer"}';

$payload = json_encode([
    'model' => $modelUsed,
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userMessage],
    ],
    'temperature' => 0.7,
    'max_tokens' => 1024,
    'response_format' => ['type' => 'json_object'],
]);

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_TIMEOUT => 30,
]);

$startTime = microtime(true);
$response = curl_exec($ch);
$responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    logPromptRequest($pdo, array_merge($logParams, [
        'groq_response'    => null,
        'tokens_sent'      => null,
        'tokens_received'  => null,
        'response_time_ms' => $responseTimeMs,
        'status'           => 'error',
        'error_message'    => 'Curl error: ' . $curlError,
    ]));
    http_response_code(502);
    echo json_encode(['error' => 'Failed to connect to Groq API: ' . $curlError]);
    exit();
}

if ($httpCode !== 200) {
    logPromptRequest($pdo, array_merge($logParams, [
        'groq_response'    => $response,
        'tokens_sent'      => null,
        'tokens_received'  => null,
        'response_time_ms' => $responseTimeMs,
        'status'           => 'error',
        'error_message'    => 'Groq HTTP ' . $httpCode,
    ]));
    http_response_code($httpCode);
    echo json_encode(['error' => 'Groq API error', 'details' => json_decode($response, true)]);
    exit();
}

$data = json_decode($response, true);
$replyRaw = $data['choices'][0]['message']['content'] ?? '';
$reply = json_decode($replyRaw, true);

$tokensSent     = $data['usage']['prompt_tokens'] ?? null;
$tokensReceived = $data['usage']['completion_tokens'] ?? null;

logPromptRequest($pdo, array_merge($logParams, [
    'groq_response'    => $replyRaw,
    'tokens_sent'      => $tokensSent,
    'tokens_received'  => $tokensReceived,
    'response_time_ms' => $responseTimeMs,
    'status'           => 'success',
    'error_message'    => null,
]));

$replyOutput = is_array($reply) && isset($reply['answer'])
    ? [
        'answer'       => $reply['answer'] ?? '',
        'topic'        => $reply['topic'] ?? '',
        'difficulty'   => $reply['difficulty'] ?? '',
        'key_concepts' => $reply['key_concepts'] ?? [],
        'summary'      => $reply['summary'] ?? '',
    ]
    : [
        'answer'       => $replyRaw,
        'topic'        => '',
        'difficulty'   => '',
        'key_concepts' => [],
        'summary'      => '',
    ];

echo json_encode([
    'reply'           => $replyOutput,
    'tokens_sent'     => $tokensSent,
    'tokens_received' => $tokensReceived,
]);
