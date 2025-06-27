<?php

// Path to the history log file
define('HISTORY_FILE', 'history.txt');

// Function to add a log entry (appends to the file)
function addHistory($message) {
    $date_time = date("m-d-Y H:i:s");
    $log_entry = "[$date_time] $message" . PHP_EOL;
    file_put_contents(HISTORY_FILE, $log_entry, FILE_APPEND);
}

// Function to clear the history file
function clearHistory() {
    file_put_contents(HISTORY_FILE, '');
}

// Function to display the history (read from the file)
function displayHistory() {
    if (!file_exists(HISTORY_FILE) || filesize(HISTORY_FILE) === 0) {
        echo "<p>No history available.</p>";
    } else {
        $logs = array_reverse(file(HISTORY_FILE, FILE_IGNORE_NEW_LINES));
        echo '<ul style="list-style: none; padding: 0;">';
        foreach ($logs as $log) {
            echo '<li>🔹 ' . htmlspecialchars($log) . '</li>';
        }
        echo '</ul>';
    }
}

?>
