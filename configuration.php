<?php

/**
*	App Configuration Page
*	
*	- loaded through an Iframe in the Shop's Administration Section
*/

if (empty($_GET['place']) || empty($_GET['shop']) || empty($_GET['timestamp'])) {
	die('<p>Invalid Request!</p>');
}

require 'config.php';
require 'lib/ConfigSystem.php';
//use Retargeting\Lib\App;

$app = new App(Config());

?>
<?php if ($app->validRequest) : ?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Retargeting</title>

		<link rel="stylesheet" type="text/css" href="css/configuration.css">
		<link href='//fonts.googleapis.com/css?family=Lato:300,400,700,900,300italic' rel='stylesheet' type='text/css'>

		<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
		<script src="https://developers.shoper.pl/public/sdk.js"></script>
		<script type="text/javascript">
			var RA_SHOPKEY = '<?php echo $app->shopKey; ?>';
			var RA_SHOP = '<?php echo $app->shop; ?>';
		</script>
		<script type="text/javascript" src="js/configuration.js"></script>
	</head>

	<body>

		<section>
			<header class="span-1">
				<h1>App Status</h1>
				<div id="btn-changeStatus" class="btn btn-save"><?php echo ($app->status ? 'Disable' : 'Enable'); ?></div>
			</header>
			<div class="content span-3">
				
				<div class="input">
					<label for="ra_status"><?php echo ($app->status ? 'Running' : 'Not running'); ?></label>
					<p class="description"><?php echo ($app->status ? 'Your <a href="http://retargeting.biz">Retargeting</a> App is <strong>up</strong> and <strong>running</strong>' : 'Currently your <a href="http://retargeting.biz">Retargeting</a> App is <strong>not</strong> tracking any of your users. To activate it please press the <strong>Enable</strong> button from to top right of your screen.'); ?></p>
				</div>

			</div>
		</section>

		<section>
			<header class="span-1">
				<h1>Secure Keys</h1>
				<div id="btn-save" class="btn btn-save">Save</div>
			</header>
			<div class="content span-3">

				<div class="input">
					<label for="ra_domain_api_key">Domain API Key</label>
					<input type="text" name="ra_domain_api_key" id="ra_domain_api_key" placeholder="ex: 1238BFDOS0SFODBSFKJSDFU2U32" value="<?php echo $app->domainApiKey; ?>">
					<p class="description">You can find your Secure Domain API Key in your <a href="http://retargeting.biz">Retargeting</a> account.</p>
				</div>

				<div class="input">
					<label for="ra_discounts_api_key">Discounts API Key</label>
					<input type="text" name="ra_discounts_api_key" id="ra_discounts_api_key" placeholder="ex: 1238BFDOS0SFODBSFKJSDFU2U32" value="<?php echo $app->discountsApiKey; ?>">
					<p class="description">You can find your Secure Discounts API Key in your <a href="http://retargeting.biz">Retargeting</a> account.</p>
				</div>

			</div>
		</section>

		<section>
			<header class="span-1">
				<h1>Preferences</h1>
				<div id="btn-savePreferences" class="btn btn-save">Save</div>
			</header>
			<div class="content span-3">
				
				<div class="input">
					<label for="ra_help_pages">Help Pages</label>
					<input type="text" name="ra_help_pages" id="ra_help_pages" placeholder="about-us" value="<?php echo $app->helpPages; ?>">
					<p class="description">Please add the URL handles for the pages on which you want the "visitHelpPage" event to fire. Use a comma as a separator for listing multiple handles. For example: http://yourshop.com/<strong>about-us</strong> is represented by the "about-us" handle.</p>
				</div>

				<div class="info">
					<label>JavaScript Query Selectors</label>
					<p>The <a href="http://retargeting.biz">Retargeting</a> App should work out of the box for most themes. But, as themes implementation can vary, in case there would be any problems with events not working as expected you can modify the following settings to make sure the events are linked to the right theme elements.</p>
				</div>

				<div class="input">
					<label for="ra_">Add To Cart Button</label>
					<input type="text" name="ra_add_to_cart" id="ra_add_to_cart" placeholder='form[action="/cart/add"] [type="submit"]' value="<?php echo $app->querySelectors['addToCart']; ?>">
					<p class="description">Query selector for the button used to add a product to cart.</p>
				</div>

			</div>
		</section>

		<section class="init <?php if ($app->init) echo 'disabled'; ?>">
			
			<article>
				<!--<img src="imgs/logo-big.jpg">-->
				<h1>Hello!</h1>
				<h2>To have access to our awesome features you need a <a href="https://retargeting.biz" target="_blank">Retargeting account</a>.</h2>
				<div class="row">
					<div class="btn-init btn-disableInit">I already have an account</div>
					<a href="https://retargeting.biz/signup"<div class="btn-init btn-cta">Start your 14-day Free Trial</div></a>
				</div>
			</article>
		
		</section>

	</body>
</html>
<?php else : ?>
<p>Unauthorized Request!</p>
<?php endif ?>