<?php
session_start();
require_once('core.php');
require_once('filebrowser.php');

// Init the core helper
$core = new Core();

// get the root path
$rootPath = $core->getCache('rootPath');
$rootPath = $rootPath ?? $core->getDocumentRoot();

// get the filters
$fileTypes = $core->getCache('fileTypes');

/*
 * Add your filebrowser definition code here
 */
$fileBrowser = new FileBrowser($rootPath);

// Set the filter
$fileBrowser->SetExtensionFilter(explode(',', $fileTypes));

// Get the url params
$action = $core->getUrlParameters();

switch ($action) {

	case 'setconfig':

		// Cache the post
		if($core->postvalue('rootPath')) {
			$core->setCache('rootPath', $core->postvalue('rootPath'));
		}

		$fileTypes = $core->postvalue('fileTypes');
		$fileTypes = explode(',', $fileTypes);
		$fileTypes = array_filter($fileTypes);
		$fileTypes = array_unique($fileTypes);
		$fileTypes = array_map(function($a) {
			return trim($a);
		}, $fileTypes);

		$core->setCache('fileTypes', implode(', ', $fileTypes));

		// Notify that the configuration is changes
		$core->setFlashMessage('Configuration updated!', 'SUCCESS');

		// Redirect if all good
		$core->redirect('/backend/');

	break;

	case 'navigate':

		// Get the passed value
		$path = $core->getUrlParameters(2, '/', true);
		$fileBrowser->SetCurrentPath($path);

	break;

}

// Get the files of current path browsed
$files = $fileBrowser->Get();

// Collect any errors
if($fileBrowser->hasErrors())
	$core->setFlashMessage($fileBrowser->getErrors());

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>File browser</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  	<div class="container">

		<!-- Main component for a primary marketing message or call to action -->
		<div class="row">
			<h1>Configs</h1><hr/>
			<form class="form-horizontal" method="POST" action="/backend/setconfig">
				<div class="form-group">
					<label class="col-sm-2 control-label">Root Path</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="rootPath" value="<?= $rootPath ?>">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Extension Filter</label>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="fileTypes" value="<?= $fileTypes ?>" placeholder="Comma separated extensions..">
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" class="btn btn-default">Apply Config</button>
					</div>
				</div>
			</form>
			<hr/>
		</div>

		<div class="row">

			<?= $core->getFlashMessage() ?>

			<h2>Output</h2><hr/>
		  	<!-- Output file list HTML here -->
		  	<div class="col-md-4">
		  		<table class="table table-condensed table-striped">
					<tr>
						<th>File</th>
						<th>Type</th>
						<th>Size</th>
						<th>Last Modified</th>
					</tr>

					<?php foreach($files as $file): ?>

			  			<tr>
			  				<td><a class="name" href="/backend/navigate/<?= $file['path'] ?>"><?= $file['name'] ?></a></td>
			  				<td><?= $file['type'] ?></td>
			  				<td><?= $file['size'] ?></td>
			  				<td><?= $file['modified'] ?></td>
			  			</tr>

			  		<?php endforeach; ?>
				</table>
		  	</div>
		  	<div class="col-md-8" style="background: #f9f2f4; overflow: scroll;">
		  		<?php
		  		if($fileBrowser->isFileRequest()){
					$fileBrowser->getFileContent();
				}
				?>
		  	</div>

		</div>

    </div> <!-- /container -->


    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  </body>
</html>