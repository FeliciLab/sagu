$(document).bind("mobileinit", function(){
    $.mobile.ajaxEnabled = false;
    //$.mobile.ajaxFormsEnabled = false;
    $.mobile.hashListeningEnabled = false;
    $.mobile.button.prototype.options.inline = "true";
    $.mobile.ignoreContentEnabled = "true";
});