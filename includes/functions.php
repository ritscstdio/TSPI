/**
 * Get the current logged in admin user's information
 * 
 * @return array|null The admin user data or null if not logged in
 */
function get_current_admin_user() {
    global $pdo;
    
    if (!isset($_SESSION['admin_id'])) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM administrators WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
} 