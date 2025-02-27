/**
 * Update String Params
 * @param  {object}
 * @return {string}
 */
 export default (uri, key, value) => {
	const re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
	const separator = uri.indexOf('?') !== -1 ? "&" : "?";
	
	if (uri.match(re)) {
		return uri.replace(re, '$1' + key + "=" + value + '$2');
	}

	return uri + separator + key + "=" + value;
};
