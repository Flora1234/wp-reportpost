<?php
if (!defined ('ABSPATH')) die ('No direct access allowed');

	// Get the Reports
	include_once("ReportPost.class.php");
	
	$wprp = new ReportPost();
	
	// Handle Archive & DELETE
	if($_SERVER['REQUEST_METHOD']=='POST')
	{
	  	//echo $current_user->ID;
		
		if ( get_magic_quotes_gpc() ) {
			$_POST      = array_map( 'stripslashes_deep', $_POST );
			$_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
		}
		
		$selected = $_POST['reportID'];
		
		if($selected && is_array($selected) && count($selected) > 0)
		{
			// DELETE
			if(isset($_POST['deleteit']))
			{
				foreach($selected as $archive)
				{
					if(!$wprp->delete($archive))
					{
						echo "ERROR: ".$wprp->last_error;
						break; // EXIT LOOP
					}
				}
			}
		} // IF SELECTED
	}
	
	
	// Calculate Paggination
	$p = (int) isset($_GET['p']) && is_numeric($_GET['p'])? $_GET['p'] : 1;
	$limit= 20;
	
	$offset = ($limit * ($p - 1));
	
	// Search Based on Paggination
	$results = $wprp->findArchives('ORDER BY reportID DESC',$limit, '', $offset);
	
	// Calculate Pages
	$total_found = $wprp->totalRows;
	
	$pages = ceil($total_found / $limit);
?>
<div class="wrap"> 
	<h2><?php _e('Archived Reports', 'wp-reportpost'); ?></h2>
	
    <form action="" method="post">
    <div class="wprp-info">
    	<div class="wprp-buttons">
        	selected: <input type="button" value="Delete it" name="delete-expand" class="button-secondary delete" onclick="jQuery('#delete-confirm').slideToggle('slow');" /> <small>(* will be removed permanently)</small>
        </div>
    	<span>Total Reports: <?php echo $total_found;?></span>
    </div>
    
    <div class="wprp-archive" id="delete-confirm" style="display:none">
    	<strong>Once deleted, the records will be permanently removed from database.</strong><br />
        Confirm Deleting?  <input type="submit" value="Confirm Delete" name="deleteit" class="button-secondary delete" /> 
    </div>
    <?php 
	if($total_found > 0):
	
	?>
	<table class="widefat post fixed" cellspacing="0">
		<thead>
			<tr>
				<th scope="col" class="check-column"><input type="checkbox" /></th>
                <th scope="col">Post Title</th>
                <th scope="col" style="width:80px;"># Reports</th>
			</tr>
		</thead>
        <tfoot>
			<tr>
				<th scope="col" class="check-column"><input type="checkbox" /></th>
                <th scope="col">Post Title</th>
                <th scope="col"># Reports</th>
			</tr>
		</tfoot>
		<tbody>
        <?php
		$alt = '';
		foreach($results as $report):
			$alt = ($alt == '') ? ' class = "alt"' : '';
			$current_blog_details = get_blog_details( array( 'blog_id' => $report->blogID ) );
			if (!$current_blog_details){
			    $permalink = '#';
			} else if ($report->commentID > 0){
				$permalink = $current_blog_details->siteurl . "/wp-admin/comment.php?action=editcomment&c=" . $report->commentID;
			} else {
				$permalink = $current_blog_details->siteurl . "/wp-admin/post.php?action=edit&post=" . $report->postID;
			}
			?>
			<tr <?php echo $alt;?>>			
				<th scope="row" class="check-column"><input type="checkbox" name="reportID[]" value="<?php echo $report->reportID;?>" /></th>
				<td><?php 
				        if ($current_blog_details){
				            echo ('<a href="' . $permalink . '" title="' . ($report->commentID > 0?'Edit The Comment':'Edit The Post') . '">');
				        }
				    ?>
				    <?php echo $report->post_title . ($report->commentID > 0?' (Comment Reported)':' (Post Reported)');?>
				    <?php 
				        if ($current_blog_details){
				            echo ('</a>');
				        }
				    ?>
				    </td>
                <td align="center"><a href="<?php echo WP_PLUGIN_URL;?>/wp-reportpost/reports.php?id=<?php echo $report->reportID;?>&display=archive&TB_iframe=true" title="Archived Report Details" class="thickbox" onclick="return false;"># View Details</a></td>
			</tr>
        <?php endforeach;?>
            
		</tbody>
	</table>
    <?php
	else:
		echo 'No Reports Found!';
	endif;
	?>
    </form>
    <?php
	
	if($pages > 1)
	{
	?>
    <div class="wprp-pages">
    	<ul>
        	<li class="pageinfo">Pages: </li>
            <?php 
			for($i=1; $i <= $pages; $i++): 
				if($i == $p)
				{?>
                <li class="current"><?php echo $i;?></li>
				<?php 
				continue;
				}
			?>
        	<li><a href="<?php echo get_bloginfo('wpurl')."/wp-admin/admin.php?".url_filter($_SERVER['QUERY_STRING'],'p')."&p=".$i;?>"><?php echo $i;?></a></li>
            <?php
			endfor;
			?>
        </ul>
    </div>
    <?php 
	}
	?>
</div>