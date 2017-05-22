(function( factory ) {
	if ( typeof define === "function" && define.amd ) {
		define( ["jquery", "../jquery.validate"], factory );
	} else {
		factory( jQuery );
	}
}(function( $ ) {

	/*
	 * Localized default methods for the jQuery validation plugin.
	 * Locale: FR
	 */
	$.extend($.validator.methods, {
		date: function(value, element) {
			//validate date like 18 sept. 2015 or 19 dÃ©c. 2015
			return this.optional(element) || /^\d\d\s.+?\.\s\d\d\d\d?$/.test(value);
		}
	});
}));