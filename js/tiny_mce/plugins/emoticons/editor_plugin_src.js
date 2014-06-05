/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('emoticons', 'en,sv,si,zh_cn,cs,fa,fr_ca,fr,de,pl');

/**
 * Returns the HTML contents of the emotions control.
 */
function TinyMCE_emoticons_getControlHTML(control_name) {
	switch (control_name) {
		case "emoticons":
			return '<img id="{$editor_id}_emoticons" src="{$pluginurl}/images/emotions.gif" title="{$lang_emoticons_desc}" width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');" onclick="tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mceEmoticon\');">';
	}

	return "";
}

/**
 * Executes the mceEmoticon command.
 */
function TinyMCE_emoticons_execCommand(editor_id, element, command, user_interface, value) {
	// Handle commands
	switch (command) {
		case "mceEmoticon":
			var template = new Array();

			template['file'] = '../../plugins/emoticons/emoticons.htm'; // Relative to theme
			template['width'] = 150;
			template['height'] = 180;

			tinyMCE.openWindow(template, {editor_id : editor_id});

			return true;
	}

	// Pass to next handler in chain
	return false;
}
