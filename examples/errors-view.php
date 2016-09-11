<?php

// where's the log directory?
$log_directory = __DIR__ . '/error-logs';

// get fatal errors
$fatal_errors = check_logs($log_directory . '/fatal.txt');

// get background errors
$background_errors = check_logs($log_directory . '/background.txt');

/**
 * Checks for a file at submitted path and extracts log details (each line in the file is JSON encoded data)
 * @param $path string The path to the log file
 * @return array Log details
 */
function check_logs($path) {
	// See http://php.net/manual/en/errorfunc.constants.php for error descriptions
	$error_list = [
		'1' => 'E_ERROR',
		'2' => 'E_WARNING',
		'4' => 'E_PARSE',
		'8' => 'E_NOTICE',
		'16' => 'E_CORE_ERROR',
		'32' => 'E_CORE_WARNING',
		'64' => 'E_COMPILE_ERROR',
		'128' => 'E_COMPILE_WARNING',
		'256' => 'E_USER_ERROR',
		'512' => 'E_USER_WARNING',
		'1024' => 'E_USER_NOTICE',
		'4096' => 'E_RECOVERABLE_ERROR',
		'8192' => 'E_DEPRECATED',
		'16384' => 'E_USER_DEPRECATED'
	];
	$log = [];
	if (file_exists($path)) {
		$string = file_get_contents($path);
		if ($string != '') {
			// each log is on its own line
			$json_errors = explode("\n", trim($string));
			if (count($json_errors)) {
				foreach($json_errors as $json_error) {
					$details = json_decode($json_error, true);
					// add error name to second position in each array
					foreach ($details['log'] as &$error) {
						$error_id = $error['error'];
						$new_start = [
							'error' => $error_id,
							'error_name' => $error_list[$error_id]
						];
						array_shift($error);
						$error = $new_start + $error;
					}
					unset($error);
					$log[] = $details;
				}
			}
		}
	}
	return $log;
}

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Error Logs</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">

	<h1>Error logs</h1>

	<div class="alert alert-info">
		Each row represents a single run time which may contain more than one error.
	</div>

	<h3>Fatal errors <small>(<?= count($fatal_errors) ?>)</small></h3>

	<?php if (count($fatal_errors)): ?>

		<table class="table table-hover">
			<tbody>
			<?php foreach ($fatal_errors as $id => $error): ?>
				<tr>
					<?php $final_error = end($error['log']); // get final error details ?>
					<td><?= date('Y-m-d H:i:s', $error['timestamp']) ?></td>
					<td><?= $final_error['error_name'] ?></td>
					<td><?= count($error['log']) ?> total errors</td>
					<td class="text-right"><a class="btn btn-default btn-xs" data-toggle="fatal-<?= $id ?>">details</a></td>
				</tr>
				<tr style="display:none;" id="fatal-<?= $id ?>">
					<td colspan="4">
						<pre><?php print_r($error) ?></pre>
					</td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>

	<?php endif ?>

	<hr />

	<h3>Background errors <small>(<?= count($background_errors) ?>)</small></h3>

	<?php if (count($background_errors)): ?>

		<table class="table table-hover">
			<tbody>
				<?php foreach ($background_errors as $id => $error): ?>
					<tr>
						<td><?= date('Y-m-d H:i:s', $error['timestamp']) ?></td>
						<td><?= count($error['log']) ?> total errors</td>
						<td class="text-right"><a class="btn btn-default btn-xs" data-toggle="background-<?= $id ?>">details</a></td>
					</tr>
					<tr style="display:none;" id="background-<?= $id ?>">
						<td colspan="3">
							<pre><?php print_r($error) ?></pre>
						</td>
					</tr>
				<?php endforeach ?>
			</tbody>
		</table>

	<?php endif ?>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>
$(function() {
	$("[data-toggle]").click(function(){
		var element_id = $( this ).attr("data-toggle");
		$("#"+element_id).toggle();
	});
});
</script>
</body>
</html>
