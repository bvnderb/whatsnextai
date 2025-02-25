<?php
class TaskManager {
    private $conn;
    private $table_name = "tasks";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createTask($userId, $data) {
        try {
            // Convert checkbox 'on' value to boolean
            $isRecurring = isset($data['is_recurring']) ? 1 : 0;

            $stmt = $this->conn->prepare("
                INSERT INTO tasks 
                (user_id, title, description, mood_level, estimated_time, deadline, is_recurring)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $userId,
                $data['title'],
                $data['description'] ?? null,
                $data['mood_level'],
                $data['estimated_time'],
                $data['deadline'] ?? null,
                $isRecurring
            ]);

            $taskId = $this->conn->lastInsertId();

            if ($isRecurring && isset($data['recurring'])) {
                $this->createRecurringTask($taskId, $data['recurring']);
            }

            return ["success" => true, "message" => "Task created successfully"];
        } catch (Exception $e) {
            return ["success" => false, "message" => "Failed to create task: " . $e->getMessage()];
        }
    }

    public function selectTask($userId, $currentMood, $availableTime) {
        try {
            // Get appropriate mood levels based on current mood
            $moodLevels = $this->getMoodLevels($currentMood);
            
            $placeholders = str_repeat('?,', count($moodLevels) - 1) . '?';
            
            $sql = "
                SELECT * FROM tasks 
                WHERE user_id = ? 
                AND status = 'pending'
                AND mood_level IN ($placeholders)
                AND estimated_time <= ?
                ORDER BY 
                    CASE 
                        WHEN deadline IS NOT NULL 
                        THEN DATEDIFF(deadline, CURRENT_DATE)
                        ELSE 999999
                    END ASC,
                    priority DESC
                LIMIT 1
            ";

            $params = array_merge([$userId], $moodLevels, [$availableTime]);
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
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

    public function getUserTasks($userId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM tasks 
                WHERE user_id = ? AND status = 'pending'
                ORDER BY 
                    CASE 
                        WHEN deadline IS NOT NULL 
                        THEN DATEDIFF(deadline, CURRENT_DATE)
                        ELSE 999999
                    END ASC
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>