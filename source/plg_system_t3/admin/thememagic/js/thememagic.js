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
 

var CANVASTheme = window.CANVASTheme || {};

!function ($) {

	$.extend(CANVASTheme, {

		placeholder: 'placeholder' in document.createElement('input'),

		//cache the original link
		initialize: function(){
			this.initCPanel();
			this.initCacheSource();
			this.initThemeAction();
			this.initModalDialog();
			this.initRadioGroup();
		},
		
		initCacheSource: function(){
			CANVASTheme.links = [];

			$('link[rel="stylesheet/less"]').each(function(){
				$(this).data('original', this.href.split('?')[0]);
			});

			$.each(CANVASTheme.data, function(key){
				CANVASTheme.data[key] = $.extend({}, CANVASTheme.data.base, this);
			});
		},

		initCPanel: function(){
			
			$('#canvas-admin-thememagic .themer-minimize').on('click', function(){
				if($(this).hasClass('active')){
					$(this).removeClass('active');
					$('#canvas-admin-thememagic').css('left', 0);
					$('#canvas-admin-tm-preview').css('left', $('#canvas-admin-thememagic').outerWidth(true));
				} else {
					$(this).addClass('active');
					$('#canvas-admin-thememagic').css('left', - $('#canvas-admin-thememagic').outerWidth(true));
					$('#canvas-admin-tm-preview').css('left', 0);
				}
				
				return false;
			});
		},

		initRadioGroup: function(){
			//clone from J3.0 a2
			$('#canvas-admin-thememagic .radio.btn-group label').addClass('btn')
			$('#canvas-admin-thememagic').on('click', '.btn-group label', function(){
				var label = $(this),
					input = $('#' + label.attr('for'));

				if (!input.prop('checked')){
					label.closest('.btn-group')
						.find('label')
						.removeClass('active btn-success btn-danger btn-primary');

					label.addClass('active ' + (input.val() == '' ? 'btn-primary' : (input.val() == 0 ? 'btn-danger' : 'btn-success')));
					
					input.prop('checked', true).trigger('change.less');
				}
			});
			$('#canvas-admin-thememagic .radio.btn-group input:checked').each(function(){
				$('label[for=' + $(this).attr('id') + ']').addClass('active ' + ($(this).val() == '' ? 'btn-primary' : ($(this).val() == 0 ? 'btn-danger' : 'btn-success')));
			});

			$('#canvas-admin-thememagic').on('change.depend', 'input[type=radio]', function(){
				if(this.checked){
					$(this)
						.closest('.btn-group')
						.find('label').removeClass('active btn-primary')
						.filter('[for="' + this.id + '"]').addClass('active ' + ($(this).val() == '' ? 'btn-primary' : ($(this).val() == 0 ? 'btn-danger' : 'btn-success')));
				}
			});
			
		},
		
		initThemeAction: function(){
			CANVASTheme.idle = true;
			this.jel = document.getElementById('canvas-admin-theme-list');
			
			//change theme
			$('#canvas-admin-theme-list').on('change', function(){
				
				var val = this.value;

				if(CANVASTheme.admin && $(document.adminForm).find('.canvas-changed').length > 0){

					if(CANVASTheme.active == 'base' || CANVASTheme.active == -1){
						CANVASTheme.confirm(CANVASTheme.langs.saveChange.replace('%THEME%', CANVASTheme.langs.lblDefault), function(option){
							if(option){
								CANVASTheme.nochange = 1;
								CANVASTheme.saveThemeAs(function(){
									CANVASTheme.changeTheme(val);
								});
							} else {
								setTimeout(function(){
									CANVASTheme.changeTheme(val);
								}, 250); //delay to hide popup
							}
						});
					} else {
						CANVASTheme.confirm(CANVASTheme.langs.saveChange.replace('%THEME%', CANVASTheme.active), function(option){
							if(option){
								CANVASTheme.saveTheme();

								$('#canvas-admin-thememagic-dlg').modal('hide');
							}

							CANVASTheme.changeTheme(val);
						});
					}
				} else {
					CANVASTheme.changeTheme(val);
				}
								
				return false;
			});
			
			//preview theme
			$('#canvas-admin-tm-pvbtn').on('click', function(){
				if(CANVASTheme.idle){
					CANVASTheme.applyLess();
				}

				return false;
			});
			

			if(CANVASTheme.admin){

				//save theme
				$('#canvas-admin-tm-save').on('click', function(e){
					e.preventDefault();

					if(!$(this).hasClass('disabled') && CANVASTheme.idle){
						setTimeout(CANVASTheme.saveTheme, 1);
					}
				});
				//saveas theme
				$('#canvas-admin-tm-saveas').on('click', function(e){
					e.preventDefault();
					
					if(!$(this).hasClass('disabled') && CANVASTheme.idle){
						setTimeout(CANVASTheme.saveThemeAs, 1);
					}
				});
				
				//delete theme
				$('#canvas-admin-tm-delete').on('click', function(e){
					e.preventDefault();
					
					if(!$(this).hasClass('disabled') && CANVASTheme.idle){
						setTimeout(CANVASTheme.deleteTheme, 1);
					}
				});

				$(this.serializeArray()).on('change.less', function(){
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

					jinput.closest('.control-group')[eq ? 'removeClass' : 'addClass']('canvas-changed');
				});
			}

			$(this.serializeArray()).each(function() {
				if(!$(this).attr('placeholder')){
					$(this).attr('placeholder', CANVASTheme.data.base[CANVASTheme.getName(this)]);
				}
			});

			if(CANVASTheme.active != -1){
				CANVASTheme.fillData();
			}

			$('#canvas-admin-tm-save, #canvas-admin-tm-delete').parent().toggle($('#canvas-admin-theme-list').val() != 'base');
		},

		initModalDialog: function(){
			$('#canvas-admin-thememagic-dlg').on('click', '.modal-footer a', function(){
				CANVASTheme.addtime = 500; //add time for close popup

				if($.isFunction(CANVASTheme.modalCallback)){
					CANVASTheme.modalCallback($(this).hasClass('btn-primary'));
					return false;
				} else if($(this).hasClass('btn-primary')){
					$('#canvas-admin-thememagic-dlg').modal('hide');
				}
			});

			$('#prompt-form').on('submit', function(){
				$('#canvas-admin-thememagic-dlg .modal-footer a.btn-primary').trigger('click');

				return false;
			});
		},
		
		applyLess: function(force){
			
			CANVASTheme.setProgress(0);

			var nvars = CANVASTheme.rebuildData(true),
				jsonstr = JSON.stringify(nvars);

			if(!force && CANVASTheme.jsonstr === jsonstr){
	
				CANVASTheme.setProgress(100);
			
				return false;
			}

			CANVASTheme.variables = nvars;
			CANVASTheme.jsonstr = jsonstr;

			setTimeout(function(){

				var wnd = (document.getElementById('canvas-admin-tm-ifr-preview').contentWindow || window.frames['canvas-admin-tm-ifr-preview']);
				if(wnd.location.href.indexOf('themer=') == -1){
					var urlparts = wnd.location.href.split('#');
					urlparts[0] += urlparts[0].indexOf('?') == -1 ? '?themer=1' : '&themer=1';
					wnd.location.href = urlparts.join('#');
					
				} else {
					if(!wnd.CANVASTheme || !wnd.CANVASTheme.applyLess({
							template: CANVASTheme.template,
							vars: CANVASTheme.variables,
							theme: CANVASTheme.active,
							others: CANVASTheme.themes[CANVASTheme.active]
						})){

						CANVASTheme.showMsg(CANVASTheme.langs.previewError, '', true, function(option){
							$('#canvas-admin-thememagic-dlg').modal('hide');
						});
					}
				}
			}, 10);

      // trigger preview window resize event to update display
      setTimeout(function(){
        var wnd = (document.getElementById('canvas-admin-tm-ifr-preview').contentWindow || window.frames['canvas-admin-tm-ifr-preview']),
          _$ = wnd.jQuery;
          _$(wnd).trigger('resize');
			}, 10000);			
            
			return false;
		},
		
		changeTheme: function(theme, pass){
			if($.trim(theme) == ''){
				return false;
			}
			
			//enable or disable control buttons
			$('#canvas-admin-tm-save, #canvas-admin-tm-delete').parent().toggle(theme != 'base');

			CANVASTheme.active = theme;	//store the current theme
			
			if(!pass){
				this.fillData();			//fill the data
				this.applyLess();			//refresh   	
			}
			
			return true;
		},
		
		serializeArray: function(){
			var els = [],
				allelms = document.adminForm.elements,
				pname1 = 'canvasform\\[thememagic\\]\\[.*\\]',
				pname2 = 'canvasform\\[thememagic\\]\\[.*\\]\\[\\]';
				
			for (var i = 0, il = allelms.length; i < il; i++){
				var el = allelms[i];
				
				if (el.name && (el.name.match(pname1) || el.name.match(pname2))){
					els.push(el);
				}
			}
			
			return els;
		},

		fillData: function (){
			
			var els = this.serializeArray(),
				data = CANVASTheme.data[CANVASTheme.active];
				
			if(els.length == 0 || !data){
				return;
			}
			
			$.each(els, function(){
				var name = CANVASTheme.getName(this),
					values = (data[name] != undefined) ? data[name] : '';
				
				CANVASTheme.setValues(this, $.makeArray(values));

				//store new original value
				$(this).data('org-val', (this.type == 'radio' || this.type == 'checkbox') ? $(this).prop('checked') : $(this).val());
			});

			if(typeof CANVASDepend != 'undefined'){
				CANVASDepend.update();
			}

			//reset form state when new data is filled
			CANVASTheme.updateColor();
			$(document.adminForm).find('.canvas-changed').removeClass('canvas-changed');
		},

		updateColor: function(){
			$(document.adminForm).find('.canvastm-color').each(function(){
				var hex = this.value;
				if(hex == ''){
					hex = $(this).attr('placeholder');
				}

				if(hex.charAt(0) === '@' || hex.toLowerCase() == 'inherit' || hex.toLowerCase() == 'transparent' || hex.match(/[\(\){}]/)){
					$(this).nextAll('.miniColors-triggerWrap').find('.miniColors-trigger').css('background-color', '#fff');
				} else {
					$(this).next().val(hex).trigger('keyup.miniColors');
				}
			});
		},
		
		valuesFrom: function(els){
			var vals = [];
			
			$(els).each(function(){
				var type = this.type,
					val = $.makeArray(((type == 'radio' || type == 'checkbox') && !this.checked) ? null : $(this).val());

				if(type == 'text' && !val[0]){
					val[0] = $(this).attr('placeholder');
				}

				for (var i = 0, l = val.length; i < l; i++){
					if($.inArray(val[i], vals) == -1){
						vals.push(val[i]);
					}
				}
			});
			
			return vals;
		},

		elmsFrom: function(name){
			var el = document.adminForm[name];
			if(!el){
				el = document.adminForm[name + '[]'];
			}
			
			return $(el);
		},
		
		setValues: function(el, vals){
			var jel = $(el);
			
			if(jel.prop('tagName').toUpperCase() == 'SELECT'){
				jel.val(vals);
				
				if($.makeArray(jel.val())[0] != vals[0]){

					if(CANVASTheme.placeholder && CANVASTheme.data.base[CANVASTheme.getName(el)] == vals[0]){
						jel.val('-1');
					} else {
						var name = CANVASTheme.getName(el),
							celm = CANVASTheme.elmsFrom('canvasform[thememagic][' + name + '-custom]');

						if(!celm.length){
							celm = CANVASTheme.elmsFrom('canvasform[thememagic][' + name + '_custom]');						
						}

						if(celm.length){
							jel.val('undefined').trigger('change.depend');

							//CANVASTheme.setValues(celm, vals);
						} else {
							jel.val('-1');
						}
					}
				}
			}else {
				if(jel.prop('type') == 'checkbox' || jel.prop('type') == 'radio'){
					jel.prop('checked', $.inArray(el.value, vals) != -1).trigger('change.depend');

				} else {
					jel.val(vals[0]);

					if(CANVASTheme.placeholder && CANVASTheme.data.base[CANVASTheme.getName(el)] == vals[0]){
						jel.val('');
					}
				}
			}
		},
		
		rebuildData: function(optimize){
			var els = this.serializeArray(),
				json = {};
				
			$.each(els, function(){
				var values = CANVASTheme.valuesFrom(this);
				if(values.length && values[0] != '' && (!optimize || (optimize && !this._disabled))){
					var name = CANVASTheme.getName(this),
						val = this.name.substr(-2) == '[]' ? values : values[0],
						adjust = null,
						filter = this.className.match(/canvastm-(\w*)\s?/);

					if(filter && $.isFunction(CANVASTheme['filter' + filter[1]])){
						adjust = CANVASTheme['filter' + filter[1]](val);
					}

					if(adjust != null && adjust != val){
						val = adjust;
						CANVASTheme.setValues(this, $.makeArray(val));
					}

					json[name] = val;
				}
			});

			for(var k in json){
				if(json.hasOwnProperty(k)){
					
					if(json[k] == 'undefined' || json[k] == ''){
						delete json[k];
					} else {
						if(k.match(/([_-])custom/)){
							json[k.replace(/[_-]custom/, '')] = json[k];	
						}
					}
				}
			}
			
			return json;
		},

		filtercolor: function(hex){
			if(hex.charAt(0) === '@' || hex.toLowerCase() == 'inherit' || hex.toLowerCase() == 'transparent' || CANVASTheme.colors[hex.toLowerCase()] || hex.match(/[\(\){}]/)){
				return hex;
			}

			if(!/^#(?:[0-9a-fA-F]{3}){1,2}$/.test(hex)){
				hex = hex.replace(/[^A-F0-9]/ig, '');
				hex = hex.substr(0, 6);

				if(hex.length !== 3 && hex.length !== 6){
					hex = CANVASTheme.padding(hex, hex.length < 3 ? 3 : 6);
				}

				hex = '#' + hex;
			}

			return hex;
		},

		filterdimension: function(val){
			val = /^(-?\d*\.?\d+)(px|%|em|rem|pc|ex|in|deg|s|ms|pt|cm|mm|rad|grad|turn)?/.exec(val);
			if(val && val[1]){
				val = val[1] + (val[2] || 'px');
			} else {
				val = '0px';
			}

			return val;
		},

		filterfont: function(val){			
			val = val.split(',');
			if(val.length > 1){
				for(var i = 0; i < val.length; i++){
					if($.trim(val[i]).indexOf(' ') !== -1){
						val[i] = '\'' + val[i].replace(/['"]/g, '') + '\'';
					}
				}
			}

			val = val.join(', ');
			return val.replace(/\s+/g, ' ');
		},

		padding: function(str, limit, pad){
			pad = pad || '0';

			while(str.length < limit){
				str = pad + str;
			}

			return str;
		},
		
		getName: function(el){
			var matches = (el.name || el[0].name).match('canvasform\\[thememagic\\]\\[([^\\]]*)\\]');
			if (matches){
				return matches[1];
			}
			
			return '';
		},
		
		deleteTheme: function(){

			CANVASTheme.confirm(CANVASTheme.langs.delTheme, function(option){
				if(option){
					CANVASTheme.submitForm({
						canvastask: 'delete',
						theme: CANVASTheme.active
					});

					$('#canvas-admin-thememagic-dlg').modal('hide');
				}
			});
		},
		
		cloneTheme: function(){
			CANVASTheme.prompt(CANVASTheme.langs.addTheme, function(option){
				if(option){
					var nname = $('#theme-name').val();
					if(nname){
						nname = nname.replace(/[^0-9a-zA-Z_-]/g, '').replace(/ /, '').toLowerCase();
						if(nname == ''){
							CANVASTheme.alert('warning', CANVASTheme.langs.correctName);
							return CANVASTheme.cloneTheme();
						}
						
						CANVASTheme.data[nname] = CANVASTheme.data[CANVASTheme.active];
						CANVASTheme.themes[nname] = $.extend({}, CANVASTheme.themes[CANVASTheme.active]);
						
						CANVASTheme.submitForm({
							canvastask: 'duplicate',
							theme: nname,
							from: CANVASTheme.active
						});
					}

					$('#canvas-admin-thememagic-dlg').modal('hide');
				}
			});
			
			return true;
		},
		
		saveTheme: function(){
			CANVASTheme.data[CANVASTheme.active] = CANVASTheme.rebuildData();
			CANVASTheme.submitForm({
				canvastask: 'save',
				theme: CANVASTheme.active
			}, CANVASTheme.data[CANVASTheme.active])		
		},
		
		saveThemeAs: function(callback){
			CANVASTheme.prompt(CANVASTheme.langs.addTheme, function(option){
				if(option){

					var nname = $('#theme-name').val() || '';
					nname = nname.replace(/[^0-9a-zA-Z_-]/g, '').replace(/ /, '').toLowerCase();

					if(nname == ''){

						CANVASTheme.saveThemeAs(callback);
						CANVASTheme.showMsg(CANVASTheme.langs.correctName);
						
						return false;
					} else if(CANVASTheme.themes && CANVASTheme.themes[nname] && nname != CANVASTheme.active){
						return CANVASTheme.confirm(CANVASTheme.langs.overwriteTheme.replace('%THEME%', nname), function(option){
							if(option){
								
								$('#canvas-admin-thememagic-dlg').modal('hide');

								CANVASTheme.active = nname;
								CANVASTheme.saveTheme();
								$(CANVASTheme.jel).val(nname);

								if($.isFunction(callback)){
									callback();
								}
							}
						});
					}
					
					CANVASTheme.data[nname] = CANVASTheme.rebuildData();
					CANVASTheme.themes[nname] = $.extend({}, CANVASTheme.themes[CANVASTheme.active]);

					CANVASTheme.submitForm({
						canvastask: 'save',
						theme: nname,
						from: CANVASTheme.active
					}, CANVASTheme.data[nname]);
				

					$('#canvas-admin-thememagic-dlg').modal('hide');
				}

				if($.isFunction(callback)){
					callback();
				}

				return true;
			});

			return true;
		},

		//simple progress bar
		setProgress: function(ajax, less){
			var jpg = $('#canvas-admin-tm-recss'),
				ajaxp = typeof ajax != 'undefined' ? ajax : ((jpg.data('ajaxpercent') || 100)),
				lessp = typeof less != 'undefined' ? less : ((jpg.data('lesspercent') || 100)),
				percent = Math.max((ajaxp + lessp) / 2, 1);

			if(jpg.hasClass('canvas-anim-finish')){
				jpg.removeClass('canvas-anim-slow canvas-anim-finish').css('width', '0%');
			}

			jpg
				.data('ajaxpercent', ajaxp)
				.data('lesspercent', lessp)
				.addClass('canvas-anim-slow')
				.css('width', percent + '%');
			
			clearTimeout(CANVASTheme.progressid);

			if(percent >= 100){
				jpg
					.removeClass('canvas-anim-slow')
					.addClass('canvas-anim-finish')
					.one($.support.transition.end, function () {
						setTimeout(function(){
							if(jpg.hasClass('canvas-anim-finish')){
								jpg.removeClass('canvas-anim-finish').css('width', '0%');
							}
						}, 1000);
					});

				CANVASTheme.idle = true;
			} else {
				CANVASTheme.idle = false;
			}
		},
		
		submitForm: function(params, data){
			if(CANVASTheme.run){
				CANVASTheme.ajax.abort();
			}

			//set initial to 1%
			CANVASTheme.setProgress(1);

			clearTimeout(CANVASTheme.progressid);
			CANVASTheme.progressid = setTimeout(function(){
				CANVASTheme.setProgress(10);
			}, 500);
			
			CANVASTheme.run = true;
			CANVASTheme.ajax = $.post(
				CANVASTheme.url + (CANVASTheme.url.indexOf('?') != -1 ? '' : '?') +
				$.param($.extend(params, {
					canvasaction: 'theme',
					canvastemplate: CANVASTheme.template,
					styleid: CANVASTheme.templateid,
					rand: Math.random()
				})) , data, function(result){
					
				CANVASTheme.run = false;

				clearTimeout(CANVASTheme.progressid);
				CANVASTheme.setProgress(100);

				if(result == ''){
					return;
				}
				
				try {
					result = $.parseJSON(result);
				} catch (e) {
					result = { error: CANVASTheme.langs.unknownError };
				}

				CANVASTheme.alert(result.error || result.success, result.error ? 'error' : (result.success ? 'success' : 'info'), result.theme);

				if(result.theme){
					
					var jel = CANVASTheme.jel;

					switch (result.type){	
						
						case 'new':
						case 'duplicate':			
							jel.options[jel.options.length] = new Option(result.theme, result.theme);							
							
							if(!CANVASTheme.nochange){
								jel.options[jel.options.length - 1].selected = true;
								CANVASTheme.changeTheme(result.theme, true);
								CANVASTheme.nochange = 0;
							}
						break;
						
						case 'delete':
							var opts = jel.options;
							
							for(var j = 0, jl = opts.length; j < jl; j++){
								if(opts[j].value == result.theme){
									jel.remove(j);
									break;
								}
							}

							try {
								delete CANVASTheme.themes[result.theme];
							} catch(e){
								CANVASTheme.themes[result.theme] = null;
							}

							jel.options[0].selected = true;					
							CANVASTheme.changeTheme(jel.options[0].value);
						break;

						default:
						break;
					}

					if(result.type != 'delete'){
						$(document.adminForm).find('.canvas-changed').removeClass('canvas-changed');
					}
				}
			});
		},

		alert: function(msg, type, title){
			$('#canvas-admin-thememagic .alert').remove();

			CANVASTheme.jalert = $([
				'<div class="alert alert-', (type || 'info'), '">',
					'<button type="button" class="close" data-dismiss="alert">×</button>',
					(title ? '<h4 class="alert-heading">' + title + '</h4>' : ''),
					'<p>', msg, '</p>',
				'</div>'].join(''))
				.prependTo($('#canvas-admin-tm-variable-form'))
				.on('closed', function(){
					clearTimeout(CANVASTheme.salert);
					CANVASTheme.jalert = null;
				}).alert();

			clearTimeout(CANVASTheme.salert);
			CANVASTheme.salert = setTimeout(function(){
				if(CANVASTheme.jalert){
					CANVASTheme.jalert.alert('close');
					CANVASTheme.jalert = null;
				}
			}, 10000);
		},

		showMsg: function(msg, type, hideprompt, callback){
			if(callback && $.isFunction(callback)){
				CANVASTheme.modalCallback = callback;
			}

			var jdialog = $('#canvas-admin-thememagic-dlg');

			jdialog.find('.message-block').show().html('<div class="alert fade in">' + msg + '</div>');
			if(hideprompt){
				jdialog.find('.prompt-block').hide();
			}
			
			jdialog.find('.cancel').html(CANVASTheme.langs.lblCancel);
			jdialog.find('.btn-primary').html(CANVASTheme.langs.lblOk);

			jdialog.modal('show');
		},

		confirm: function(msg, callback){
			CANVASTheme.modalCallback = callback;

			var jdialog = $('#canvas-admin-thememagic-dlg');
			jdialog.find('.prompt-block').hide();
			jdialog.find('.message-block').show().html(msg);
			jdialog.find('.cancel').html(CANVASTheme.langs.lblNo);
			jdialog.find('.btn-primary').html(CANVASTheme.langs.lblYes);

			jdialog.removeClass('modal-prompt modal-alert')
				.addClass('modal-confirm')
				.modal('show');
		},

		prompt: function(msg, callback){
			CANVASTheme.modalCallback = callback;

			var jdialog = $('#canvas-admin-thememagic-dlg');
			jdialog.find('.message-block').hide();
			jdialog.find('.prompt-block').show().find('span').html(msg);
			jdialog.find('.cancel').html(CANVASTheme.langs.lblCancel);
			jdialog.find('.btn-primary').html(CANVASTheme.langs.lblOk);

			jdialog.removeClass('modal-alert modal-confirm')
				.addClass('modal-prompt')
				.modal('show');
		},
		
		onCompile: function(completed, total){
			CANVASTheme.setProgress(undefined, Math.max(1, Math.ceil(completed / total * 100)));
		}
	});

	$(document).ready(function(){
		CANVASTheme.initialize();
	});
	
}(jQuery);

!function ($) {
	
	$(document).ready(function(){
		if(typeof MooRainbow == 'undefined'){ //only initialize when there was no Joomla default color picker

			$.extend(CANVASTheme, {

				colors: {
					aliceblue: '#F0F8FF',
					antiquewhite: '#FAEBD7',
					aqua: '#00FFFF',
					aquamarine: '#7FFFD4',
					azure: '#F0FFFF',
					beige: '#F5F5DC',
					bisque: '#FFE4C4',
					black: '#000000',
					blanchedalmond: '#FFEBCD',
					blue: '#0000FF',
					blueviolet: '#8A2BE2',
					brown: '#A52A2A',
					burlywood: '#DEB887',
					cadetblue: '#5F9EA0',
					chartreuse: '#7FFF00',
					chocolate: '#D2691E',
					coral: '#FF7F50',
					cornflowerblue: '#6495ED',
					cornsilk: '#FFF8DC',
					crimson: '#DC143C',
					cyan: '#00FFFF',
					darkblue: '#00008B',
					darkcyan: '#008B8B',
					darkgoldenrod: '#B8860B',
					darkgray: '#A9A9A9',
					darkgrey: '#A9A9A9',
					darkgreen: '#006400',
					darkkhaki: '#BDB76B',
					darkmagenta: '#8B008B',
					darkolivegreen: '#556B2F',
					darkorange: '#FF8C00',
					darkorchid: '#9932CC',
					darkred: '#8B0000',
					darksalmon: '#E9967A',
					darkseagreen: '#8FBC8F',
					darkslateblue: '#483D8B',
					darkslategray: '#2F4F4F',
					darkslategrey: '#2F4F4F',
					darkturquoise: '#00CED1',
					darkviolet: '#9400D3',
					deeppink: '#FF1493',
					deepskyblue: '#00BFFF',
					dimgray: '#696969',
					dimgrey: '#696969',
					dodgerblue: '#1E90FF',
					firebrick: '#B22222',
					floralwhite: '#FFFAF0',
					forestgreen: '#228B22',
					fuchsia: '#FF00FF',
					gainsboro: '#DCDCDC',
					ghostwhite: '#F8F8FF',
					gold: '#FFD700',
					goldenrod: '#DAA520',
					gray: '#808080',
					grey: '#808080',
					green: '#008000',
					greenyellow: '#ADFF2F',
					honeydew: '#F0FFF0',
					hotpink: '#FF69B4',
					indianred : '#CD5C5C',
					indigo : '#4B0082',
					ivory: '#FFFFF0',
					khaki: '#F0E68C',
					lavender: '#E6E6FA',
					lavenderblush: '#FFF0F5',
					lawngreen: '#7CFC00',
					lemonchiffon: '#FFFACD',
					lightblue: '#ADD8E6',
					lightcoral: '#F08080',
					lightcyan: '#E0FFFF',
					lightgoldenrodyellow: '#FAFAD2',
					lightgray: '#D3D3D3',
					lightgrey: '#D3D3D3',
					lightgreen: '#90EE90',
					lightpink: '#FFB6C1',
					lightsalmon: '#FFA07A',
					lightseagreen: '#20B2AA',
					lightskyblue: '#87CEFA',
					lightslategray: '#778899',
					lightslategrey: '#778899',
					lightsteelblue: '#B0C4DE',
					lightyellow: '#FFFFE0',
					lime: '#00FF00',
					limegreen: '#32CD32',
					linen: '#FAF0E6',
					magenta: '#FF00FF',
					maroon: '#800000',
					mediumaquamarine: '#66CDAA',
					mediumblue: '#0000CD',
					mediumorchid: '#BA55D3',
					mediumpurple: '#9370D8',
					mediumseagreen: '#3CB371',
					mediumslateblue: '#7B68EE',
					mediumspringgreen: '#00FA9A',
					mediumturquoise: '#48D1CC',
					mediumvioletred: '#C71585',
					midnightblue: '#191970',
					mintcream: '#F5FFFA',
					mistyrose: '#FFE4E1',
					moccasin: '#FFE4B5',
					navajowhite: '#FFDEAD',
					navy: '#000080',
					oldlace: '#FDF5E6',
					olive: '#808000',
					olivedrab: '#6B8E23',
					orange: '#FFA500',
					orangered: '#FF4500',
					orchid: '#DA70D6',
					palegoldenrod: '#EEE8AA',
					palegreen: '#98FB98',
					paleturquoise: '#AFEEEE',
					palevioletred: '#D87093',
					papayawhip: '#FFEFD5',
					peachpuff: '#FFDAB9',
					peru: '#CD853F',
					pink: '#FFC0CB',
					plum: '#DDA0DD',
					powderblue: '#B0E0E6',
					purple: '#800080',
					red: '#FF0000',
					rosybrown: '#BC8F8F',
					royalblue: '#4169E1',
					saddlebrown: '#8B4513',
					salmon: '#FA8072',
					sandybrown: '#F4A460',
					seagreen: '#2E8B57',
					seashell: '#FFF5EE',
					sienna: '#A0522D',
					silver: '#C0C0C0',
					skyblue: '#87CEEB',
					slateblue: '#6A5ACD',
					slategray: '#708090',
					slategrey: '#708090',
					snow: '#FFFAFA',
					springgreen: '#00FF7F',
					steelblue: '#4682B4',
					tan: '#D2B48C',
					teal: '#008080',
					thistle: '#D8BFD8',
					tomato: '#FF6347',
					turquoise: '#40E0D0',
					violet: '#EE82EE',
					wheat: '#F5DEB3',
					white: '#FFFFFF',
					whitesmoke: '#F5F5F5',
					yellow: '#FFFF00',
					yellowgreen: '#9ACD32'
				},

				cleanHex: function(hex) {
					return hex.replace(/[^A-F0-9]/ig, '');
				},

				expandHex: function(hex) {
					hex = CANVASTheme.cleanHex(hex);
					if( !hex ) return null;
					if( hex.length === 3 ) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
					return hex.length === 6 ? hex : null;
				}
			});

			$('.input-colorpicker, .minicolors, .canvastm-color').on('keyup.canvascolor paste.canvascolor', function(e){
				if( e.keyCode === 9 ) {
					this.value = $(this).next().val();
				} else {
					var color = $.trim(this.value);
					if(!color){
						color = $(this).attr('placeholder');
					}

					if(color.charAt(0) === '@' || color.toLowerCase() == 'inherit' || color.toLowerCase() == 'transparent' || color.match(/[\(\){}]/)){
						$(this).nextAll('.miniColors-triggerWrap').find('.miniColors-trigger').css('background-color', '#fff');
						return;
					}

					color = CANVASTheme.colors[$.trim(this.value.toLowerCase())];

					if(!color){
						color = CANVASTheme.expandHex(this.value);
					}
					
					if(color){
						$(this).next().data('canvasforce', 1).val(color).trigger('keyup.miniColors');
					}
				}	
			}).after('<input type="hidden" />').next().miniColors({
				opacity: true,
				change: function(hex, rgba) {
					if($(this).data('canvasforce')){
						$(this).data('canvasforce', 0);
					} else {
						$(this).prev().val(hex).trigger('change.less');
					}
				}
			});
		}
	});
	
}(jQuery);
