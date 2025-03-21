//////////// begin  helper functions /////////////
function getRowElementPlain(name, value){
	return "\n <td>\n  <div column='" + name + "'>\n   <span>" + value + "</span>\n  </div>\n </td>";
}

function getRowElementSelector(identifier){
	return "\n <td>\n  <div column='" + name + "'>\n   <input identifier='"+identifier+"' type='checkbox' class='fileselect jobselect'>\n  </div>\n </td>";
}

function getExpandedRowElementView(name, items, urls){
	var ret = "\n<tr><td  class='expanded-column-name'><span>"+name+"</span></td><td  class='expanded-column-value'><span>";
	var item;
	var proxyAttr;
	var re = new RegExp( "https://"+window.location.host+".*");
	var re1 = new RegExp( "https://10\.2\.[0-9]+\.[0-9]+/.*");
	for(var i=0; i<items.length; ++i){
		item = items[i].replace(/^.*\/([^\/]+)$/, '$1');
		proxyAttr = "filename='"+item+"' ";
		if(urls[i].match(re) || urls[i].match(re1)){
			// We're getting a file from our own files on this silo
			proxyAttr = proxyAttr+"class='proxy' proxy='files' ";
		}
		else if(!urls[i].match(/https:\/\/[^\.\/]+\.[^\.\/]+.*/) || items[i]=="job"){
			// We're getting a file from https://batch/
			proxyAttr = proxyAttr+"class='proxy' proxy='batch' ";
		}
		ret = ret + "&nbsp;<a "+proxyAttr+"href='"+localToGlobal(urls[i])+"'>"+item+"</a>";
		if(items[i]=="stdout" || items[i]=="stderr" || items[i]=="job" || items[i].endsWith(".sh")){
			ret = ret +"<a title='Direct link' class='link' href='"+localToGlobal(urls[i])+"'>🔗</a>&nbsp;";
		}
	}
	ret = ret + "</span></td></tr>";
	return ret;
}

// E.g. https://batch.sciencedata.dk/db/jobs/78c06e05-fa1c-11ef-95b0-ab51157ffb1b
//
// identifier name csStatus userInfo inputFileURLs outFileMapping providerInfo stdoutDest stderrDest createdlastModified outTmp errTmp jobID metaData host runningSeconds ramMB executable executables opSys runtimeEnvironments allowedVOs virtualize dbUrl

function setExpandedTable(job, tr){
	var identifier = job['identifier'].replace(/^.*\/([^\/]+)$/, '$1');
	getJobInfo(identifier, job, tr, updateTr);
}

