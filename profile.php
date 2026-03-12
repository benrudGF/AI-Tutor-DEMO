<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
		header("Location: login.php");
		exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Profile</title>
		<!-- Bootstrap CSS -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
		<div class="container mt-5">
				<div class="row justify-content-center">
						<div class="col-md-6">
								<div class="card">
										<div class="card-header">
												<h3 class="text-center">Profile</h3>
										</div>
										<div class="card-body">
												<p><strong>Username:</strong> <?php echo $user['username']; ?></p>
												<p><strong>Email:</strong> <?php echo $user['email']; ?></p>
												<a href="logout.php" class="btn btn-danger w-100">Logout</a>
										</div>
								</div>
						</div>
				</div>
		</div>

		<!-- Bootstrap JS Bundle with Popper -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
