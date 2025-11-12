<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../forgot_password.php');
    exit();
}

$user_id = $_POST['user_id'] ?? null;
$a1 = trim($_POST['security_a1'] ?? '');
$a1_re = trim($_POST['security_a1_re'] ?? '');
$a2 = trim($_POST['security_a2'] ?? '');
$a2_re = trim($_POST['security_a2_re'] ?? '');
$a3 = trim($_POST['security_a3'] ?? '');
$a3_re = trim($_POST['security_a3_re'] ?? '');

if (empty($user_id) || (empty($a1) && empty($a2) && empty($a3))) {
    $_SESSION['errors'] = ['form' => 'Please answer all security questions.'];
    header('Location: ../forgot_password.php');
    exit();
}

// Check if answers match re-enter answers
if ($a1 !== $a1_re || $a2 !== $a2_re || $a3 !== $a3_re) {
    $_SESSION['errors'] = ['form' => 'Your answers and re-enter answers do not match.'];
    header('Location: ../forgot_password.php');
    exit();
}

$stmt = $pdo->prepare('SELECT security_a1_hash, security_a2_hash, security_a3_hash FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['errors'] = ['form' => 'User not found. Please start over.'];
    header('Location: ../forgot_password.php');
    exit();
}

// Check all three answers
$answer1_correct = !empty($a1) && password_verify($a1, $user['security_a1_hash']);
$answer2_correct = !empty($a2) && password_verify($a2, $user['security_a2_hash']);
$answer3_correct = !empty($a3) && password_verify($a3, $user['security_a3_hash']);

$answer_correct = $answer1_correct && $answer2_correct && $answer3_correct;


if ($answer_correct) {
    // Success! Authorize password reset and redirect.
    $_SESSION['reset_authorized_for_user_id'] = $user_id;
    header('Location: ../change_password.php');
    exit();
} else {
    $_SESSION['errors'] = ['form' => 'One or more of the provided answers were incorrect. Please try again.'];
    // Store username in session to preserve the form state
    $stmt = $pdo->prepare('SELECT username FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $userData = $stmt->fetch();
    if ($userData) {
        $_SESSION['forgot_password_username'] = $userData['username'];
    }
    header('Location: ../forgot_password.php');
    exit();
}

?>
