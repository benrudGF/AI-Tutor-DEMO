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
                        <label for="gradeRange" class="form-label">Grade Range</label>
                        <select class="form-select" id="gradeRange" name="gradeRange" required>
                            <option value="" selected disabled>Select a grade range</option>
                            <option value="kindergarden">Kindergarden School</option>
                            <option value="grade">Grade School</option>
                            <option value="middle">Middle School</option>
                            <option value="high">High School</option>
                            <option value="college">College</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <select class="form-select" id="subject" name="subject" required>
                            <option value="" selected disabled>Select a subject</option>
                            <option value="english">English</option>
                            <option value="math">Math</option>
                            <option value="history">History</option>
                            <option value="science">Science</option>
                            <option value="business">Business</option>
                            <option value="technology">Technology</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="voice" class="form-label">Voice</label>
                        <select class="form-select" id="voice" name="voice" required>
                            <option value="" selected disabled>Select a voice</option>
                            <option value="Mickey Mouse">Mickey Mouse</option>
                            <option value="Superman">Superman</option>
                            <option value="Darth Vader">Darth Vader</option>
                            <option value="Yoda">Yoda</option>
                            <option value="SpongeBob SquarePants">SpongeBob SquarePants</option>
                            <option value="Morgan Freeman">Morgan Freeman</option>
                            <option value="Albert Einstein">Albert Einstein</option>
                            <option value="Shakespeare">Shakespeare</option>
                            <option value="Elmo">Elmo</option>
                            <option value="Dumbledore">Dumbledore</option>
                            <option value="Siri">Siri</option>
                            <option value="Batman">Batman</option>
                            <option value="Tinkerbell">Tinkerbell</option>
                            <option value="Stephen Hawking">Stephen Hawking</option>
                            <option value="Mr. Rogers">Mr. Rogers</option>
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

    const gradeRange = document.getElementById('gradeRange').value.trim();
    const subject = document.getElementById('subject').value.trim();
    const message = document.getElementById('userMessage').value.trim();
    if (!gradeRange || !subject || !message) return;

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
        body: JSON.stringify({
            gradeRange: gradeRange,
            subject: subject,
            message: message
        })
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
