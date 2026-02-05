<?php
/**
 * PetPal - Registration Page
 * User registration with validation
 */

require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL);
}

$errors = [];
$formData = [
    'username' => '',
    'email' => '',
    'full_name' => ''
];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $formData['username'] = trim($_POST['username'] ?? '');
        $formData['email'] = trim($_POST['email'] ?? '');
        $formData['full_name'] = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($formData['username'])) {
            $errors[] = 'Username is required.';
        } elseif (strlen($formData['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $formData['username'])) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }

        if (empty($formData['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (empty($formData['full_name'])) {
            $errors[] = 'Full name is required.';
        }

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (!isset($_POST['terms'])) {
            $errors[] = 'You must agree to the terms and conditions.';
        }

        // Check if username or email already exists
        if (empty($errors)) {
            try {
                $conn = getDBConnection();

                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$formData['username']]);
                if ($stmt->fetch()) {
                    $errors[] = 'Username is already taken.';
                }

                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$formData['email']]);
                if ($stmt->fetch()) {
                    $errors[] = 'Email is already registered.';
                }
            } catch (PDOException $e) {
                $errors[] = 'An error occurred. Please try again.';
            }
        }

        // Create account
        if (empty($errors)) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $formData['username'],
                    $formData['email'],
                    $hashedPassword,
                    $formData['full_name']
                ]);

                setFlash('success', 'Account created successfully! Please log in.');
                redirect(SITE_URL . '/pages/login.php');

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
    <meta name="description" content="Create your PetPal account">
    <title>Sign Up -
        <?php echo SITE_NAME; ?>
    </title>
    <?php include '../includes/head.php'; ?>
</head>

<body>
    <?php include '../includes/navbar.php'; ?>

    <main class="auth-page">
        <div class="auth-card" style="max-width: 500px;">
            <div class="auth-logo">
                <a href="<?php echo SITE_URL; ?>">
                    <span class="logo-icon">🐾</span>
                    <span>
                        <?php echo SITE_NAME; ?>
                    </span>
                </a>
            </div>

            <div class="auth-title">
                <h2>Create Account</h2>
                <p>Join the PetPal community</p>
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

            <form method="POST" action="" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo h($csrfToken); ?>">

                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control"
                        placeholder="Enter your full name" value="<?php echo h($formData['full_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                        placeholder="Choose a username" value="<?php echo h($formData['username']); ?>" required>
                    <span class="form-hint">Letters, numbers, and underscores only</span>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email"
                        value="<?php echo h($formData['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Create a password" required minlength="6">
                    <span class="form-hint">At least 6 characters</span>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                        placeholder="Confirm your password" required>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="terms" required>
                        <span>I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#"
                                class="text-primary">Privacy Policy</a></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="<?php echo SITE_URL; ?>/pages/login.php">Sign in</a></p>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        // Password match validation
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>

</html>