<?php
$title = "Prompt";
require 'functions.php';
checkLoggedIn();
require 'db.php';

ob_start();
?>
<div class="row justify-content-center mt-5">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Prompt</h3>
            </div>
            <div class="card-body">
                <p>This is the prompt page. Here you can interact with the AI Tutor.</p>
                <!-- Add your prompt form or content here -->
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include 'layout.php';
?>
