<?php


/**
 * The directory to place logs into (no trailing '/').
 */
define('ERROR_HANDLER_LOG_DIRECTORY', substr(__DIR__, 0, -7).'/examples/error-logs');


/**
 * Timestamp to set against any logged errors.
 */
define('ERROR_HANDLER_TIMESTAMP', time());


/**
 * Error codes that will not stop script
 */
define('ERROR_HANDLER_CONTINUE', E_WARNING | E_CORE_WARNING | E_NOTICE | E_COMPILE_WARNING | E_USER_WARNING | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED);


/**
 * How to deal with fatal errors.
 * If false, error details will be echo'd (development),
 * if string begins 'http' script will redirect to URL, (if re-directing to same domain, a static page will avoid loops if error is triggered during core framework processing)
 * else the constant value will be echo'd.
 */
define('ERROR_HANDLER_FATAL_ACTION', false);


// do not display errors
error_reporting(0);

// register error handling functions
set_error_handler('error_handler');
register_shutdown_function('error_handler_check_for_fatal');
register_shutdown_function('error_handler');


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
