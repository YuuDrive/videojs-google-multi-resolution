<!doctype html>
<html>
<head>
	<meta charset="utf-8" />
  	<meta name="robots" content="noindex"/>
	<meta name="googlebot" content="noindex"/>
  	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0"/>
	<meta property='og:description' content="Streaming - <?= $file['name']; ?>"/>
	<title><?= $file['name']; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.statically.io/gh/yuudrivedev/yuudrive-cdn/master/videojs/css/video-js.min.css" rel="stylesheet" />
	<link href="https://cdn.statically.io/gh/yuudrivedev/yuudrive-cdn/master/videojs/css/videojs-resolution-switcher.css" rel="stylesheet" />
	<!-- VIDEOJS -->
	<script src="https://cdn.statically.io/gh/yuudrivedev/yuudrive-cdn/master/videojs/js/video.js"></script>
	<script src="https://cdn.statically.io/gh/yuudrivedev/yuudrive-cdn/master/videojs/js/videojs-resolution-switcher.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
	<!-- / -->
	<style type="text/css">
		*{margin:0;padding:0}
		#yuudrive-player{position:absolute;width:100%!important;height:100%!important}
	</style>
</head>

<body>
<video id="yuudrive-player" class="video video-js vjs-default-skin"
	controls
	preload
	muted>
<?php foreach ($resolutions as $key => $value) : ?>
    <source src="<?= $base_url; ?>/stream/<?= $id; ?>?res=<?= $key; ?>" type='video/mp4' label='<?= $value; ?>' res='<?= $key; ?>' />
<?php endforeach; ?>
</video>
<script>
videojs('yuudrive-player').videoJsResolutionSwitcher()
</script>
</body>