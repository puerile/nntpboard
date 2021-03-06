<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />

		<script src="libs/jquery-1.8.2.min.js"></script>
		<script src="libs/bootstrap-2.1.1/js/bootstrap.min.js"></script>
		<script src="libs/stylesheet.js"></script>

		<link href="libs/bootstrap-2.1.1/css/bootstrap.min.css" rel="stylesheet" />
		<link href="libs/font-awesome/css/font-awesome.css" rel="stylesheet" />
		<link href="classes/template/smarty/style.css" rel="stylesheet" />
		<link href="css/additional.css" rel="stylesheet" />
		<link href="css/jupis.css" rel="alternate stylesheet" title="JuPi-Theme" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		{literal}
		<script type="text/javascript">
		$(function() {
			$("a.btn").click(function (event) {
				if ($(this).hasClass("disabled")) {
					return false;
				}

				$(this).addClass("disabled");
			});

			$("form").submit(function (event) {
				if ($(this).hasClass("disabled")) {
					return false;
				}

				$(this).addClass("disabled").find(".btn").addClass("disabled");
			});
		});
		</script>
		{/literal}

		<!--[if lt IE 9]>
			<script src="libs/ie-html5.js"></script>
		<![endif]-->

		<title>{$title|escape:html} – NNTPBoard</title>
	</head>

	<body>
		<div class="visible-desktop spacer-top">&nbsp;</div>

		<div class="navbar navbar-fixed-top navbar-inverse">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="brand" href="index.php">
						<span>NNTP</span>Board
					</a>
					<ul class="nav">
						<li class="active hidden-phone"><a href="index.php">Forenübersicht</a></li>
					</ul>

					{if $ISANONYMOUS}
						<a href="login.php" class="btn btn-success pull-right hidden-desktop"><i class="icon-home icon-white"></i> Anmelden</a>
						<form class="navbar-form pull-right form-inline visible-desktop" action="login.php" method="POST">
							<input type="hidden" name="login" value="1" />
							<input type="text" name="username" class="span2" placeholder="Loginname" />
							<input type="password" name="password" class="span2" placeholder="Passwort" />
							<button type="submit" class="btn btn-primary">Anmelden</button>
						</form>
					{else}
						<a href="logout.php" class="btn btn-danger pull-right"><i class="icon-off icon-white"></i> Abmelden</a>
					{/if}
				</div>
			</div>
		</div>

		<div class="container-fluid">
			<div class="row-fluid">
				<div class="span3 hidden-phone">
					<div class="well sidebar-nav">
						<ul class="nav nav-list">
							<li class="nav-header">Navigation</li>
							<li><a href="index.php"><i class="icon-home"></i> Forenübersicht</a></li>
							<li><a href="search.php"><i class="icon-search"></i> Suchen</a></li>
							{if $ISANONYMOUS}
								<li><a href="login.php"><i class="icon-user"></i> Anmelden</a></li>
								<li><a href="https://ucp.junge-piraten.de/register"><i class="icon-cog"></i> Registrieren</a></li>
							{else}
								<li><a href="logout.php"><i class="icon-off"></i> Abmelden</a></li>
							{/if}
							<li><a href="unread.php?markread="><i class="icon-flag"></i> Alle als gelesen markieren</a></li>
{php}
function isSubBoard($destBoard, $curBoard) {
	if (!isset($destBoard["parent"])) {
		return false;
	} else if ($curBoard["boardid"] == $destBoard["parent"]["boardid"] || $curBoard["boardid"] == $destBoard["boardid"]) {
		return true;
	} else {
		return isSubBoard($destBoard["parent"], $curBoard);
	}
}
{/php}

							<li class="nav-header">Foren</li>
							{include file="header_forennavigation.html.tpl" curboard=$ROOTBOARD}
						</ul>
					</div>
				</div>

			        <div class="span9">
					<h1>{$title|escape:html}
						<span class="stylesheet"><form action="dummy" method="post"><select name="choice" onchange="setActiveStyleSheet(this.form.choice.options[this.form.choice.selectedIndex].value)"><option value="">Blau-Wei&szlig;</option><option value="JuPi-Theme">Schwarz-Cyan</option></select></form></span>
					</h1>

