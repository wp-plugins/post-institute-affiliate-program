this.randompiaad = function(){
	var pause = 12000; // define the pause for each ad (in milliseconds)
	var length = jQuery("#sidebar-pia-products li").length;
	var temp = -1;
	this.getRan = function(){
		// get the random number
		var ran = Math.floor(Math.random()*length) + 1;
		return ran;
	};
	this.show = function(){
		var ran = getRan();
		// to avoid repeating
		while (ran == temp){
			ran = getRan();
		};
		temp = ran;
		jQuery("#sidebar-pia-products li").hide();
		jQuery("#sidebar-pia-products li:nth-child(" + ran + ")").fadeIn("fast");
	};
	show(); setInterval(show,pause);
};

jQuery(document).ready(function(){
	randompiaad();
});
