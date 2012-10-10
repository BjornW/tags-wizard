/*
 * Tags Wizard is built upon on the excellent Page-Tagger plugin javascript code
 * with some minor changes.
 */ 


function tag_flush_to_text(id, a) {
	a = a || false;
  
  var taxbox, text, tags, newtags;

	taxbox = jQuery('#'+id);
	text = a ? jQuery(a).text() : taxbox.find('input.newtag').val();


	// is the input box empty (i.e. showing the 'Add new tag' tip)?
	if ( taxbox.find('input.newtag').hasClass('form-input-tip') && ! a )
		return false;

	tags = taxbox.find('.the-tags').val();
	newtags = tags ? tags + ',' + text : text;
  
	// massage
	newtags = newtags.replace(/\s+,+\s*/g, ',').replace(/,+/g, ',').replace(/,+\s+,+/g, ',').replace(/,+\s*$/g, '').replace(/^\s*,+/g, '');
	newtags = array_unique_noempty(newtags.split(',')).join(',');
	taxbox.find('.the-tags').val(newtags);
	tag_update_quickclicks(taxbox);

	if ( ! a )
		taxbox.find('input.newtag').val('').focus();

	return false;
}

function tag_update_quickclicks(taxbox) {
  if ( jQuery(taxbox).find('.the-tags').length == 0 )
		return;

	var current_tags = jQuery(taxbox).find('.the-tags').val().split(',');
	jQuery(taxbox).find('.tagchecklist').empty();
	shown = false;

	jQuery.each( current_tags, function( key, val ) {
		var txt, button_id;

		val = jQuery.trim(val);
		if ( !val.match(/^\s+$/) && '' != val ) {
			button_id = jQuery(taxbox).attr('id') + '-check-num-' + key;
 			txt = '<span><a id="' + button_id + '" class="ntdelbutton">X</a>&nbsp;' + val + '</span> ';
 			jQuery(taxbox).find('.tagchecklist').append(txt);
 			jQuery( '#' + button_id ).click( new_tag_remove_tag );
		}
	});
	if ( shown )
		jQuery(taxbox).find('.tagchecklist').prepend('<strong>'+pageTaggerL10n.tagsUsed+'</strong><br />');
}

function new_tag_remove_tag() {
	var id = jQuery( this ).attr( 'id' ), num = id.split('-check-num-')[1], taxbox = jQuery(this).parents('.tagsdiv'), current_tags = taxbox.find( '.the-tags' ).val().split(','), new_tags = [];
	delete current_tags[num];

	jQuery.each( current_tags, function(key, val) {
		val = jQuery.trim(val);
		if ( val ) {
			new_tags.push(val);
		}
	});

	taxbox.find('.the-tags').val( new_tags.join(',').replace(/\s*,+\s*/, ',').replace(/,+/, ',').replace(/,+\s+,+/, ',').replace(/,+\s*$/, '').replace(/^\s*,+/, '') );

	tag_update_quickclicks(taxbox);
	return false;
}

function tag_save_on_publish() {
	jQuery('.tagsdiv').each( function(i) {
		if ( !jQuery(this).find('input.newtag').hasClass('form-input-tip') ) {
        	tag_flush_to_text(jQuery(this).parents('.tagsdiv').attr('id'));
		} else {
			// just in case tag_flush_to_text gets called later on anyway
			jQuery(this).find('input.newtag').val(''); 
		}
	} );
}


jQuery(document).ready( function($) {

  jQuery('a.tagwizard').click( function($){
    //var tag_html = jQuery(this).html();
    //alert('Tag_html' + tag_html);
    var tag = jQuery(this);
    tag_flush_to_text('post_tag', tag);
    //jQuery('div.tagchecklist').append('<span><a class="ntdelbutton">X</a>&nbsp;' +tag_html +'</span>');
    return false;
  });

  // auto-save tags on post preview/save/publish
  jQuery('#post-preview, #save-post, #publish').click( tag_save_on_publish );
});
