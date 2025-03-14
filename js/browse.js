function setBatchFolder(folder){
  $('#batch_folder').val(folder);
	if(folder){
		enableGetDefaultBatchScripts(true);
	}
}

function getBatchFolder(){
  return $('#batch_folder').val();
}

function stripTrailingSlash(str) {
  if(str.substr(-1)=='/') {
	str = str.substr(0, str.length - 1);
  }
  if(str.substr(1)!='/') {
	str = '/'+str;
  }
  return str;
}

function stripLeadingSlash(str) {
  if(str.substr(0,1)=='/') {
	str = str.substr(1, str.length-1);
  }
  return str;
}

$(document).ready(function(){

	var choose_batch_folder_dialog;
	var buttons = {};
	buttons[t("batch", "Choose")] = function() {
		folder = stripTrailingSlash($('#batch_folder').val());
		setBatchFolder(folder);
		choose_batch_folder_dialog.dialog("close");
 	};
 	buttons[t("batch", "Cancel")] = function() {
 		choose_batch_folder_dialog.dialog("close");
	};
	choose_batch_folder_dialog = $("div.batch_folder_dialog").dialog({//create dialog, but keep it closed
   title: t("batch", "Choose folder"),
    autoOpen: false,
    height: 440,
    width: 620,
    modal: true,
    dialogClass: "no-close",
    autoOpen: false,
    resizeable: false,
    draggable: false,
    buttons: buttons
  });

  $('.batch_choose_batch_folder').live('click', function(){
  	choose_batch_folder_dialog.dialog('open');
  	choose_batch_folder_dialog.show();
  	$('.ui-dialog-buttonpane').show();
  	$('.ui-dialog-titlebar').show();
	folder = stripLeadingSlash(getBatchFolder());
	group = $('#user_groups_move_select').val();
	if(group=='home'){
		group = '';
	}
	$('.batch_folder_dialog div.loadFolderTree').fileTree({
	  //root: '/',
	  script: '../../apps/chooser/jqueryFileTree.php',
	  multiFolder: false,
	  selectFile: false,
	  selectFolder: true,
	  folder: folder,
	  file: '',
	  group: group
	},
	// single-click
	function(file) {
	  $('#batch_folder').val(file);
	},
	// double-click
	function(file) {
	  if(file.indexOf("/", file.length-1)!=-1){// folder double-clicked
	  	setBatchFolder(file);
	  	choose_batch_folder_dialog.dialog("close");
	  }
	});
  });

});
