<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$email = $_POST['email'];
		$password = $_POST['password'];

		$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
		$stmt->execute([$email]);
		$user = $stmt->fetch();

		if ($user && password_verify($password, $user['password_hash'])) {
				$_SESSION['user_id'] = $user['id'];
				echo "Login successful!";
		} else {
				echo "Invalid email or password.";
		}
}
?>

<!-- Simple HTML Form -->
<form method="POST">
		<input type="email" name="email" placeholder="Email" required>
		<input type="password" name="password" placeholder="Password" required>
		<button type="submit">Login</button>
</form>
