<?php
$title = "Profile";
require 'functions.php';
checkLoggedIn();
require 'db.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

ob_start();
?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Profile</h3>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="profile-img d-inline-block mb-2" style="width: 80px; height: 80px; font-size: 40px;"><?php echo substr($user['username'], 0, 1); ?></div>
                    <h4><?php echo $user['username']; ?></h4>
                    <p class="text-muted"><?php echo $user['email']; ?></p>
                </div>
                <a href="logout.php" class="btn btn-danger w-100">Logout</a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include 'layout.php';
?>
