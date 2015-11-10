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

;(function ($) {
	$.fn.equalHeight = function (options){

		//only set min-height if we have more than 1 element
		if(this.length > 1 || (options && options.force)){
			
			var tallest = 0;
			this.each(function() {

				var height = $(this).css({height: '', 'min-height': ''}).height();

				if(height > tallest) {
					tallest = height;
				}
			});

			this.each(function() {
				$(this).css('min-height', tallest);
			});
		}

		return this;
	}
})(jQuery);