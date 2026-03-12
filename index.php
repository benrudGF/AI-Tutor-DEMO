<?php
$title = "Home";
ob_start();
?>
<h1 class="mt-5">Welcome to My App</h1>
<p>This is the home page.</p>
<?php
$content = ob_get_clean();
include 'layout.php';
?>
