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
                <h3 class="text-center">AI Tutor</h3>
            </div>
            <div class="card-body">
                <form id="promptForm">
                    <div class="mb-3">
                        <label for="tutorSelect" class="form-label">Choose your tutor</label>
                        <select class="form-select" id="tutorSelect" name="tutor">
                            <option value="general">General Tutor</option>
                            <option value="socratic">Socratic Guide</option>
                            <option value="math">Math Tutor</option>
                            <option value="science">Science Tutor</option>
                            <option value="writing">Writing Coach</option>
                            <option value="coding">Coding Mentor</option>
                            <option value="history">History Tutor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="userMessage" class="form-label">Ask a question</label>
                        <textarea class="form-control" id="userMessage" name="message" rows="3" placeholder="Type your question here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">Send</button>
                </form>
                <div id="responseArea" class="mt-4" style="display:none;">
                    <h5>Response:</h5>
                    <div id="responseContent" class="p-3 bg-light rounded border" style="white-space: pre-wrap;"></div>
                </div>
                <div id="errorArea" class="mt-4" style="display:none;">
                    <div class="alert alert-danger" id="errorContent"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('promptForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const message = document.getElementById('userMessage').value.trim();
    if (!message) return;

    const tutor = document.getElementById('tutorSelect').value;
    const submitBtn = document.getElementById('submitBtn');
    const responseArea = document.getElementById('responseArea');
    const responseContent = document.getElementById('responseContent');
    const errorArea = document.getElementById('errorArea');
    const errorContent = document.getElementById('errorContent');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    responseArea.style.display = 'none';
    errorArea.style.display = 'none';

    fetch('api_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: message, tutor: tutor })
    })
    .then(function(res) { return res.json().then(function(data) { return { ok: res.ok, data: data }; }); })
    .then(function(result) {
        if (result.ok && result.data.reply) {
            responseContent.textContent = result.data.reply;
            responseArea.style.display = 'block';
        } else {
            errorContent.textContent = result.data.error || 'An unexpected error occurred.';
            errorArea.style.display = 'block';
        }
    })
    .catch(function(err) {
        errorContent.textContent = 'Network error: ' + err.message;
        errorArea.style.display = 'block';
    })
    .finally(function() {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send';
    });
});
</script>
<?php
$content = ob_get_clean();
include 'layout.php';
?>
