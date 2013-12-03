/**
 * Data tables layouts
 */
(function($) {
	$.fn.mTableLayout = function(options) {
		var settings = $.extend({}, $.fn.mTableLayout.defaults, settings);
		return this.each(function() {
			$this = $(this);
			$.fn.mTableLayout.table100h(settings);	
			//On window resize
			$(window).resize(function() {
				$.fn.mTableLayout.table100hFrameSet(settings);	       
			});
		});    
	}; 
	/**
	 * Table 100 
	 * Single table on a page - 100% width, 100% height, scroll bars, no paging
	 * 
	 */  
	$.fn.mTableLayout.table100h = function(settings) { 
		var table = $('table.' + settings.mtable100h);
		table.parents($.fn.mUI.defaults.mWWrap + ':first').addClass(settings.mtable100h);
		$.fn.mTableLayout.table100hFrameSet(settings);	
		$('.table-100h .t-filter-tog').on('click', function(event){
			$.fn.mTableLayout.table100hFrameSet(settings);		
		});
	};	
	/**
	 * Table 100 scroll frame set
	 * 
	 */  
	$.fn.mTableLayout.table100hFrameSet = function(settings) { 
		var posTop = $('.dataTables_scrollHead').position().top + $('.dataTables_scrollHead').outerHeight() - 1;
		$('.dataTables_scrollBody').css('top', posTop + 'px');
		if ($('.table-100h .w-f').length > 0){
			var posBottom =$('.table-100h .w-f').height();
			$('.dataTables_scrollBody').css('bottom', posBottom + 'px');
		}
	};	
	//default settings
	$.fn.mTableLayout.defaults = {  
			/**
			 * Settings
			 * 
			 * 
			 * UI variables
			 * 
			 * Classes
			 * 
			 * mtable100h: table100h class
			 * 
			 * HTML
			 * 
			 * 
			 * Selectors
			 * 
			 */ 
			mtable100h: 'table-100h'
	};
})(jQuery);


jQuery(document).ready(function($) {
	//delaying to fix google chrome / jquery bug: when page is refreshed, heights are not calculated correctly
	setTimeout( function() {
		$('body').mTableLayout();
	}, 100 );	
});	
