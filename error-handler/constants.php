<?php


/**
 * The directory error logs will be place in (include trailing "/").
 */
define('ERROR_HANDLER_LOG_DIRECTORY', substr(__DIR__, 0, -14).'/examples/logs/');


/**
 * Unix timestamp to set against any logged errors.
 */
define('ERROR_HANDLER_UNIX', time());


/**
 * http address to redirect to if a fatal error occurs.
 */
define('ERROR_HANDLER_REDIRECT', false);


/**
 * Plain text message to echo if ERROR_HANDLER_REDIRECT is not set.
 * If ERROR_HANDLER_MESSAGE is false, fatal errors will be echo'd.
 */
define('ERROR_HANDLER_MESSAGE', false);
