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

CANVASAdminLayout = window.CANVASAdminLayout || {};

!function ($) {

	$.extend(CANVASAdminLayout, {
		layout: {
			maxcol: {
				'default': 6,
				'normal': 6,
				'wide': 6,
				'xtablet': 4,
				'tablet': 3,
				'mobile': 2
			},
			minspan: {
				'default': 2,
				'normal': 2,
				'wide': 2,
				'xtablet': 3,
				'tablet': 4,
				'mobile': 6
			},
			unitspan: {
				'default': 1,
				'normal': 1,
				'wide': 1,
				'xtablet': 1,
				'tablet': 1,
				'mobile': 6
			},
			clayout: 'default',
			nlayout: 'default',
			maxgrid: 12,
			maxcols: 6,
			mode: 0,
			spancls: /(\s*)span(\d+)(\s*)/g,
			spanptrn: 'span{width}',
			span: 'span',
			rspace: /\s+/,
			rclass: /[\t\r\n]/g
		},

		initPreSubmit: function(){

			var form = document.adminForm;
			if(!form){
				return false;
			}

			var onsubmit = form.onsubmit;

			form.onsubmit = function(e){

				(form.task.value && form.task.value.indexOf('.cancel') != -1) ?
					($.isFunction(onsubmit) ? onsubmit() : false) : CANVASAdminLayout.canvassavelayout(onsubmit);
			};
		},

		initPrepareLayout: function(){
			var jlayout = $('#canvas-admin-layout').appendTo($('#jform_params_mainlayout').closest('.controls')),
				jelms = $('#canvas-admin-layout-container'),
				jdevices = jlayout.find('.canvas-admin-layout-devices'),
				jresetdevice = jlayout.find('.canvas-admin-layout-reset-device'),
				jresetposition = jlayout.find('.canvas-admin-layout-reset-position'),
				jresetall = jlayout.find('.canvas-admin-layout-reset-all'),
				jfullscreen = jlayout.find('.canvas-admin-tog-fullscreen'),
				jselect = $('#canvas-admin-layout-tpl-positions');

			jlayout
				.find('.canvas-admin-layout-modes')
				.on('click', 'li', function(){
					if($(this).hasClass('canvas-admin-layout-mode-layout')){
						jelms.removeClass('canvas-admin-layout-mode-m').addClass('canvas-admin-layout-mode-r');
						CANVASAdminLayout.layout.mode = 1;

						jdevices.removeClass('hide');
						jresetdevice.removeClass('hide');
						jresetposition.addClass('hide');
						jselect.hide();

						jelms.find('.canvas-admin-layout-vis').each(CANVASAdminLayout.canvasupdatevisible);
						jdevices.find('[data-device]:first').removeClass('active').trigger('click');
					} else {
						jelms.removeClass('canvas-admin-layout-mode-r').addClass('canvas-admin-layout-mode-m');
						CANVASAdminLayout.layout.mode = 0;

						jdevices.addClass('hide');
						jresetdevice.addClass('hide');
						jresetposition.removeClass('hide');

						jelms.removeClass(CANVASAdminLayout.layout.clayout).addClass(CANVASAdminLayout.layout.dlayout);
						CANVASAdminLayout.canvasupdatedevice(CANVASAdminLayout.layout.dlayout);
					}

					$(this).addClass('active').siblings().removeClass('active');
					return false;
				});

			jdevices.on('click', '.btn', function(e){
				if(!$(this).hasClass('active')){
					var nlayout = $(this).attr('data-device');
					$(this).addClass('active').siblings('.active').removeClass('active');

					jelms.removeClass(CANVASAdminLayout.layout.clayout);
					jelms.addClass(nlayout);

					CANVASAdminLayout.canvasupdatedevice(nlayout);
				}

				return false;
			});

			CANVASAdminLayout.jresetall = jresetall.on('click', CANVASAdminLayout.canvasresetall);
			CANVASAdminLayout.jfullscreen = jfullscreen.on('click', CANVASAdminLayout.canvasfullscreen);
			CANVASAdminLayout.jresetposition = jresetposition.on('click', CANVASAdminLayout.canvasresetposition);
			CANVASAdminLayout.jresetdevice = jresetdevice.on('click', CANVASAdminLayout.canvasresetdevice);
			CANVASAdminLayout.jselect = jselect.appendTo(document.body).on('click', function(e){ return false; });
			CANVASAdminLayout.jallpos = jselect.find('select');

			CANVASAdminLayout.jallpos.on('change', function(){

				var curspan = CANVASAdminLayout.curspan;

				if(curspan){
					$(curspan).parent().removeClass('pos-off pos-active').find('h3').html(this.value || CANVASAdmin.langs.emptyLayoutPosition);
					$(this).closest('.popover').hide();

					var jspl = $(curspan).parent().parent().parent();
					if(jspl.attr('data-spotlight')){
						var spanidx = $(curspan).closest('.canvas-admin-layout-unit').index();
						jspl.nextAll('.canvas-admin-layout-hiddenpos').children().eq(spanidx).html((this.value || CANVASAdmin.langs.emptyLayoutPosition) + '<i class="icon-eye-close">');
					} else {
						$(curspan).parent().next('.canvas-admin-layout-hiddenpos').children().html((this.value || CANVASAdmin.langs.emptyLayoutPosition) + '<i class="icon-eye-close">');
					}

					if(!this.value){
						$(curspan).parent().addClass('pos-off');
					}

					$(this)
						.next('.canvas-admin-layout-rmvbtn').toggleClass('disabled', !this.value)
						.next('.canvas-admin-layout-combtn').toggleClass('disabled', this.value == 'core-component')
						.next('.canvas-admin-layout-defbtn').toggleClass('disabled', this.value == $(curspan).closest('[data-original]').attr('data-original'));
				}

				return false;
			}).on('mousedown', 'optgroup', function(e){

				if(e.target && e.target.tagName.toLowerCase() == 'optgroup'){
					return false;
				}
			});

			jselect.find('.canvas-admin-layout-rmvbtn, .canvas-admin-layout-defbtn, .canvas-admin-layout-combtn')
				.on('click', function(){
					var curspan = CANVASAdminLayout.curspan;

					if(curspan && !$(this).hasClass('disabled')){
						if($(this).hasClass('canvas-admin-layout-combtn')){
							var vdef = $(this).hasClass('canvas-admin-layout-combtn') ? 'core-component' : '';
						}else{
							var vdef = $(this).hasClass('canvas-admin-layout-defbtn') ? $(curspan).closest('[data-original]').attr('data-original') : '';
						}
						
						CANVASAdminLayout.jallpos.val(vdef).trigger('change');
					}

					return false;
				});

			$(document).off('click.canvaslayout').on('click.canvaslayout', function(){
				var curspan = CANVASAdminLayout.curspan;

				if(curspan){
					$(curspan).parent().removeClass('pos-active');
				}

				jselect.hide();
			});

			$(window).load(function(){
				setTimeout(function(){
					$('#jform_params_mainlayout').trigger('change.less');
				}, 500);
			});
		},

		initMarkChange: function(){
			clearTimeout(CANVASAdminLayout.chsid);

			var jgroup = $('#canvas-admin-layout').closest('.control-group'),
				jpane = jgroup.closest('.tab-pane'),
				jtab = $('.canvas-admin-nav .nav li').eq(jpane.index()),

			check = function(){
				if(!jgroup.data('chretain')){
					var eq = JSON.stringify(CANVASAdminLayout.canvasgetlayoutdata()) == CANVASAdminLayout.curconfig;

					jgroup.toggleClass('canvas-changed', !eq);

					jtab.toggleClass('canvas-changed', !!(!eq || jpane.find('.canvas-changed').length));
				}

				CANVASAdminLayout.chsid = setTimeout(check, 1500);
			};

			CANVASAdminLayout.curconfig = JSON.stringify(CANVASAdminLayout.canvasgetlayoutdata());
			CANVASAdminLayout.chsid = setTimeout(check, 1500);
		},

		initChosen: function(){
			//remove chosen on position list
			var jtplpos = $('#tpl-positions-list');
			if(jtplpos.hasClass('chzn-done')){
				var chosen = jtplpos.data('chosen');
				if(chosen && chosen.destroy) {
					chosen.destroy();
				} else {
					jtplpos
						.removeClass('chzn-done').show()
						.next().remove();
				}
			}
		},

		initLayoutClone: function(){
			$('#canvas-admin-layout-clone-dlg')
				.on('show', function(){
					$('#canvas-admin-layout-cloned-name').val($('#jform_params_mainlayout').val() + '-copy');
				})
				.on('shown', function(){
					$('#canvas-admin-layout-cloned-name').focus();
				});

			$('#canvas-admin-layout-clone-btns')
				.insertAfter('#jform_params_mainlayout');
			$('#canvas-admin-layout-clone-copy').on('click', function(){
                CANVASAdminLayout.prompt(CANVASAdmin.langs.askCloneLayout, CANVASAdminLayout.canvasclonelayout);
                return false;
            });
            $('#canvas-admin-layout-clone-delete').on('click', function(){
                CANVASAdminLayout.confirm(CANVASAdmin.langs.askDeleteLayout, CANVASAdmin.langs.askDeleteLayoutDesc, CANVASAdminLayout.canvasdeletelayout);
                return false;
            });
            $('#canvas-admin-layout-clone-purge').on('click', function(){
                CANVASAdminLayout.confirm(CANVASAdmin.langs.askPurgeLayout, CANVASAdmin.langs.askPurgeLayoutDesc, CANVASAdminLayout.canvaspurgelayout);
                return false;
            });
		},

		initModalDialog: function(){
			$('#canvas-admin-layout-clone-dlg')
				.appendTo(document.body)
				.prop('hide', false) //remove mootool hide function
				.on('click', '.modal-footer button', function(e){
					if($.isFunction(CANVASAdminLayout.modalCallback)){
						CANVASAdminLayout.modalCallback($(this).hasClass('yes'));
					} else if($(this).hasClass('yes')){
						$('#canvas-admin-layout-clone-dlg').modal('hide');
					}
					return false;
				})
				.find('.form-horizontal').on('submit', function(){
					$('#canvas-admin-layout-clone-dlg .modal-footer .yes').trigger('click');

					return false;
				});
		},

		alert: function(msg, type, title, placeholder){
			//remove
			$('#canvas-admin-layout-alert').remove();

			//add new
			$([
				'<div id="canvas-admin-layout-alert" class="alert alert-', (type || 'info'), '">',
					'<button type="button" class="close" data-dismiss="alert">×</button>',
					(title ? '<h4 class="alert-heading">' + title + '</h4>' : ''),
					'<p>', msg, '</p>',
				'</div>'].join(''))
			.appendTo(placeholder || $('#system-message').show())
			.alert();
		},

		confirm: function(title, msg, callback){
			CANVASAdminLayout.modalCallback = callback;

			var jdialog = $('#canvas-admin-layout-clone-dlg');
			jdialog.find('h3').html(title);
			jdialog.find('.prompt-block').hide();
			jdialog.find('.message-block').show().html(msg);
			jdialog.find('.btn-danger').show();
			jdialog.find('.btn-success').hide();

			jdialog.removeClass('modal-prompt modal-alert')
				.addClass('modal-confirm')
				.modal('show');
		},

		prompt: function(msg, callback){
			CANVASAdminLayout.modalCallback = callback;

			var jdialog = $('#canvas-admin-layout-clone-dlg');
			jdialog.find('h3').html(msg);
			jdialog.find('.message-block').hide();
			jdialog.find('.prompt-block').show();
			jdialog.find('.btn-success').show();
			jdialog.find('.btn-danger').hide();

			jdialog.removeClass('modal-alert modal-confirm')
				.addClass('modal-prompt')
				.modal('show');
		},

		canvasreset: function(){
			var jlayout = $('#canvas-admin-layout'),
				jelms = $('#canvas-admin-layout-container');

			jelms.removeClass('canvas-admin-layout-mode-r').addClass('canvas-admin-layout-mode-m');
			jelms.removeClass(CANVASAdminLayout.layout.clayout).addClass(CANVASAdminLayout.layout.dlayout);

			CANVASAdminLayout.layout.mode = 0;
			CANVASAdminLayout.layout.clayout = CANVASAdminLayout.layout.dlayout;

			jlayout.find('.canvas-admin-layout-mode-structure').addClass('active').siblings().removeClass('active');
			jlayout.find('.canvas-admin-layout-devices').addClass('hide');
			jlayout.find('.canvas-admin-layout-reset-device').addClass('hide');
			jlayout.find('.canvas-admin-layout-reset-position').removeClass('hide');
		},

		canvasclonelayout: function(ok){
			if(ok != undefined && !ok){
				return false;
			}

			var nname = $('#canvas-admin-layout-cloned-name').val();
			if(nname){
				nname = nname.replace(/[^0-9a-zA-Z_-]/g, '').replace(/ /, '').toLowerCase();
			}

			if(nname == ''){
				CANVASAdminLayout.alert(CANVASAdmin.langs.correctLayoutName, 'warning', '', $('#canvas-admin-layout-cloned-name').parent());
				return false;
			}

			CANVASAdminLayout.submit({
				canvasaction: 'layout',
				canvastask: 'copy',
				template: CANVASAdmin.template,
				original: $('#jform_params_mainlayout').val(),
				layout: nname
			}, CANVASAdminLayout.canvasgetlayoutdata(), function(json){
				if(typeof json == 'object'){
					if(json && (json.error || json.successful)){
						CANVASAdmin.systemMessage(json.error || json.successful);
					}

					if(json.successful){
						var mainlayout = document.getElementById('jform_params_mainlayout');
						mainlayout.options[mainlayout.options.length] = new Option(json.layout, json.layout);
						mainlayout.options[mainlayout.options.length - 1].selected = true;
						$(mainlayout).trigger('change.less').trigger('liszt:updated');
					}
				}
			});
		},

		canvasdeletelayout: function(ok){
			if(ok != undefined && !ok){
				return false;
			}

			var nname = $('#jform_params_mainlayout').val();
			
			if(nname == ''){
				return false;
			}

			CANVASAdminLayout.submit({
				canvasaction: 'layout',
				canvastask: 'delete',
				template: CANVASAdmin.template,
				layout: nname
			}, function(json){
				if(typeof json == 'object'){
					if(json.successful && json.layout){
						var mainlayout = document.getElementById('jform_params_mainlayout'),
							options = mainlayout.options;

						for(var j = 0, jl = options.length; j < jl; j++){
							if(options[j].value == json.layout){
								mainlayout.remove(j);
								break;
							}
						}
					
						options[0].selected = true;
						$(mainlayout).trigger('change.less').trigger('liszt:updated');
					}
				}
			});
		},

		canvaspurgelayout: function(ok){
			if(ok != undefined && !ok){
				return false;
			}

			var nname = $('#jform_params_mainlayout').val();

			if(nname == ''){
				return false;
			}

			CANVASAdminLayout.submit({
				canvasaction: 'layout',
				canvastask: 'purge',
				template: CANVASAdmin.template,
				layout: nname
			}, function(json){
				if(typeof json == 'object'){
					if(json.successful && json.layout){
						var mainlayout = document.getElementById('jform_params_mainlayout'),
							options = mainlayout.options;

						for(var j = 0, jl = options.length; j < jl; j++){
							if(options[j].value == json.layout){
								mainlayout.remove(j);
								break;
							}
						}

						options[0].selected = true;
						$(mainlayout).trigger('change.less').trigger('liszt:updated');
					}
				}
			});
		},

		canvassavelayout: function(callback){
			//TODO::TEST
			//console.log(CANVASAdminLayout.canvasgetlayoutdata());
			$.ajax({
				async: false,
				url: CANVASAdminLayout.mergeurl(
					$.param({
						canvasaction: 'layout',
						canvastask: 'save',
						template: CANVASAdmin.template,
						layout: $('#jform_params_mainlayout').val()
					})
				),
				type: 'post',
				data: CANVASAdminLayout.canvasgetlayoutdata(),
				complete: function(){
					if($.isFunction(callback)){
						callback();
					}
				}
			});
			CANVASAdminLayout.canvassaveblocksettings();
			return false;
		},

		canvasgetlayoutdata: function(){
			var json = {},
				jcontainer = $(document.adminForm).find('.canvas-admin-layout-container'),
				jblocks = jcontainer.find('.canvas-admin-layout-pos'),
				jspls = jcontainer.find('[data-spotlight]'),
				jsplblocks = jspls.find('.canvas-admin-layout-pos');

			jblocks.not(jspls).not(jsplblocks).not('.canvas-admin-layout-uneditable').each(function(){
				var name = $(this).attr('data-original'),
					val = $(this).find('.canvas-admin-layout-posname').html(),
					vis = $(this).closest('[data-vis]').data('data-vis'),
					others = $(this).closest('[data-others]').data('data-others'),
					info = CANVASAdminLayout.canvasemptydv();
					
				info.position = val ? val : '';

				if(vis){
					vis = CANVASAdminLayout.canvasvisible(0, vis.vals);
					CANVASAdminLayout.canvasformatvisible(info, vis);
					CANVASAdminLayout.canvasformatothers(info, others);
				}

				//optimize
				CANVASAdminLayout.canvasopimizeparam(info);

				json[name] = info;
			});
			
			jspls.each(function(){
				var name = $(this).attr('data-spotlight'),
					vis = $(this).data('data-vis'),
					widths = $(this).data('data-widths'),
					firsts = $(this).data('data-firsts'),
					others = $(this).data('data-others');

				$(this).children().each(function(idx){
					var jpos = $(this),
						//pname = jpos.find('.canvas-admin-layout-pos').attr('data-original'),
						val = jpos.find('.canvas-admin-layout-posname').html(),
						info = CANVASAdminLayout.canvasemptydv(),
						width = CANVASAdminLayout.canvasgetwidth(idx, widths),
						visible = CANVASAdminLayout.canvasvisible(idx, vis.vals),
						first = CANVASAdminLayout.canvasfirst(idx, firsts),
						other = CANVASAdminLayout.canvasothers(idx, others);
					
					info.position = val ? val : '';
					
					CANVASAdminLayout.canvasformatwidth(info, width);
					CANVASAdminLayout.canvasformatvisible(info, visible);
					CANVASAdminLayout.canvasformatfirst(info, first);
					CANVASAdminLayout.canvasformatothers(info, other);

					//optimize
					CANVASAdminLayout.canvasopimizeparam(info);

					json['block' + (idx + 1) + '@' + name] = info;
				
				});
			});

			return json;
		},
		
		//TODO:: creating new func to store the blocksettings data 
		canvassaveblocksettings: function(){
			CANVASAdminLayout.canvasgetblocksettingsdata();
			return;
			$.ajax({
				async: false,
				url: CANVASAdminLayout.mergeurl(
					$.param({
						canvasaction: 'layout',
						canvastask: 'save',
						template: CANVASAdmin.template,
						layout: $('#jform_params_mainlayout').val()
					})
				),
				type: 'post',
				data: CANVASAdminLayout.canvasgetblocksettingsdata(),
				complete: function(){
					if($.isFunction(callback)){
						callback();
					}
				}
			});
			return false;
		},
		
		//TODO:: creating new func to prepare the blocksettings data 
		canvasgetblocksettingsdata: function(){
			var json = {},
				jcontainer = $(document.adminForm).find('.canvas-admin-layout-container'),
				jblocks = jcontainer.find('.canvas-admin-layout-section'),
				jspls = jcontainer.find('[data-container]');
			
			jspls.each(function(){
				var name = jspls.find('.canvas-block-title').html();
				/*,
				var name = jspls.find('.canvas-block-title'),
					vis = $(this).data('data-vis'),
					widths = $(this).data('data-widths'),
					firsts = $(this).data('data-firsts'),
					others = $(this).data('data-others');
				*/
				console.log(name);
				return;
				$(this).children().each(function(idx){
					var jpos = $(this),
						//pname = jpos.find('.canvas-admin-layout-pos').attr('data-original'),
						val = jpos.find('.canvas-admin-layout-posname').html(),
						info = CANVASAdminLayout.canvasemptydv(),
						width = CANVASAdminLayout.canvasgetwidth(idx, widths),
						visible = CANVASAdminLayout.canvasvisible(idx, vis.vals),
						first = CANVASAdminLayout.canvasfirst(idx, firsts),
						other = CANVASAdminLayout.canvasothers(idx, others);
					
					info.position = val ? val : '';
					
					CANVASAdminLayout.canvasformatwidth(info, width);
					CANVASAdminLayout.canvasformatvisible(info, visible);
					CANVASAdminLayout.canvasformatfirst(info, first);
					CANVASAdminLayout.canvasformatothers(info, other);

					//optimize
					CANVASAdminLayout.canvasopimizeparam(info);

					json['block' + (idx + 1) + '@' + name] = info;
				
				});
			});

			return json;
		},

		submit: function(params, data, callback){
			if(!callback){
				callback = data;
				data = null;
			}

			$.ajax({
				async: false,
				url: CANVASAdminLayout.mergeurl($.param(params)),
				type: data ? 'post' : 'get',
				data: data,
				success: function(rsp){
					
					rsp = $.trim(rsp);
					if(rsp){
						var json = rsp;
						if(rsp.charAt(0) != '[' && rsp.charAt(0) != '{'){
							json = rsp.match(/{.*?}/);
							if(json && json[0]){
								json = json[0];
							}
						}

						if(json && typeof json == 'string'){
							json = $.parseJSON(json);

							if(json && (json.error || json.successful)){
								CANVASAdmin.systemMessage(json.error || json.successful);
							}
						}
					}

					if($.isFunction(callback)){
						callback(json || rsp);
					}
				},
				complete: function(){
					$('#canvas-admin-layout-clone-dlg').modal('hide');
				}
			});
		},

		mergeurl: function(query, base){
			base = base || window.location.href;
			var urlparts = base.split('#');
			
			if(urlparts[0].indexOf('?') == -1){
				urlparts[0] += '?' + query;
			} else {
				urlparts[0] += '&' + query;
			}
			
			return urlparts.join('#');
		},

		canvasfullscreen: function(){
			if ($(this).hasClass('canvas-fullscreen-full')) {
				$('.subhead-collapse').removeClass ('subhead-fixed');
				$('#canvas-admin-layout').closest('.controls').removeClass ('canvas-admin-control-fixed');
				$(this).removeClass ('canvas-fullscreen-full').find('i').removeClass().addClass('icon-resize-full');
			} else {
				$('.subhead-collapse').addClass ('subhead-fixed');
				$('#canvas-admin-layout').closest('.controls').addClass ('canvas-admin-control-fixed');
				$(this).addClass ('canvas-fullscreen-full').find('i').removeClass().addClass('icon-resize-small');
			}

			return false;
		},

		//this is not a general function, just use for canvas only - better performance
		canvascopy: function(dst, src, valueonly){
			for(var p in src){
				if(src.hasOwnProperty(p)){
					if(!dst[p]){
						dst[p] = [];
					}

					for(var i = 0, s = src[p], il = s.length; i < il; i++){
						if(!valueonly || valueonly && s[i]){
							dst[p][i] = s[i];
						}
					}
				}
			}

			return dst;
		},

		canvasequalheight: function(){
			// Store the tallest element's height
			$(CANVASAdminLayout.jelms.find('.row, .row-fluid').not('.canvas-spotlight').get().reverse()).each(function(){
				var jrow = $(this),
					jchilds = jrow.children(),
					//offset = jrow.offset().top,
					height = 0,
					maxHeight = 0;

				jchilds.each(function () {
					height = $(this).css('height', '').css('min-height', '').height();
					maxHeight = (height > maxHeight) ? height : maxHeight;
				});

				if(CANVASAdminLayout.layout.clayout != 'mobile'){
					jchilds.css('min-height', maxHeight);
				}
			});
		},

		canvasremoveclass: function(clslist, clsremove){
			var removes = ( clsremove || '' ).split( CANVASAdminLayout.layout.rspace ),
				lookup = (' '+ clslist + ' ').replace( CANVASAdminLayout.layout.rclass, ' '),
				result = [];

			// loop over each item in the removal list
			for ( var c = 0, cl = removes.length; c < cl; c++ ) {
				// Remove until there is nothing to remove,
				if ( lookup.indexOf(' '+ removes[ c ] + ' ') == -1 ) {
					result.push(removes[c]);
				}
			}
			
			return result.join(' ');
		},

		//we will do this only for not responsive class (old bootstrap spanX style)
		canvasopimizeparam: function(pos){

			if(!CANVASAdminLayout.layout.responcls){

				//optimize
				var defdv  = CANVASAdminLayout.layout.dlayout,
					defcls = pos[defdv];

				for(var p in pos){
					if(pos.hasOwnProperty(p) && pos[p] === defcls && p != defdv){
						pos[p] = CANVASAdminLayout.canvasremoveclass(defcls, pos[p]);
					}
				}

				//remove span100, should we do this?
				if(pos.mobile){
					pos.mobile = CANVASAdminLayout.canvasremoveclass('span100 ' + CANVASAdminLayout.canvasfirstclass('mobile'), pos.mobile);
				}
			}
			
			//remove empty property
			for(var p in pos){
				if(pos[p] === ''){
					delete pos[p];
				} else {
					pos[p] = $.trim(pos[p]);
				}
			}
		},

		canvasformatwidth: function(result, info){
			for(var p in info){
				if(info.hasOwnProperty(p)){
					//width always be first - no need for a space
					result[p] += this.canvaswidthclass(p, CANVASAdminLayout.canvaswidthconvert(info[p], p));
				}
			}
		},

		canvasformatvisible: function(result, info){
			for(var p in info){
				if(info.hasOwnProperty(p) && info[p] == 1){
					result[p] += ' ' + CANVASAdminLayout.canvashiddenclass(p);
				}
			}
		},

		canvasformatfirst: function(result, info){
			for(var p in info){
				if(info.hasOwnProperty(p) && info[p] == 1){
					result[p] += ' ' + CANVASAdminLayout.canvasfirstclass(p);
				}
			}
		},

		canvasformatothers: function(result, info){
			for(var p in info){
				if(info.hasOwnProperty(p) && info[p] != ''){
					result[p] += ' ' + info[p];
				}
			}
		},

		//generate auto calculate width
		canvaswidthoptimize: function(numpos){
			var result = [],
				avg = Math.floor(CANVASAdminLayout.layout.maxgrid / numpos),
				sum = 0;

			for(var i = 0; i < numpos - 1; i++){
				result.push(avg);
				sum += avg;
			}

			result.push(CANVASAdminLayout.layout.maxgrid - sum);

			return result;
		},

		canvasgenwidth: function(layout, numpos){
			var cminspan = CANVASAdminLayout.layout.minspan[layout],
				total = cminspan * numpos;

			if(total <= CANVASAdminLayout.layout.maxgrid) {
				return CANVASAdminLayout.canvaswidthoptimize(numpos);
			} else {

				var result = [],
					rows = Math.ceil(total / CANVASAdminLayout.layout.maxgrid),
					cols = Math.ceil(numpos / rows);

				for(var i = 0; i < rows - 1; i++){
					result = result.concat(CANVASAdminLayout.canvaswidthoptimize(cols));
					numpos -= cols;
				}

				result = result.concat(CANVASAdminLayout.canvaswidthoptimize(numpos));
			}
			
			return result;
		},

		canvaswidthbyvisible: function(widths, visibles, numpos){
			var i, dv, nvisible,
				width, visible, visibleIdxs = [];

			for(dv in widths){
				if(widths.hasOwnProperty(dv)){
					visible = visibles[dv],
					visibleIdxs.length = 0,
					nvisible = 0;

					for(i = 0; i < numpos; i++){
						if(visible[i] == 0 || visible[i] == undefined){
							visibleIdxs.push(i);
						}
					}

					width = CANVASAdminLayout.canvasgenwidth(dv, visibleIdxs.length);

					for(i = 0; i < visibleIdxs.length; i++){
						widths[dv][visibleIdxs[i]] = width[i];
					}
				}
			}
		},

		canvasgetwidth: function(pidx, widths){
			var result = this.canvasemptydv(0),
				dv;

			for(dv in widths){
				if(widths.hasOwnProperty(dv)){
					result[dv] = widths[dv][pidx];
				}
			}
			
			return result;
		},

		canvaswidthconvert: function(span, layout){
			return ((layout || CANVASAdminLayout.layout.clayout) == 'mobile') ? Math.floor(span / CANVASAdminLayout.layout.maxgrid * 100) : span;
		},

		canvasvisible: function(pidx, visible){
			var result = this.canvasemptydv(0),
				dv;

			for(dv in visible){
				if(visible.hasOwnProperty(dv)){
					result[dv] = visible[dv][pidx] || 0;
				}
			}
			
			return result;
		},

		canvasfirst: function(pidx, firsts){
			var result = this.canvasemptydv(0),
				dv;

			for(dv in firsts){
				if(firsts.hasOwnProperty(dv)){
					result[dv] = firsts[dv][pidx] || 0;
				}
			}
			
			return result;
		},

		canvasothers: function(pidx, others){
			var result = this.canvasemptydv(),
				dv;

			for(dv in others){
				if(others.hasOwnProperty(dv)){
					result[dv] = others[dv][pidx] || '';
				}
			}
			
			return result;
		},

		// change the grid limit 
		canvasupdategrid: function (spl) {
			//update grid and limit for resizable
			var jspl = $(spl),
				layout = CANVASAdminLayout.layout.clayout,
				cmaxcol = CANVASAdminLayout.layout.maxcol[layout],
				junitspan = $('<div class="' + CANVASAdminLayout.canvaswidthclass(layout, CANVASAdminLayout.canvaswidthconvert(CANVASAdminLayout.layout.unitspan[layout], layout)) + '"></div>').appendTo(jspl),
				jminspan = $('<div class="' + CANVASAdminLayout.canvaswidthclass(layout, CANVASAdminLayout.canvaswidthconvert(CANVASAdminLayout.layout.minspan[layout], layout)) + '"></div>').appendTo(jspl),
				gridgap = parseInt(junitspan.css('marginLeft')),
				absgap = Math.abs(gridgap),
				gridsize = Math.floor(junitspan.outerWidth())
				minsize = Math.floor(jminspan.outerWidth()),
				widths = jspl.data('data-widths'),
				firsts = jspl.data('data-firsts'),
				visible = jspl.data('data-vis').vals[layout],
				width = widths[layout],
				first = firsts[layout],
				needfirst = visible[0] == 1,
				sum = 0;
			
			junitspan.remove();
			jminspan.remove();

			jspl.data('rzdata', {
				grid: gridsize + absgap,
				gap: absgap,
				minwidth: gridsize,
				maxwidth: (gridsize + absgap) * (CANVASAdminLayout.layout.maxgrid / CANVASAdminLayout.layout.unitspan[layout])  - absgap + 6
			});

			jspl.find('.canvas-admin-layout-unit').each(function(idx){
				if(visible[idx] == 0 || visible[idx] == undefined){ //ignore all hidden spans
					if(needfirst || (sum + parseInt(width[idx]) > CANVASAdminLayout.layout.maxgrid)){
						$(this).addClass(CANVASAdminLayout.canvasfirstclass(layout));
						sum = parseInt(width[idx]);
						first[idx] = 1;
						needfirst = false;
					} else {
						$(this).removeClass(CANVASAdminLayout.canvasfirstclass(layout));
						sum += parseInt(width[idx]);
						first[idx] = 0;
					}
				}
			});

			jspl.find('.canvas-admin-layout-rzhandle').css('right', Math.min(CANVASAdminLayout.layout.responcls ? -3 : -7, -3.5 - absgap / 2));
		},

		// apply the visibility value for current device - trigger when change device
		canvasupdatevisible: function(index, item){
			var jvis = $(item),
				jpos = jvis.parent(),
				jdata = jvis.closest('[data-vis]'),
				visible = jdata.data('data-vis').vals[CANVASAdminLayout.layout.clayout],
				state, idx = 0,
				spotlight = jdata.attr('data-spotlight');

			//if spotlight -> get the index
			if(spotlight){
				idx = jvis.closest('.canvas-admin-layout-unit').index();
			}
			
			state = visible[idx] || 0;
			
			if(spotlight){
				jvis.closest('.canvas-admin-layout-unit').toggle(state == 0);

				var jhiddenpos = jdata.nextAll('.canvas-admin-layout-hiddenpos');
				jhiddenpos.children().eq(idx).toggleClass('hide', state == 0);
				jhiddenpos.toggleClass('has-pos', !!(jhiddenpos.children().not('.hide, .canvas-hide').length));
			} else {
				var jhiddenpos = jpos.next('.canvas-admin-layout-hiddenpos');
				if(jhiddenpos.length){
					jhiddenpos.toggleClass('has-pos', state != 0);
					jpos.toggleClass('hide', state != 0);
				}
			}

			jvis.parent().toggleClass('pos-hidden', state == 1 && CANVASAdminLayout.layout.mode);
			jvis.children().removeClass('icon-eye-close icon-eye-open').addClass(state == 1 ? 'icon-eye-close' : 'icon-eye-open');
		},

		// apply the change (width, columns) of spotlight block when change device 
		canvasupdatespl: function(si, spl){
			var jspl = $(spl),
				layout = CANVASAdminLayout.layout.clayout,
				width = jspl.data('data-widths')[layout];

			jspl.children().each(function(idx){
				//remove all class and reset style width
				this.className = this.className.replace(CANVASAdminLayout.layout.spancls, ' ');
				$(this).css('width', '').addClass(CANVASAdminLayout.canvaswidthclass(layout, CANVASAdminLayout.canvaswidthconvert(width[idx]))).find('.canvas-admin-layout-poswidth').html(width[idx]);
			});

			CANVASAdminLayout.canvasupdategrid(spl);
		},

		//apply responsive class - maybe we do not need this
		canvasupdatedevice: function(nlayout){
			
			var clayout = CANVASAdminLayout.layout.clayout;

			CANVASAdminLayout.jrlems.each(function(){
				var jelm = $(this);
				// no override for all devices 
				if (!jelm.data('default')){
					return;
				}

				// keep default 
				if (!jelm.data(nlayout) && (!clayout || !jelm.data(clayout))){
					return;
				}

				// remove current
				if (jelm.data(clayout)){
					jelm.removeClass(jelm.data(clayout));
				} else {
					jelm.removeClass (jelm.data('default'));
				}

				// add new
				if (jelm.data(nlayout)){
					jelm.addClass (jelm.data(nlayout));
				} else{
					jelm.addClass (jelm.data('default'));
				}
			});

			CANVASAdminLayout.layout.clayout = nlayout;
			
			//apply width from previous settings
			CANVASAdminLayout.jspls.each(CANVASAdminLayout.canvasupdatespl);
			CANVASAdminLayout.jelms.find('.canvas-admin-layout-vis').each(CANVASAdminLayout.canvasupdatevisible);

			CANVASAdminLayout.canvasequalheight();
		},

		canvasresetdevice: function(){
			
			var layout = CANVASAdminLayout.layout.clayout,
				jcontainer = CANVASAdminLayout.jelms,
				jblocks = jcontainer.find('.canvas-admin-layout-pos'),
				jspls = jcontainer.find('[data-spotlight]'),
				jsplblocks = jspls.find('.canvas-admin-layout-pos');

			jblocks.not(jspls).not(jsplblocks).not('.canvas-admin-layout-uneditable').each(function(){
				var name = $(this).attr('data-original'),
					vis = $(this).closest('[data-vis]').data('data-vis');

				if(layout && vis){
					$.extend(true, vis.vals[layout], vis.deft[layout]);
				}
			});
			
			jspls.each(function(){
				var name = $(this).attr('data-spotlight'),
					vis = $(this).data('data-vis'),
					widths = $(this).data('data-widths'),
					owidths = $(this).data('data-owidths'),
					firsts = $(this).data('data-firsts'),
					ofirsts = $(this).data('data-ofirsts');

				$.extend(true, vis.vals[layout], vis.deft[layout]);
				$.extend(true, widths[layout], widths[layout].length == owidths[layout].length ? owidths[layout] : CANVASAdminLayout.canvasgenwidth(layout, widths[layout].length));
				$.extend(true, firsts[layout], ofirsts[layout]);

				for(var i = vis.deft[layout].length; i < CANVASAdminLayout.layout.maxcols; i++){
					vis.vals[layout][i] = 0;
				}

				for(var i = firsts[layout].length; i < CANVASAdminLayout.layout.maxcols; i++){
					firsts[layout][i] = '';
				}
			});

			jspls.each(CANVASAdminLayout.canvasupdatespl);
			jcontainer.find('.canvas-admin-layout-vis').each(CANVASAdminLayout.canvasupdatevisible);

			return false;
		},

		canvasresetall: function(){
			var layout = CANVASAdminLayout.layout.clayout,
				jcontainer = CANVASAdminLayout.jelms,
				jblocks = jcontainer.find('.canvas-admin-layout-pos'),
				jspls = jcontainer.find('[data-spotlight]'),
				jsplblocks = jspls.find('.canvas-admin-layout-pos');

			jblocks.not(jspls).not(jsplblocks).not('.canvas-admin-layout-uneditable').each(function(){
				if($(this).find('[data-original]').length){
					return;
				}

				var name = $(this).attr('data-original'),
					vis = $(this).closest('[data-vis]').data('data-vis');

				//change the name
				$(this).find('.canvas-admin-layout-posname').html(name);
				if(vis){
					$.extend(true, vis.vals, vis.deft);
				}
			});
			
			jspls.each(function(){
				var jspl = $(this),
					jhides = jspl.nextAll('.canvas-admin-layout-hiddenpos').children(),
					vis = jspl.data('data-vis'),
					widths = jspl.data('data-widths'),
					original = jspl.attr('data-original').split(','),
					owidths = jspl.data('data-owidths'),
					numcols = owidths[CANVASAdminLayout.layout.dlayout].length,
					html = [];

				for(var i = 0; i < numcols; i++){
					html = html.concat([
					'<div class="canvas-admin-layout-unit ', CANVASAdminLayout.canvaswidthclass(CANVASAdminLayout.layout.clayout, owidths[CANVASAdminLayout.layout.dlayout][i]), '">', //we do not need convert width here
						'<div class="canvas-admin-layout-pos block-', original[i], (original[i] == CANVASAdmin.langs.emptyLayoutPosition ? ' pos-off' : ''), '" data-original="', (original[i] || ''), '">',
							'<span class="canvas-admin-layout-edit"><i class="icon-cog"></i></span>',
							'<span class="canvas-admin-layout-poswidth" title="', CANVASAdmin.langs.layoutPosWidth, '">', owidths[CANVASAdminLayout.layout.dlayout][i], '</span>',
							'<h3 class="canvas-admin-layout-posname" title="', CANVASAdmin.langs.layoutPosName, '">', original[i], '</h3>',
							'<span class="canvas-admin-layout-vis" title="', CANVASAdmin.langs.layoutHidePosition, '"><i class="icon-eye-open"></i></span>',
						'</div>',
						'<div class="canvas-admin-layout-rzhandle" title="', CANVASAdmin.langs.layoutDragResize, '"></div>',
					'</div>']);

					jhides.eq(i).html(original[i] + '<i class="icon-eye-close">').removeClass('canvas-hide');
				}

				for(var i = numcols; i < CANVASAdminLayout.layout.maxcols; i++){
					jhides.eq(i).addClass('canvas-hide');
				}

				//reset value
				$(this)
					.empty()
					.html(html.join(''));

				$.extend(true, vis.vals, vis.deft);
				$.extend(true, widths, owidths);

				$(this).nextAll('.canvas-admin-layout-ncolumns').children().eq(owidths[CANVASAdminLayout.layout.dlayout].length - 1).trigger('click');
			});

			//change to default view
			jcontainer.prev().find('.canvas-admin-layout-mode-structure').trigger('click');

			return false;
		},

		canvasresetposition: function(){
			var layout = CANVASAdminLayout.layout.clayout,
				jcontainer = CANVASAdminLayout.jelms,
				jblocks = jcontainer.find('.canvas-admin-layout-pos'),
				jspls = jcontainer.find('[data-spotlight]'),
				jsplblocks = jspls.find('.canvas-admin-layout-pos');

			jblocks.not(jspls).not(jsplblocks).not('.canvas-admin-layout-uneditable').each(function(){
				//reset position
				$(this).find('.canvas-admin-layout-posname')
					.html(
						$(this).attr('data-original')
					)
					.parent()
					.removeClass('pos-off pos-active');
			});
			
			jspls.each(function(){
				var original = $(this).attr('data-original').split(','),
					jhides = $(this).nextAll('.canvas-admin-layout-hiddenpos').children();

				$(this).find('.canvas-admin-layout-pos').each(function(idx){
					if(original[idx] != undefined){
						$(this).toggleClass('pos-off', original[idx] == CANVASAdmin.langs.emptyLayoutPosition)
						.find('.canvas-admin-layout-posname')
						.html(original[idx]);
						
						jhides.eq(idx).html(original[idx] + '<i class="icon-eye-close">');
					} else {
						$(this).addClass('pos-off').find('.canvas-admin-layout-posname').html(CANVASAdmin.langs.emptyLayoutPosition);
					}
				});
			});

			return false;
		},

		canvasonvisible: function(){
			var jvis = $(this),
				jpos = jvis.parent(),
				jdata = jvis.closest('[data-vis]'),
				junits = null,
				layout = CANVASAdminLayout.layout.clayout,
				state = jpos.hasClass('pos-hidden'),
				visible = jdata.data('data-vis').vals[layout],
				spotlight = jdata.attr('data-spotlight'),
				idx = 0;

			//if spotlight -> the name is based on block, else use the name property
			if(spotlight){
				idx = jvis.closest('.canvas-admin-layout-unit').index();
				junits = jdata.children();
			}

			//toggle state
			state = 1 - state;
			
			if(spotlight){
				jvis.closest('.canvas-admin-layout-unit')[state == 0 ? 'show' : 'hide']();
			
				var jhiddenpos = jdata.nextAll('.canvas-admin-layout-hiddenpos');
				jhiddenpos.children().eq(idx).toggleClass('hide', state == 0);
				jhiddenpos.toggleClass('has-pos', !!(jhiddenpos.children().not('.hide, .canvas-hide').length));

				var visibleIdxs = [];
				for(var i = 0, il = junits.length; i < il; i++){
					if(junits[i].style.display != 'none'){
						visibleIdxs.push(i);
					}
				}

				if(visibleIdxs.length){
					var widths = jdata.data('data-widths')[layout],
						width = CANVASAdminLayout.canvasgenwidth(layout, visibleIdxs.length),
						vi = 0;

					for(var i = 0, il = visibleIdxs.length; i < il; i++){
						vi = visibleIdxs[i];
						widths[vi] = width[i];
						junits[vi].className = junits[vi].className.replace(CANVASAdminLayout.layout.spancls, ' ');
						junits.eq(vi).addClass(CANVASAdminLayout.canvaswidthclass(layout, CANVASAdminLayout.canvaswidthconvert(width[i]))).find('.canvas-admin-layout-poswidth').html(width[i]);
					}
				}
			} else {
				var jhiddenpos = jpos.next('.canvas-admin-layout-hiddenpos');
				if(jhiddenpos.length){
					jhiddenpos.toggleClass('has-pos', state != 0);
					jpos.toggleClass('hide', state != 0);
				}
			}
			
			jpos.toggleClass('pos-hidden', state == 1);
			jvis.children().removeClass('icon-eye-close icon-eye-open').addClass(state == 1 ? 'icon-eye-close' : 'icon-eye-open');
			
			visible[idx] = state;

			if(spotlight){
				CANVASAdminLayout.canvasupdategrid(jdata);
			}
			return false;
		},

		canvasemptydv: function(val){
			var result = {},
				devices = CANVASAdminLayout.layout.devices;
				
			val = typeof val != 'undefined' ? val : '';

			for(var i = 0; i < devices.length; i++){
				result[devices[i]] = val;
			}

			return result;
		},

		canvaswidthclass: function(device, width){
			return CANVASAdminLayout.layout.spanptrn.replace('{device}', device).replace('{width}', width);
		},

		canvashiddenclass: function(device){
			return CANVASAdminLayout.layout.hiddenptrn.replace('{device}', device);
		},

		canvasfirstclass: function(device){
			return CANVASAdminLayout.layout.firstptrn.replace('{device}', device);
		},

		canvaslayout: function(form, ctrlelm, ctrl, rsp){
			
			if(rsp){
				var bdhtml = rsp.match(/<body[^>]*>([\w|\W]*)<\/body>/im),
					vname = ctrlelm.name.replace(/[\[\]]/g, ''),
					jcontrol = $(ctrlelm).closest('.control-group');

				//stripScripts
				if(bdhtml){
					bdhtml = bdhtml[1].replace(new RegExp('<script[^>]*>([\\S\\s]*?)<\/script\\s*>', 'img'), '');
				}

				if(bdhtml){
					//clean those bootstrap fixed class
					bdhtml = bdhtml.replace(/navbar-fixed-(top|bottom)/gi, '');

					var jtabpane = jcontrol.closest('.tab-pane'),
						active = jtabpane.hasClass('active');

					if(!active){	//if not active, then we show it
						jtabpane.addClass('active');
					}

					var	curspan = CANVASAdminLayout.curspan = null,
						jelms = CANVASAdminLayout.jelms = $('#canvas-admin-layout-container').empty().html(bdhtml),
						jrlems = CANVASAdminLayout.jrlems = jelms.find('[class*="span"]').each(function(){
							var jelm = $(this);
							jelm.data();
							jelm.removeAttr('data-default data-wide data-normal data-xtablet data-tablet data-mobile');
							if (!jelm.data('default')){
								jelm.data('default', jelm.attr('class'));
							}
						}),
						jselect = CANVASAdminLayout.jselect,
						jallpos = CANVASAdminLayout.jallpos,
						jspls = CANVASAdminLayout.jspls = jelms.find('[data-spotlight]');

					//reset
					CANVASAdminLayout.canvasreset();

					jelms
						.find('.logo h1:first')
						.html(CANVASAdmin.langs.logoPresent);

					jelms
						.find('.canvas-admin-layout-pos')
						.not('.canvas-admin-layout-uneditable')
						.prepend('<span class="canvas-admin-layout-edit" title="' + CANVASAdmin.langs.layoutEditPosition + '"><i class="icon-cog"></i></span>');
					
					jelms
						.find('.canvas-admin-layout-section')
						.not('.canvas-admin-layout-uneditable')
						.prepend('<span class="canvas-admin-block-edit" title="' + CANVASAdmin.langs.layoutEditPosition + '"><i class="icon-fullscreen"></i></span>');
					
					jelms
						.find('[data-vis]')
						.not('[data-spotlight]')
						.each(function(){ 
							$(this)
								.data('data-vis', $.parseJSON($(this).attr('data-vis')))
								.data('data-others', $.parseJSON($(this).attr('data-others')))
								.attr('data-vis', '')
								.attr('data-others', '')
						})
						.find('.canvas-admin-layout-pos')
						.each(function(){
							var jpos = $(this);

							jpos
							.append('<span class="canvas-admin-layout-vis" title="' + CANVASAdmin.langs.layoutHidePosition + '"><i class="icon-eye-open"></i></span>')
							.after(['<div class="canvas-admin-layout-hiddenpos" title="', CANVASAdmin.langs.layoutHiddenposDesc, '">',
									'<span class="pos-hidden" title="', CANVASAdmin.langs.layoutShowPosition, '">', jpos.find('h3').html() ,'<i class="icon-eye-close"></i></span>',
								'</div>'].join(''))
							.next()
							.find('.pos-hidden')
								.on('click', function(){
									CANVASAdminLayout.canvasonvisible.call(jpos.find('.canvas-admin-layout-vis'));
									return false;
								});
						});
						

					jelms
						.find('.canvas-admin-layout-pos')
						.find('h3, h1')
						.addClass('canvas-admin-layout-posname')
						.attr('title', CANVASAdmin.langs.layoutPosName)
						.each(function(){
							var jparent = $(this).parentsUntil('.row-fluid, .row').last(),
								span = parseInt(jparent.prop('className').replace(/(.*?)span(\d+)(.*)/, "$2"));

							if(isNaN(span)){
								span = CANVASAdmin.langs.layoutUnknownWidth;
							}

							$(this).before('<span class="canvas-admin-layout-poswidth" title="' + CANVASAdmin.langs.layoutPosWidth + '">' + span + '</span>');
						});
					
					//TODO:: added full-width data-container option on html
					jelms
						.on('click', '.canvas-admin-block-edit', function(e){
							var current_block = $(this),
								active = current_block.parent().attr('data-container');
							
							if(!active){	//if not active, then we show it
								current_block.parent().attr('data-container','container-fluid');
								current_block.addClass('displayblock');
								current_block.find('i').addClass('text-success');
							}else{
								current_block.parent().removeAttr('data-container');
								current_block.removeClass('displayblock');
								current_block.find('i').removeClass('text-success');
							}
							
						});
						
					jelms
						.off('click.canvaslvis').off('click.canvasledit')
						.on('click.canvaslvis', '.canvas-admin-layout-vis', CANVASAdminLayout.canvasonvisible)
						.on('click.canvasledit', '.canvas-admin-layout-edit', function(e){
							if(curspan){
								$(curspan).parent().removeClass('pos-active');
							}
							curspan = CANVASAdminLayout.curspan = this;

							var jspan = $(this),
								offs = $(this).offset();

							jspan.parent().addClass('pos-active');
							jselect.removeClass('top').addClass('right');

							var top = offs.top + (jspan.height() - jselect.height()) / 2,
								left = offs.left + jspan.width();

							if(left + jselect.outerWidth(true) > $(window).width()){
								jselect.removeClass('right').addClass('top');
								top = offs.top - jselect.outerHeight(true);
								left = offs.left + (jspan.width() - jselect.width()) / 2;
							}

							jselect.css({
								top: top,
								left: left
							}).show()
								.find('select')
								.val(jspan.siblings('h3').html())
								.next('.canvas-admin-layout-rmvbtn').toggleClass('disabled', !jallpos.val())
								.next('.canvas-admin-layout-defbtn').toggleClass('disabled', jspan.siblings('h3').html() == jspan.closest('[data-original]').attr('data-original'))
								.next('.canvas-admin-layout-combtn').toggleClass('disabled', jspan.siblings('h3').html() == 'core-component');

							jallpos.scrollTop(Math.min(jallpos.prop('scrollHeight') - jallpos.height(), jallpos.prop('selectedIndex') * (jallpos.prop('scrollHeight') / jallpos[0].options.length)));
							
							return false;
						});
						
						jspls.each(function(){

							var jncols = $([
								'<div class="btn-group canvas-admin-layout-ncolumns">',
									'<span class="btn" title="', CANVASAdmin.langs.layoutChangeNumpos, '">1</span>',
									'<span class="btn" title="', CANVASAdmin.langs.layoutChangeNumpos, '">2</span>',
									'<span class="btn" title="', CANVASAdmin.langs.layoutChangeNumpos, '">3</span>',
									'<span class="btn" title="', CANVASAdmin.langs.layoutChangeNumpos, '">4</span>',
									'<span class="btn" title="', CANVASAdmin.langs.layoutChangeNumpos, '">5</span>',
									'<span class="btn" title="', CANVASAdmin.langs.layoutChangeNumpos, '">6</span>',
								'</div>'].join('')).appendTo(this.parentNode),

								jcols = $(this).children(),
								numpos = jcols.length,
								spotlight = this,
								positions = [],
								defpos = $(this).attr('data-original').replace(/\s+/g, '').split(','),
								visibles = $.parseJSON($(this).attr('data-vis')),
								twidths = $.parseJSON($(this).attr('data-widths')),
								widths = {},
								owidths = $.parseJSON($(this).attr('data-owidths')),
								ofirsts = $.parseJSON($(this).attr('data-ofirsts')),
								firsts = $.parseJSON($(this).attr('data-firsts'));
							
							$(spotlight)
								.data('data-widths', widths).removeAttr('data-widths', '') //store and clean the data
								.data('data-owidths', owidths).removeAttr('data-owidths', '') //store and clean the data
								.data('data-vis', visibles).attr('data-vis', '') //store and clean the data - keep the marker for selector
								.data('data-ofirsts', ofirsts).removeAttr('data-ofirsts', '') //store and clean the data
								.data('data-firsts', firsts).removeAttr('data-firsts', '') //store and clean the data
								.data('data-others', $.parseJSON($(this).attr('data-others'))).removeAttr('data-others', '') //store and clean the data
								.parent().addClass('canvas-admin-layout-splgroup');

							jcols.each(function(idx){
								positions[idx] = $(this).find('h3').html();

								$(this)
								.addClass('canvas-admin-layout-unit')
								.find('.canvas-admin-layout-pos')
								.attr('data-original', defpos[idx])
								.append('<span class="canvas-admin-layout-vis" title="' + CANVASAdmin.langs.layoutHidePosition + '"><i class="icon-eye-open"></i></span>');
							});

							for(var i = numpos; i < 6; i++){
								positions[i] = defpos[i] || CANVASAdmin.langs.emptyLayoutPosition;
							}

							var jhides = $([
								'<div class="canvas-admin-layout-hiddenpos" title="', CANVASAdmin.langs.layoutHiddenposDesc, '">',
									'<span class="pos-hidden" title="', CANVASAdmin.langs.layoutShowPosition, '">', positions[0], '<i class="icon-eye-close"></i></span>',
									'<span class="pos-hidden" title="', CANVASAdmin.langs.layoutShowPosition, '">', positions[1], '<i class="icon-eye-close"></i></span>',
									'<span class="pos-hidden" title="', CANVASAdmin.langs.layoutShowPosition, '">', positions[2], '<i class="icon-eye-close"></i></span>',
									'<span class="pos-hidden" title="', CANVASAdmin.langs.layoutShowPosition, '">', positions[3], '<i class="icon-eye-close"></i></span>',
									'<span class="pos-hidden" title="', CANVASAdmin.langs.layoutShowPosition, '">', positions[4], '<i class="icon-eye-close"></i></span>',
									'<span class="pos-hidden" title="', CANVASAdmin.langs.layoutShowPosition, '">', positions[5], '<i class="icon-eye-close"></i></span>',
								'</div>'].join('')).appendTo(this.parentNode),
								jhcols = jhides.children();

							for(var i = 0; i < CANVASAdminLayout.layout.maxcols; i++){
								jhcols.eq(i).toggleClass('canvas-hide', i >= numpos);	
							}

							//temporary calculate the widths for each devices size
							CANVASAdminLayout.canvascopy(widths, twidths); //first - clone the current object
							CANVASAdminLayout.canvaswidthbyvisible(widths, visibles.vals, numpos); //then extend it with autogenerate width
							CANVASAdminLayout.canvascopy(widths, twidths); // if widths has value, it should be priority
							
							$(spotlight).xresize({
								grid: false,
								gap: 0,
								selector: '.canvas-admin-layout-unit'
							});

							jncols.on('click', '.btn', function(e){

								if(!e.isTrigger){
									numpos = $(this).index() + 1;
									for(var i = 0; i < numpos; i++){
										if(!positions[i] || positions[i] == CANVASAdmin.langs.emptyLayoutPosition){
											positions[i] = defpos[i] || CANVASAdmin.langs.emptyLayoutPosition;
										}

										jhcols.eq(i).html(positions[i] + '<i class="icon-eye-close">').removeClass('canvas-hide');
									}

									for(var i = numpos; i < CANVASAdminLayout.layout.maxcols; i++){
										jhcols.eq(i).addClass('canvas-hide');	
									}

									//automatic re-calculate the widths for each devices size
									CANVASAdminLayout.canvaswidthbyvisible(widths, visibles.vals, numpos);

									var html = [];
									for(i = 0; i < numpos; i++){
										html = html.concat([
										'<div class="canvas-admin-layout-unit ', CANVASAdminLayout.canvaswidthclass(CANVASAdminLayout.layout.clayout, widths[CANVASAdminLayout.layout.dlayout][i]), '">',
											'<div class="canvas-admin-layout-pos block-', positions[i], (positions[i] == CANVASAdmin.langs.emptyLayoutPosition ? ' pos-off' : ''), '" data-original="', (defpos[i] || ''), '">',
												'<span class="canvas-admin-layout-edit"><i class="icon-cog"></i></span>',
												'<span class="canvas-admin-layout-poswidth" title="', CANVASAdmin.langs.layoutPosWidth, '">', widths[CANVASAdminLayout.layout.dlayout][i], '</span>',
												'<h3 class="canvas-admin-layout-posname" title="', CANVASAdmin.langs.layoutPosName, '">', positions[i], '</h3>',
												'<span class="canvas-admin-layout-vis" title="', CANVASAdmin.langs.layoutHidePosition, '"><i class="icon-eye-open"></i></span>',
											'</div>',
											'<div class="canvas-admin-layout-rzhandle" title="', CANVASAdmin.langs.layoutDragResize, '"></div>',
										'</div>']);
									}

									//reset value
									$(spotlight)
										.empty()
										.html(html.join(''));
								}

								//change gridsize for resize 
								CANVASAdminLayout.canvasupdategrid(spotlight);

								$(this).addClass('active').siblings().removeClass('active');

							}).children().removeClass('active').eq(numpos -1).addClass('active').trigger('click');

							jhides.on('click', 'span', function(){
								CANVASAdminLayout.canvasonvisible.call($(spotlight).children().eq($(this).index()).find('.canvas-admin-layout-vis'));
								return false;
							});
						});

					CANVASAdminLayout.canvasequalheight();

					if(!active){	//restore current status
						jtabpane.removeClass('active');
					}

					$('#canvas-admin-layout').removeClass('hide');

					CANVASAdminLayout.initMarkChange();

				} else {
					jcontrol.find('.controls').html('<p class="canvas-admin-layout-error">' + CANVASAdmin.langs.layoutCanNotLoad + '</p>');
				}
			}
		}

	});
	
	$(document).ready(function(){
		CANVASAdminLayout.initPrepareLayout();
		CANVASAdminLayout.initLayoutClone();
		CANVASAdminLayout.initModalDialog();
		CANVASAdminLayout.initPreSubmit();
	});

	$(window).load(function(){
		CANVASAdminLayout.initChosen();
	});
	
}(jQuery);

