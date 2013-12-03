<div class="wrap">
    <div id="icon-edit-pages" class="icon32 icon32-posts-page"><br></div>
    <h2>wpDataTables <a href="admin.php?page=wpdatatables-addnew" class="add-new-h2">Add new</a></h2>
<table class="wp-list-table widefat fixed posts" cellspacing="0" style="margin-top: 10px">
	<thead>
	<tr>
		<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
        <th scope="col" id="title" class="manage-column column-id" style="width: 30px;">ID</th>
        <th scope="col" id="title" class="manage-column column-title" style="">Title</th>
        <th scope="col" id="type" class="manage-column column-type" style="width: 100px;">Type</th>
        <th scope="col" id="type" class="manage-column column-type" style="width: 100px">Shortcode</th>
    </tr>
	</thead>

	<tfoot>
	<tr>
		<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
        <th scope="col" id="title" class="manage-column column-id" style="">ID</th>
        <th scope="col" id="title" class="manage-column column-title" style="">Title</th>
        <th scope="col" id="type" class="manage-column column-type" style="">Type</th>
        <th scope="col" id="type" class="manage-column column-type" style="">Shortcode</th>
    </tr>
    </tfoot>

	<tbody id="the-list">
    <?php if(!empty($wpAllTables)) { ?>
    <?php foreach($wpAllTables as $wpTable) { ?>
        <tr id="post-<?php echo $wpTable['id']?>" class="" valign="top">
            <th scope="row" class="check-column"><input type="checkbox" name="post[]" value="<?php echo $wpTable['id']?>"></th>
            <td class="id"><?php echo $wpTable['id']?></td>
            <td class="title"><strong><a class="row-title" href="admin.php?page=wpdatatables-administration&action=edit&table_id=<?php echo $wpTable['id']?>" title="Edit the table or column settings"><?php echo ($wpTable['title'] ? $wpTable['title'] : 'No title') ?></a></strong>
                <div class="row-actions">
                    <span class="edit"><a href="admin.php?page=wpdatatables-administration&action=edit&table_id=<?php echo $wpTable['id']?>" title="Edit">Edit</a> | </span>
                    <span class="trash"><a class="submitdelete" title="Delete" href="admin.php?page=wpdatatables-administration&action=delete&table_id=<?php echo $wpTable['id']?>" rel="<?php echo $wpTable['id']?>">Delete</a></span>
                </div>
            </td>
            <td class="type"><?php echo $wpTable['table_type'] ?></td>
			<td class="date column-date">[wpdatatable id=<?php echo $wpTable['id']?>]</td>			
        </tr>
    <?php } ?>
    <?php } else { ?>
    <tr>
        <td colspan="5" style="padding: 10px;">
            No wpDataTables in the system yet.
        </td>
    </tr>
    <?php } ?>
    </tbody>
</table>
</div>

<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery('a.submitdelete').click(function(e){
		e.preventDefault();
		e.stopImmediatePropagation();
		if(confirm("Are you sure?")){
			window.location = jQuery(this).attr('href');
		}
	})
});
</script>
