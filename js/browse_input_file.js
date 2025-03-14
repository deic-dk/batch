function setInputFile(file){
  $('#batch_input_file').val(file);
}

$(document).ready(function(){

	//var choose_input_file_dialog;
	var buttons = {};
	buttons[t("batch", "Choose")] = function() {
		folder = $('#batch_input_file').val();
		if(folder.substr(-1)=='/') {
			folder = folder.substr(0, folder.length - 1);
		}
		if(folder.substr(1)!='/') {
			folder = '/'+folder;
		}
		setInputFile(folder);
		choose_input_file_dialog.dialog("close");
 	};
 	buttons[t("batch", "Cancel")] = function() {
 		choose_input_file_dialog.dialog("close");
	};
	choose_input_file_dialog = $("div.batch_input_file_dialog").dialog({//create dialog, but keep it closed
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

  $('.batch_choose_input_file').live('click', function(){
  	choose_input_file_dialog.dialog('open');
  	choose_input_file_dialog.show();
  	$('.ui-dialog-buttonpane').show();
  	$('.ui-dialog-titlebar').show();
	file = $('#batch_input_file').val();
	if(file.substr(0,1)=='/') {
		file = file.substr(1, folder.length-1);
	}
	group = $('#batch_in_out_files #group_folder').val();
	if(group=='home'){
		group = '';
	}
	$('.batch_input_file_dialog div.loadFolderTree').fileTree({
	  //root: '/',
	  script: '../../apps/chooser/jqueryFileTree.php',
	  multiFolder: false,
	  selectFile: true,
	  selectFolder: false,
	  folder: '/',
	  file: '',
	  group: group
	},
	// single-click
	function(file) {
	  $('#batch_input_file').val(file);
	},
	// double-click
	function(file) {
	  if(file.slice(-1)!="/"){// file double-clicked
	  	setInputFile(file);
	  	choose_input_file_dialog.dialog("close");
	  }
	});
  });

});
