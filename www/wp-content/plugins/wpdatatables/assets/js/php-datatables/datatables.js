jQuery(document).ready(function($) {

	/* Clear filters */
	$.fn.dataTableExt.oApi.fnFilterClear  = function ( oSettings )
	{
		/* Remove global filter */
		oSettings.oPreviousSearch.sSearch = "";

		/* Remove the text of the global filter in the input boxes */
		if ( typeof oSettings.aanFeatures.f != 'undefined' )
		{
			var n = oSettings.aanFeatures.f;
			for ( var i=0, iLen=n.length ; i<iLen ; i++ )
			{
				$('input', n[i]).val( '' );
			}
		}

		/* Remove the search text for the column filters - NOTE - if you have input boxes for these
		* filters, these will need to be reset
		*/
		for ( var i=0, iLen=oSettings.aoPreSearchCols.length ; i<iLen ; i++ )
		{
			oSettings.aoPreSearchCols[i].sSearch = "";
		}

		/* Redraw */
		oSettings.oApi._fnReDraw( oSettings );
	};	

	jQuery.extend( jQuery.fn.dataTableExt.oSort, {
		"formatted-num-pre": function ( a ) {
			if($(a).text()!=''){
				a = $(a).text();
			}
			a = (a==="-") ? -1 : a.replace( /[^\d\-\.]/g, "" );

			if(a!=-1){
				while(a.indexOf('.')!=-1){
					a = a.replace(".","");
				}

				a = a.replace(',','.');

			}

			return parseFloat( a );
		},

		"formatted-num-asc": function ( a, b ) {
			return a - b;
		},

		"formatted-num-desc": function ( a, b ) {
			return b - a;
		},

		"statuscol-pre": function ( a ) {

			a = $(a).find('div.percents').text();
			return parseFloat( a );
		},

		"statuscol-asc": function ( a, b ) {
			return a - b;
		},

		"statuscol-desc": function ( a, b ) {
			return b - a;
		}						
	} );
	
	$.fn.dataTableExt.oApi.fnGetColumnIndex = function ( oSettings, sCol ) 
	{
		var cols = oSettings.aoColumns;
		for ( var x=0, xLen=cols.length ; x<xLen ; x++ )
		{
			if ( (typeof(cols[x].sTitle) == 'string') && ( cols[x].sTitle.toLowerCase() == sCol.toLowerCase() ) )
			{
				return x;
			};
		}
		return -1;
	};	

	
});
