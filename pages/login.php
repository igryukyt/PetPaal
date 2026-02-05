<?php
/**
 * PetPal - Login Page
 * User authentication with session management
 */

require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL);
}

$errors = [];
$username = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($username)) {
            $errors[] = 'Username is required.';
        }
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }

        if (empty($errors)) {
            try {
                $conn = getDBConnection();
                $stmt = $conn->prepare("SELECT id, username, password, full_name FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
                    redirect(SITE_URL);
                } else {
                    $errors[] = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                $errors[] = 'An error occurred. Please try again.';
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to your PetPal account">
    <title>Login -
        <?php echo SITE_NAME; ?>
    </title>
    <?php include '../includes/head.php'; ?>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <main class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <a href="<?php echo SITE_URL; ?>">
                    <span class="logo-icon">🐾</span>
                    <span>
                        <?php echo SITE_NAME; ?>
                    </span>
                </a>
            </div>

            <div class="auth-title">
                <h2>Welcome Back!</h2>
                <p>Sign in to your account</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <p style="margin: 0;">
                                <?php echo h($error); ?>
                            </p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php $flash = getFlash();
            if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
                    <?php echo h($flash['message']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">

                <div class="form-group">
                    <label for="username" class="form-label">Username or Email</label>
                    <input type="text" id="username" name="username" class="form-control"
                        placeholder="Enter your username or email" value="<?php echo h($username); ?>" required
                        autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Enter your password" required>
                </div>

                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                    <label class="form-check">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="text-primary" style="font-size: 0.9rem;">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/pages/register.php">Sign up</a></p>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields.');
            }
        });
    </script>
</body>

</html>