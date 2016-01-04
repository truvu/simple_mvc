<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta httpequiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $title; ?></title>
	<?php $this->asset->css('account')->js('index', true); ?>
</head>
<body>
	<div class="container" id="header" async>
		<div class="text-center">Simple MVC - WebSocket - DomJS</div>
	</div>
	<div class="container" id="content" async>
		<ul>
			<li>
				<a href="/demo/chat">Chat</a>
			</li>
		</ul>
	</div>
	<div id="footer" async></div>
</body>
</html>