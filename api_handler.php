<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');
$tutorType = $input['tutor'] ?? 'general';

if ($userMessage === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit();
}

$apiKey = getenv('GROQ_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'GROQ_API_KEY is not configured']);
    exit();
}

$tutorPrompts = [
    'general' => 'You are a helpful AI tutor. Explain concepts clearly and encourage the student to think critically.',
    'socratic' => 'You are a Socratic tutor. Never give direct answers. Instead, guide the student by asking thoughtful questions that lead them to discover the answer on their own. Be patient and encouraging.',
    'math' => 'You are a math tutor. Break down problems step by step. Use clear notation. When the student makes an error, identify the specific step where they went wrong and explain why.',
    'science' => 'You are a science tutor. Explain scientific concepts using real-world examples and analogies. Encourage curiosity and connect ideas to everyday phenomena.',
    'writing' => 'You are a writing coach. Help the student improve their writing by focusing on clarity, structure, and voice. Give specific, actionable feedback rather than vague praise. Ask questions about their intended audience and purpose.',
    'coding' => 'You are a coding mentor. Help the student learn to code by explaining concepts clearly, reviewing their code, and suggesting improvements. Encourage good practices like readable variable names and breaking problems into smaller parts. When showing code, explain the reasoning behind each step.',
    'history' => 'You are a history tutor. Bring historical events to life by connecting them to causes, consequences, and human experiences. Encourage the student to analyze primary sources and consider multiple perspectives.',
];

$systemPrompt = $tutorPrompts[$tutorType] ?? $tutorPrompts['general'];

$payload = json_encode([
    'model' => 'llama-3.1-8b-instant',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userMessage],
    ],
    'temperature' => 0.7,
    'max_tokens' => 1024,
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

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(502);
    echo json_encode(['error' => 'Failed to connect to Groq API: ' . $curlError]);
    exit();
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    echo json_encode(['error' => 'Groq API error', 'details' => json_decode($response, true)]);
    exit();
}

$data = json_decode($response, true);
$reply = $data['choices'][0]['message']['content'] ?? 'No response from Groq.';

echo json_encode(['reply' => $reply]);
