<?php
session_start();
require_once "config.php";

require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';
require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/GoogleAuthenticator.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($pdo, $email, $code) {
    // Fetch Gmail settings from DB
    $stmt = $pdo->query("SELECT email, password, from_name FROM email_settings LIMIT 1");
    $sender = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$sender) {
        echo "<div style='color:red;'>No email settings found.</div>";
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        // Production: No screen debug
        $mail->SMTPDebug = 0;
        // For troubleshooting only:
        // $mail->SMTPDebug = 2;
        // $mail->Debugoutput = function($str, $level) { error_log("PHPMailer debug [$level]: $str"); };

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $sender['email'];
        $mail->Password = $sender['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($sender['email'], $sender['from_name']);
        $mail->addAddress($email); // User email from argument
        $mail->Subject = 'Your Login Verification Code';
        $mail->Body    = "Your verification code is: $code";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "<div style='color:red;'>Mailer Error: {$mail->ErrorInfo}</div>";
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Check main login
    if (isset($_POST['email']) && isset($_POST['password']) && !isset($_POST['email_code'])) {
        $email = $_POST['email'];
        $password = trim($_POST['password']);
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['2fa_user'] = $user;
            $code = random_int(100000, 999999);
            $_SESSION['email_code'] = $code;
            $_SESSION['email_code_time'] = time();
            if (!sendVerificationEmail($pdo, $email, $code)) {
                $error = "Could not send verification email. Please check SMTP settings and debug output.";
            } else {
                $_SESSION['awaiting_email_code'] = true;
            }
        } else {
            $error = "Invalid email or password!";
        }

    // Step 2: Verify email code (timeout: 90 seconds)
    } elseif (isset($_POST['email_code'])) {
        if (!isset($_SESSION['2fa_user']) || !isset($_SESSION['email_code'])) {
            $error = "Session expired. Please login again!";
        } elseif ($_POST['email_code'] == $_SESSION['email_code'] && time() - $_SESSION['email_code_time'] < 90) {
            $_SESSION['awaiting_email_code'] = false;
            $_SESSION['awaiting_ga_code'] = true;
        } else {
            $error = "Invalid or expired email verification code!";
            unset(
                $_SESSION['2fa_user'],
                $_SESSION['email_code'],
                $_SESSION['email_code_time'],
                $_SESSION['awaiting_email_code'],
                $_SESSION['awaiting_ga_code'],
                $_SESSION['ga_code_time']
            );
        }

    // Step 3: Verify Google Authenticator code
    } elseif (isset($_POST['ga_code'])) {
        if (!isset($_SESSION['2fa_user'])) {
            $error = "Session expired. Please login again!";
        } else {
            $user = $_SESSION['2fa_user'];
            $ga = new PHPGangsta_GoogleAuthenticator();
            $secret = $user['ga_secret'];
            if ($ga->verifyCode($secret, $_POST['ga_code'], 2)) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $pos = trim($user['position']); // trim to avoid hidden spaces
                unset(
                    $_SESSION['2fa_user'],
                    $_SESSION['email_code'],
                    $_SESSION['email_code_time'],
                    $_SESSION['awaiting_email_code'],
                    $_SESSION['awaiting_ga_code'],
                    $_SESSION['ga_code_time']
                );
                switch ($pos) {
                    case 'HR Manager':
                        header("Location: hr.php");
                        break;
                    case 'Finance':
                        header("Location: finance.php");
                        break;
                    case 'Project Manager':
                        header("Location: project_manager.php");
                        break;
                    case 'Administrator':
                        header("Location: admin.php");
                        break;
                    case 'Time Keeper': // matches DB value exactly
                        header("Location: timekeeper.php");
                        break;
                    default:
                        header("Location: hr.php");
                        break;
                }
                exit;
            } else {
                $error = "Invalid Google Authenticator code!";
            }
        }
    }
}

