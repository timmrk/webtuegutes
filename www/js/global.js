var isMobileSize = false;

function toggleMenu(close)
{
	if(close)
		$('.mobileMenu').slideUp(function()
		{
			$('.mobile').removeClass('closed');
		});
	else
		$('.mobileMenu').slideToggle(function()
		{
			$('.mobile').toggleClass('closed');
		});
}
function toggleProfile(close)
{
	if(close)
		$('.mobileProfileContent').slideUp(function()
		{
			$('.mobileProfileContent').addClass('closed');
		});
	else
		$('.mobileProfileContent').slideToggle(function()
		{
			$('.mobileProfileContent').toggleClass('closed');
		});
}

$(document).ready(function()
{
	checkForMobileSize();

	//mobilemenu
	$('.mobileMenuButton').click(function()
	{
		toggleProfile(true);
		toggleMenu();
	});

	//navigation script
	$('.navigation ul li a').click(function()
	{
		$('.mobileMenu').removeAttr('style');
		$('#mobileSection .mobile').removeClass('closed');
	});

	//====DO NOT TOUCH====
	$('a.slicknav_btn').click(function()
	{
		$('.mobilemenu ul').css({ 'display': 'block' });
	});
	//====/DO NOT TOUCH====
	
	//mobile profile
	$('.mobile .profile').click(function()
	{
		toggleMenu(true);
		toggleProfile();
	});
	
	//====Loop through all anchors====
	/*$('a[href*=\\#]:not([href=\\#])').click(function()
	{
	});*/

	window.addEventListener('scroll', function(e)
	{
		if(!isMobileSize)
		{
			var distanceY = window.pageYOffset || document.documentElement.scrollTop,
				shrinkOn = 50;

			if(distanceY > shrinkOn)
				$('header').addClass('smaller');
			else
				$('header').removeClass('smaller');
		}
	});
	window.addEventListener('resize', function(e)
	{
		checkForMobileSize();
	});
	
	
	
	
	//if(!Modernizr.inputtypes.date) // fallback to jQueryUI dateSelector if there is no input[type=date]
	//{
	//	$('input[type=date]').datepicker({
	//		dateFormat: 'dd.mm.yy'
	//	});
	//}
	$('input[type=date]').each(function()
	{
		$('<input type="text" />').attr({ name: this.name, placeholder: this.placeholder, class: this.class, requires: this.required }).insertBefore(this).datepicker({
			dateFormat: 'dd.mm.yy'
		});
	}).remove();
}); 

function checkForMobileSize()
{
	isMobileSize = window.matchMedia("only screen and (min-width: 480px) and (max-width: 767px)").matches ||
	window.matchMedia("only screen and (max-width: 479px)").matches;
}

$(window).on('load', function()
{
	//$('header').removeClass('smaller'); // header hat standardm��ig smaller nur bei aktiven js wird smaller entfernt
});

