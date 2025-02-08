function submit_batch_form(){
	$.ajax({
		type:'POST',
		url:OC.linkTo('batch','ajax/set_settings.php'),
				dataType:'json',
				data: {script_folder:  getScriptFolder()},
				async: false,
				success: function(s){
					if(s.length!=0 && s.status){
						OC.msg.finishedSaving('#batch_msg', {status: 'success', data: {message:  	t('batch', s.status)}});
					}
					else{
						OC.msg.finishedSaving('#batch_msg', {status: 'success', data: {message:  	t('batch', 'Settings saved')}});
					}
				},
				error:function(s){
					$("#batch_msg").html("Unexpected error!");
				}
	});
}

function get_default_scripts(){
	$.ajax({
		type:'GET',
		url:OC.linkTo('batch','ajax/get_default_scripts.php'),
				dataType:'json',
				data: {},
				async: false,
				success: function(s){
					if(s.length!=0 && s.status){
						OC.msg.finishedSaving('#batch_scripts_msg', {status: 'success', data: {message:  	t('batch', s.status)}});
					}
					else{
						OC.msg.finishedSaving('#batch_scripts_msg', {status: 'success', data: {message:  	t('batch', "Scripts copied to "+getScriptFolder())}});
					}
				},
				error:function(s){
					$("#batch_scripts_msg").html("Unexpected error!");
				}
	});
}

function batchInfo(){
	var html = "<div><h2>"+t("user_orcid", "About ScienceData Batch")+" <img class='batchAboutIcon' src='"+OC.webroot+"/apps/batch/img/stack.svg'></h2>\
	<a class='oc-dialog-close close svg'></a>\
	<div class='about-batch'></div></div>";

	$(html).dialog({
	  dialogClass: "oc-dialog-batch",
	  resizeable: true,
	  draggable: true,
	  modal: false,
	  height: 420,
	  width: 460,
		buttons: [{
			"id": "batchinfo",
			"text": "OK",
			"click": function() {
				$( this ).dialog( "close" );
			}
		}]
	});
	
	$('body').append('<div class="modalOverlay"></div>');
	
	$('.oc-dialog-close').live('click', function() {
	$(".oc-dialog-batch").remove();
	$('.modalOverlay').remove();
	});
	
	$('.ui-helper-clearfix').css("display", "none");
	
	$.ajax(OC.linkTo('batch', 'ajax/about_batch.php'), {
	type: 'GET',
	success: function(jsondata){
		if(jsondata) {
			$('.about-batch').html(jsondata.data.page);
		}
	},
	error: function(data) {
		alert("Unexpected error!");
	}
	});
}

function enableGetDefaultBatchScripts(enabled){
	if(enabled){
		$('#get_default_batch_scripts').css('cursor', 'pointer');
		$('#get_default_batch_scripts').removeAttr('disabled');
	}
	else{
		$('#get_default_batch_scripts').attr('disabled', 'disabled');
		$('#get_default_batch_scripts').css('cursor', 'default');
	}
}

$(document).ready(function(){
	$("#batch_settings_submit").bind('click', function(){
		enableGetDefaultBatchScripts( getScriptFolder());
		submit_batch_form();
	});
	if(!getScriptFolder()){
		enableGetDefaultBatchScripts(false);
	}
	$("#get_default_batch_scripts").bind('click', function(){
		if(!$(this).attr('disabled')){
			get_default_scripts();
		}
	});
	$("#batch-info").click(function (ev) {
		batchInfo();
	});
	
	$(document).click(function(e){
		if (!$(e.target).parents().filter('.oc-dialog-batch').length && !$(e.target).filter('#batch-info').length ) {
			$(".oc-dialog-batch").remove();
			$('.modalOverlay').remove();
		}
	});
	
});

