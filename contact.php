<?php
// エラー表示（開発時のみ。本番環境では削除してください）
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// CORSヘッダー（必要に応じて調整）
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');

// POSTリクエストのみ許可
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POSTメソッドのみ許可されています']);
    exit;
}

// POSTデータの取得
$data = json_decode(file_get_contents('php://input'), true);

// 入力値の検証
$company = isset($data['company']) ? trim($data['company']) : '';
$name = isset($data['name']) ? trim($data['name']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$phone = isset($data['phone']) ? trim($data['phone']) : '';
$subject = isset($data['subject']) ? trim($data['subject']) : '';
$message = isset($data['message']) ? trim($data['message']) : '';

// バリデーション
$errors = [];

if (empty($company)) {
    $errors[] = '会社名を入力してください';
}

if (empty($name)) {
    $errors[] = 'お名前を入力してください';
}

if (empty($email)) {
    $errors[] = 'メールアドレスを入力してください';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = '有効なメールアドレスを入力してください';
}

if (empty($subject)) {
    $errors[] = '件名を入力してください';
}

if (empty($message)) {
    $errors[] = 'お問い合わせ内容を入力してください';
}

// エラーがある場合
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode('、', $errors)]);
    exit;
}

// ===== メール送信設定 =====
// ここに受信したいメールアドレスを設定してください
$to = 'takeuchi.kg414@gmail.com'; // ★ここを変更★

// メール件名
$email_subject = '【お問い合わせ】' . $subject;

// メール本文
$email_body = "GMT Coreのウェブサイトからお問い合わせがありました。\n\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━\n";
$email_body .= "■ 会社名\n";
$email_body .= $company . "\n\n";
$email_body .= "■ お名前\n";
$email_body .= $name . "\n\n";
$email_body .= "■ メールアドレス\n";
$email_body .= $email . "\n\n";
$email_body .= "■ 電話番号\n";
$email_body .= !empty($phone) ? $phone : '未記入' . "\n\n";
$email_body .= "■ 件名\n";
$email_body .= $subject . "\n\n";
$email_body .= "■ お問い合わせ内容\n";
$email_body .= $message . "\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━\n";

// メールヘッダー
$headers = "From: " . $email . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// メール送信
if (mail($to, $email_subject, $email_body, $headers)) {
    // 送信成功
    
    // 自動返信メール（オプション）
    $auto_reply_subject = 'お問い合わせを受け付けました - GMT Core';
    $auto_reply_body = $name . " 様\n\n";
    $auto_reply_body .= "この度は、GMT Coreへお問い合わせいただき、誠にありがとうございます。\n";
    $auto_reply_body .= "以下の内容でお問い合わせを受け付けました。\n\n";
    $auto_reply_body .= "━━━━━━━━━━━━━━━━━━━━\n";
    $auto_reply_body .= "■ 会社名: " . $company . "\n";
    $auto_reply_body .= "■ お名前: " . $name . "\n";
    $auto_reply_body .= "■ 件名: " . $subject . "\n";
    $auto_reply_body .= "■ お問い合わせ内容:\n" . $message . "\n";
    $auto_reply_body .= "━━━━━━━━━━━━━━━━━━━━\n\n";
    $auto_reply_body .= "内容を確認の上、担当者より折り返しご連絡させていただきます。\n";
    $auto_reply_body .= "今しばらくお待ちくださいますよう、お願い申し上げます。\n\n";
    $auto_reply_body .= "※このメールは自動送信されています。\n";
    $auto_reply_body .= "※お心当たりのない方は、お手数ですが削除をお願いいたします。\n\n";
    $auto_reply_body .= "────────────────────\n";
    $auto_reply_body .= "株式会社GMT Core\n";
    $auto_reply_body .= "〒151-0051\n";
    $auto_reply_body .= "東京都渋谷区千駄ケ谷5丁目15-6 マークス北参道\n";
    $auto_reply_body .= "────────────────────\n";
    
    $auto_reply_headers = "From: " . $to . "\r\n";
    $auto_reply_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // 自動返信を送信
    mail($email, $auto_reply_subject, $auto_reply_body, $auto_reply_headers);
    
    // 成功レスポンス
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'お問い合わせを受け付けました。ご連絡ありがとうございます。'
    ]);
} else {
    // 送信失敗
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'メールの送信に失敗しました。お手数ですが、後ほど再度お試しください。'
    ]);
}
?>
