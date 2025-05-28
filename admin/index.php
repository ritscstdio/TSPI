<?php
$page_title = "Admin Dashboard";
$body_class = "admin-dashboard-page";
require_once '../includes/config.php';
require_admin_login();

// Get stats
$stmt = $pdo->query("SELECT 
                    (SELECT COUNT(*) FROM content) as total_contents,
                    (SELECT COUNT(*) FROM content WHERE status = 'draft') as draft_contents,
                    (SELECT COUNT(*) FROM comments WHERE status = 'pending') as pending_comments,
                    (SELECT COUNT(*) FROM members_information WHERE status = 'pending') as pending_applications");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Recent contents - Keep for later section
$stmt = $pdo->query("SELECT a.*, u.name as author_name, u.email as author_email, u.role as author_role 
                      FROM content a 
                      JOIN administrators u ON a.author_id = u.id 
                      ORDER BY a.published_at DESC
                      LIMIT 5");
$recent_contents = $stmt->fetchAll();

// Recent comments - Keep for later section
$stmt = $pdo->query("SELECT c.*, a.title as content_title, a.slug as content_slug 
                      FROM comments c 
                      JOIN content a ON c.content_id = a.id 
                      ORDER BY c.posted_at DESC 
                      LIMIT 5");
$recent_comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - TSPI CMS</title>
    <link rel="icon" type="image/png" href="../src/assets/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        :root {
            --primary-color: #0070f3;
            --primary-light: #e3f2fd;
            --primary-dark: #005bc1;
            --accent-color: #00c853;
            --danger-color: #f44336;
            --warning-color: #ffc107;
            --gray-100: #f8f9fa;
            --gray-200: #e9ecef;
            --gray-300: #dee2e6;
            --gray-400: #ced4da;
            --gray-500: #adb5bd;
            --gray-600: #6c757d;
            --gray-700: #495057;
            --gray-800: #343a40;
            --gray-900: #212529;
            --border-radius: 8px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        }

        /* Fix spacing issue in dashboard */
        .dashboard-container {
            padding: 20px; /* Add consistent padding */
        }
        
        /* Page header styling to match content.php */
        .page-header {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gray-300);
        }
        
        .page-header h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        /* Make stat boxes more responsive */
        .stat-boxes {
            display: flex;
            flex-wrap: nowrap;
            gap: 20px;
            margin-bottom: 30px;
            width: 100%;
            overflow-x: auto;
            padding-bottom: 10px; /* Add padding for scrollbar */
        }
        
        .stat-box {
            flex: 1;
            min-width: 250px;
            height: 100%;
            min-height: 130px;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }
        
        .stat-box:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        /* Custom scrollbar for stat boxes */
        .stat-boxes::-webkit-scrollbar {
            height: 6px;
        }
        
        .stat-boxes::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .stat-boxes::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }
        
        .stat-boxes::-webkit-scrollbar-thumb:hover {
            background: #aaa;
        }
        
        /* Additional style to fix the header spacing issue */
        .admin-main {
            padding-top: 0 !important;
        }
        
        /* Dashboard section styling */
        .dashboard-section {
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
            padding: 1.5rem;
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }
        
        .dashboard-section:hover {
            box-shadow: var(--shadow-md);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-300);
        }
        
        .section-header h2 {
            margin: 0;
            font-size: 1.4rem;
        }
        
        /* Hide scrollbar on larger screens */
        @media (min-width: 1200px) {
            .stat-boxes {
                flex-wrap: wrap;
                overflow-x: visible;
            }
        }
        
        /* Improve table responsiveness */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        @media (max-width: 768px) {
            table {
                min-width: 650px; /* Ensure table is wide enough for scrolling */
            }
            
            .dashboard-sections {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body class="<?php echo $body_class; ?>">
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="dashboard-container">
                <div class="page-header">
                    <h1>Dashboard</h1>
                </div>
                
                <?php if ($message = get_flash_message()): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <div class="stat-boxes">
                    <div class="stat-box" data-link="content.php">
                        <div class="stat-content">
                            <i class="fas fa-file-alt stat-icon"></i>
                            <h3>Total Content</h3>
                            <p class="stat-number"><?php echo $stats['total_contents']; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-box" data-link="content.php?status_filter=draft">
                        <div class="stat-content">
                            <i class="fas fa-pencil-alt stat-icon"></i>
                            <h3>Draft Content</h3>
                            <p class="stat-number"><?php echo $stats['draft_contents']; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-box" data-link="comments.php?status_filter=pending">
                        <div class="stat-content">
                            <i class="fas fa-comment-dots stat-icon"></i>
                            <h3>Pending Comments</h3>
                            <p class="stat-number"><?php echo $stats['pending_comments']; ?></p>
                        </div>
                    </div>

                    <div class="stat-box" data-link="applications.php">
                        <div class="stat-content">
                            <i class="fas fa-file-contract stat-icon"></i>
                            <h3>Membership Applications</h3>
                            <p class="stat-number"><?php echo $stats['pending_applications']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-sections">
                    <div class="dashboard-section recent-contents">
                        <div class="section-header">
                            <h2>Recent Content</h2>
                            <a href="content.php" class="view-all">View All</a>
                        </div>
                        
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_contents)): ?>
                                        <tr>
                                            <td colspan="5">No contents found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_contents as $content): ?>
                                            <tr>
                                                <td><?php echo sanitize($content['title']); ?></td>
                                                <td class="content-author-name" 
                                                    data-name="<?php echo sanitize($content['author_name']); ?>" 
                                                    data-email="<?php echo sanitize($content['author_email'] ?? 'N/A'); ?>" 
                                                    data-role="<?php echo sanitize(ucfirst($content['author_role'] ?? 'N/A')); ?>"
                                                    style="cursor: pointer; text-decoration: underline; color: var(--primary-blue);">
                                                    <?php echo sanitize($content['author_name']); ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $content['status']; ?>">
                                                        <?php echo ucfirst($content['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($content['published_at'])); ?></td>
                                                <td class="actions">
                                                    <a href="edit-content.php?id=<?php echo $content['id']; ?>" class="btn-icon" title="Edit"><i class="fas fa-edit"></i></a>
                                                    <a href="../content.php?slug=<?php echo $content['slug']; ?>" target="_blank" class="btn-icon" title="View"><i class="fas fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
