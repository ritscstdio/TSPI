<?php
require_once '../includes/config.php';
require_login();
require_role(['admin','editor','comment_moderator']);

// Get parameters
$comment_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle different comment actions
if ($comment_id > 0 && in_array($action, ['approve', 'deny', 'hide', 'delete'])) {
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
        $stmt->execute([$comment_id]);
        $_SESSION['message'] = "Comment approved successfully.";
    } elseif ($action === 'deny') {
        $stmt = $pdo->prepare("UPDATE comments SET status = 'spam' WHERE id = ?");
        $stmt->execute([$comment_id]);
        $_SESSION['message'] = "Comment marked as spam.";
    } elseif ($action === 'hide') {
        $stmt = $pdo->prepare("UPDATE comments SET status = 'pending' WHERE id = ?");
        $stmt->execute([$comment_id]);
        $_SESSION['message'] = "Comment hidden and set to pending.";
    } else {
        // delete comment permanently
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        $_SESSION['message'] = "Comment deleted successfully.";
    }
}

// Redirect back to comments list
redirect('/admin/comments.php'); 