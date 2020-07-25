/**
 * @package Logbook
 * @copyright Copyright (c)2017 Amit Kumar Shukla
 * @license GNU General Public License version 3, or later
 * @contact akshukla.dev@gmail.com
 */


(function($) {

	//Run a function when the page is fully loaded including graphics.
	$(window).load(function() {
	  //Get the value of the item id to determine if it is new or not.
	  var itemId = $('#jform_id').val();

	  //Existing item.
	  if(itemId != 0) {
		$.fn.replaceHide();
		$('#switch_replace').toggle(function() { $.fn.replaceShow(); }, function() { $.fn.replaceHide(); });
	  }
	});


	$.fn.replaceShow = function() {
	  $('#jform_replace_file').val('1');
	  $('#jform_uploaded_file').parent('div').parent('div').show();
	  $('#jform_uploaded_file').prop('required', true);
	  $('#replace-title').hide();
	  $('#cancel-title').show();
	};

	$.fn.replaceHide = function() {
	  //
	  $('#jform_replace_file').val('0');
	  $('#jform_uploaded_file').parent('div').parent('div').hide();
	  $('#jform_uploaded_file').prop('required', false);
	  $('#cancel-title').hide();
	  $('#replace-title').show();
	};

  })(jQuery);



