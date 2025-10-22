<?php
/**
 * Language/Locale Management Functions
 */

if (!defined('LANGUAGE_FUNCTIONS_LOADED')) {
    define('LANGUAGE_FUNCTIONS_LOADED', true);

// Get current language setting
function get_current_language() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if language is set in session
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }

    // Check database settings
    include __DIR__ . "/connection.php";
    $stmt = $connection->prepare("SELECT language FROM settings WHERE id=1");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $settings = $result->fetch_assoc();
            $language = $settings['language'] ?? 'id';
            $_SESSION['language'] = $language;
            $stmt->close();
            return $language;
        }
        $stmt->close();
    }

    return 'id'; // Default to Indonesian
}

// Set language
function set_language($language) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $valid_languages = ['id', 'en'];
    if (!in_array($language, $valid_languages)) {
        $language = 'id';
    }

    $_SESSION['language'] = $language;

    // Update database
    include __DIR__ . "/connection.php";
    $stmt = $connection->prepare("UPDATE settings SET language=? WHERE id=1");
    if ($stmt) {
        $stmt->bind_param("s", $language);
        $stmt->execute();
        $stmt->close();
    }

    return $language;
}

// Translation function
function __($key, $language = null) {
    static $translations = null;

    if ($language === null) {
        $language = get_current_language();
    }

    // Load translations if not loaded
    if ($translations === null) {
        $json_file = __DIR__ . '/translations.json';
        if (file_exists($json_file)) {
            $translations = json_decode(file_get_contents($json_file), true);
        } else {
            // Fallback to empty array if file not found
            $translations = [];
        }
    }

    // Return translation or original key if not found
    return $translations[$language][$key] ?? $key;
}
}
?>
