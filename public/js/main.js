document.addEventListener('DOMContentLoaded', function() {
    // Task form visibility toggle
    window.showTaskForm = function() {
        // Check if form already exists
        if (document.querySelector('.task-form-modal')) {
            return;
        }

        // Create modal with task form
        const modal = document.createElement('div');
        modal.className = 'task-form-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h2>Create New Task</h2>
                <form method="POST" action="" class="create-task-form">
                    <input type="hidden" name="action" value="create_task">
                    
                    <div class="form-group">
                        <label for="title">Task Title*</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="mood_level">Required Mood Level*</label>
                        <select id="mood_level" name="mood_level" required>
                            <option value="1">1 - Very Low Energy</option>
                            <option value="2">2 - Low Energy</option>
                            <option value="3">3 - Normal Energy</option>
                            <option value="4">4 - High Energy</option>
                            <option value="5">5 - Very High Energy</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estimated_time">Estimated Time (minutes)*</label>
                        <input type="number" id="estimated_time" name="estimated_time" 
                               min="5" max="480" required>
                    </div>

                    <div class="form-group">
                        <label for="deadline">Deadline</label>
                        <input type="datetime-local" id="deadline" name="deadline">
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="is_recurring" name="is_recurring">
                            Recurring Task
                        </label>
                    </div>

                    <div id="recurring_options" class="form-group hidden">
                        <label for="interval_type">Repeat Every</label>
                        <select id="interval_type" name="recurring[interval_type]">
                            <option value="daily">Day</option>
                            <option value="weekly">Week</option>
                            <option value="monthly">Month</option>
                        </select>
                    </div>

                    <button type="submit">Create Task</button>
                </form>
            </div>
        `;

        // Add modal to page
        document.body.appendChild(modal);

        // Handle modal close
        const closeButton = modal.querySelector('.close-button');
        closeButton.onclick = function() {
            modal.remove();
        };

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.remove();
            }
        };

        // Toggle recurring options
        const recurringCheckbox = modal.querySelector('#is_recurring');
        const recurringOptions = modal.querySelector('#recurring_options');
        recurringCheckbox.onchange = function() {
            recurringOptions.classList.toggle('hidden', !this.checked);
        };
    };

    // Time input validation
    const timeInput = document.querySelector('#available_time');
    if (timeInput) {
        timeInput.addEventListener('input', function(e) {
            // Remove any non-numeric characters
            let value = this.value.replace(/[^0-9]/g, '');
            
            // Convert to number
            value = parseInt(value) || 0;
            
            // Clamp between 5 and 480
            if (value < 5) value = 5;
            if (value > 480) value = 480;
            
            // Update input value
            this.value = value;
        });

        // Prevent non-numeric keys
        timeInput.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
            }
        });

        // Handle paste events
        timeInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            if (/^\d+$/.test(pastedText)) {
                let value = parseInt(pastedText);
                if (value < 5) value = 5;
                if (value > 480) value = 480;
                this.value = value;
            }
        });
    }

    // Form validation
    const taskForms = document.querySelectorAll('form');
    taskForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    });
});