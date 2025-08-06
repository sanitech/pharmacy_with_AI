<?php
require_once '../classes/Auth.php';
require_once '../classes/AIService.php';

$auth = new Auth();
$auth->requireRole('pharmacist');

$user = $auth->getCurrentUser();
$ai_service = new AIService();

$error = '';
$success = '';
$suggestions = null;

if ($_POST && isset($_POST['symptoms'])) {
    $symptoms = trim($_POST['symptoms']);
    if (!empty($symptoms)) {
        $result = $ai_service->getDrugSuggestions($symptoms);
        if ($result['success']) {
            $suggestions = $result['suggestions'];
        } else {
            $error = $result['message'] ?? 'Failed to get drug suggestions.';
        }
    } else {
        $error = 'Please enter symptoms.';
    }
}

// Set page title for header
$page_title = 'AI Drug Suggestion';
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-robot me-2"></i>AI Drug Suggestion</h2>
            <div class="text-muted">Get AI-powered drug recommendations</div>
        </div>

        <!-- Alerts -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Input Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-search me-2"></i>Enter Symptoms</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="symptoms" class="form-label">Describe the symptoms:</label>
                        <textarea class="form-control" id="symptoms" name="symptoms" rows="4" placeholder="Enter symptoms like: headache, fever, cough, etc." required><?php echo htmlspecialchars($_POST['symptoms'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-robot me-2"></i>Get AI Suggestions
                    </button>
                </form>
            </div>
        </div>

        <!-- AI Suggestions -->
        <?php if ($suggestions): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>AI Drug Suggestions</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> These are AI-generated suggestions. Always verify with medical guidelines and consult with healthcare professionals when necessary.
                    </div>

                    <div class="row">
                        <?php foreach ($suggestions as $index => $suggestion): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">Suggestion #<?php echo $index + 1; ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <h6><?php echo htmlspecialchars($suggestion['drug_name'] ?? 'Drug Name'); ?></h6>
                                        <p class="text-muted"><?php echo htmlspecialchars($suggestion['description'] ?? 'No description available'); ?></p>

                                        <?php if (isset($suggestion['dosage'])): ?>
                                            <p><strong>Dosage:</strong> <?php echo htmlspecialchars($suggestion['dosage']); ?></p>
                                        <?php endif; ?>

                                        <?php if (isset($suggestion['side_effects'])): ?>
                                            <p><strong>Side Effects:</strong> <?php echo htmlspecialchars($suggestion['side_effects']); ?></p>
                                        <?php endif; ?>

                                        <?php if (isset($suggestion['precautions'])): ?>
                                            <p><strong>Precautions:</strong> <?php echo htmlspecialchars($suggestion['precautions']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Help Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>How to Use</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Tips for Better Results:</h6>
                        <ul>
                            <li>Be specific about symptoms</li>
                            <li>Include severity (mild, moderate, severe)</li>
                            <li>Mention duration of symptoms</li>
                            <li>Include any existing conditions</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Example Queries:</h6>
                        <ul>
                            <li>"Severe headache for 2 days"</li>
                            <li>"Mild fever and cough"</li>
                            <li>"Stomach pain after eating"</li>
                            <li>"Joint pain and swelling"</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>