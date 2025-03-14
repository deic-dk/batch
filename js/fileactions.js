function batchDrop(event){
	// submit multiple files
	event.stopPropagation();
	if($('#batchDrop').length>0){
		$('#batchDrop').detach();
		return;
	}
	var selectedFiles = FileList.getSelectedFiles();
	var files = [];
	for( var i=0;i<selectedFiles.length;++i){
		files.push(selectedFiles[i].name);
	}
	//batchCreateUI(files);
	getJobTemplatesFolder(files, batchCreateUI);
	return false;
};

function addSelectedBatchAction(){
	$('#headerName .selectedActions').each(function(){
		if(!$(this).find('.batch').length){
			$(this).prepend('<a class="batch btn btn-xs btn-default" href=""><img src="'+OC.imagePath('batch', 'stack.svg')+'" />'+t('batch',' Process')+'</a>&nbsp;');
			$(this).find('.batch').click(batchDrop)
		}
	});
}

function getJobTemplatesFolder(files, callback){
	$.ajax(OC.linkTo('batch', 'ajax/actions.php'), {
		data: {
			action: 'get_job_templates_folder'
		},
		type: "GET",
		dataType: 'json',
		success: function(data) {
			callback(files, data.data.job_templates_folder);
		}
	});
}

function batchCreateUI(files, jobTemplatesFolder){
	var html = '<div id="batchDrop" class="batchUI">';
	html += '<form action="#" id="batchForm">';
	//html += '<select id=user_groups_move_select><option value="home">'+t('batch', 'Home')+'</option></select>';
	html += '<input id="fileList" placeholder="'+t('batch','Job template')+'"><br>';
	// Files to be processed
	html += '<input type="hidden" id="dirFiles" value="'+encodeURIComponent(JSON.stringify(files))+'" />';
	html += '<input type="submit" id="fileListSend" value="'+t('batch','Submit')+'" />';
	html += '<strong id="mvWarning"></strong></form>';
	html += '</div>';
	$(html).addClass('batch').appendTo('.viewcontainer:not(.hidden) #headerName .selectedActions');
	$('#fileList').focus(function(){
		$('#fileList').autocomplete("search","");
	});
	// get autocompletion names

	$('#fileList').autocomplete({minLength:0,
		appendTo: '#batchForm',
		open: function() {
			$("ul.ui-menu").width($(this).innerWidth());
			$("ul.ui-menu").css('max-height', '178px');
			$("ul.ui-menu").css('background', '#e0e0e0');
			$("ul.ui-menu li").css('width', 'max-content');
			$("ul.ui-menu").css('overflow',  'scroll');
			$("ul.ui-menu").css('scrollbar-color',  '#747474 #e0e0e0');
		},
		source: function(request, response) {
			$.getJSON(
				OC.filePath('batch','ajax', 'autocompletescriptfiles.php'),
				{
					dir: jobTemplatesFolder,
					StartDir: jobTemplatesFolder, // using current input to allow access to more than n levels depth,
					//StartDir: $('.viewcontainer:not(.hidden) #dir').val(),
					},
					function(scriptFile){
						$('#fileList').autocomplete('option','autoFocus', true);
						response(scriptFile);
				}
			);
		}
	});
	$('#fileList').focus();
	}

$(document).ready(function() {
	if(/(public)\.php/i.exec(window.location.href)!=null) return; // escape when the requested file is public.php

	addSelectedBatchAction();
	
	$('#batchForm').live('submit', function(e){
		var tr = $(e.target).closest('tr');
		var group = '';
		if(typeof OCA.Files.App.fileList.getGroup !== 'undefined'){
			group = OCA.Files.App.fileList.getGroup();
		}
		var script = $('.viewcontainer:not(.hidden) #fileList').val();
		var selectedFiles = FileList.getSelectedFiles();
		var files = [];
		var dir = FileList.getCurrentDirectory();
		for( var i=0;i<selectedFiles.length;++i){
			if(selectedFiles[i].mimetype!='httpd/unix-directory'){
				files.push(dir+'/'+selectedFiles[i].name);
			}
		}
		var dir  = $('.viewcontainer:not(.hidden) #dir').val();
		if(group){
			var re = new RegExp( "^/*"+group+"/");
			dir = dir.replace(re,"/");
		}
		var srcId = tr.attr('data-id');
		var srcOwner = tr.attr('data-share-owner-uid');
		var dirId = getParam($('.crumb.last a').attr('href'), 'id');
		if(!$('.viewcontainer:not(.hidden) .batch-message').length){
			$('<div class="msg batch-message"><span class="msg wait"></span></div>').insertAfter('.viewcontainer:not(.hidden) #controls');
		}
		OC.msg.startAction('.viewcontainer:not(.hidden) .batch-message span', t("batch", "Submitting - please wait..."));
		$.ajax({
			type: 'POST',
			url: OC.linkTo('batch','ajax/actions.php'),
			cache: false,
			data: {action: 'submit',
									job_template: script,
									dir: dir,
									input_files: JSON.stringify(files),
									group: group,
									id: srcId,
									owner: srcOwner,
									parent_id: dirId
			},
			success: function(data){
				if(data.status=="error"){
					OC.msg.finishedAction('.viewcontainer:not(.hidden) .batch-message span',  {status: 'error',
						data: {message: t("batch", "Error submitting job(s). ")+(data.message?data.message:'')}});
				}
				else{
					OC.msg.finishedAction('.viewcontainer:not(.hidden) .batch-message span',  {status: 'success',
						data: {message: t("batch", "Finished submitting.")}});
				}
			},
			error: function(data){
				OC.Notification.show("Unexpected error");
			}
		});
		$('#fileList').autocomplete("close");
		$('#batchDrop').detach();
		return false;
	});
	
	$(this).click(function(event){
		if( (!($(event.target).hasClass('ui-corner-all')) && $(event.target).parents().index($('.ui-menu'))==-1) &&
			(!($(event.target).hasClass('batchUI')) && $(event.target).parents().index($('#batchDrop'))==-1)){
			$('#batchDrop').detach();
		}
	});

});
