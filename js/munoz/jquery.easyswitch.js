
(function($) {
	$.fn.easyswitch = function(options) {
		var default_options = {
			'label-on' : 'ON',	
			'label-off' : 'OFF',	
			'name' : 'easyswitch',	
			'default' : 0,	
			'callback' : '',	
			'class-on' : 'on',	
			'class-off' : 'off',	
			'class-slider' : 'easyswitch-slider',	
			'class-label' : 'easyswitch-label'
		};
		options = $.extend(default_options, options);

		this.each(function(i, item) {
			var self = $(item), opts = {}, html = '';

			$.each(options, function(k, v) {
				var custom = self.attr('data-'+k);
				opts[k] = (custom == undefined ? v : custom);
			});

			html = '<span class="'+opts['class-label']+'">'+opts['label-on']+'</span>\
					<span class="'+opts['class-label']+'">'+opts['label-off']+'</span>\
					<span class="'+opts['class-slider']+'"></span>';
			if (opts['name']) {
				html += '<input type="hidden" id="easyswitch-'+opts['name']+'" name="'+opts['name']+'" value="" />';
			}
			try {
				opts['callback'] = eval('('+opts['callback']+')');
			} catch (e) {
				opts['callback'] = function(){};
			}

			self.append(html).click(function() {
				if (self.hasClass(opts['class-off'])) {
				
					self.find('.'+opts['class-slider']).animate({left:'50%'}, 'fast', function() {
						self.removeClass(opts['class-off']).addClass(opts['class-on']);
						var value = 1;
						$('#easyswitch-'+opts['name']).val(value);
						opts['callback'](value, self);
					});
				} else {
					self.find('.'+opts['class-slider']).animate({left:'1px'}, 'fast', function() {
						self.removeClass(opts['class-on']).addClass(opts['class-off']);
						var value = 0;
						$('#easyswitch-'+opts['name']).val(value);
						opts['callback'](value, self);
					});
				}
			});

			opts['default'] = parseInt(opts['default']);
			if (opts['default']) {
				
				self.addClass(opts['class-on']);
				$('#easyswitch-'+opts['name']).val(1);
				self.find('.'+opts['class-slider']).css({left:'50%'});
			} else {
				self.addClass(opts['class-off']);
				$('#easyswitch-'+opts['name']).val(0);
				self.find('.'+opts['class-slider']).css({left:'1px'});
			}
		});
	}
})(jQuery);