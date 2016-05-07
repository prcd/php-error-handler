<?php

/*
 * Uncomment or comment the code below and run this file to generate different errors.
 * All errors will be logged in the logs folder and fatal errors will be echo'd.
 * Custom actions for fatal errors can be set in error-handler.php.
 */ 


// Include error handling scripts.
require __DIR__ . '/../error-handler/constants.php';
require __DIR__ . '/../error-handler/functions.php';


/*
 * E_NOTICE
 * Undefined variable: undefined
 */
echo $undefined;


/*
 * E_WARNING
 * Division by zero
 */
$x = 10 / 0;


/*
 * E_USER_NOTICE
 */
trigger_error('A user notice error, this will be logged but the script will continue to run', E_USER_NOTICE);


/*
 * E_COMPILE_ERROR
 * require(): Failed opening required...
 */
//require 'file/not/there.php';


/*
 * E_USER_ERROR
 */
//trigger_error('A user error that will cause the script to stop', E_USER_ERROR);


echo 'We made it to the end of the script. Check the logs folder for any logged errors or use view-errors.php to see them.';
