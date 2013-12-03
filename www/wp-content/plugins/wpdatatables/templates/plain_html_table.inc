<?php
/**
* Template file for the plain HTML table
* phpDataTables Module
* 
* @author cjbug@ya.ru
* @since 10.10.2012
*
**/
?>
<table id="<?php echo $table->getId() ?>" class="<?php echo $table->getClasses() ?>" style="<?php echo $table->getStyle() ?> <?php if( $table->columnsFixed() ) { ?>position: relative;<?php } ?>" >
	<?php if( $table->headerVisible() ) { ?>
    <thead>
	<tr>
	    <?php if( $table->hasCollapsible() ) { ?>
	    <th class="collapsible"></th>
	    <?php } ?>
	    <?php foreach($table->getColumns() as $column) { ?>
		<th class="header <?php if( $column->sortingEnabled() ) { ?>sort<?php } ?> <?php echo $column->getClasses(); ?>" style="<?php echo $column->getStyle(); ?>"><?php echo $column->getHeader(); ?></th>
	    <?php } ?>
	</tr>
    </thead>
	<?php } ?>
    <?php if( $table->footerVisible() ||  ($table->advancedFilterEnabled() && (get_option('wdtRenderFilter') == 'footer')) ) { ?>
    <tfoot>
	<tr>
	    <?php if( $table->hasCollapsible() ) { ?>
	    <td></td>
	    <?php } ?>		
	    <?php foreach( $table->getColumns() as $column) { ?>
		<td class="header <?php if( $column->sortingEnabled() ) { ?>sort<?php } ?> <?php echo $column->getClasses(); ?>" style="<?php echo $column->getStyle(); ?>"><?php echo $column->getHeader(); ?></td>
	    <?php } ?>
	</tr>
    </tfoot>
    <?php } ?>
    <tbody>
    <?php $pdt_row_index = 0; ?>
	<?php foreach( $table->getRows() as $row) { ?>
	<tr class="<?php if( $pdt_row_index % 2 == 0 ) { ?>even <?php } else { ?>odd<?php } ?> <?php echo $row->getClasses(); ?>" style="<?php echo $row->getStyle(); ?>" id="<?php echo $row->getId() ?>">
	    <?php if( $table->hasCollapsible() ) {?>
	    <td class="collapsible closed">
		<?php if( $row->hasChildTable() ) { ?>
			<a class="collapsed"></a>
			<div class="child_table" style="display: none">
				<?php echo $row->getChildTable()->renderTable(); ?>
			</div>
		<?php } ?>
	    </td>
	    <?php } ?>	    
	    <?php foreach( $table->getColumnsByKeys() as $column_key=>$column ) { ?>
	    <?php $cell = $row->getCell( $column_key ); ?>
		<td class="<?php echo $cell->getClasses() ?>" style="<?php echo $column->getStyle().' '.$cell->getStyle(); ?>">
			<?php echo $table->getFormattedCellVal( $cell, $column_key ); ?>
		</td>
	    <?php } ?>
	</tr>
        <?php $pdt_row_index++; ?>
	<?php } ?>
    </tbody>
    
</table>
