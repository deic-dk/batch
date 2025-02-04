function setScriptFolder(folder){
  $('#batch_script_folder').val(folder);
	if(folder){
		enableGetDefaultBatchScripts(true);
	}
}

function getScriptFolder(){
  return $('#batch_script_folder').val();
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

	var choose_script_folder_dialog;
	var buttons = {};
	buttons[t("batch", "Choose")] = function() {
		folder = stripTrailingSlash($('#batch_script_folder').text());
		setScriptFolder(folder);
		choose_script_folder_dialog.dialog("close");
 	};
 	buttons[t("batch", "Cancel")] = function() {
 		choose_script_folder_dialog.dialog("close");
	};
	choose_script_folder_dialog = $("div.script_folder_dialog").dialog({//create dialog, but keep it closed
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

  $('.batch_choose_script_folder').live('click', function(){
  	choose_script_folder_dialog.dialog('open');
  	choose_script_folder_dialog.show();
  	$('.ui-dialog-buttonpane').show();
  	$('.ui-dialog-titlebar').show();
	folder = stripLeadingSlash(getScriptFolder());
	group = $('#user_groups_move_select').val();
	if(group=='home'){
		group = '';
	}
	$('.script_folder_dialog div.loadFolderTree').fileTree({
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
	  $('#batch_script_folder').text(file);
	},
	// double-click
	function(file) {
	  if(file.indexOf("/", file.length-1)!=-1){// folder double-clicked
	  	setScriptFolder(file);
	  	choose_script_folder_dialog.dialog("close");
	  }
	});
  });

});
