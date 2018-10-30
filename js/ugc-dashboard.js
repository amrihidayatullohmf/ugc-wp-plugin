/*
* admin.js
* javascript common function for dashboard
* by Amri Hidayatulloh
*/

$(document).ready(function(){
	$(".checkall").click(function(){
		if($(this).prop('checked') == true) {
			$(".checkall").prop('checked',true);
			$(".checkitem").prop('checked',true);
		} else {
			$(".checkall").prop('checked',false);
			$(".checkitem").prop('checked',false);
		}
	});

	function removeRow(ids) {
		swal({
          title: "Are you sure?",
          text: 'This action will remove user content from site',
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, I Confirm!",
          cancelButtonText: "No, Cancel!",
          closeOnConfirm: true,
          closeOnCancel: true
        },
        function(isConfirm){
          if (isConfirm) {
          	$.ajax({
          		type : 'POST',
          		url : ajaxurl,
          		data : {action  : "ugc_remove_post",post_id:ids,is_bulk:1},
          		dataType : 'json',
          		success : function(d) {
          			console.log(d);
          			if(d.code == 200) {
          				swal('Yeay!',d.msg,'success');
          				setTimeout(function(){
          					location.reload();
          				},2000);
          			} else {
          				swal('Ops',d.msg,'error');
          			}

          			$(".remove-item").html('Remove');
          			$("#doaction").html('Apply');
          			$("#doaction2").html('Apply');
          		},
          		error : function(e) {
          			console.log(e);
          			swal('Ops','Unknown error occured !','error');
          			$(".remove-item").html('Remove');
          			$("#doaction").html('Apply');
          			$("#doaction2").html('Apply');
          		}
          	});
          } else {
          	$(".remove-item").html('Remove');
          	$("#doaction").html('Apply');
          	$("#doaction2").html('Apply');
          }
      	});

      	return false;
	}

	function setQueue(datas,idx) {
		console.log(datas);
		$("#counter").html(idx+'/'+datas.length);
		$("#sendlog").html('Sending to '+datas[idx][1]);

		$.ajax({
			type : 'POST',
          	url : ajaxurl,
          	data : {
          			action  : "ugc_approve_post",
          			ids : datas[idx][0],
          			email : datas[idx][1],
          			type : 'sending'
          			},
          	dataType : 'json',
          	success : function(d) {
          		if(d.code == 200) {
          			$("#sendlog").html('Sent to '+datas[idx][1]);
          		} else {
          			$("#sendlog").html('Failed and skipped !');
          		}

          		var new_index = idx + 1;
          		$("#counter").html(new_index+'/'+datas.length);

          		var len = (new_index / datas.length) * 100;
          		len = Math.round(len);

          		$("#progressbar").animate({'width':len+'%'},50);

          		if(new_index < datas.length) {
          			setTimeout(function(){
          				setQueue(datas,new_index);
          			},500);
          		} else {
          			$("#sendlog").html('Completed !');
          			setTimeout(function(){
          				$("#blackbg").fadeOut(100);
 	         			$("#sending-progress").fadeOut(100);
 	         			location.reload();
 	         		},1000);
          		}
          	},
          	error : function(e) {
          		console.log(e);
          		$("#sendlog").html('Failed! Queue is terminated.');	
          		$("#blackbg").fadeOut(100);
 	         	$("#sending-progress").fadeOut(100);
          	}
		});
	}

	function approveRow(ids) {
		swal({
          title: "Are you sure?",
          text: 'This action will publish user content from site',
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, I Confirm!",
          cancelButtonText: "No, Cancel!",
          closeOnConfirm: true,
          closeOnCancel: true
        },
        function(isConfirm){
          if (isConfirm) {
          	$("#blackbg").fadeIn(200);
          	setTimeout(function(){
          		$("#sending-progress").fadeIn(100);
          	},100);

          	$("#sendlog").html('Verifying...');

          	$.ajax({
          		type : 'POST',
          		url : ajaxurl,
          		data : {
          				action  : "ugc_approve_post",
          				ids : ids,
          				type : 'verify'
          			},
          		dataType : 'json',
          		success : function(d) {
          			console.log(d);
                         //console.table({hola:yes});
          			if(d.code == 200) {
          				setQueue(d.datas,0);
          			} else {
          				swal('Ops',d.msg,'error');
          				$("#blackbg").fadeOut(100);
 	         			$("#sending-progress").fadeOut(100);
          			}

          			$(".show-approve").html('Approve');
          			$("#doaction").html('Apply');
          			$("#doaction2").html('Apply');
          			
          		},
          		error : function(e) {
          			console.log(e);
          			swal('Ops','Unknown error occured !','error');
          			$(".show-approve").html('Approve');
          			$("#doaction").html('Apply');
          			$("#doaction2").html('Apply');
          			$("#blackbg").fadeOut(100);
          			$("#sending-progress").fadeOut(100);
          		}
          	});
          } else {
          	$(".show-approve").html('Approve');
          	$("#doaction").html('Apply');
          	$("#doaction2").html('Apply');
          	$("#blackbg").fadeOut(100);
          	$("#sending-progress").fadeOut(100);
          }
      	});

      	return false;
	}

	function revokeVoter(ids) {
		
		swal({
          title: "Are you sure?",
          text: 'This action will revoke user vote from site',
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#DD6B55",
          confirmButtonText: "Yes, I Confirm!",
          cancelButtonText: "No, Cancel!",
          closeOnConfirm: true,
          closeOnCancel: true
        },
        function(isConfirm){
          if (isConfirm) {
          	var parent_id = $("#parent_id").val();
          	//console.log($("#ajaxurl").val());
          	$.ajax({
          		type : 'POST',
          		url : ajaxurl,
          		data : {
          			action  : "ugc_remove_like",
          			ids : ids,
          			parent_id : parent_id
          		},
          		dataType : 'json',
          		success : function(d) {
          			console.log(d);
          			if(d.code == 200) {
          				swal('Yeay!',d.msg,'success');
          				setTimeout(function(){
          					location.reload();
          				},2000);
          			} else {
          				swal('Ops',d.msg,'error');
          			}

          			$(".remove-voter").html('Remove');
          			$("#doaction").html('Apply');
          			$("#doaction2").html('Apply');
          		},
          		error : function(e) {
          			console.log(e);
          			swal('Ops','Unknown error occured !','error');
          			$(".remove-voter").html('Remove');
          			$("#doaction").html('Apply');
          			$("#doaction2").html('Apply');
          		}
          	});
          } else {
          	$(".remove-voter").html('Remove');
          	$("#doaction").html('Apply');
          	$("#doaction2").html('Apply');
          }
      	});

      	return false;
	}

	function doAction(type) {
		var ids = [];

		$(".checkitem").each(function(){
			if($(this).prop('checked') == true) {
				ids.push($(this).val());
			}
		});

		var _ids = ids.join(',');

		$("#doaction").html('Processing...');
        $("#doaction2").html('Processing...');

		if(type == 'remove') {
			removeRow(_ids);
		} else if(type == 'approve') {
			approveRow(_ids);
		} else if(type == 'revoke') {
			revokeVoter(_ids);
		}	
	}

	$(".remove-item").click(function(){
		var id = $(this).data('id');
		$(this).html('Loading');
		removeRow(id);
	});

	$(".show-approve").click(function(){
		var id = $(this).data('id');
		$(this).html('Loading');
		approveRow(id);
	});

	$(".remove-voter").click(function(){
		var id = $(this).data('id');
		$(this).html('Loading');
		revokeVoter(id);
	});



	$("#doaction").click(function(){
		var val = $("#bulk-action-selector-top").val();
		if(val == '-1') {
			val = $("#bulk-action-selector-bottom").val();
		}

		if(val == '-1') {
			$("#bulk-action-selector-top").focus();
			return false;
		}

		doAction(val);
	});

	$("#doaction2").click(function(){
		var val = $("#bulk-action-selector-bottom").val();
		if(val == '-1') {
			val = $("#bulk-action-selector-top").val();
		}

		if(val == '-1') {
			$("#bulk-action-selector-bottom").focus();
			return false;
		}

		doAction(val);
	});
});