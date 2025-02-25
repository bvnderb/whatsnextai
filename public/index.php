<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";
require_once "../includes/tasks.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$taskManager = new TaskManager($db);
$message = '';
$selectedTask = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'select_task':
                $result = $taskManager->selectTask(
                    $_SESSION['user_id'],
                    $_POST['mood_level'],
                    $_POST['available_time']
                );
                if ($result['success']) {
                    $selectedTask = $result['task'];
                } else {
                    $message = $result['message'];
                }
                break;
            
            case 'create_task':
                $result = $taskManager->createTask($_SESSION['user_id'], $_POST);
                $message = $result['message'];
                break;

            case 'logout':
                session_destroy();
                header("Location: login.php");
                exit();
                break;
        }
    }
}

// Get user's tasks
$tasks = $taskManager->getUserTasks($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procrastinator's Task Manager</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Procrastinator's Task Manager</h1>
            <div class="user-info">
                Welcome, <?= htmlspecialchars($_SESSION['first_name']) ?>!
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="main-content">
            <!-- Task Selection Form -->
            <section class="task-selector">
                <h2>How are you feeling today?</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="select_task">
                    
                    <div class="mood-selector">
                        <label>Current Mood:</label>
                        <div class="mood-buttons">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="mood_level" value="<?= $i ?>" id="mood<?= $i ?>" required>
                                <label for="mood<?= $i ?>"><?= str_repeat('😊', $i) ?></label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="time-selector">
                        <label for="available_time">Available Time (minutes):</label>
                        <input type="number" name="available_time" id="available_time" 
                               min="5" max="480" value="30" required>
                    </div>

                    <button type="submit">Find Me a Task</button>
                </form>
            </section>

            <!-- Selected Task Display -->
            <?php if ($selectedTask): ?>
                <section class="selected-task">
                    <h2>Your Task</h2>
                    <div class="task-card">
                        <h3><?= htmlspecialchars($selectedTask['title']) ?></h3>
                        <p><?= htmlspecialchars($selectedTask['description'] ?? '') ?></p>
                        <div class="task-details">
                            <span>Estimated Time: <?= $selectedTask['estimated_time'] ?> minutes</span>
                            <?php if ($selectedTask['deadline']): ?>
                                <span>Deadline: <?= date('M d, Y', strtotime($selectedTask['deadline'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Create New Task Button -->
            <section class="create-task">
                <button onclick="showTaskForm()">Create New Task</button>
            </section>

            <!-- Task List -->
            <?php if (!empty($tasks)): ?>
                <section class="task-list">
                    <h2>Your Tasks</h2>
                    <?php foreach ($tasks as $task): ?>
                        <div class="task-card">
                            <h3><?= htmlspecialchars($task['title']) ?></h3>
                            <div class="task-details">
                                <span>Mood Level: <?= $task['mood_level'] ?></span>
                                <span>Time: <?= $task['estimated_time'] ?> minutes</span>
                                <?php if ($task['deadline']): ?>
                                    <span>Due: <?= date('M d, Y', strtotime($task['deadline'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>