function getApiUrl(){
  return $('#api_url').val();
}

function submit_batch_form(){
	$.ajax({
		type:'POST',
		url:OC.linkTo('batch','ajax/set_settings.php'),
				dataType:'json',
				data: {batch_folder:  getBatchFolder(), api_url: getApiUrl()},
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

function get_job_templates(){
	$.ajax({
		type:'GET',
		url:OC.linkTo('batch','ajax/actions.php'),
				dataType:'json',
				data: {action: 'copy_over_job_templates'},
				async: false,
				success: function(s){
					if(s.length!=0 && s.status){
						OC.msg.finishedSaving('#batch_scripts_msg', {status: 'success', data: {message:  	t('batch', s.status)}});
					}
					else{
						OC.msg.finishedSaving('#batch_scripts_msg', {status: 'success', data: {message:  	t('batch', "Scripts copied to "+getBatchFolder())}});
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
		$('#get_job_templates').css('cursor', 'pointer');
		$('#get_job_templates').removeAttr('disabled');
	}
	else{
		$('#get_job_templates').attr('disabled', 'disabled');
		$('#get_job_templates').css('cursor', 'default');
	}
}

$(document).ready(function(){
	$("#batch_settings_submit").bind('click', function(){
		enableGetDefaultBatchScripts(getBatchFolder());
		submit_batch_form();
	});
	if(!getBatchFolder()){
		enableGetDefaultBatchScripts(false);
	}
	$("#get_job_templates").bind('click', function(){
		if(!$(this).attr('disabled')){
			get_job_templates();
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