!function($){

	var isdown = false,
		curelm = null,
		opts, memwidth, memfirst, memvisible, owidth, 
		rzleft, rzwidth, rzlayout, rzindex, rzminspan,

		snapoffset = function(grid, size) {
			var limit = grid / 2;
			if ((size % grid) > limit) {
				return grid-(size % grid);
			} else {
				return -size % grid;
			}
		},

		spanfirst = function(rwidth){
			var sum = 0,
				needfirst = (memvisible[0] == 1);

			$(curelm).parent().children().each(function(idx){
				if(memvisible[idx] == 0 || memvisible[idx] == undefined){
					if(needfirst || ((sum + parseInt(memwidth[idx]) > CANVASAdminLayout.layout.maxgrid) || (rzindex + 1 == idx && sum + parseInt(memwidth[idx]) == CANVASAdminLayout.layout.maxgrid && (rwidth > owidth)))){
						$(this).addClass(CANVASAdminLayout.canvasfirstclass(rzlayout));
						memfirst[idx] = 1;
						sum = parseInt(memwidth[idx]);
						needfirst = false;
					} else {
						$(this).removeClass(CANVASAdminLayout.canvasfirstclass(rzlayout));
						memfirst[idx] = 0;
						sum += parseInt(memwidth[idx]);
					}
				}
			});
		},

		updatesize = function(e, togrid) {
			var mx = e.pageX,
				width = rwidth = (mx - rzleft + rzwidth);

			if(opts.grid){
				width = width + snapoffset(opts.grid, width) - opts.gap;
			}

			if(rwidth < opts.minwidth){
				rwidth = opts.minwidth;
			} else if (rwidth > opts.maxwidth){
				rwidth = opts.maxwidth;
			}

			if(width < opts.minwidth){
				width = opts.minwidth;
			} else if (width > opts.maxwidth){
				width = opts.maxwidth;
			}

			if(owidth != width){
				memwidth[rzindex] = rzminspan * ((width + opts.gap) / opts.grid) >> 0;
				owidth = width;

				$(curelm).find('.canvas-admin-layout-poswidth').html(memwidth[rzindex]);
			}

			curelm.style['width'] = (togrid ? width : rwidth) + 'px';

			spanfirst(rwidth);
		},

		updatecls = function(e){
			var mx = e.pageX,
				width = (mx - rzleft + rzwidth);

			if(opts.grid){
				width = width + snapoffset(opts.grid, width) - opts.gap;
			}

			if(width < opts.minwidth){
				width = opts.minwidth;
			} else if (width > opts.maxwidth){
				width = opts.maxwidth;
			}

			curelm.className = curelm.className.replace(CANVASAdminLayout.layout.spancls, ' ');
			$(curelm).css('width', '').addClass(CANVASAdminLayout.canvaswidthclass(rzlayout, CANVASAdminLayout.canvaswidthconvert((rzminspan * ((width + opts.gap) / opts.grid) >> 0))));
			spanfirst(width);
		},

		mousedown = function (e) {
			curelm = this.parentNode;
			isdown = true;
			rzleft = e.pageX;
			owidth = rzwidth  = $(curelm).outerWidth();

			var jdata = $(this).closest('.canvas-admin-layout-xresize');
			
			opts = jdata.data('rzdata');
			rzlayout = CANVASAdminLayout.layout.clayout;
			rzminspan = CANVASAdminLayout.layout.unitspan[rzlayout];
			rzindex = $(this).parent().index();
			memwidth = jdata.data('data-widths')[rzlayout];
			memfirst = jdata.data('data-firsts')[rzlayout];
			memvisible = jdata.data('data-vis').vals[rzlayout];

			updatesize(e);

			$(document)
			.on('mousemove.xresize', mousemove)
			.on('mouseup.xresize', mouseup);

			return false;
		},
		mousemove = function (e) {
			if(isdown) {
				updatesize(e);
				return false;
			}
		},
		mouseup = function (e) {
			isdown = false;
			updatecls(e);
			$(document).unbind('.xresize');
		};

	$.fn.xresize = function(opts) {
		return this.each(function () {
			$(opts.selector ? $(this).find(opts.selector) : this).append('<div class="canvas-admin-layout-rzhandle" title="' + CANVASAdmin.langs.layoutDragResize + '"></div>');			
			$(this)
			.addClass('canvas-admin-layout-xresize')
			.data('rzdata', $.extend({
				selector: '',
				minwidth: 0,
				maxwidth: 100000,
				minheight: 0,
				maxheight: 100000,
				grid: 0,
				gap: 0
			}, opts))
			.on('mousedown.wresize', '.canvas-admin-layout-rzhandle', mousedown);
		});
	};

}(jQuery);