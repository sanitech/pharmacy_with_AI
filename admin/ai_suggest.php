<?php
require_once '../classes/Auth.php';
require_once '../classes/AIService.php';

$auth = new Auth();
$auth->requireRole('admin');

$user = $auth->getCurrentUser();
$ai_service = new AIService();

$error = '';
$ai_suggestions = '';
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'ai_recommend') {
    $symptoms = $_POST['symptoms'] ?? '';
    if (!empty($symptoms)) {
        $result = $ai_service->getSuggestions($symptoms, $user['id']);
        if ($result['success']) {
            $ai_suggestions = $result['suggestions'];
        } else {
            $error = $result['message'] ?? 'Failed to get AI recommendations';
        }
    } else {
        $error = 'Please enter symptoms';
    }
}

// Set page title for header
$page_title = 'AI Drug Suggestion';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Mobile Sidebar Toggle -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Main Content -->
<div class="main-content">
    <div class="container mt-5">
        <h2 class="mb-4">AI Drug Suggestion (Admin)</h2>
        <form method="POST" class="mb-4">
            <input type="hidden" name="action" value="ai_recommend">
            <div class="mb-3">
                <label for="symptoms" class="form-label">Enter symptoms or case description:</label>
                <textarea name="symptoms" id="symptoms" class="form-control" rows="3" placeholder="e.g. headache, fever, cough..."><?php echo isset($_POST['symptoms']) ? htmlspecialchars($_POST['symptoms']) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Get AI Suggestions</button>
        </form>
        <?php if ($ai_suggestions): ?>
            <div class="alert alert-info">
                <h5>AI Suggestions:</h5>
                <pre style="white-space: pre-wrap;"><?php echo htmlspecialchars($ai_suggestions); ?></pre>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle for mobile
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    });
    document.getElementById('sidebarOverlay').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.remove('show');
        this.classList.remove('show');
    });
</script>
</body>

</html>