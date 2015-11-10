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

var CANVASAdmin = window.CANVASAdmin || {};

!function ($) {

	$.extend(CANVASAdmin, {
		
		initToolbar: function(){
			//canvas added
			$('#canvas-admin-tb-compile-all').on('click', function(){
				CANVASAdmin.compileLESS();
				return false;
			});

			$('#canvas-admin-tb-compile-this').on('click', function(){
				CANVASAdmin.compileLESS($('#jform_params_theme').val() || 'default');
				return false;
			});

			$('#jform_params_theme').on('change', function(){
				var compileThis = $('#canvas-admin-tb-compile-this');

				compileThis.find('a').html(compileThis.attr('data-msg').replace('%s', this.value || compileThis.attr('data-default')));
			});

			$('#canvas-admin-tb-themer button').on('click', function(){
				if(!CANVASAdmin.themermode){
					
					$('#canvas-admin-tb-megamenu button').popover('hide');
					CANVASAdmin.tbmmid = 0;
					
					$(this).popover('show');

					clearTimeout(CANVASAdmin.tbthemerid);
					CANVASAdmin.tbthemerid = setTimeout(function(){
						$('#canvas-admin-tb-themer button').popover('hide');
					}, 2000);
				} else {
					$(this).popover('hide');
					
					window.location.href = CANVASAdmin.themerUrl;
				}
				return false;
			}).popover({
				trigger: 'manual',
				placement: 'bottom',
				container: 'body'
			});
		

			$('#canvas-admin-tb-megamenu button').on('click', function(){
				
				if($('#jform_params_navigation_type :checked').val() != 'megamenu' && !CANVASAdmin.tbmmid){
					
					$('#canvas-admin-tb-themer button').popover('hide');
					$(this).popover('show');

					clearTimeout(CANVASAdmin.tbmmid);
					CANVASAdmin.tbmmid = setTimeout(function(){
						$('#canvas-admin-tb-megamenu button').popover('hide');
						CANVASAdmin.tbmmid = 0;
					}, 5000);
				} else {
					window.location.href = CANVASAdmin.megamenuUrl;
				}
				
				return false;
			}).popover({
				trigger: 'manual',
				placement: 'bottom',
				container: 'body'
			});		

			//for style toolbar
			$('#canvas-admin-tb-style-save-save').on('click', function(){
				Joomla.submitbutton('style.apply');
			});

			$('#canvas-admin-tb-style-save-close').on('click', function(){
				Joomla.submitbutton('style.save');
			});
			
			$('#canvas-admin-tb-style-save-clone').on('click', function(){
				Joomla.submitbutton('style.save2copy');
			});

			$('#canvas-admin-tb-close').on('click', function(){
				Joomla.submitbutton(($(this).hasClass('template') ? 'template' : 'style') + '.cancel');
			});

            // menu assignment toggle
            $('.menu-assignment-toggle').on ('click', function () {
               var $this = $(this),
                   $parent = $this.parents('label').length ? $this.parents('label') : $this.parents('h5'),
                   level = $parent.data('level');
                $parent.nextAll().each (function () {
                   if (!level || $(this).data('level') > level) {
                       var chk = $(this).find ('.chk-menulink');
                       chk.prop('checked', !chk.prop('checked'));
                   } else {
                       return false;
                   }
               });
            });

            // menu tree toggle
            $('.menu-tree-toggle').on ('click', function () {
               var $this = $(this),
                   $parent = $this.parents('label'),
                   level = $parent.data('level'),
                   status = $this.data('status');
                $parent.nextAll().each (function () {
                   if ($(this).data('level') > level) {
                       if (status == 'hide') $(this).removeClass ('hide'); else $(this).addClass('hide');
                   } else {
                       return false;
                   }
               });
               if (status == 'hide') {
                   $this.data('status', 'show');
                   $this.addClass ('icon-minus').removeClass ('icon-plus');
               } else {
                   $this.data('status', 'hide');
                   $this.removeClass ('icon-minus').addClass ('icon-plus');
               }
            });
		},

		initRadioGroup: function(){

			//convert to on/off
			$('fieldset.radio').filter(function(){
			
				return $(this).find('input').length == 2 && $(this).find('input').filter(function(){
						return $.inArray(this.value + '', ['0', '1']) !== -1;
					}).length == 2;

			}).addClass('canvasonoff')
				.find('label').addClass(function(){
					return $(this).prev('input').val() == '0' ? 'off' : 'on'
				});

			//support eplicit define class
			$('.canvasonoff').removeClass('btn-group').find('label').removeClass('btn');
			
			//action
			$('fieldset.radio').find('label').removeClass('btn-success btn-danger btn-primary').unbind('click').click(function() {
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

			//update state
			$('.canvas-admin-form').on('update', 'input[type=radio]', function(){
				if(this.checked){
					$(this)
						.closest('.radio')
						.find('label').removeClass('active')
						.filter('[for="' + this.id + '"]')
							.addClass('active');
				}
			});
		},
		
		initChosen: function(){

			$('#style-form').find('select').chosen({
				disable_search_threshold : 10,
				allow_single_deselect : true
			});
		},

		improveMarkup: function(){
			var jptitle = $('.pagetitle');
			if (!jptitle.length){
				jptitle = $('.page-title');
			}

			if(!jptitle.length){
				return;
			}

			var titles = jptitle.html().split(':');

			jptitle.removeClass('icon-48-thememanager').html(titles[0] + '<small>' + titles[1] + '</small>');

			//remove joomla title
			$('#template-manager .tpl-desc-name').remove();

			//template manager - J2.5
			$('#template-manager-css')
				.closest('form').addClass('form-inline')
				.find('button[type=submit]').addClass('btn');
		},

		hideDisabled: function(){
			$('#style-form').find(':input[disabled="disabled"]').filter(function(){
				return this.name && this.name.match(/^.*?\[params\]\[(.*?)\]/)
			}).closest('.control-group').hide();
		},

		initPreSubmit: function(){

			var form = document.adminForm;
			if(!form){
				return false;
			}

			var onsubmit = form.onsubmit;

			form.onsubmit = function(e){
				var json = {},
					urlparts = form.action.split('#');
					
				if(/apply|save2copy/.test(form['task'].value)){
					canvasactive = $('.canvas-admin-nav .active a').attr('href').replace(/.*(?=#[^\s]*$)/, '').substr(1);

					if(urlparts[0].indexOf('?') == -1){
						urlparts[0] += '?canvaslock=' + canvasactive;
					} else {
						urlparts[0] += '&canvaslock=' + canvasactive;
					}
					
					form.action = urlparts.join('#');
				}
					
				if($.isFunction(onsubmit)){
					onsubmit();
				}
			};
		},

		initChangeStyle: function(){
			$('#canvas-styles-list').on('change', function(){
				window.location.href = CANVASAdmin.baseurl + '/index.php?option=com_templates&task=style.edit&id=' + this.value + window.location.hash;
			});
		},

		initMarkChange: function(){
			var allinput = $(document.adminForm).find(':input')
				.each(function(){
					$(this).data('org-val', (this.type == 'radio' || this.type == 'checkbox') ? $(this).prop('checked') : $(this).val());
				});

			setTimeout(function() {
				allinput.on('change', function(){
					var jinput = $(this),
						oval = jinput.data('org-val'),
						nval = (this.type == 'radio' || this.type == 'checkbox') ? jinput.prop('checked') : jinput.val(),
						eq = true;

					if(oval != nval){
						if($.isArray(oval) && $.isArray(nval)){
							if(oval.length != nval.length){
								eq = false;
							} else {
								for(var i = 0; i < oval.length; i++){
									if(oval[i] != nval[i]){
										eq = false;
										break;
									}
								}
							}
						} else {
							eq = false;
						}
					}

					var jgroup = jinput.closest('.control-group'),
						jpane = jgroup.closest('.tab-pane'),
						chretain = Math.max(0, (jgroup.data('chretain') || 0) + (!eq && jinput.data('included') ? 0 : (eq ? -1 : 1)));

					jgroup.data('chretain', chretain).toggleClass('canvas-changed', !!(chretain));

					$('.canvas-admin-nav .nav li').eq(jpane.index()).toggleClass('canvas-changed', !!(!eq || jpane.find('.canvas-changed').length));

					if(this.type == 'radio'){
						jinput = jinput.add(jgroup.find('[name="' + this.name + '"]'));
					}
					jinput.data('included', !eq);
				});
			}, 500);
		},

		initCheckupdate: function(){
			
			var tinfo = $('#canvas-admin-tpl-info dd'),
				finfo = $('#canvas-admin-frmk-info dd');

			CANVASAdmin.chkupdating = null;
			CANVASAdmin.tplname = tinfo.eq(0).html();
			CANVASAdmin.tplversion = tinfo.eq(1).html();
			CANVASAdmin.frmkname = finfo.eq(0).html();
			CANVASAdmin.frmkversion = finfo.eq(1).html();
			
			$('#canvas-admin-framework-home .updater, #canvas-admin-template-home .updater').on('click', 'a.btn', function(){
				
				//if it is outdated, then we go direct to link
				if($(this).closest('.updater').hasClass('outdated')){
					return true;
				}

				//if we are checking, ignore this click, wait for it complete
				if(CANVASAdmin.chkupdating){
					return false;
				}

				//checking
				$(this).addClass('loading');
				CANVASAdmin.chkupdating = this;
				CANVASAdmin.checkUpdate();

				return false;
			});
		},

		checkUpdate: function(){
			$.ajax({
				url: CANVASAdmin.canvasupdateurl,
				data: {eid: CANVASAdmin.eids},
				success: function(data) {
					var jfrmk = $('#canvas-admin-framework-home .updater:first'),
						jtemp = $('#canvas-admin-template-home .updater:first');

					jfrmk.find('.btn').removeClass('loading');
					jtemp.find('.btn').removeClass('loading');
					
					try {
						var ulist = $.parseJSON(data);
					} catch(e) {
						CANVASAdmin.alert(CANVASAdmin.langs.updateFailedGetList, CANVASAdmin.chkupdating);
					}

					if (ulist instanceof Array) {
						if (ulist.length > 0) {
							
							var	chkfrmk = !jfrmk.hasClass('outdated'),
								chktemp = !jtemp.hasClass('outdated');

							if(chkfrmk || chktemp){
								for(var i = 0, il = ulist.length; i < il; i++){

									if(chkfrmk && ulist[i].element == CANVASAdmin.felement && ulist[i].type == 'plugin'){
										jfrmk.addClass('outdated');
										jfrmk.find('.btn').attr('href', CANVASAdmin.jupdateUrl).html(CANVASAdmin.langs.updateDownLatest);
										jfrmk.find('h3').html(CANVASAdmin.langs.updateHasNew.replace(/%s/g, CANVASAdmin.frmkname));
										
										var ridx = 0,
											rvals = [CANVASAdmin.frmkversion, CANVASAdmin.frmkname, ulist[i].version];
										jfrmk.find('p').html(CANVASAdmin.langs.updateCompare.replace(/%s/g, function(){
											return rvals[ridx++];
										}));

										CANVASAdmin.langs.updateCompare.replace(/%s/g, function(){ return '' })
									}
									if(chktemp && ulist[i].element == CANVASAdmin.telement && ulist[i].type == 'template'){
										jtemp.addClass('outdated');
										jtemp.find('.btn').attr('href', CANVASAdmin.jupdateUrl).html(CANVASAdmin.langs.updateDownLatest);

										jtemp.find('h3').html(CANVASAdmin.langs.updateHasNew.replace(/%s/g, CANVASAdmin.tplname));
										
										var ridx = 0,
											rvals = [CANVASAdmin.tplversion, CANVASAdmin.tplname, ulist[i].version];
										jtemp.find('p').html(CANVASAdmin.langs.updateCompare.replace(/%s/g, function(){
											return rvals[ridx++];
										}));
									}
								}

								CANVASAdmin.alert(CANVASAdmin.langs.updateChkComplete, CANVASAdmin.chkupdating);
							}
						}
					} else {
						CANVASAdmin.alert(CANVASAdmin.langs.updateFailedGetList, CANVASAdmin.chkupdating);
					}

					CANVASAdmin.chkupdating = null;
				},
				error: function() {
					CANVASAdmin.alert(CANVASAdmin.langs.updateFailedGetList, CANVASAdmin.chkupdating);
					CANVASAdmin.chkupdating = null;
				}
			});
		},

		initSystemMessage: function(){
			var jmessage = $('#system-message');
				
			if(!jmessage.length){
				jmessage = $('' + 
					'<dl id="system-message">' +
						'<dt class="message">Message</dt>' +
						'<dd class="message">' +
							'<ul><li></li></ul>' +
						'</dd>' +
					'</dl>').hide().appendTo($('#system-message-container'));
			}

			CANVASAdmin.message = jmessage;
		},

		systemMessage: function(msg){
			CANVASAdmin.message.show();
			if(CANVASAdmin.message.find('li:first').length){
				CANVASAdmin.message.find('li:first').html(msg).show();
			} else {
				CANVASAdmin.message.html('' + 
					'<div class="alert">' +
						'<h4>Message</h4>' + 
						'<p>' + msg + '</p>' +
					'</div>');
			}
			
			clearTimeout(CANVASAdmin.msgid);
			CANVASAdmin.msgid = setTimeout(function(){
				CANVASAdmin.message.hide();
			}, 5000);
		},

		alert: function(msg, place){
			clearTimeout($(place).data('alertid'));
			$(place).after('' + 
				'<div class="alert">' +
					'<p>' + msg + '</p>' +
				'</div>').data('alertid', setTimeout(function(){
					$(place).nextAll('.alert').remove();
				}, 5000));
		},

		initLoadingBar: function(){
			if(!CANVASAdmin.progElm){
				CANVASAdmin.progElm = $('.canvas-progress');

				if(!CANVASAdmin.progElm.length){
					CANVASAdmin.progElm = $('<div class="canvas-progress"></div>')
				}

				CANVASAdmin.progElm.appendTo(document.body);

				var placed = $('#toolbar-box');
				if(!placed.length){
					placed = $('#canvas-admin-toolbar');
				}

				if(placed.length){
					CANVASAdmin.progElm.appendTo(placed);
				}
			}
		},

		switchTab: function () {
			$('.canvas-admin-nav a[data-toggle="tab"]').on('shown', function (e) {
				var url = e.target.href;
			  	window.location.hash = url.substring(url.indexOf('#')).replace ('_params', '');
			});

			var hash = window.location.hash;
			if (hash) {
				$('a[href="' + hash + '_params' + '"]').tab ('show');
			} else {
				var url = $('.canvas-admin-nav .nav-tabs li.active a').attr('href');
				if (url) {
			  		window.location.hash = url.substring(url.indexOf('#')).replace ('_params', '');
				} else {
					$('.canvas-admin-nav .nav-tabs li:first a').tab ('show');
				}
			}
		},

		fixValidate: function(){
			if(typeof JFormValidator != 'undefined'){
				
				//overwrite
				JFormValidator.prototype.isValid = function (form) {
					
					var valid = true;

					// Precompute label-field associations
					var labels = document.getElementsByTagName('label');
					for (var i = 0; i < labels.length; i++) {
						if (labels[i].htmlFor != '') {
							var element = document.getElementById(labels[i].htmlFor);
							if (element) {
								element.labelref = labels[i];
							}
						}
					}

					// Validate form fields
					var elements = form.getElements('fieldset').concat(Array.from(form.elements));
					for (var i = 0; i < elements.length; i++) {
						if (this.validate(elements[i]) == false) {
							valid = false;
						}
					}

					// Run custom form validators if present
					new Hash(this.custom).each(function (validator) {
						if (validator.exec() != true) {
							valid = false;
						}
					});

					if (!valid) {
						var message = Joomla.JText._('JLIB_FORM_FIELD_INVALID');
						var errors = jQuery("label.invalid");
						var error = new Object();
						error.error = new Array();
						for (var i=0;i < errors.length; i++) {
							var label = jQuery(errors[i]).text();
							if (label != 'undefined') {
								error.error[i] = message+label.replace("*", "");
							}
						}
						Joomla.renderMessages(error);
					}

					return valid;
				};

				JFormValidator.prototype.handleResponse = function(state, el){
					// Find the label object for the given field if it exists
					//if (!(el.labelref)) {
					//	var labels = $$('label');
					//	labels.each(function(label){
					//		if (label.get('for') == el.get('id')) {
					//			el.labelref = label;
					//		}
					//	});
					//}

					// Set the element and its label (if exists) invalid state
					if (state == false) {
						el.addClass('invalid');
						el.set('aria-invalid', 'true');
						if (el.labelref) {
							document.id(el.labelref).addClass('invalid');
							document.id(el.labelref).set('aria-invalid', 'true');
						}
					} else {
						el.removeClass('invalid');
						el.set('aria-invalid', 'false');
						if (el.labelref) {
							document.id(el.labelref).removeClass('invalid');
							document.id(el.labelref).set('aria-invalid', 'false');
						}
					}
				};

			}
		},

		compileLESS: function(theme){
			var recompile = $('#canvas-admin-tb-recompile');

			//progress bar
			recompile.addClass('loading');
			if($.support.transition){
				CANVASAdmin.progElm
					.removeClass('canvas-anim-slow canvas-anim-finish')
					.css('width', '');

				setTimeout(function(){
					var width = 5 + Math.floor(Math.random() * 10),
						iid = null;

					CANVASAdmin.progElm
						.addClass('canvas-anim-slow')
						.css('width', width + '%');

					iid = setInterval(function(){
						if(!CANVASAdmin.progElm.hasClass('canvas-anim-slow')) {
							clearInterval(iid);
							return false;
						}

						width += Math.floor(Math.random() * 5);

						CANVASAdmin.progElm
							.addClass('canvas-anim-slow')
							.css('width', Math.min(90, width) + '%');
					}, 3000);
				});
			} else {
				CANVASAdmin.progElm.stop(true).css({
					width: '0%',
					display: 'block'
				}).animate({
					width: 50 + Math.floor(Math.random() * 20) + '%'
				});
			}

			$.ajax({
				url: CANVASAdmin.adminurl,
				data: {'canvasaction': 'lesscall', 'styleid': CANVASAdmin.templateid, 'theme': theme || '' }
			}).always(function(){
				
				//progress bar
				recompile.removeClass('loading');
				if($.support.transition){
					
					CANVASAdmin.progElm
						.removeClass('canvas-anim-slow')
						.addClass('canvas-anim-finish')
						.one($.support.transition.end, function () {
							setTimeout(function(){
								if(CANVASAdmin.progElm.hasClass('canvas-anim-finish')){
									$(CANVASAdmin.progElm).removeClass('canvas-anim-finish');
								}
							}, 1000);
						});

				} else {
					$(CANVASAdmin.progElm).stop(true).animate({
						width: '100%'
					}, function(){
						$(CANVASAdmin.progElm).hide();
					});
				}
				
			}).done(function(rsp){
					
				rsp = $.trim(rsp);
				if(rsp){
					var json = rsp;
					if(rsp.charAt(0) != '[' && rsp.charAt(0) != '{'){
						json = rsp.match(new RegExp('{[\["].*}'));
						if(json && json[0]){
							json = json[0];
						}
					}

					if(json && typeof json == 'string'){
						
						rsp = rsp.replace(json, '');

						try {
							json = $.parseJSON(json);
						} catch (e){
							json = {
								error: CANVASAdmin.langs.unknownError
							}
						}
					}

					CANVASAdmin.systemMessage(rsp || json.error || json.successful);
				}

			}).fail(function(){
				recompile.removeClass('loading');
				CANVASAdmin.systemMessage(CANVASAdmin.langs.unknownError);
			});
		},

		initCANVASThemeExtras: function(){
			$('.canvas-extra-setting').on('change', function(e, val){
				if(val.selected == '0' || val.selected == '-1'){
					$(e.target).val(val.selected).trigger('liszt:updated');
				} else {
					var hasExclusive = 0,
						vals = $(e.target).val(),
						filterd = $.isArray(vals) && $.grep(vals, function(val){
							hasExclusive = hasExclusive || (val == '0' || val == '-1');

							return !(val == '0' || val == '-1'); 
						});

					if(hasExclusive){
						$(e.target).val(filterd).trigger('liszt:updated');
					}
				}
			})
		},

        noticeChange: function () {
            // show notice message when responsive mode change
            $('input[name="jform[params][responsive]"]').on('change', function(){
                // this is radio
                if ($(this).data('org-val') != $(this).prop('checked')) {
                    CANVASAdmin.systemMessage(CANVASAdmin.langs['switchResponsiveMode']);
                }
            })
        },


	});
	
	$(document).ready(function(){
		CANVASAdmin.initSystemMessage();
		CANVASAdmin.initLoadingBar();
		CANVASAdmin.improveMarkup();
		CANVASAdmin.initMarkChange();
		CANVASAdmin.initToolbar();
		CANVASAdmin.initRadioGroup();
		CANVASAdmin.initChosen();
		CANVASAdmin.initPreSubmit();
		CANVASAdmin.hideDisabled();
		CANVASAdmin.initChangeStyle();
		CANVASAdmin.initCANVASThemeExtras();
		//CANVASAdmin.initCheckupdate();
		CANVASAdmin.switchTab();
		CANVASAdmin.fixValidate();
        CANVASAdmin.noticeChange ();
	});
	
}(jQuery);