<?php
require "../app/bootstrap.php";
header('Content-Type: application/json; charset=utf-8');

$userMessage = trim($_POST['message'] ?? '');
if (!$userMessage) { echo json_encode(['error' => 'empty']); exit; }
// Ограничение частоты: не чаще 1 запроса в 5 секунд и не больше 30 в час
$now = time();
$_SESSION['ai_log'] = array_values(array_filter(
    $_SESSION['ai_log'] ?? [],
    fn($t) => $t > $now - 3600
));

if (($_SESSION['ai_last'] ?? 0) > $now - 5) {
    http_response_code(429);
    echo json_encode(['error' => 'too_fast'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (count($_SESSION['ai_log']) >= 30) {
    http_response_code(429);
    echo json_encode(['error' => 'limit_reached'], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['ai_last']  = $now;
$_SESSION['ai_log'][] = $now;

// Ограничение длины вопроса
$userMessage = mb_substr($userMessage, 0, 500);

$stmt = $pdo->query("
    SELECT id, name, short_description, price,
           usage_info, composition, contraindications
    FROM products
    ORDER BY name ASC
");
$allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$productList = '';
foreach ($allProducts as $p) {
    $productList .= "- {$p['name']} ({$p['price']} руб.)";
    if ($p['short_description'])  $productList .= "\n  Описание: " . mb_strimwidth($p['short_description'], 0, 100, '...');
    if ($p['usage_info'])         $productList .= "\n  Способ применения: " . mb_strimwidth($p['usage_info'], 0, 150, '...');
    if ($p['composition'])        $productList .= "\n  Состав: " . mb_strimwidth($p['composition'], 0, 150, '...');
    if ($p['contraindications'])  $productList .= "\n  Противопоказания: " . mb_strimwidth($p['contraindications'], 0, 150, '...');
    $productList .= "\n\n";
}

$systemPrompt = <<<PROMPT
Ты фармацевт-консультант интернет-аптеки. Отвечай только на русском языке, кратко и по делу (2-4 предложения).

ВАЖНО: Ты можешь рекомендовать ТОЛЬКО товары из списка ниже. Не придумывай названия которых нет в списке.
Если подходящего товара нет в списке — НЕ добавляй блок ###MEDICINES### вообще, просто напиши текстом что такого товара нет.
Когда рекомендуешь товар — кратко упомяни способ применения и главные противопоказания если они есть.

=== ТОВАРЫ В НАШЕМ КАТАЛОГЕ ===
{$productList}
================================

Когда рекомендуешь товар — указывай его название ТОЧНО как в списке выше.
В конце ответа ОБЯЗАТЕЛЬНО добавь блок строго в таком формате (только если нашёл подходящий товар):
###MEDICINES###
["Точное название товара 1","Точное название товара 2"]
###END###

Если вопрос не про здоровье — ответь вежливо что ты только фармацевт-консультант, блок ###MEDICINES### не добавляй.
PROMPT;

$payload = json_encode([
    'model'  => 'qwen3:8b',
    'stream' => false,
    'think'  => false,
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' => $userMessage],
    ]
], JSON_UNESCAPED_UNICODE);

$ch = curl_init('http://localhost:11434/api/chat');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 120,
]);
$raw     = curl_exec($ch);
$curlErr = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['error' => 'ollama_unavailable']); exit;
}

$ollama   = json_decode($raw, true);
$fullText = $ollama['message']['content'] ?? '';

$products  = [];
$cleanText = $fullText;

if (preg_match('/###MEDICINES###(.*?)(?:###END###|$)/s', $fullText, $m)) {
    $cleanText = trim(preg_replace('/###MEDICINES###.*?(?:###END###|$)/s', '', $fullText));
    $block     = trim($m[1]);

    $medicines = json_decode($block, true);
    if (!is_array($medicines)) {
        preg_match_all('/"([^"]+)"|[\-\*]\s*(.+)/u', $block, $hits);
        $medicines = array_filter(array_map('trim', array_merge($hits[1], $hits[2])));
    }

foreach ($medicines as $name) {
        $name = trim($name, " \t\n\r-*\"'");
        if (!$name) continue;
    
        $stmt = $pdo->prepare("SELECT id, name, price, image, prescription, usage_info, composition, contraindications FROM products WHERE name = ? LIMIT 1");
        $stmt->execute([$name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$row) {
            $stmt = $pdo->prepare("SELECT id, name, price, image, prescription, usage_info, composition, contraindications FROM products WHERE name LIKE ? LIMIT 1");
            $stmt->execute(['%' . $name . '%']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    
        if (!$row) continue;
    
        // рецептурный препарат. карточку не показываем, предупреждаем
        if ((int)$row['prescription'] === 1) {
            $hasRx = true;
            continue;
        }
    
        $products[] = $row;
    }
}
    
if (!empty($hasRx)) {
     $cleanText .= "\n\n⚠ Часть подходящих препаратов отпускается по рецепту — их может назначить только врач.";
}

$cleanText = preg_replace('/###\w+###/s', '', $cleanText);
$cleanText = preg_replace('/<think>.*?<\/think>/s', '', $cleanText);
$cleanText = trim($cleanText);

echo json_encode([
    'text'     => $cleanText,
    'products' => $products,
], JSON_UNESCAPED_UNICODE);
