var ShopAppInstance = new ShopApp(function(app) {
    app.init(null, function(params, app) {
        if (localStorage.getItem('styles') === null) {
            for(var x = 0; x < params.styles.length; ++x) {
                var el = document.createElement('link');
                el.rel = 'stylesheet';
                el.type = 'text/css';
                el.href = params.styles[x];
                document.getElementsByTagName('head')[0].appendChild(el);     
            }
        }
        localStorage.setItem('styles', JSON.stringify(params.styles));

        app.show(null ,function () {
            app.monitorIframeSize();
			$('#btn-save').click(saveConfiguration);
			$('#btn-savePreferences').click(saveConfiguration);
			$('#btn-changeStatus').click(changeStatus);

			$('.btn-disableInit').click(function(){
			    disableInit();
				$('.config-content').show(0);
                $('#s1').show(0);
                $('#s2').show(0);
				$('#s3').show(0);
			});
        });
    }, function(errmsg, app) {
        alert(errmsg);
    });
}, true);

function disableInit() {
	$.post( "configurationAjax.php", {
			shopKey: RA_SHOPKEY,
			shop: RA_SHOP,
			disableInit: true
		}, function(data) {
			var data = JSON.parse(data);
			if (!data) {
				console.log('RA_INFO: Sorry, but we could not disable the initial screen. Please try again later');
			} else {
				$('section.init').fadeOut('fast');
			}
		} 
	)
}

function saveConfiguration() {
	console.info('Retargeting Info :: Saving configuration.');

	ShopAppInstance.flashMessage({
		msg : 'Saving configuration..',
		type : 'info'
	}, function(params, app) {
		// ok
	}, function(errmsg, app) {
		alert(errmsg);
	});

	$.post( "configurationAjax.php", {
			shopKey: RA_SHOPKEY,
			shop: RA_SHOP,
			domainApiKey: $('input[name="ra_domain_api_key"]').val(), 
			discountsApiKey: $('input[name="ra_discounts_api_key"]').val(), 
			help_pages: $('input[name="ra_help_pages"]').val(), 
			qs_add_to_cart: $('input[name="ra_add_to_cart"]').val(), 
			qs_variation: $('input[name="ra_variation"]').val(), 
			qs_add_to_wishlist: $('input[name="ra_add_to_wishlist"]').val(), 
			qs_product_images: $('input[name="ra_product_images"]').val(), 
			qs_review: $('input[name="ra_review"]').val(), 
			qs_price: $('input[name="ra_price"]').val(),
			qs_old_price: $('input[name="ra_old_price"]').val()
		}, function(data) {
			var data = JSON.parse(data);

			if (!data) {
				ShopAppInstance.flashMessage({
					msg : "Sorry, but we could not save the new configuration. Please try again later",
					type : 'error'
				}, function(params, app) {
					// ok
				}, function(errmsg, app) {
					alert(errmsg);
				});
			} else {
				ShopAppInstance.flashMessage({
					msg : 'Configuration saved successfully!',
					type : 'success'
				}, function(params, app) {
					location.reload();
				}, function(errmsg, app) {
					alert(errmsg);
				});	
			}
		} 
	)
}

function changeStatus() {
	console.info('Retargeting Info :: Change status.');

	ShopAppInstance.flashMessage({
		msg : 'Changing app status..',
		type : 'info'
	}, function(params, app) {
		// ok
	}, function(errmsg, app) {
		alert(errmsg);
	});

	$.post( "configurationAjax.php", {
			shopKey: RA_SHOPKEY,
			shop: RA_SHOP,
			changeStatus: true,
			domainApiKey: $('input[name="ra_domain_api_key"]').val(), 
			discountsApiKey: $('input[name="ra_discounts_api_key"]').val(), 
			help_pages: $('input[name="ra_help_pages"]').val(), 
			qs_add_to_cart: $('input[name="ra_add_to_cart"]').val(), 
			qs_variation: $('input[name="ra_variation"]').val(), 
			qs_add_to_wishlist: $('input[name="ra_add_to_wishlist"]').val(), 
			qs_product_images: $('input[name="ra_product_images"]').val(), 
			qs_review: $('input[name="ra_review"]').val(), 
			qs_price: $('input[name="ra_price"]').val(),
			qs_old_price: $('input[name="ra_old_price"]').val()
		}, function(data) {
			var data = JSON.parse(data);

			if (!data) {
				ShopAppInstance.flashMessage({
					msg : "Sorry, but we could not change the status. Please try again later",
					type : 'error'
				}, function(params, app) {
					// ok
				}, function(errmsg, app) {
					alert(errmsg);
				});
			} else {
				ShopAppInstance.flashMessage({
					msg : 'App status changed successfully!',
					type : 'success'
				}, function(params, app) {
					location.reload();
				}, function(errmsg, app) {
					alert(errmsg);
				});
			}
		}
	)
}

$(document).ready(function () {
    $('.btn-new-account').click(function () {
        $('.config-content').hide(0);
        $('.signup-now').prepend('<iframe class="signupiframe" scrolling="no" src="https://retargeting.biz/en/regiframe"></iframe>');
    });

    $('.btn-close-signup').click(function(){
        disableInit();
        $('.signupiframe').hide(0);
        $('.config-content').show(0);
        $('#s1').show(0);
        $('#s2').show(0);
        $('#s3').show(0);
    });
    
    if(typeof $('#template-registeriframe_success')[0] !== 'undefined') {
	$('.btn-close-signup').show(0);
    }
    
});