<script type="text/javascript">
$(function() {
	
	<?php if( $table->hasCollapsible() ) { ?>
	
	/**
	 * Handlers for rows expand / collapse
	 * Version for plain tables render type
	 * 
	 * Using child " > " selectors here to avoid affecting the sub-tables.
	 * 
	 */
	$('table#<?php echo $table->getId(); ?> > tbody > tr > td.collapsible > a').live('click', function(e){
		e.preventDefault();
		e.stopImmediatePropagation();
		var row		= $(this).parent().parent();
		if ($(this).hasClass('collapsed')) {
                    $(this).removeClass('collapsed').addClass('expanded');
                    var table = row.find('td.collapsible div.child_table');
                    var new_row = $('<tr><td></td></tr>').insertAfter(row);
                    <?php if( $table->getChildRenderType() == PHPDataTable::CHILD_TABLE ) { ?>
                    $(new_row).find('td').html(table.html()).attr('colspan', '<?php echo ($table->getColumnCount()-$table->getHiddenColumnCount()+1) ?>');
                    <?php } else { ?>
                    table.find('tr').addClass(row.attr('id')+'_child');
                    $(new_row).replaceWith(table.find('tbody').html());
                    <?php } ?>
		} else {
                    $(this).removeClass('expanded').addClass('collapsed');
                    <?php if( $table->getChildRenderType() == PHPDataTable::CHILD_ROWS ) { ?>
                    $('table#<?php echo $table->getId(); ?> > tbody > tr.'+row.attr('id')+'_child').remove();
                    <?php } ?>
		}
	})
	<?php } ?>
		
	
});
</script>
