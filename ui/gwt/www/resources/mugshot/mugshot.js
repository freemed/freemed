// $Id$

function mugshotTake(mid,murl,mturl,preview) {
	$('#mugshot_preview').show();
	$('#mugshot_preview_img').attr('src',mturl);
	$('#mugshot_status').show();
	if (preview == 'true') {
		$('#mugshot_url').val(mturl);
		$('#mugshot_mid').val(mid);
	}
}