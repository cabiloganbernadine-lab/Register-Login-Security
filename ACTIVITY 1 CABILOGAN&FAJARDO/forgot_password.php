<?php
session_start();
require_once __DIR__ . '/Php/db.php';

$errors = [];
$stage = 1; // Stage 1: Enter username. Stage 2: Answer questions.
$user = null;
$username = '';

// Check if we need to preserve stage 2 state after error
if (isset($_SESSION['forgot_password_username'])) {
    $username = $_SESSION['forgot_password_username'];
    $stmt = $pdo->prepare('SELECT id, username, security_q1, security_q2, security_q3 FROM users WHERE username = :username OR id_number = :username');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();
    if ($user) {
        $stage = 2;
        unset($_SESSION['forgot_password_username']); // Clear after use
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'])) {
        // --- Stage 1 Submission ---
        $username = trim($_POST['username']);
        if (empty($username)) {
            $errors['username'] = 'Please enter your username or ID number.';
        } else {
            $stmt = $pdo->prepare('SELECT id, username, security_q1, security_q2, security_q3 FROM users WHERE username = :username OR id_number = :username');
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user) {
                $stage = 2; // User found, proceed to stage 2
            } else {
                $errors['username'] = 'User not found.';
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LiquorLink - Forgot Password</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .error-text { color: #e74c3c; font-size: 0.9em; margin-top: 4px; display: block; }
    </style>
</head>
<body>
    <div class="container full-width">
        <!-- Prospect Name Above Header -->
        <div class="prospect-name">LiquorLink - Bar Management System</div>
        
        <header class="elegant-header">
            <div class="logo"><h1>LiquorLink</h1></div>
            <nav><ul><li><a href="index.html">HOME</a></li></ul></nav>
            <div class="header-buttons">
                <a href="index.html" class="btn text-btn">Home</a>
                <a href="login.php" class="btn text-btn">Log-in</a>
            </div>
        </header>

        <main>
            <section class="auth-section">
                <div class="auth-container">
                    <h2 class="auth-title">Account Recovery</h2>

                    <?php if ($stage === 1): ?>
                        <p>Please enter your username or ID number to begin the recovery process.</p>
                        <form id="forgot-password-form-1" class="elegant-form" action="forgot_password.php" method="post">
                            <div class="form-section">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="username">Username or ID Number</label>
                                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($username); ?>">
                                        <?php if(isset($errors['username'])) { echo '<div class="error-text">' . htmlspecialchars($errors['username']) . '</div>'; } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn primary-btn">Find Account</button>
                            </div>
                        </form>
                    <?php else: // Stage 2 
                        // Get full question text from value
                        $q1_text = $user['security_q1'];
                        $q2_text = $user['security_q2'];
                        $q3_text = $user['security_q3'];
                        
                        $question_map = [
                            'best_friend_elementary' => 'Who is your best friend in Elementary?',
                            'favorite_pet_name' => 'What is the name of your favorite pet?',
                            'favorite_teacher_hs' => 'Who is your favorite teacher in high school?',
                            'first_crush_name' => 'What is the name of your first crush?',
                            'mother_maiden_name' => "What is your mother's maiden name?",
                            'city_of_birth' => 'In what city were you born?',
                            'first_car_model' => 'What was the model of your first car?',
                            'childhood_nickname' => 'What was your childhood nickname?',
                            'favorite_book' => 'What is your favorite book?',
                            'paternal_grandmother_name' => "What is your paternal grandmother's first name?",
                            'first_concert' => 'What was the first concert you attended?',
                            'dream_job' => 'What was your dream job as a child?'
                        ];
                        
                        $q1_display = isset($question_map[$q1_text]) ? $question_map[$q1_text] : $q1_text;
                        $q2_display = isset($question_map[$q2_text]) ? $question_map[$q2_text] : $q2_text;
                        $q3_display = isset($question_map[$q3_text]) ? $question_map[$q3_text] : $q3_text;
                    ?>
                        <p>Please answer the following security questions for user <strong><?php echo htmlspecialchars($user['username']); ?></strong>.</p>
                        <?php 
                        if (isset($_SESSION['errors']['form'])) {
                            echo '<div class="error-text" style="margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['errors']['form']) . '</div>';
                            unset($_SESSION['errors']['form']);
                        }
                        ?>
                        <form id="forgot-password-form-2" class="elegant-form" action="Php/reset_password.php" method="post">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                            <div class="form-section">
                                <div class="form-group">
                                    <label for="security_a1">Choose the Following Question: <?php echo htmlspecialchars($q1_display); ?></label>
                                    <label for="security_a1_ans">Your Answer:</label>
                                    <input type="text" id="security_a1_ans" name="security_a1" required>
                                    <label for="security_a1_re">Re-enter answer:</label>
                                    <input type="text" id="security_a1_re" name="security_a1_re" required>
                                </div>
                                <div class="form-group">
                                    <label for="security_a2">Choose the Following Question: <?php echo htmlspecialchars($q2_display); ?></label>
                                    <label for="security_a2_ans">Your Answer:</label>
                                    <input type="text" id="security_a2_ans" name="security_a2" required>
                                    <label for="security_a2_re">Re-enter answer:</label>
                                    <input type="text" id="security_a2_re" name="security_a2_re" required>
                                </div>
                                <div class="form-group">
                                    <label for="security_a3">Choose the Following Question: <?php echo htmlspecialchars($q3_display); ?></label>
                                    <label for="security_a3_ans">Your Answer:</label>
                                    <input type="text" id="security_a3_ans" name="security_a3" required>
                                    <label for="security_a3_re">Re-enter answer:</label>
                                    <input type="text" id="security_a3_re" name="security_a3_re" required>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn primary-btn">Verify Answers</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2023 LiquorLink Bar Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
