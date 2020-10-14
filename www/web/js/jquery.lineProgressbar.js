/**
 * jQuery Line Progressbar
 * Author: KingRayhan<rayhan095@gmail.com>
 * Author URL: http://rayhan.info
 * Version: 1.0.0
 */

(function($){
	'use strict';

	$.fn.LineProgressbar = function(options){

		var options = $.extend({
			percentage : null,
            next_level_money : null,
			ShowProgressCount: true,
			duration: 1000,

			// Styling Options
			fillBackgroundColor: '#fff',
			backgroundColor: '#d1bc9e',
			radius: '5px',
			height: '8px',
			width: '3.5rem'
		},options);

		return this.each(function(index, el) {
			// Markup
			// $(el).html('<div class="progressbar"><div class="proggress"></div><div class="percentCount"><span class="percentCount1"></span>/20000</div></div>');
			var progressFill = $(el).find('.proggress');
			var progressBar= $(el).find('.progressbar');
			progressFill.css({
				backgroundColor : options.fillBackgroundColor,
				height : options.height,
				borderRadius: options.radius
			});
			progressBar.css({
				width : options.width,
				backgroundColor : options.backgroundColor,
				borderRadius: options.radius
			});

			// Progressing
			progressFill.animate(
				{
					width: options.percentage/options.next_level_money * 3.5+"rem"
				},
				{	
					step: function(x) {
						if(options.ShowProgressCount){
							//$(el).find(".percentCount").text(Math.round(x));
							$(el).find(".percentCount1").text(options.percentage);
						}
					},
					duration: options.duration
				}
			);
		////////////////////////////////////////////////////////////////////
		});
	}
})(jQuery);