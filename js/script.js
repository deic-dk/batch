//////////// begin  helper functions /////////////
function getRowElementPlain(name, value){
	return "\n <td>\n  <div column='" + name + "'>\n   <span>" + value + "</span>\n  </div>\n </td>";
}

function getRowElementView(name, job){
	if(job['status'].includes("Running")){
		if(job['dbUrl'].length){
			return "\n <td>\n  <div column='" + name + "'>\n   <span><a href='" + job['dbUrl'] +
				"'>"+ job['dbUrl']+"</a></span>\n  </div>\n </td>";
		}
		else{
			return getRowElementPlain(name, "none");
		}
	}
	return getRowElementPlain(name, "wait");
}

// identifier name csStatus userInfo inputFileURLs outFileMapping providerInfo stdoutDest stderrDest createdlastModified outTmp errTmp jobID metaData host runningSeconds ramMB executable executables opSys runtimeEnvironments allowedVOs virtualize dbUrl

function getExpandedTable(job){
	var str = "\n <tr hidden class='expanded-row' identifier='" + job['identifier'] + "'> <td colspan='5'>" +
		"\n<table id='expanded-" + job['identifier'] + "' class='panel expanded-table'>" +
		"\n <tr><td class='expanded-column-name'>identifier:</td> <td class='expanded-column-value'><span>" + job['identifier'] + "</span></td></tr>" +
		"\n <tr><td class='expanded-column-name'>executable:</td> <td class='expanded-column-value'><span>" + job['executable'] + "</span></td></tr>" +
		"\n <tr><td class='expanded-column-name'>host:</td> <td class='expanded-column-value'><span>" + job['host'] + "</span></td></tr>" +
		"\n <tr><td class='expanded-column-name'>userInfo:</td> <td class='expanded-column-value'><span>" + job['userInfo'] + "</span></td></tr>" +
		"\n <tr><td class='expanded-column-name'>providerInfo:</td> <td class='expanded-column-value'><span>" + job['providerInfo'] + "</span></td></tr>" +
		"\n <tr><td class='expanded-column-name'>runningSeconds:</td> <td class='expanded-column-value'><span>" + job['runningSeconds'] + "</span></td></tr>" +
		"\n</table>" +
		"\n </td> </tr>";
	return str;
}

function formatStatusRunning(status, seconds){
	if(status.includes("Running")){
		try{
			var runningTimeStr = new Date(seconds * 1000).toISOString().slice(11, 19);
			return "Running: ".concat(runningTimeStr);
		}
		catch(error){
			console.log(error);
		}
	}
	return status;
}

function getRow(job){
	//visible part
	var str = "  <tr class='simple-row' userInfo='" + job['userInfo'] + "' providerInfo='"+ job['providerInfo'] + "' runningSeconds='" + job['runningSeconds'] + "'>" +
		getRowElementPlain('name', job['name']) +
		getRowElementPlain('status', formatStatusRunning(job['csStatus'], job['runningSeconds'])) +
		getRowElementView('view', job) +
		"\n<td class='td-button'><a href='#' title=" + t('batch', 'Expand') + " class='expand-view permanent action icon icon-down-open'></a></td>" +
		"\n<td class='td-button'><a href='#' title=" + t('batch', 'Delete job') + " class='delete-job permanent action icon icon-trash-empty'></a></td>" +
		"\n</tr>";
	//expanded information
	str += getExpandedTable(job);
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
			pod_names: ''
		},
		beforeSend: function(xhr){
			ajaxBefore(xhr, "Retrieving table data...");
		},
		success: function(jsondata){
			if(jsondata.status == 'success'){
				var expanded_views = [];
				// make an array of the job identifiers whose views are expanded
				$('#jobstable #fileList tr.simple-row td a.icon-up-open').closest('tr').each(function(){
					expanded_views.push($(this).attr('identifier'));
				});
				$('#jobstable #fileList tr').remove();
				// remove all of the table rows, and clear any remaining tooltips
				$('body > div.tipsy').remove();
				jsondata.data.forEach(function(value, index, array){
					$('tbody#fileList').append(getRow(value));
				});
				updateJobsCount();
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
					OC.dialogs.alert(t("batch", "list_jobs: Something went wrong..."), t("batch", "Error"));
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

function submitJob(script){
	$.ajax({
		url: OC.filePath('batch', 'ajax', 'actions.php'),
		data: {
			action: 'submit_job',
			script: script
		},
		method: 'post',
		beforeSend: function(xhr){
			ajaxBefore(xhr, "Submitting job...");
		},
		success: function(jsondata){
			if(jsondata.status == 'success'){
				if(jsondata.data.identifier){
					listJobs();
					// if a previous run_pod call has outstanding timeouts, clear them
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
				else{
					OC.dialogs.alert(t("batch", "submit_job: Something went wrong..."), t("batch", "Error"));
				}
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

function deleteJob(job_db_url){
	$.ajax({
		url: OC.filePath('batch', 'ajax', 'actions.php'),
		data: {
			action: "delete_job",
			job_db_url: job_db_url
		},
		method: 'post',
		beforeSend: function(xhr){
			ajaxBefore(xhr, "Deleting your job...");
			$('#jobstable tr[identifier="' + identifier + '"] td a.delete-job').hide();
			$('#jobstable tr[identifier="' + identifier + '"] td div[column=status] span').text('Deleting');
		},
		complete: function(xhr){
			ajaxCompleted(xhr);
		},
		success: function(data){
			if(data.status == 'success'){
				$('tr[identifier="' + data.identifier + '"]').remove();
				// if a tooltip is shown when the element is removed, then there is no mouseover event to get rid of it.
				$('body > div.tipsy').remove();
				updateJobCount();
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
	if(expander.attr("class").search("icon-up-open") === -1){
		expander.closest('tr').next().show();
		expander.removeClass("icon-down-open").addClass("icon-up-open");
	}
	else{
		expander.closest('tr').next().hide();
		expander.removeClass("icon-up-open").addClass("icon-down-open");
	}
}

function loadScript(job_script){
	$('#public_key').val('');
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

// TODO:  get the script and load it into textfield

			}
			else if(jsondata.status == 'error'){
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
	$('#newjob').slideToggle();
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

$(document).ready(function(){

	var hostname = $(location).attr('host');

	$.submitJobTimeouts = [];
	$.xhrPool = [];

	$('a#job-create').click(function(){
		toggleNewJob()
	});

	$('#newpod #cancel').click(function(){
		toggleNewJob()
	});

	$("#job_script").prop("selectedIndex", -1);

	$("#job_script").change(function(){
		loadScript();
	});

	$('#newjob #ok').on('click', function(){
		var job_script = $('#job_script').val();
		submitJob(job_script);
		return false;
	});

	$("#jobstable td .expand-view").live('click', function(){
		toggleExpanded($(this));
	});

	$("#jobstable td .delete-job").live('click', function(){
		var jobSelectedID = $(this).closest('tr').attr('identifier');
		$('#dialogalert').html("<div>" + t("batch", "Are you sure you want to delete the job") + " " +
				jobSelectedID + "?</div>");
		$('#dialogalert').dialog({
			buttons: [{
					text: 'Delete',
					click: function(){
						deleteJob(jobSelectedID);
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
		$('table#jobstable tfoot.summary tr td span.info').remove();
		listJobs();
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