$show_email_code = isset($_SESSION['awaiting_email_code']) && $_SESSION['awaiting_email_code'];
$show_ga_code = isset($_SESSION['awaiting_ga_code']) && $_SESSION['awaiting_ga_code'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Rocelyn RJ Building Trades Inc</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { background: #040f2a; display: flex; justify-content: center; align-items: center; height: 100vh; position: relative; }
    .back-home { position: absolute; top: 20px; left: 20px; color: #fff; background: #2d5996ff;}
    .login-box { background: #fff; border-radius: 12px; padding: 2rem; box-shadow: 0px 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 420px; }
    .btn-green { background: #c69046ff; color: #fff; font-weight: bold; }
    .btn-green:hover { background: #edededff; }
    .form-control:focus { border-color: #510400; box-shadow: 0 0 0 0.2rem rgba(26,159,63,0.25); }
    .logo { display: flex; justify-content: center; margin-bottom: 1rem; }
    .logo img { max-width: 200px; border-radius: 10px; }
  </style>
</head> 
<body>

<div class="login-box">
  <div class="logo"><img src="../Capstone pics/LOGO4.jpg" alt="Company logo"></div>
  <h3 class="text-center">iRis Solution</h3>
  <p class="text-center text-muted">Log-in nalang</p>
  <?php if (!empty($error)) { ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php } ?>

  <?php if ($show_email_code): ?>
    <?php
      $timeoutSeconds = 90;
      $startTime = isset($_SESSION['email_code_time']) ? $_SESSION['email_code_time'] : time();
      $remaining = $timeoutSeconds - (time() - $startTime);
      if ($remaining < 0) $remaining = 0;
    ?>
    <form id="codeForm" method="POST" action="">
      <div class="mb-3">
        <label class="form-label">Check your email for the verification code</label>
        <input type="text" name="email_code" class="form-control" placeholder="Enter email verification code" required autofocus>
      </div>
      <div class="mb-3">
        <span id="timer" style="color:#c00;font-size:1.1em"></span>
      </div>
      <button type="submit" class="btn btn-green w-100">Verify Code</button>
    </form>
    <script>
      let remaining = <?php echo $remaining; ?>;
      let timerBox = document.getElementById('timer');
      let intvl = setInterval(function() {
        if (remaining <= 0) {
          clearInterval(intvl);
          window.location.href = "login.php?expired=1";
        } else {
          let min = Math.floor(remaining / 60);
          let sec = remaining % 60;
          timerBox.textContent = "Time remaining: " + min + ":" + (sec<10?"0":"") + sec;
        }
        remaining--;
      }, 1000);
    </script>
  <?php elseif ($show_ga_code): ?>
  <?php
    $user = $_SESSION['2fa_user'];
    $twoFATimeout = 60;
    if (!isset($_SESSION['ga_code_time'])) {
      $_SESSION['ga_code_time'] = time();
    }
    $remaining = $twoFATimeout - (time() - $_SESSION['ga_code_time']);
    if ($remaining < 0) $remaining = 0;

    $ga = new PHPGangsta_GoogleAuthenticator();
    // Always show QR, even if secret is already set
    if (!empty($user['ga_secret'])) {
      $secret = $user['ga_secret'];
    } else {
      $secret = $ga->createSecret();
      $stmt = $pdo->prepare("UPDATE users SET ga_secret = ? WHERE id = ?");
      $stmt->execute([$secret, $user['id']]);
      $user['ga_secret'] = $secret;
      $_SESSION['2fa_user']['ga_secret'] = $secret;
    }
    $label = 'RocelynRJ-' . $user['email'];
    $qrUrl = $ga->getQRCodeGoogleUrl($label, $secret, 'RocelynRJ');
    echo "<div style='text-align:center;margin-bottom:10px;'>
            <b>Scan with Google Authenticator:</b><br>
            <img src='" . htmlspecialchars($qrUrl) . "' alt='QR Code' style='margin:10px;'>
            <br><small>Scan this QR with your app. Use the code it shows below.</small>
          </div>";
  ?>
  <form id="gaForm" method="POST" action="">
    <div class="mb-3">
      <label class="form-label">Google Authenticator Code</label>
      <input type="text" name="ga_code" class="form-control" placeholder="Enter Google Authenticator code" required autofocus>
    </div>
    <div class="mb-3">
      <span id="ga_timer" style="color:#c00;font-size:1.1em"></span>
    </div>
    <button type="submit" class="btn btn-green w-100">Verify 2FA</button>
  </form>
  <script>
    let gaRemaining = <?php echo $remaining; ?>;
    let gaTimerBox = document.getElementById('ga_timer');
    let gaIntvl = setInterval(function() {
      if (gaRemaining <= 0) {
        clearInterval(gaIntvl);
        window.location.href = "login.php?expired=2";
      } else {
        let min = Math.floor(gaRemaining / 60);
        let sec = gaRemaining % 60;
        gaTimerBox.textContent = "Time remaining: " + min + ":" + (sec<10?"0":"") + sec;
      }
      gaRemaining--;
    }, 1000);
  </script>
  <?php else: ?>
    <form method="POST" action="">
      <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-green w-100">Sign In</button>
    </form>
  <?php endif; ?>
</div>
</body>
</html>