function localToGlobal(url){
	if(url.match(/https:\/\/[^\.\/]+\.[^\.\/]+.*/)){
		return url;
	}
	var api_url = $('#app-content-batch').attr('apiUrl');
	var local_api_host = api_url.replace(/^https:\/\/([^\/\.]+[\.\/].*)/, '$1');
	return url.replace(/^https:\/\/[^\/\.]+\/*/, api_url);
}

function updateTr(job, jobInfo, tr){
	var inputFileNames = [];
	var inputFileURLs = [];
	var api_url = $('#app-content-batch').attr('apiUrl');
	//inputFileNames.push("job");
	//inputFileURLs.push(api_url+"gridfactory/jobs/"+job['identifier']+"/job");
	inputFileNames.push(...jobInfo['inputFileURLs'].split(" "));
	inputFileURLs.push(...jobInfo['inputFileURLs'].split(" "));
	var html = "<tr class='expanded-row' identifier='" + job['identifier'] + "'> <td colspan='5'>" +
	"\n<table id='expanded-" + job['identifier'] + "' class='panel expanded-table'>" +
	"\n <tr><td class='expanded-column-name'>created:</td> <td class='expanded-column-value'><span>" +  job['created'] + "</span></td></tr>" +
	"\n <tr><td class='expanded-column-name'>lastModified:</td> <td class='expanded-column-value'><span>" + jobInfo['lastModified'] + "</span></td></tr>" +
	"\n <tr><td class='expanded-column-name'>nodeId:</td> <td class='expanded-column-value'><span>" + jobInfo['nodeId'] + "</span></td></tr>" +
	"\n <tr><td class='expanded-column-name'>userInfo:</td> <td class='expanded-column-value'><span>" + jobInfo['userInfo'] + "</span></td></tr>" +
	"\n <tr><td class='expanded-column-name'>providerInfo:</td> <td class='expanded-column-value'><span>" + jobInfo['providerInfo'] + "</span></td></tr>" +
	getExpandedRowElementView('output', ['stdout', 'stderr'], [ jobInfo['stdoutDest'],  jobInfo['stderrDest']]) +
	getExpandedRowElementView('input files', inputFileNames, inputFileURLs) +
	getExpandedRowElementView('output files', [jobInfo['outFileMapping'].split(" ")[0]], [jobInfo['outFileMapping'].split(" ")[1]]) +
	"\n</table>" +
	"\n </td> </tr>";
	tr.after(html);
	$('a.proxy').click(function(ev){
		ev.stopPropagation();
		ev.preventDefault();
		if($(this).attr('proxy')=='batch' ){
			var proxyUrl = "ajax/actions.php?action=get_file&identifier="+job['identifier']+"&filename="+$(this).attr('filename')+"&url="+$(this).attr('href')+"&status="+(jobInfo['csStatus']).split(':')[0]+"&requesttoken="+oc_requesttoken;
			if($(this).attr('filename')=='job' || $(this).attr('filename')=='stdout' || $(this).attr('filename')=='stderr' || $(this).attr('filename').endsWith('.sh')){
					// Request will be sent for output if stdout/err requested
					// Pop up alert and load output into window.
				var title = $(this).text();
				$("#loading-text").text(t("batch",  "Retrieving file..."));
				$('#loading').show();
				$('#textLoad').load(proxyUrl, "", function(text, status, xhr){$('#loading').hide(); OC.dialogs.info(text, title, function(){$('.oc-dialog').remove()});});
			}
			else{
				window.location=proxyUrl+'&download=true';
			}
		}
		else 	if($(this).attr('proxy')=='files'){
			var path = $(this).attr('href').replace(/https:\/\/[^\/]+\/[^\/]+\//, '/');
			// Just redirect to local SD file
			var dir = path.replace(/\/[^\/]+$/, '');
			var file = path.replace(/.*\/([^\/]+)$/, '$1');
			OC.redirect(OC.webroot+'/index.php/apps/files?dir='+dir+'&file='+file);
		}
	});
}

function getRow(job){
	//visible part
	var identifier = job['identifier'].replace(/^.*\/([^\/]+)$/, '$1');
	var str = "  <tr class='simple-row' userInfo='" + job['userInfo'] + "' identifier='"+ identifier +
		"' created='" + job['created'] + 	"' url='" + job['identifier'] +"'>" + "'>" +
		getRowElementSelector(identifier)+
		getRowElementPlain('ID', identifier) +
		getRowElementPlain('name', job['name']) +
		getRowElementPlain('status', job['csStatus']) +
		"\n<td class='td-button'><a href='#' title=" + t('batch', 'Expand') + " class='expand-view permanent action icon icon-right-open'></a></td>" +
		"\n<td class='td-button'><a href='#' title=" + t('batch', 'Delete job') + " class='delete-job permanent action icon icon-trash-empty'></a></td>" +
		"\n</tr>";
	return str;
}

function updateJobCount(){
	var count_shown = $('table#jobstable tbody#fileList').children('tr.simple-row').length;
	$('table#jobstable tfoot.summary tr td span.info').remove();
	$('table#jobstable tfoot.summary tr td').append("<span class='info' jobs='" + count_shown + "'>" +
		count_shown + " " + (count_shown === 1 ? t("batch", "job") : t("batch", "jobs")) +
		"</span");
}

//////////// begin core api functions /////////////
function listJobs(callback){
	$.ajax({
		url: OC.filePath('batch', 'ajax', 'actions.php'),
		data: {
			action: 'list_jobs',
			job_names: ''
		},
		beforeSend: function(xhr){
			ajaxBefore(xhr, "Retrieving table data...");
		},
		success: function(jsondata){
			$('#selectAllJobs').prop('checked', false);
			if(jsondata.status == 'success'){
				var expanded_views = [];
				// make an array of the job identifiers whose views are expanded
				$('#jobstable #fileList tr.simple-row td a.icon-down-open').closest('tr').each(function(){
					expanded_views.push($(this).attr('identifier'));
				});
				$('#jobstable #fileList tr').remove();
				// remove all of the table rows, and clear any remaining tooltips
				$('body > div.tipsy').remove();
				jsondata.data.forEach(function(value, index, array){
					$('tbody#fileList').append(getRow(value));
				});
				updateJobCount();
				$('table#jobstable #fileList tr.simple-row').each(function(){
					if($.inArray($(this).attr("identifier"), expanded_views) !== -1){
						toggleExpanded($(this).find('td a.expand-view'));
					}
				});
				if(callback){
					callback();
				}
			}
			else if(jsondata.status == 'error'){
				if(jsondata.data && jsondata.data.error && jsondata.data.error == 'authentication_error'){
					OC.redirect('/');
				}
				else{
					OC.dialogs.alert(t("batch", "list_jobs: Something went wrong. Please set a work directory in your preferences."), t("batch", "Error"));
				}
			}
		},
		error:  function(jsondata){
			OC.dialogs.alert(t("batch", "list_jobs: Something went wrong. "+jsondata), t("batch", "Error"));
		},
		complete: function(xhr){
			ajaxCompleted(xhr);
		}
	});
}

function submitJob(job_template_text, input_file, group){
	var files = [];
	files.push(input_file);
	$.ajax({
		url: OC.filePath('batch', 'ajax', 'actions.php'),
		data: {
			action: 'submit',
			job_template_text: job_template_text,
			input_files: JSON.stringify(files),
			group: group
		},
		method: 'post',
		beforeSend: function(xhr){
			ajaxBefore(xhr, "Submitting job...");
		},
		success: function(jsondata){
			if(jsondata.status == 'success'){
				listJobs();
				// if a previous run_job call has outstanding timeouts, clear them
				$.submitJobTimeouts.forEach(function(timeout){
					clearTimeout(timeout);
				});
				$.submitJobTimeouts = [];
				$.submitJobTimeouts.push(setTimeout(function(){
					listJobs();
				}, 10000));
				$.submitJobTimeouts.push(setTimeout(function(){
					listJobs();
				}, 30000));
				$.submitJobTimeouts.push(setTimeout(function(){
					listJobs();
				}, 60000));
			}
			else if(jsondata.status == 'error'){
				if(jsondata.data && jsondata.data.error && jsondata.data.error == 'authentication_error'){
					OC.redirect('/');
				}
				else if(jsondata.data.message){
					OC.dialogs.alert(t("batch", "submit_job: " + jsondata.data.message), t("batch", "Error"));
				}
			}
		},
		error:  function(jsondata){
			OC.dialogs.alert(t("batch", "submit_job: Something went wrong. "+jsondata), t("batch", "Error"));
		},
		complete: function(xhr){
			ajaxCompleted(xhr);
		}
	});
}

function getJobInfo(identifier, job, tr, callback){
	$.ajax({
		url: OC.filePath('batch', 'ajax', 'actions.php'),
		data: {
			action: "get_job_info",
			identifier: identifier
		},
		method: 'get',
		beforeSend: function(xhr){
			ajaxBefore(xhr, "Getting job info...");
		},
		complete: function(xhr){
			ajaxCompleted(xhr);
		},
		success: function(data){
			if(data.status == 'success'){
				$('tr[identifier="' + data.identifier + '"]').remove();
				// if a tooltip is shown when the element is removed, then there is no mouseover event to get rid of it.
				$('body > div.tipsy').remove();
				if(typeof data.data!=='undefined' && data.data!=null){
					callback(job, data.data, tr);
				}
				else{
					OC.dialogs.alert(t("batch", "get_job_info: Something went wrong..."), t("batch", "Error"));
					$('#jobstable tr[identifier="' + identifier + '"] td div[column=status] span').text('Could not retrieve job info.');
				}
			}
			else if(data.status == 'error'){
				if(data.data && data.data.error && data.data.error == 'authentication_error'){
					OC.redirect('/');
				}
				else{
					OC.dialogs.alert(t("batch", "get_job_info: Something went wrong..."), t("batch", "Error"));
					$('#jobstable tr[identifier="' + identifier + '"] td div[column=status] span').text('Could not retrieve job info.');
				}
			}
		},
		error:  function(jsondata){
			OC.dialogs.alert(t("batch", "run_job: Something went wrong. "+jsondata), t("batch", "Error"));
		}
	});
}

function deleteJobs(identifiers){
	$.ajax({
		url: OC.filePath('batch', 'ajax', 'actions.php'),
		data: {
			action: "delete_jobs",
			identifiers: JSON.stringify(identifiers)
		},
		method: 'post',
		beforeSend: function(xhr){
			ajaxBefore(xhr, "Deleting your job(s)...");
			for(var i=0; i<identifiers.length; ++i){
				$('#jobstable tr[identifier="' + identifiers[i] + '"] td a.delete-job').hide();
				$('#jobstable tr[identifier="' + identifiers[i] + '"] td div[column=status] span').text('Deleting');
			}
		},
		complete: function(xhr){
			ajaxCompleted(xhr);
		},
		success: function(data){
			if(data.status == 'success'){
				$('tr[identifier="' + data.identifier + '"]').remove();
				// if a tooltip is shown when the element is removed, then there is no mouseover event to get rid of it.
				$('body > div.tipsy').remove();
				$.submitJobTimeouts.forEach(function(timeout){
					clearTimeout(timeout);
				});
				$.submitJobTimeouts = [];
				$.submitJobTimeouts.push(setTimeout(function(){
					listJobs();
				}, 10000));
			}
			else if(data.status == 'error'){
				if(data.data && data.data.error && data.data.error == 'authentication_error'){
					OC.redirect('/');
				}
				else{
					OC.dialogs.alert(t("batch", "delete_job: Something went wrong..."), t("batch", "Error"));
					$('#jobstable tr[identifier="' + identifier + '"] td a.delete-job').show();
					$('#jobstable tr[identifier="' + identifier + '"] td div[column=status] span').text('Delete failed');
				}
			}
		},
		error:  function(jsondata){
			OC.dialogs.alert(t("batch", "run_job: Something went wrong. "+jsondata), t("batch", "Error"));
		}
	});
}

//////////// begin page interaction functions /////////////
function toggleExpanded(expander){
	if(expander.attr("class").search("icon-down-open") === -1){
		expander.closest('tr').next().show();
		expander.removeClass("icon-right-open").addClass("icon-down-open");
		//expanded information
		var tr = expander.closest('tr.simple-row');
		var job = [];
		job['identifier'] =  tr.attr('identifier');
		job['userInfo'] =  tr.attr('userInfo');
		job['name'] =  tr.attr('name');
		job['created'] =  tr.attr('created');
		setExpandedTable(job, tr);
	}
	else{
		expander.closest('tr').next().remove();
		expander.removeClass("icon-down-open").addClass("icon-right-open");
	}
}

function saveScript(job_script){
	var select_value = job_script || $('#job_script').val();
	if(!select_value){
		return;
	}
	$.ajax({
		url: OC.filePath('batch', 'ajax', 'actions.php'),
		method: 'post',
		data: {
			action: 'save_script',
			job_script: select_value,
			job_script_text: $('#job_script_text').val()
		},
		beforeSend: function(xhr){
			ajaxBefore(xhr, "Saving script...");
		},
		complete: function(xhr){
			ajaxCompleted(xhr);
		},
		success: function(jsondata){
			if(jsondata.status == 'success'){
				OC.msg.finishedAction('#batch_message',  {status: 'success', 	data: {message: t("batch", "Script saved.")}});
			}
			else if(jsondata.status == 'error'){
				OC.msg.finishedAction('#batch_message',  {status: 'error', 	data: {message: t("batch", "Script NOT saved.")}});
				if(jsondata.data && jsondata.data.error && jsondata.data.error == 'authentication_error'){
					OC.redirect('/');
				}
			}
		},
		error:  function(jsondata){
			OC.dialogs.alert(t("batch", "get_script: Something went wrong. "+jsondata), t("batch", "Error"));
		}
	});
}

function loadScript(job_script){
	var select_value = job_script || $('#job_script').val();
	if(!select_value){
		return;
	}
	$.ajax({
		url: OC.filePath('batch', 'ajax', 'actions.php'),
		method: 'post',
		data: {
			action: 'get_script',
			job_script: select_value
		},
		beforeSend: function(xhr){
			ajaxBefore(xhr, "Retrieving script...");
		},
		complete: function(xhr){
			ajaxCompleted(xhr);
		},
		success: function(jsondata){
			if(jsondata.status == 'success'){
				$('#job_script_text').val(jsondata.data);
			}
			else if(jsondata.status == 'error'){
				OC.msg.finishedAction('#batch_message',  {status: 'error', 	data: {message: t("batch", "Script could not be loaded.")}});
				if(jsondata.data && jsondata.data.error && jsondata.data.error == 'authentication_error'){
					OC.redirect('/');
				}
			}
		},
		error:  function(jsondata){
			OC.dialogs.alert(t("batch", "get_script: Something went wrong. "+jsondata), t("batch", "Error"));
		}
	});
}

function toggleNewJob(){
	$('#newjob').slideToggle(400, batchCreateScriptSelect);
	$('#job-create').toggleClass('btn-primary');
	$('#job-create').toggleClass('btn-default');
	$('#newjob #ok a').toggleClass('btn-default');
	$('#newjob #ok a').toggleClass('btn-primary');
}

// Before each ajax call, display the loading gif, and add the ajax request to the array $.xhrPool,
// so that it can keep other completed calls from removing the loading gif
function ajaxBefore(xhr, loadingString){
	$.xhrPool.push(xhr);
	$("#loading-text").text(t("batch", loadingString));
	$('#loading').show();
}

function ajaxCompleted(xhr){
	var index = $.xhrPool.indexOf(xhr);
	if(index > -1){
		$.xhrPool.splice(index, 1);
	}
	if(!$.xhrPool.length){
		$('#loading').hide();
	}
}

function batchCreateScriptSelect(){
	$('#job_script').focus(function(){
		$('#job_script').autocomplete("search","");
	});
	$('#job_script').autocomplete({minLength:0,
		appendTo: '#newjob',
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
					StartDir: $('#job_script').attr('batch_folder'),
					dir: $('#job_script').val(), 
				},
				function(scriptFile){
					$('#job_script').autocomplete('option','autoFocus', true);
					response(scriptFile);
				}
			);
		}
	});
	$('#job_script:visible').focus();
	}

function deleteSelectedJobs(){
	var identifiers = $(".jobselect:checked").map(function () {
		return $(this).attr("identifier");
	}).get();
	$('#dialogalert').html("<div>" + t("batch", "Are you sure you want to delete the selected jobs")+"?</div>");
	$('#dialogalert').dialog({
		buttons: [{
				text: 'Delete',
				click: function(){
					deleteJobs(identifiers);
					$(this).dialog('close');
				}
			},
			{
				text: 'Cancel',
				click: function(){
					$(this).dialog('close');
				}
			}
		]
	});
}


$(document).ready(function(){

	var hostname = $(location).attr('host');

	$.submitJobTimeouts = [];
	$.xhrPool = [];

	$('a#job-create').click(function(){
		toggleNewJob();
	});

	$('#newjob #cancel').click(function(){
		toggleNewJob();
	});
	
	$('#newjob #load').click(function(){
		loadScript();
	});
	
	$('#newjob #save').click(function(){
		saveScript();
	});

	$("#job_script").prop("selectedIndex", -1);

	$("#job_script").change(function(){
		loadScript();
	});

	$('#newjob #ok').on('click', function(){
		var job_script_text = $('#job_script_text').val();
		submitJob(job_script_text, $('input#batch_input_file').val(), group = $('#group_folder').val());
	});

	$("#jobstable td .expand-view").live('click', function(){
		toggleExpanded($(this));
	});

	$("#jobstable td .delete-job").live('click', function(){
		var identifier = $(this).closest('tr').attr('identifier');
		$('#dialogalert').html("<div>" + t("batch", "Are you sure you want to delete the job") + " " +
				identifier + "?</div>");
		$('#dialogalert').dialog({
			buttons: [{
					text: 'Delete',
					click: function(){
						deleteJobs([identifier]);
						$(this).dialog('close');
					}
				},
				{
					text: 'Cancel',
					click: function(){
						$(this).dialog('close');
					}
				}
			]
		});
	});

	$('#jobs_refresh').click(function(e){
		//$('table#jobstable tfoot.summary tr td span.info').remove();
		listJobs();
	});
	
	$('#selectAllJobs').click(function(e){
		var isChecked = $('#selectAllJobs').is(":checked");
		$('.jobselect').prop( 'checked', isChecked);
		$('#delete_jobs').attr('disabled', !isChecked);
	});
	
	$('.jobselect').live('click', function(e){
		var enableButton = $(".jobselect:checked").length>0 || $(".jobselect:checked").length==1 && !$(this).is(":checked");
		var allSelected = $(".jobselect").length==$(".jobselect:checked").length;
		$('#delete_jobs').attr('disabled', !enableButton);
		$('#selectAllJobs').prop('checked', allSelected);
	});
	
	$('#delete_jobs').click(function(e){
		deleteSelectedJobs();
	});

	listJobs(function(){
		if(typeof getGetParam !== 'undefined' && getGetParam('file') && getGetParam('job_sript')){
			var job_script = decodeURIComponent(getGetParam('job_sript'));
			$('#newjob').show();
			$('#job-create').removeClass('btn-primary');
			$('#job-create').addClass('btn-default');
			$.when(loadScript(job_script)).then(function(){
				$('#job_script').val(yaml_file);
				$('#newjob #ok a').removeClass('btn-default');
				$('#newjob #ok a').addClass('btn-primary');
			});
		}
	});

});
