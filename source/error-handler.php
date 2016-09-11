<?php

// =====================
//   PHP ERROR HANDLER
// =====================
//
// v1.0.2
//
// Logs all PHP errors to a directory of your choice into two files - fatal.txt and background.txt.
//
// Set the constant values below, then include this file as early as possible in your script.
//
// You may also retrieve any errors during run time with error_handler_clear_log(). This function
// will return an array of all errors with details - these errors will not be logged. This is useful
// for debugging, you could for example call error_handler_clear_log() at the end of your script and
// echo the array.


// ====================
//   DEFINE CONSTANTS
// ====================
//
// ERROR_HANDLER_LOG_DIRECTORY
//
// The directory to place logs into (no trailing '/') - make sure you have write permissions within this directory
//
define('ERROR_HANDLER_LOG_DIRECTORY', substr(__DIR__, 0, -6) . 'examples/error-logs');
//
//
// ERROR_HANDLER_FATAL_ACTION
//
// Determines how fatal errors are dealt with -
//  - if false, error details will be echo'd and not logged (useful for development),
//  - if string begins 'http' script will redirect to URL (if redirect includes this file and script that produces a fatal error, an infinite loop will occur),
//  - else the constant value will be echo'd.
//
define('ERROR_HANDLER_FATAL_ACTION', false);
//
//
// ERROR_HANDLER_TIMESTAMP
//
// A string added to each error log so you know when the error(s) occurred.
//
define('ERROR_HANDLER_TIMESTAMP', time());
//
//
// ERROR_HANDLER_CONTINUE
//
// Any errors encountered that are not listed here will be treated as fatal, script will not continue to run.
//
define('ERROR_HANDLER_CONTINUE', E_WARNING | E_CORE_WARNING | E_NOTICE | E_COMPILE_WARNING | E_USER_WARNING | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED);
//
//


// do not display errors
error_reporting(0);

// register error handling functions
set_error_handler('error_handler');
register_shutdown_function('error_handler_check_for_fatal');
register_shutdown_function('error_handler');

// check for write permissions to log files
if (!is_writable(ERROR_HANDLER_LOG_DIRECTORY . '/background.txt') || !is_writable(ERROR_HANDLER_LOG_DIRECTORY . '/fatal.txt')) exit('Error handler cannot write to log files or log files do not exist.');


/**
 * Registered to set_error_handler() and register_shutdown_function()
 * @param $err_no
 * @param $err_str
 * @param $err_file
 * @param $err_line
 * @return null|array depending on use
 */
function error_handler($err_no = null, $err_str = null, $err_file = null, $err_line = null)
{
	static $log = array();

	if ($err_no === null) {
		// being called as a shutdown function, if there are any logged errors pass them through to error_handler_final()
		if (count($log)) {
			error_handler_final(false);
		}
		return null;
	}
	else if ($err_no == 'clear_log') {
		// being called by error_handler_clear_log()
		$return = $log;
		$log = array();
		return $return;
	}

	// collect all error data and add to log
	$log[] = array(
		'error'      => $err_no,
		'message'    => $err_str,
		'file'       => $err_file,
		'line'       => $err_line,
		'backtrace'  => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
	);

	if (0 == ($err_no & ERROR_HANDLER_CONTINUE)) {
		// do not allow script to continue if error is not listed in ERROR_HANDLER_CONTINUE
		error_handler_final(true);
	}

	return null;
}

/**
 * Registered to register_shutdown_function()
 */
function error_handler_check_for_fatal()
{
	// get the last error
	$error = error_get_last();

	// the following error types bypass the error handler...
	$core_fatal = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);

	// ...so pass their details to error_handler()
	if ($error !== NULL && in_array($error['type'], $core_fatal, true)) {
		error_handler($error['type'], $error['message'], $error['file'], $error['line']);
	}
}

/**
 * Final handling of errors.
 * Logs errors (retrieved with error_handler_clear_log()) and initiates fatal actions (if necessary).
 * @param $fatal
 */
function error_handler_final($fatal)
{
	if ($fatal && ERROR_HANDLER_FATAL_ACTION == false) {
		echo '<hr>';
		echo '<h1>PHP Fatal Error</h1>';
		echo '<hr>';
		echo '<pre>';
		print_r(error_handler_clear_log());
		exit;
	}

	// log the error
	$log_file = ERROR_HANDLER_LOG_DIRECTORY . '/' . ($fatal ? 'fatal.txt' : 'background.txt');

	$data = array(
		'timestamp' => ERROR_HANDLER_TIMESTAMP,
		'log' => error_handler_clear_log()
	);

	$data = json_encode($data) . "\n";

	file_put_contents($log_file, $data, FILE_APPEND | LOCK_EX);

	if ($fatal) {
		if (substr(ERROR_HANDLER_FATAL_ACTION, 0, 4) == 'http') {
			header('Location: ' . ERROR_HANDLER_FATAL_ACTION);
		}
		else {
			echo ERROR_HANDLER_FATAL_ACTION;
		}
	}
	exit;
}

/**
 * Returns the log array, then clears it
 * @return array Any logged errors
 */
function error_handler_clear_log()
{
	return error_handler('clear_log');
}
