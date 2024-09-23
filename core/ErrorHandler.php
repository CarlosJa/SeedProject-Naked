<?php

class ErrorHandler {
    public static function handleError($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno)) {
            // This error code is not included in error_reporting
            return;
        }

        self::logError($errno, $errstr, $errfile, $errline);
        self::displayError($errno, $errstr, $errfile, $errline);

        // Don't execute PHP internal error handler
        return true;
    }

    public static function handleException($exception) {
        self::logError(E_ERROR, $exception->getMessage(), $exception->getFile(), $exception->getLine());
        self::displayError(E_ERROR, $exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString());
    }

    public static function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::logError($error['type'], $error['message'], $error['file'], $error['line']);
            self::displayError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    private static function logError($errno, $errstr, $errfile, $errline) {
        $message = date('[Y-m-d H:i:s]') . " Error: [$errno] $errstr in $errfile on line $errline\n";
        error_log($message, 3, 'app_errors.log');
    }

    private static function displayError($errno, $errstr, $errfile, $errline, $trace = null) {
        $errorTypes = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Standards',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];

        $errorType = isset($errorTypes[$errno]) ? $errorTypes[$errno] : 'Unknown Error';

        if (DEBUG) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "<h1 style='color: #721c24;'>$errorType Occurred</h1>";
            echo "<p><strong>Message:</strong> $errstr</p>";
            echo "<p><strong>File:</strong> $errfile</p>";
            echo "<p><strong>Line:</strong> $errline</p>";
            if ($trace) {
                echo "<h2>Stack Trace:</h2>";
                echo "<pre>$trace</pre>";
            }
            echo "<h2>Request Details:</h2>";
            echo "<pre>";
            echo "URL: " . $_SERVER['REQUEST_URI'] . "\n";
            echo "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
            echo "Time: " . date('Y-m-d H:i:s') . "\n";
            echo "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
            echo "</pre>";
            echo "</div>";
        }
    }
}
