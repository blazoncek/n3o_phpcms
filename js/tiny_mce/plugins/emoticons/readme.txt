 Emoticons plugin for TinyMCE
------------------------------

Installation instructions:
  * Copy the emoticons directory to the plugins directory of TinyMCE (/jscripts/tiny_mce/plugins).
  * Add plugin to TinyMCE plugin option list example: plugins : "emoticons".
  * Add the emotions button name to button list, example: theme_advanced_buttons3_add : "emoticons".

Initialization example:
  tinyMCE.init({
    theme : "advanced",
    mode : "textareas",
    plugins : "emoticons",
    theme_advanced_buttons3_add : "emoticons"
  });

Copyright notice:
  Original code adapted from TinyMCE emotions.