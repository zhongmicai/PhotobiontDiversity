<?php
/**
 * Javascript init template file for the dataTables
 * phpDataTables Module
 * 
 * @author cjbug@ya.ru
 * @since 10.10.2012
 *
 * */
?>

<script type="text/javascript">
	
    /**
     * global var for the datatable
     */
    var oDt_<?php echo $table->getId() ?>;
    
    /**
     * global var for the fixed columns
     */ 
    var oFH_<?php echo $table->getId() ?>;

    var fc;	
    
(function($) {
	$(function() {

	/**
	 * Init the datatable
	 */
	var table_selector	= '#<?php echo $table->getId() ?>';
		
	<?php if ($table->groupingEnabled()) { ?>
		var groupingColumnIndex = <?php if ($table->hasCollapsible()) {
			echo $table->groupingColumn() + 1;
		} else {
			echo $table->groupingColumn();
		} ?>;
	<?php } ?>
		
	// assigning the dataTable global var.
	oDt_<?php echo $table->getId() ?> = $(table_selector).dataTable({
					"sDom": 'T<"clear">lfrtip',
					"bFilter": <?php echo ($table->filterEnabled() ? 'true' : 'false') ?>,  
				<?php if ($table->paginationEnabled()) { ?>
					"bPaginate": true,
					"iDisplayLength": <?php echo $table->getDisplayLength(); ?>,
				<?php } else { ?>
					"bPaginate": false,
					<?php if ( $table->groupingEnabled() ) { ?>
					"aaSortingFixed": [ [ groupingColumnIndex, 'asc'] ],
					<?php } ?>
				<?php } ?>
					 "aoColumnDefs" : [
						<?php echo $table->getColumnDefs(); ?>
					 ], 
					 "bAutoWidth" : false,
				<?php if ($table->sortingEnabled() 
						&& !is_null($table->getDefaultSortColumn())) { ?>
					 "aaSorting": [[<?php echo $table->getDefaultSortColumn() ?>, "asc" ]]
				<?php } else { ?>
					<?php if (!$table->sortingEnabled()) { ?>
					 "bSort": false
					<?php } else { ?>
					 "aaSorting": [[0, "asc"]]
					<?php } ?>
				<?php } ?>
				<?php if ($table->getInterfaceLanguage()) { ?>,
						"oLanguage" : <?php echo file_get_contents($table->getInterfaceLanguage()) ?>
				<?php } ?>
				<?php if ($table->tableToolsEnabled()) { ?>,
					"oTableTools": {
						"sSwfPath": "<?php echo PDT_JS_PATH ?>jquery-datatables/media/swf/copy_cvs_xls_pdf.swf"
					}
				<?php } ?>
				<?php if($table->serverSide()) { ?>,
					"bProcessing": true,
					"bServerSide": true,
					"sAjaxSource": "<?php echo site_url() ?>/wp-admin/admin-ajax.php?action=get_wdtable&table_id=<?php echo $table->getWpId(); ?>"
				<?php } ?>
            })<?php if ($table->groupingEnabled() 
			&& !$table->columnsFixed() && !$table->headersFixed()) { ?>.rowGrouping({ iGroupingColumnIndex: groupingColumnIndex });
	  <?php } ?>;
		  
	/**   Fixed columns   **/
	<?php if ($table->columnsFixed() || $table->headersFixed()) { ?>
				/** Init of "frozen" fixed columns **/ 
				/** binded to window.load() not to "flash" with wrong positioning **/
				$(window).load(function(){
					oFH_<?php echo $table->getId() ?> 
						= new FixedHeader( oDt_<?php echo $table->getId() ?>, 
										   { "left": <?php echo (int)$table->columnsFixed() ?>, 
											 "tableId": '<?php echo $table->getId() ?>' 
											 <?php if($table->getLeftOffset()){?>, "offsetLeft": <?php echo $table->getLeftOffset(); } ?>
											 <?php if($table->getTopOffset()){?>, "offsetTop": <?php echo $table->getTopOffset(); } ?>
										   } );
										   
				})
	<?php } ?>
		
	/** end fixed columns **/

	/** advanced filter **/
	<?php if($table->advancedFilterEnabled()) { ?>
	oDt_<?php echo $table->getId() ?>.columnFilter({ <?php if(get_option('wdtRenderFilter') == 'header') {?>sPlaceHolder: "head:before", <?php } ?>aoColumns: [<?php echo $table->getColumnFilterDefs() ?>] });
    <?php 
        $init_format = get_option('wdtDateFormat');
        $datepick_format = str_replace('d','dd',$init_format);
        $datepick_format = str_replace('m','mm',$datepick_format);
        $datepick_format = str_replace('Y','yy',$datepick_format);
    ?>
    jQuery.datepicker.regional[""].dateFormat = "<?php echo $datepick_format ?>";
    jQuery.datepicker.setDefaults(jQuery.datepicker.regional['']);
	<?php } ?>
	/** end advanced filter **/
			   
	<?php if ($table->hasCollapsible()) { ?>
	/**
	 * Handler for rows expand / collapse
	 * for the main dataTable
	 * 
	 * Using child " > " selectors here to avoid affecting the sub-tables.
	 * 
	 */
	var collapsibleDtClickHandler = function(e){
		e.preventDefault();
		e.stopImmediatePropagation();
		var row = $(this).parent().parent();
		var oTable  = row.parent().parent().dataTable();
		if ($(this).hasClass('collapsed')) {
		// expanding handle
			$(this).removeClass('collapsed').addClass('expanded');
			var table = row.find('td.collapsible div.child_table');
			<?php if ($table->getChildRenderType() == PHPDataTable::CHILD_TABLE) { ?>
				var new_row = oTable.fnOpen(row.get(0), table.html(), 'child_table');
				$(new_row).find(' > td').attr('colspan', '<?php echo ($table->getColumnCount() - $table->getHiddenColumnCount() + 1); ?>');
			<?php } else { ?>
				if(row.attr('class').indexOf('child') == -1){
					var new_row = oTable.fnOpen(row.get(0), '', 'child_table');
				}else{
					var new_row = $('<tr><td></td></tr>').insertAfter(row);
				}
				table.find('tr').addClass(row.attr('id')+'_child');
				$(new_row).replaceWith(table.find('tbody').html());
			<?php } ?>
		} else {
		// collapsing handle
			$(this).removeClass('expanded').addClass('collapsed');
			row.next().html('');
			oTable.fnClose(row.get(0))
			<?php if ($table->getChildRenderType() == PHPDataTable::CHILD_ROWS) { ?>
				 $('.dataTable > tbody > tr.'+row.attr('id')+'_child').remove();
			<?php } ?>
		}
	 }
	 
	 // bind the collapsible handler for the table itself
	 $('table#<?php echo $table->getId() ?>.dataTable > tbody > tr > td.collapsible > a').live('click', collapsibleDtClickHandler);
	 

	 <?php if ($table->columnsFixed()) { ?>
	 /**
	  * Handler for rows expand / collapse
	  * for the cloned dataTable (fixed columns on the left)
	  * 
	  * Using child " > " selectors here to avoid affecting the sub-tables.
	  * 
	  */
	var collapsibleFixedClickHandler = function(e){
		e.preventDefault();
		e.stopImmediatePropagation();
		var fixed_row = $(this).parent().parent();
		var dt_row = $('table#<?php echo $table->getId() ?> tr#'+fixed_row.attr('id'));
		if ($(this).hasClass('collapsed')) {
		// expanding handle
			$(this).removeClass('collapsed').addClass('expanded');
			dt_row.find('td.collapsible a').removeClass('collapsed').addClass('expanded');
			var table = fixed_row.find('td.collapsible div.child_table');
			var new_fixed_table = '', new_dt_table = '';
			var index = dt_row.hasClass('even') ? 1 : 0;
				table.find('tr').each(function(){
					var rowClass = (index%2 == 0 ? 'even' : 'odd'); index++;
					var new_fixed_table_row = $('<tr id="'+$(this).attr('id')+'" class="'+rowClass+'"></tr>');
					var new_dt_table_row = $('<tr id="'+$(this).attr('id')+'" class="'+rowClass+'"></tr>');
					var col_count = $(this).children('td').length;
					new_fixed_table_row.addClass(fixed_row.attr('id')+'_child').css('height', $(dt_row).css('height'));
					new_dt_table_row.addClass(fixed_row.attr('id')+'_child').css('height', $(dt_row).css('height'));
					for(i=0;i<col_count;i++){
						var cell_html = $(this).children('td').get(i).outerHTML;
						if(i < <?php echo $table->columnsFixed() ?>){
							new_fixed_table_row.append(cell_html);
							new_dt_table_row.append(cell_html);
						}else{
							new_dt_table_row.append(cell_html);
						}
					}
					new_fixed_table += new_fixed_table_row.get(0).outerHTML;
					new_dt_table += new_dt_table_row.get(0).outerHTML;
				});
				$(new_fixed_table).insertAfter(fixed_row);
				$(new_dt_table).insertAfter(dt_row);
			} else {
			// collapsing handle
				$(this).removeClass('expanded').addClass('collapsed');
				// collapse sub-child rows
				$('table#<?php echo $table->getId() ?> > tbody > tr.'+dt_row.attr('id')+'_child td.collapsible a.expanded').trigger('click');
				// remove from cloned table
				fixed_row.parent().find('tr.'+dt_row.attr('id')+'_child td.collapsible a.exapnded').trigger('click');
				dt_row.find('td.collapsible a').removeClass('expanded').addClass('collapsed');
				fixed_row.next().remove()
				dt_row.next().html('');
				oDt_<?php echo $table->getId() ?>.fnClose(dt_row.get(0))
				<?php if ($table->getChildRenderType() == PHPDataTable::CHILD_ROWS) { ?>
						$('table#<?php echo $table->getId() ?> > tbody > tr.'+dt_row.attr('id')+'_child').remove();
						fixed_row.parent().find('tr.'+dt_row.attr('id')+'_child').remove();
				<?php } ?>
			}
	}	
	// bind the handler to the plus icons        	
	$('div.FixedHeader_Cloned.<?php echo $table->getId() ?> table td.collapsible a').live('click', collapsibleFixedClickHandler)
		<?php } ?>
	<?php } ?>
			
		
	/**
	 * Bind data filtering for datatables
	 */
	var filterHandle = function(e){
	<?php if ($table->columnsFixed()) { ?>
		$('#<?php echo $table->getId() ?> tr.tc-search th:eq('+$(this).parent('th').index()+') input').val($(this).val());
	<?php } ?>
		oDt_<?php echo $table->getId() ?>.fnFilter( this.value, $(this).parent().data('column_index') + <?php echo (int) $table->hasCollapsible() ?> );
	}
	// bind the filtering to the filter inputs
	$('#<?php echo $table->getId() ?> input.tc-search-i').live('keyup', filterHandle);
		 
	// if we have fixed columns, this is a special case
	<?php if ($table->columnsFixed()) { ?>
		$('div.FixedHeader_Cloned.<?php echo $table->getId() ?> table input.tc-search-i').live('keyup', filterHandle );
	<?php } ?>

	/**
	 * Bind the collapsible rows collapsing to draw callback
	 */ 
	oDt_<?php echo $table->getId() ?>.fnSettings().aoPreDrawCallback.push( {
		"fn": function () {
			// collapse in the main table
			$('table#<?php echo $table->getId() ?> td.collapsible a.expanded').trigger('click');
		},
		"sName": "CollapseAllRows"
	} );
	<?php if (!$table->hasCollapsible()) { ?>
				oDt_<?php echo $table->getId() ?>.fnSettings().aoDrawCallback.push( {
					"fn": function () {
						// collapse in the cloned table
						$('div.FixedHeader_Cloned.<?php echo $table->getId() ?> table td.collapsible a.expanded').trigger('click');
					},
					"sName": "CollapseAllClonedRows"
				} );
	<?php } ?>

	});
})(jQuery);
</script>

