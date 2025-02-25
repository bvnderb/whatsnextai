<?php
class TaskManager {
    private $conn;
    private $table_name = "tasks";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createTask($userId, $data) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO tasks 
                (user_id, title, description, mood_level, estimated_time, deadline, priority, category_id, is_recurring)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $userId,
                $data['title'],
                $data['description'],
                $data['mood_level'],
                $data['estimated_time'],
                $data['deadline'],
                $data['priority'] ?? 1,
                $data['category_id'] ?? null,
                $data['is_recurring'] ?? false
            ]);

            $taskId = $this->conn->lastInsertId();

            // Handle recurring task settings if applicable
            if ($data['is_recurring'] && isset($data['recurring'])) {
                $this->createRecurringTask($taskId, $data['recurring']);
            }

            return ["success" => true, "task_id" => $taskId];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Failed to create task: " . $e->getMessage()];
        }
    }

    public function selectTask($userId, $currentMood, $availableTime) {
        try {
            // Start with strict mood level matching
            $moodLevels = $this->getMoodLevels($currentMood);
            
            $stmt = $this->conn->prepare("
                SELECT * FROM tasks 
                WHERE user_id = ?
                AND status = 'pending'
                AND mood_level IN (" . implode(',', $moodLevels) . ")
                AND estimated_time <= ?
                ORDER BY 
                    CASE 
                        WHEN deadline IS NOT NULL 
                        THEN DATEDIFF(deadline, CURRENT_DATE)
                        ELSE 999999
                    END ASC,
                    priority DESC
                LIMIT 1
            ");

            $stmt->execute([$userId, $availableTime]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$task) {
                return ["success" => false, "message" => "You have no tasks. Go do something fun instead!"];
            }

            return ["success" => true, "task" => $task];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Failed to select task: " . $e->getMessage()];
        }
    }

    private function getMoodLevels($currentMood) {
        // If mood is high (4-5), can do tasks from lower moods
        if ($currentMood >= 4) {
            return range(1, $currentMood);
        }
        // If mood is low (1-2), only show tasks for that mood level
        else if ($currentMood <= 2) {
            return [$currentMood];
        }
        // For mood 3, show tasks from 1-3
        else {
            return range(1, 3);
        }
    }

    private function createRecurringTask($taskId, $recurringData) {
        $stmt = $this->conn->prepare("
            INSERT INTO recurring_tasks (task_id, interval_type, interval_value)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $taskId,
            $recurringData['interval_type'],
            $recurringData['interval_value']
        ]);
    }
}
?>