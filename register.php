<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$username = $_POST['username'];
		$email = $_POST['email'];
		$password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

		try {
				$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
				$stmt->execute([$username, $email, $password]);
				echo "Registration successful!";
		} catch (PDOException $e) {
				die("Registration failed: " . $e->getMessage());
		}
}
?>

<!-- Simple HTML Form -->
<form method="POST">
		<input type="text" name="username" placeholder="Username" required>
		<input type="email" name="email" placeholder="Email" required>
		<input type="password" name="password" placeholder="Password" required>
		<button type="submit">Register</button>
</form>
