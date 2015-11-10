/** 
 *------------------------------------------------------------------------------
 * @package       CANVAS Framework for Joomla!
 *------------------------------------------------------------------------------
 * @copyright     Copyright (C) 2004-2013 ThemezArt.com. All Rights Reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @authors       ThemezArt
 *                & t3-framework.org as base version
 * @Google group: https://groups.google.com/forum/#!forum/canvasfw
 * @Link:         http://themezart.com/canvas-framework 
 *------------------------------------------------------------------------------
 */

!function($){
	
	$(document).ready(function(){
		
		//frontend edit radio on/off - auto convert on-off radio if applicable
		$('fieldset.radio').filter(function(){
			
			return $(this).find('input').length == 2 && $(this).find('input').filter(function(){
					return $.inArray(this.value + '', ['0', '1']) !== -1;
				}).length == 2;

		}).addClass('canvasonoff').removeClass('btn-group');

		//add class on/off
		$('fieldset.canvasonoff').find('label').addClass(function(){
			return $(this).hasClass('off') || $(this).prev('input').val() == '0' ? 'off' : 'on'
		});

		//listen to all
		$('fieldset.radio').find('label').unbind('click').click(function() {
			var label = $(this),
				input = $('#' + label.attr('for'));

			if (!input.prop('checked')){
				label.addClass('active').siblings().removeClass('active');

				input.prop('checked', true).trigger('change');
			}
		});

		//initial state
		$('.radio input[checked=checked]').each(function(){
			$('label[for=' + $(this).attr('id') + ']').addClass('active');
		});
		
	});
	
}(jQuery);