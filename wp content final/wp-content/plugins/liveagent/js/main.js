/*
 Plugin Name: LiveAgent
 Plugin URI: http://www.qualityunit.com/liveagent
 Description: Plugin that enable integration with LiveAgent
 Author: QualityUnit
 Version: 1.0.0
 Author URI: http://www.qualityunit.com
 License: GPL2
 */

var liveagent_timerId = null;

function onResponseRecieved(data) {
	data = jQuery.parseJSON(data);
	if (data.result != '1') {
		if (data.message != undefined) {
			alert(data.message);
		}
		if (data.runonfailure != undefined) {
			window[data.runonfailure]();
		}
		return data.result;
	}
	if ((data.runfunction != undefined && data.replaceform != undefined && data.runafter == undefined)
			|| (data.runfunction != undefined && data.replaceform == undefined)) {
		window[data.runfunction](data);
	}
	if (data.replaceform == undefined) {
		return;
	}
	var div = jQuery('form[name=' + data.replaceform + ']');
	if (div.length == 0) {
		div = jQuery('div[name=' + data.replaceform + ']');
	}
	div.fadeOut('fast', function() {
		var parentDiv = div.parent();
		div.remove();
		odv = jQuery('<div>' + data.dialog + '</div>');
		odv.appendTo(parentDiv);
		parentDiv.fadeIn('fast');
	});
	if (data.runfunction != undefined && data.runafter != undefined) {
		window[data.runfunction](data);
	}
}

function onPingSuccessfull(data) {
	jQuery('div[name=liveagent_wait_status]').html(liveagentLocalizations.completing);
	if (liveagent_timerId == null) {
		return;
	}
	clearTimeout(liveagent_timerId);
	liveagent_timerId = null;
	setTimeout("window.location=liveagentHelpers.afterInstallUrl", 3000);
}

function doPing(domain, postpondPing) {
	var attributes = {
		"domain" : domain
	};
	if (!postpondPing) {		
		var data = {
				action : 'laping',
				'domain' : domain
			};
			jQuery.post(ajaxurl, data, function(response) {
				onResponseRecieved(response);
			});
	}
	liveagent_timerId = setTimeout("doPing('" + domain + "',false)", 20000);
}

function runWaitingStatusChanger() {
	setTimeout(
			"jQuery('div[name=liveagent_wait_status]').html('Initializing...');",
			10);
	var timing = 3000;
	var percentage = 2;
	for (i = 0; i < 49; i++) {
		setTimeout(
				"jQuery('div[name=liveagent_wait_status]').html(liveagentLocalizations.installing + ' "
						+ percentage + "% ...');", timing);
		timing += 1000;
		percentage += 2;
	}
	setTimeout(
			"jQuery('div[name=liveagent_wait_status]').html(liveagentLocalizations.justFewMoreSeconds);",
			timing);
	timing += 5000
	setTimeout(
			"window.location=liveagentHelpers.afterInstallUrl;",
			timing);
}

function resetLaAccount() {
	if (confirm(liveagentLocalizations.youSureResetAccount)) {
		window.location=liveagentHelpers.resetAccountUrl;
	};	
}