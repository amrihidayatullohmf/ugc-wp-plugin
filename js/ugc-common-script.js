/*
* UGC Front-end Main JS Functions
* Author : Amri Hidayatulloh
* Plugin : UGC
*/

$(document).ready(function(){
	function setCookie(name,value,days) {
	    var expires = "";
	    if (days) {
	        var date = new Date();
	        date.setTime(date.getTime() + (days*24*60*60*1000));
	        expires = "; expires=" + date.toUTCString();
	    }
	    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
	}
	function getCookie(name) {
	    var nameEQ = name + "=";
	    var ca = document.cookie.split(';');
	    for(var i=0;i < ca.length;i++) {
	        var c = ca[i];
	        while (c.charAt(0)==' ') c = c.substring(1,c.length);
	        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	    }
	    return null;
	}
	function eraseCookie(name) {   
	    document.cookie = name+'=; Max-Age=-99999999;';  
	}
	function scorePassword(pass) {
	    var score = 0;
	    if (!pass)
	        return score;

	    var letters = new Object();

	    for (var i=0; i<pass.length; i++) {
	        letters[pass[i]] = (letters[pass[i]] || 0) + 1;
	        score += 5.0 / letters[pass[i]];
	    }

	    var variations = {
	        digits: /\d/.test(pass),
	        lower: /[a-z]/.test(pass),
	        upper: /[A-Z]/.test(pass),
	        nonWords: /\W/.test(pass),
	    }

	    variationCount = 0;
	    for (var check in variations) {
	        variationCount += (variations[check] == true) ? 1 : 0;
	    }
	    score += (variationCount - 1) * 10;

	    score = parseInt(score);

	    if(score > 100) {
	    	score = 100;
	    }

	    return score;
	}

	function showError(el,msg,duration) {
		el.find('.message').html(msg);
		el.addClass('show');
		setTimeout(function(){
			el.removeClass('show');
		},duration);
	}

	function showErrorPopup(msg,duration) {
		$("#retrieve-loader").hide();
		$("#error-notif").html(msg);
		$("#error-notif").slideDown(100).delay(duration).slideUp(100);
	}


	$(".ajax-form-register").submit(function(){
		var _self = $(this);
		var ldr = _self.find('.ugc-loader');
		var sbt = _self.find('.ugc-submit');

		ldr.show();
		sbt.hide();

		var _action = $(this).attr('action');

		$.ajax({
			type : 'POST',
			url : ugc_ajaxurl,
			data : $(this).serialize(),
			dataType : 'json',
			success : function(d) {
				console.log(d);
				if(d.code == 500) {
					showError($("#ugc-error-message"),d.msg,3000);
				} else {
					location.href = ugc_registerurl+'?token='+d.token;
				}
				ldr.hide();
				sbt.show();
				grecaptcha.reset(recaptcha_register);
			},
			error : function(e) {
				console.log(e);
				ldr.hide();
				sbt.show();
				showError($("#ugc-error-message"),'Unknown error occured',3000);
				grecaptcha.reset(recaptcha_register);
			}
		});

		return false;
	});


	$(".ajax-form-resend").submit(function(){
		var _self = $(this);
		var ldr = _self.find('.ugc-loader');
		var sbt = _self.find('.ugc-submit');

		ldr.show();
		sbt.hide();

		var _action = $(this).attr('action');

		$.ajax({
			type : 'POST',
			url : ugc_ajaxurl,
			data : $(this).serialize(),
			dataType : 'json',
			success : function(d) {
				console.log(d);
				if(d.code == 500) {
					showError($("#ugc-error-message"),d.msg,3000);
				} else {
					showError($("#ugc-success-message"),d.msg,3000);
				}
				ldr.hide();
				sbt.show();
				grecaptcha.reset(recaptcha_register);
			},
			error : function(e) {
				console.log(e);
				ldr.hide();
				sbt.show();
				showError($("#ugc-error-message"),'Unknown error occured',3000);
				grecaptcha.reset(recaptcha_register);
			}
		});

		return false;
	});


	$(".ajax-form-recovery").submit(function(){
		var _self = $(this);
		var ldr = _self.find('.ugc-loader');
		var sbt = _self.find('.ugc-submit');

		ldr.show();
		sbt.hide();

		var _action = $(this).attr('action');

		$.ajax({
			type : 'POST',
			url : ugc_ajaxurl,
			data : $(this).serialize(),
			dataType : 'json',
			success : function(d) {
				console.log(d);
				if(d.code == 500) {
					showError($("#ugc-error-message"),d.msg,3000);
				} else {
					showError($("#ugc-success-message"),d.msg,3000);
					$("#activationsection").hide();
					$("#successactivation").show();
				}
				ldr.hide();
				sbt.show();
				grecaptcha.reset(recaptcha_register);
			},
			error : function(e) {
				console.log(e);
				ldr.hide();
				sbt.show();
				showError($("#ugc-error-message"),'Unknown error occured',3000);
				grecaptcha.reset(recaptcha_register);
			}
		});

		return false;
	});

	$(".ajax-form-login").submit(function(){
		var _self = $(this);
		var ldr = _self.find('.ugc-loader');
		var sbt = _self.find('.ugc-submit');

		ldr.show();
		sbt.hide();

		$.ajax({
			type : 'POST',
			url : ugc_ajaxurl,
			data : $(this).serialize(),
			dataType : 'json',
			success : function(d) {
				console.log(d);
				if(d.code == 500) {
					showError($("#ugc-error-message"),d.msg,3000);
				} else {
					location.href = ugc_profileurl;
				}
				ldr.hide();
				sbt.show();
			},
			error : function(e) {
				console.log(e);
				ldr.hide();
				sbt.show();
				showError($("#ugc-error-message"),'Unknown error occured',3000);
			}
		});

		return false;
	});

	$(".ajax-form").find('.ugc-submit').click(function(){
		var form = $(this).data('form');
		var _self = $(form);
		var ldr = _self.find('.ugc-loader');
		var sbt = _self.find('.ugc-submit');

		_self.ajaxForm({
			beforeSubmit: function(formData,jqForm,options) {
				ldr.show();
				sbt.hide();
		    },
		    success: function(d,s) {
		        console.log(d);
		        d = JSON.parse(d);
				
				if(d.code == 500) {
					showError($("#ugc-error-message"),d.msg,3000);
				} else {
					showError($("#ugc-success-message"),d.msg,3000);
					setTimeout(function(){
						location.reload();
					},3000);
				}

				ldr.hide();
				sbt.show();		            
		    },
		    error: function(e) {
		        console.log(e);
		        ldr.hide();
				sbt.show();
				showError($("#ugc-error-message"),'Unknown error occured',3000);
		    }		
	            
	    }).submit();

	    return false;
	});

	$("#checkemail").blur(function(){
		var _email = $(this).val();

		if(_email != '') {
			var parent = $(this).parent().parent();
			parent.find('.loader').show();

			parent.find('.alert').html('');
			parent.find('.alert').hide();
			parent.removeClass('has-error');

			$.ajax({
				type : 'POST',
				data : {
					action : 'ugc_check_email',
					email : _email
				},
				url : ugc_ajaxurl,
				dataType : 'json',
				success : function(d) {
					console.log(d);
					parent.find('.loader').hide();
					if(d.code == 500) {
						parent.find('.alert').html(d.msg);
						parent.find('.alert').show();
						parent.addClass('has-error');
					}
				},
				error : function(e) {
					console.log(e);
					parent.find('.loader').hide();
				}
			});
		}

		return false;
	});

	$("#checkmatchpassword").blur(function(){
		var val = $(this).val();
		var rel = $($(this).data('rel')).val();
		var parent = $(this).parent().parent();
			
		parent.find('.loader').show();

		parent.find('.alert').html('');
		parent.find('.alert').hide();
		parent.removeClass('has-error');

		if(val != rel) {
			parent.find('.alert').html('Please retype password correctly');
			parent.find('.alert').show();
			parent.addClass('has-error');
		}

		parent.find('.loader').hide();
	});

	$("#checkpassword").keyup(function(){
		var val = $(this).val();
		var score = scorePassword(val);

		if(score > 100) {
			score = 100;
		}

		//console.log(score);
		var meter = $("#passwordmeter");

		meter.find('.meter').removeClass('medium');
		meter.find('.meter').removeClass('strong');
		status = 'Weak';

		if(score > 30 && score < 65) {
			meter.find('.meter').addClass('medium');
			status = 'Medium';
		} else if(score > 65) {
			meter.find('.meter').addClass('strong');
			status = 'Strong';
		} 

		meter.show(100);
		meter.find('.meter').animate({'width':score+'%'},100);
		meter.find('.caption').find('b').html(status);

	});

	$("#checkpassword").blur(function(){
		var meter = $("#passwordmeter");
		meter.hide(100);
		meter.find('.meter').removeClass('medium');
		meter.find('.meter').removeClass('strong');
		meter.find('.meter').animate({'width':'0%'},100);
		meter.find('.caption').find('b').html('');
	});

	$("#activate-button").click(function(){
		var btn = $(this);
		var key = $(this).data('key');
		var tmp = $(this).html();

		btn.html('<i class="fa fa-spinner fa-spin"></i>');

		$.ajax({
			type : 'POST',
			url : ugc_ajaxurl,
			dataType : 'json',
			data : {
				action : 'ugc_activation',
				ugc_activation_key : key
			},
			success : function(d) {
				console.log(d);
				if(d.code == 200) {
					btn.html(tmp);
					$("#activationsection").hide();
					$("#successactivation").show();
				} else {
					btn.html(tmp);
					showError($("#ugc-error-message"),d.msg,3000);
				}
			},
			error : function(e) {
				console.log(e);
				btn.html(tmp);
				showError($("#ugc-error-message"),'Unknown error occured',3000);
			}
		});

		return false;
	});

	$(".ugc-upload").click(function(){
		$(".ugc-tab-action").removeClass('active');
		$(".ugc-form-segment").removeClass('active');
		$(".ugc-tab-action:first-child").addClass('active');
		$(".ugc-form-segment:first-child").addClass('active');
	
		$("#ugc-black-bg").fadeIn(300);
		setTimeout(function(){
			$("#ugc-popup-add").fadeIn(200);
		},200);
	});

	$(".ugc-tab-action").click(function(){
		var rel = $(this).data('rel');

		$(".ugc-tab-action").removeClass('active');
		$(".ugc-form-segment").removeClass('active');

		$(this).addClass('active');
		$(rel).addClass('active');
		$(rel).find('.special').focus();
	});

	$(".ugc-close-popup").click(function(){
		$(".ugc-tab-action").removeClass('active');
		$(".ugc-form-segment").removeClass('active');
		$(".ugc-tab-action:first-child").addClass('active');
		$(".ugc-form-segment:first-child").addClass('active');

		$(".ugc-form-segment").find('img').each(function(){
			$(this).prop('src',$(this).prop('alt'));
		});
		$("#ugc-popup-add").find('input').each(function() {
			if(!$(this).hasClass('skip-clear')) {
				$(this).val('');
			}
		});
		$("#ugc-popup-add").find('textarea').each(function() {
			$(this).val('');

		});

		$("#ugc-popup-add").fadeOut(300);
		setTimeout(function(){
			$("#ugc-black-bg").fadeOut(300);
		},200);
	});

	$("#youtube_url").blur(function(){
		if($(this).val() == "") {
			return false;
		}
		$("#retrieve-loader").show();
		$.ajax({
			type : 'POST',
			url : ugc_ajaxurl,
			data : {
				action : 'ugc_fetchYoutubeData',
				youtube_url : $(this).val()
			},
			dataType : 'json',
			success : function(d) {
				console.log(d);
				if(typeof d.pageInfo != undefined && d.pageInfo.totalResults > 0) {
					$("#youtube_id").val(d.items[0].id);
					$("#youtube_title").val(d.items[0].snippet.title);
					$("#youtube_description").val(d.items[0].snippet.description);
					$("#youtube_image").find('input').val(d.items[0].snippet.thumbnails.high.url);
					$("#youtube_image").find('img').prop('src',d.items[0].snippet.thumbnails.high.url);
					$("#retrieve-loader").hide();
				} else {
					showErrorPopup('Ops! URL video is not valid !',2000);
				}
			},
			error : function(e) {
				console.log(e);
				showErrorPopup('Ops! Unknown error occured !',2000);
			}
		});
	});

	$(".ugc-image-uploader").click(function(){
		_self_container = $(this); 
		$("#ugcmetaimage").trigger('click');
		$("#ugcmetaimage").change(function(){
			var val = $(this).val();
			if(val == '') {
				return false;
			}

			$("#imageUploader").ajaxForm({
				beforeSubmit: function(formData,jqForm,options) {
					$("#retrieve-loader").show();
		        },
		        success: function(d,s) {
		            console.log(d);
		            var d = JSON.parse(d);

		            if(d.code == 200) {
		            	_self_container.find('img').prop('src',d.url);
		            	_self_container.find('input').val(d.url);
		            	$("#retrieve-loader").hide();
		            } else {
		            	showErrorPopup(d.msg,2000);
		            }
		            
		        },
		        error: function(e) {
		            console.log(e);
		            showErrorPopup('Ops! Unknown error occured !',2000);
		        }		
	            
	        }).submit();

	        return false;			
		});

		return false;
	});


	$('.ugc-submit-submission').click(function(){
		var form = $(this).data('id');
		var _self = $(form);

		_self.ajaxForm({
			beforeSubmit: function(formData,jqForm,options) {
				$("#retrieve-loader").show();
		    },
		    uploadProgress: function(event, position, total, percentComplete) {
		    	$("#retrieve-loader").find('span').html("<br>"+percentComplete+'%');
		    },
		    success: function(d,s) {
		        console.log(d);
		        d = JSON.parse(d);
		        $("#retrieve-loader").find('span').html('');
				
				if(d.code == 200) {
					$("#ugc-submission-success-message").html(d.msg);
					$(".ugc-form-segment").removeClass('active');
					$("#popheaderarea").hide();
					$("#ugc-success").addClass('active');
					$("#retrieve-loader").hide();
				} else {
					showErrorPopup(d.msg,2000);
				}
	            
		    },
		    error: function(e) {
		        console.log(e);
		        $("#retrieve-loader").find('span').html('');
		        showErrorPopup('Ops! Unknown error occured !',2000);
		    }		
	            
	    }).submit();

	    return false;
	});

	$(".trigger-remove-post").click(function(){
		var id = $(this).data('id');
		var btn = $(this);
		var temp = btn.find('i').attr('class');

		swal({
			title: "Are you sure ?",
			text: "You are going to remove this post",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",	
			confirmButtonText: "Yes, I Understand!",
			cancelButtonText: "Cancel",
			closeOnConfirm: true,
			closeOnCancel: true
		},
		function(isConfirm){
			if (isConfirm) {
				btn.find('i').attr('class','fa fa-spinner fa-spin');
				$.ajax({
					type : 'post',
					url : ugc_ajaxurl,
					dataType : 'json',
					data : {
						action : 'ugc_remove_post',
						post_id : id
					},
					success : function(d) {
						console.log(d);
						if(d.code == 200) {
							swal('Yeay','Post has already removed, page will be reloaded','success');
							setTimeout(function(){
								location.reload();
							},3000);
						} else {
							swal('Ops',d.msg,'error');
						}
						btn.find('i').attr('class',temp);
					},
					error : function(e) {
						console.log(e);
						swal('Ops','Unknown error occured!','error');
						btn.find('i').attr('class',temp);
					}
				});
			}
		});
		return false;
	});

	if($("#ugc_cookie_post_name").size() > 0) {
		var cookieVal = $("#ugc_cookie_post_name").val();
		$get = getCookie(cookieVal);

		if($get == null) {
			$.ajax({
				type :'post',
				url : ugc_ajaxurl,
				dataType : 'json',
				data : {
					action : 'ugc_set_view_count',
					post_id : $("#ugc_post_id").val()
				},
				success : function(d) {
					console.log(d);
					if(d.code == 200) {
						setCookie(cookieVal,1,1);
						$("#ugc_view_count").html(d.count);
					}
				},
				error : function(e) {
					console.log(e);
				}
			});
		}
	}

	$(".trigger-like").click(function(){
		if($(this).hasClass('on')) {
			return false;
		}

		var _self = $(this);
		var i = $(this).find('i');
		var temp = i.attr('class');

		i.attr('class','fa fa-spin fa-spinner');

		$.ajax({
			type :'post',
			url : ugc_ajaxurl,
			dataType : 'json',
			data : {
				action : 'ugc_set_like_count',
				post_id : $("#ugc_post_id").val()
			},
			success : function(d) {
				console.log(d);
				if(d.code == 200) {
					var str = (d.count == 1) ? 'Like' : 'Likes';
					$("#ugc_like_count").html(d.count+" "+str);
					_self.addClass('on');
				}
				i.attr('class',temp);
			},
			error : function(e) {
				console.log(e);
				i.attr('class',temp);
			}
		});
		return false;
	});

	$(".trigger-facebook-share").click(function(){
		var url = location.href;
		if(typeof $(this).data('url') == 'string') {
			url = $(this).data('url');
		}
		FB.ui(
		  {
		    method: 'share',
		    href: url,
		  },
		  function(response) {
		    if (response && !response.error_message) {
		      console.log('Posting completed.');
		    } else {
		      console.log('Error while posting.');
		      return false;
		    }
		    
		  }
		);
	});

	$(".trigger-twitter-share").click(function(){
		var url = location.href;
		if(typeof $(this).data('url') == 'string') {
			url = $(this).data('url');
		}
		window.open('http://twitter.com/share?&url='+url);
	});

	$(".playlist-item").click(function(){
		if($(this).hasClass('active')) {
			return false;
		}

		var _self = $(this);
		var id = $(this).data('id');
		var embed = $(this).data('embed');
		var p = $(this).parent().parent().parent();
		var cont = p.find('.ugc-main-area');

		if(typeof embed == undefined) {
			embed = false;
		}

		cont.find('button.loader').show();

		$.ajax({
			type : 'post',
			url : ugc_ajaxurl,
			dataType : 'json',
			data : {
					action: 'ugc_playlist',
					post_id: id,
					youtube_embed : embed
			},
			success : function(d) {
				console.log(d);
				if(d.code == 200) {
					cont.find('div').html(d.obj);
					p.find('.playlist-item').each(function(){
						$(this).removeClass('active');
					});
					_self.addClass('active');
					//setTimeout(function(){
						maintainRatioImage('.maintainratio');
					//},1000);
				}
				cont.find('button.loader').hide();
			},
			error : function(e) {
				console.log(e);
				cont.find('button.loader').hide();
			}
		});
	});

	function maintainRatioSlider() {
		$(".ugc-num-2 .slide-item, .ugc-num-3 .slide-item, .ugc-num-4 .slide-item, .ugc-num-5 .slide-item, .ugc-slider.article .slide-item, .maintainratio").each(function(){
			img = $(this).find('img');
			con = $(this);
			_self = img;

			//img.on('load', function(){
				if(!img.hasClass('set')) {
					img.attr('sizes','');

					fixSizeW = con.width();
					fixSizeH = con.height();
					w = img.width();
					h = img.height();

					console.log({_self,w,h,fixSizeW,fixSizeH});

					if(h > w) {
						Wr = fixSizeW;
						Hr = (h / w) * fixSizeW;
						x  = 0;
						y  = 0;
					} else {
						Wr = (w / h) * fixSizeH;
						Hr = fixSizeH;
						x  = 0 - ((Wr - fixSizeW) / 2);
						y  = 0;				
					}
					//console.log({w,h,fixSizeW,fixSizeH,Wr,Hr,x,y});

					img.css({
						'position': 'absolute',
						'max-width': Wr+'px',
						'width': Wr+'px',
						'height': Hr+'px',
						'left': x+'px',
						'top': y+'px'
					});

					img.addClass('set');
				}
			//}).each(function() {
			//  	if($(this).complete) { 
			//  		$($(this)).load();
			  		
			//  	}
			//}).attr('src',$(this).attr('src'));
		});
	}

	function maintainRatioImage(className) {
		$(className).each(function(){
			img = $(this).find('img');
			con = $(this);
			_self = $(this);

			//img.on('load', function(){
				if(!img.hasClass('set')) {
					img.attr('sizes','');

					fixSizeW = con.width();
					fixSizeH = con.height();
					w = img.width();
					h = img.height();

					//console.log({_self,w,h,fixSizeW,fixSizeH});

					if(h > w) {
						Wr = fixSizeW;
						Hr = (h / w) * fixSizeW;
						x  = 0;
						y  = 0;
					} else {
						Wr = (w / h) * fixSizeH;
						Hr = fixSizeH;
						x  = 0 - ((Wr - fixSizeW) / 2);
						y  = 0;				
					}

					console.log({w,h,fixSizeW,fixSizeH,Wr,Hr,x,y});

					img.css({
						'position': 'absolute',
						'max-width': Wr+'px',
						'width': Wr+'px',
						'height': Hr+'px',
						'left': x+'px',
						'top': y+'px'
					});

					img.addClass('set');
				}
			//}).each(function() {
			  //	if($(this).complete) { 
			  	//	$($(this)).load();
			  		
			  	//}
			//}).attr('src',$(this).attr('src'));

		});
	}	

	maintainRatioSlider();

	$(".slide-item").click(function(){
		var url = $(this).data('link');
		location.href = url;
	});


	$('.ugc-slider').each(function(){
		var display = $(this).data('display');
		var dots = (display == 1) ? true : false;

		

		if(display > 1) {
			$(this).slick({
				autoplay: true,
				arrows: false,
				dots: dots,
				infinite : true,
				draggable : true,
				slidesToShow: display,
		  		slidesToScroll: 1,
		  		adaptiveHeight: true
			});
		} else {
			$(this).slick({
				autoplay: true,
				arrows: false,
				dots: dots,
				infinite : true,
				draggable : true,
		  		adaptiveHeight: true
			});
		}


		maintainRatioSlider();
		
	});
		

	$(".ugc-slider-area").slick({
		autoplay: true,
		arrows: false,
		dots: false,
		infinite : true,
		draggable : true,
		slidesToShow: 4,
  		slidesToScroll: 1
	});
	$(".ugc-slider-area-mobile").slick({
		autoplay: true,
		arrows: false,
		dots: false,
		infinite : true,
		draggable : true,
		slidesToShow: 1,
  		slidesToScroll: 1
	});
});