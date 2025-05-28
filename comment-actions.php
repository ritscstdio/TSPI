<?php
require_once 'includes/config.php';

// Ensure user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to perform this action.']);
    exit;
}

$user = get_logged_in_user();

// Handle different comment actions
$action = $_GET['action'] ?? '';
$comment_id = isset($_GET['comment_id']) ? (int)$_GET['comment_id'] : 0;

if (!$comment_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid comment ID.']);
    exit;
}

// Get comment information
$stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (!$comment) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Comment not found.']);
    exit;
}

// Only allow users to delete their own comments, or admins/moderators to delete any comment
$can_delete = ($comment['user_id'] == $user['id']) || 
              (in_array($user['role'], ['admin', 'moderator', 'comment_moderator']));

if ($action === 'delete') {
    if (!$can_delete) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this comment.']);
        exit;
    }

    try {
        // Delete comment
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        
        // Also delete any replies to this comment
        $stmt = $pdo->prepare("DELETE FROM comments WHERE parent_id = ?");
        $stmt->execute([$comment_id]);
        
        // Remove any votes for this comment
        $stmt = $pdo->prepare("DELETE FROM comment_votes WHERE comment_id = ?");
        $stmt->execute([$comment_id]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Comment deleted successfully.']);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error deleting comment: ' . $e->getMessage()]);
    }
    exit;
}

// If no valid action provided
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid action.']);
exit; 