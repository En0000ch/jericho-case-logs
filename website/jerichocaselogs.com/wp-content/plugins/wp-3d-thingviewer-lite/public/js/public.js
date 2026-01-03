'use strict';

var thingviewers = [];

document.querySelectorAll('.wp3dtvl-wrapper').forEach( (el, i) => {
	let thingviewer = new Thingviewer(el.id);
	thingviewers.push(thingviewer);
	thingviewer.toggleThingviewer(true);
});

function render() {
	requestAnimationFrame( render );
	thingviewers.forEach( (el, i) => {
		el.render();
	});
}
render();

/* Popup Anything plugin tweak */
document.addEventListener('custombox:content:open', function() {
	thingviewers.forEach( (tv, i) => {
		tv.resize( tv.tv_settings.parent_element.offsetWidth, tv._getHeightWidthRatio(tv.tv_settings.parent_element.offsetWidth) );
	}, false);
});		
