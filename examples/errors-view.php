<?php

// where's the log directory?
$log_directory = __DIR__.'/logs/';

// how should the dates be displayed? (null for default)
$date_format = null;


// get fatal errors
$fatal_errors = check_logs($log_directory.'error-fatal.txt');

// get background errors
$background_errors = check_logs($log_directory.'error-background.txt');

/**
 * Checks for a file at submitted path and extracts log details
 * @param $path string The path to the log file
 * @return array Log details
 */
function check_logs($path) {
	$log = [];
	if (file_exists($path)) {
		$string = file_get_contents($path);
		if ($string != '') {
			// each log is on its own line
			$json_errors = explode("\n", $string);
			// remove empty line at the end
			array_pop($json_errors);
			if (count($json_errors)) {
				foreach($json_errors as $json_error) {
					$log[] = json_decode($json_error, true);
				}
			}
		}
	}
	return $log;
}

// output HTML
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

					<td><?= ($date_format === null) ? $error['date'] : date(($date_format), $error['unix']) ?></td>
					<td><?= $final_error['error_name']?></td>
					<td><?= $final_error['message'] ?></td>
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
					<td><?= ($date_format === null) ? $error['date'] : date(($date_format), $error['unix']) ?></td>
					<td><?= count($error['log']) ?> errors</td>
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
