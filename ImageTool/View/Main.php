<!DOCTYPE html>
<html>
<head>
	<title>ImageTool</title>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="CSS/main.css" />
</head>
<body>
	<form id="ImageToolBox" class="ImageToolBox">
		<p class="message"></p>
		<div class="file">
			<p>Select image to upload:</p>
			<input type="file" name="image" />
		</div>
		<div class="preview"></div>
		<div class="done">
			<p class="doneMessage"></p>
			<p class="doneUrl"></p>
		</div>
		<p class="uploading">Uploading...</p>
		<div class="actions">
			<button name="cancel">Cancel</button>
			<button name="resize">Resize</button>
			<button name="crop">Crop</button>
			<button name="upload">Upload</button>
		</div>
		<div class="resizeAction">
			<input name="width" type="text" /> x
			<input name="height" type="text" />
			<button name="resize">Resize</button>
			<button name="cancel">Cancel</button>
		</div>
		<div class="cropAction">
			<p>Select area to crop</p>
			<button name="crop">Crop</button>
			<button name="cancel">Cancel</button>
		</div>
	</form>
	<script src="JS/ImageTool.js"></script>
	<script>
		new ImageTool("ImageToolBox",
			"index.php?action=Upload",
			function(url) {
				console.log(url);
			});
	</script>
</body>
</html>