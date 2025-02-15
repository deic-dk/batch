function keks(event){
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
	batchCreateUI(false, files);
	return false;
};

function addSelectedAction(){
	$('#headerName .selectedActions').each(function(){
		if(!$(this).find('.batch').length){
			$('<a class="move btn btn-xs btn-default" href=""><img src="'+OC.imagePath('batch', 'stack.svg')+'" />'+t('batch',' Batch process')+'</a>').prependTo($(this));
			$(this).find('.batch').click(keks)
		}
	});
}

function batchCreateUI(files){
	var html = '<div id="mvDrop" class="mvUI">';
	html += '<form action="#" id="mvForm"><div><input type="checkbox" id="dirCopy"';
	if(!permUpdate || copy) html += ' checked';
	if(!permUpdate) html += ' disabled';
	html += '></input><label for="dirCopy">'+t('files_mv','Copy')+'</label></div>';
	html += '<select id=user_groups_move_select><option value="home">'+t('files_mv', 'Home')+'</option></select>';
	html += '<input id="dirList" placeholder="'+t('files_mv','Destination directory')+'"><br>';
	html += '<input type="hidden" id="dirFiles" value="'+encodeURIComponent(JSON.stringify(files))+'" />';
	html += '<input type="submit" id="dirListSend" value="'+t('files_mv','Move')+'" />';
	html += '<strong id="mvWarning"></strong></form>';
	html += '</div>';
	$(html).addClass('mv').appendTo('.viewcontainer:not(.hidden) #headerName .selectedActions');
	$('#dirList').focus(function(){
		$('#dirList').autocomplete("search","");
	});
	// get autocompletion names

	$('#dirList').autocomplete({minLength:0,
		appendTo: '#mvForm',
		open: function() {
			$("ul.ui-menu").width($(this).innerWidth());
			$("ul.ui-menu").css('max-height', '178px');
			$("ul.ui-menu").css('background', '#e0e0e0');
			$("ul.ui-menu li").css('width', 'max-content');
			$("ul.ui-menu").css('overflow',  'scroll');
			$("ul.ui-menu").css('scrollbar-color',  '#747474 #e0e0e0');
		},
		source: function(request, response) {
			var selectedGroup = $('.viewcontainer:not(.hidden) #user_groups_move_select').val();
			if(selectedGroup=='home' && $('.viewcontainer:not(.hidden) #user_groups_move_select option:selected').text()==t('files_mv', 'Home')){
				selectedGroup = '';
			}
			$.getJSON(
				//OC.filePath('files_mv','ajax', 'autocompletedir.php'),
				OC.webroot+'/themes/deic_theme_oc7/apps/files_mv/ajax/autocompletedir.php',
				{
					files: JSON.stringify(files),
					dir: $('.viewcontainer:not(.hidden) #dir').val(),
					StartDir: $('#dirList').val(), // using current input to allow access to more than n levels depth,
					//StartDir: $('.viewcontainer:not(.hidden) #dir').val(),
					group: selectedGroup
					},
					function(dir){
						var group = '';
						if(typeof OCA.Files.App.fileList.getGroup !== 'undefined'){
							group = OCA.Files.App.fileList.getGroup();
							if(group==null){
								group = '';
							}
						}
						//alert(selectedGroup+'!='+group+'-->'+dir.toSource());
						if(selectedGroup!=group && $.inArray('/', dir)==-1){
							dir.unshift('/');
						}
						$('#dirList').autocomplete('option','autoFocus', true);
						response(dir);
				}
			);
		}
	});
	$('#dirList').focus();
	}

$(document).ready(function() {
	if(/(public)\.php/i.exec(window.location.href)!=null) return; // escape when the requested file is public.php

	addSelectedAction();
	
});