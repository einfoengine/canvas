/** 
 *------------------------------------------------------------------------------
 * @package       CANVAS Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 ThemezArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       ThemezArt
 *                & t3-framework.org as base version
 * @Link:         http://themezart.com/canvas-framework 
 *------------------------------------------------------------------------------
 */
 
!function($){
	CANVASTheme = window.CANVASTheme || {};

	$.extend(CANVASTheme, {
		handleLink: function(){
			var links = document.links,
				forms = document.forms,
				origin = [window.location.protocol, '//', window.location.hostname, window.location.port].join(''),
				tmid = /[?&]canvastmid=([^&]*)/.exec(window.location.search),
				tmparam = 'themer=1',
				iter, i, il;

			tmid = tmid ?  '&' + decodeURI(tmid[0]).substr(1) : '';
			tmparam += tmid;

			for(i = 0, il = links.length; i < il; i++) {
				iter = links[i];

				if(iter.href && iter.hostname == window.location.hostname && iter.href.indexOf('#') == -1){
					iter.href = iter.href + (iter.href.lastIndexOf('?') != -1 ? '&' : '?') + (iter.href.lastIndexOf('themer=') == -1 ? tmparam : ''); 
				}
			}
			
			for(i = 0, il = forms.length; i < il; i++) {
				iter = forms[i];

				if(iter.action.indexOf(origin) == 0){
					iter.action = iter.action + (iter.action.lastIndexOf('?') != -1 ? '&' : '?') + (iter.action.lastIndexOf('themer=') == -1 ? tmparam : ''); 
				}
			}

			//10 seconds, if the Less build not complete, we just show the page instead of blank page
			CANVASTheme.sid = setTimeout(CANVASTheme.bodyReady, 10000);
		},
		
		applyLess: function(data){

			var applicable = false;

			if(data && typeof data == 'object'){

				if(data.template == CANVASTheme.template){
					applicable = true;

					CANVASTheme.vars = data.vars;
					CANVASTheme.others = data.others;
					CANVASTheme.theme = data.theme;
				}
			}
			
			less.refresh(true);

			return applicable;
		},

		onCompile: function(completed, total){
			if(window.parent != window && window.parent.CANVASTheme){
				window.parent.CANVASTheme.onCompile(completed, total);
			}

			if(completed >= total){
				CANVASTheme.bodyReady();
			}
		},

		bodyReady: function(){
			clearTimeout(CANVASTheme.sid);

			if(!this.ready){
				$(document).ready(function(){
					CANVASTheme.ready = 1;
					$(document.body).addClass('ready');
				});
			} else {
				$(document.body).addClass('ready');
			}
		}
	});

	$(document).ready(function(){
		CANVASTheme.handleLink();
	});
	
}(jQuery);
