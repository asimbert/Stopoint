<?php
require_once(dirname(__FILE__) . '/inc/core.php');
require_once(dirname(__FILE__) . '/classes/class.table.php');
require_once(dirname(__FILE__) . '/classes/class.table_form.php');

require_once(dirname(__FILE__) . '/src/lib/Thrift/Exception/TException.php');
require_once (dirname(__FILE__) . '/src/lib/Thrift/StringFunc/TStringFunc.php');
require_once (dirname(__FILE__) . '/src/lib/Thrift/StringFunc/Core.php');
require_once (dirname(__FILE__) . '/src/lib/Thrift/Factory/TStringFuncFactory.php');
require_once (dirname(__FILE__) . '/src/lib/Thrift/Type/TType.php');
require_once (dirname(__FILE__) . '/src/lib/Thrift/Type/TMessageType.php');
require_once (dirname(__FILE__) . '/src/lib/Thrift/Protocol/TProtocol.php');
require_once (dirname(__FILE__) . '/src/lib/Thrift/Protocol/TBinaryProtocol.php');
require_once (dirname(__FILE__) . '/src/lib/Thrift/Transport/TTransport.php');
require_once (dirname(__FILE__) . '/src/lib/Thrift/Transport/TBufferedTransport.php');
require_once (dirname(__FILE__) . '/src/lib/Thrift/Transport/TSocket.php');

require_once (dirname(__FILE__) . '/src/lib/TicketService/VenuesClient.php');
require_once (dirname(__FILE__) . '/src/lib/TicketService/EventsClient.php');
require_once (dirname(__FILE__) . '/src/lib/TicketService/TicketsClient.php');

$url = "bryanlcampbell.asuscomm.com";
$portVenues = 6100;
$portEvents = 6101;
$portTickets = 6102;

$venuesClient = TicketService\VenuesApiClient::getInstance($url, $portVenues);
$eventsClient = TicketService\EventsApiClient::getInstance($url, $portEvents);
$ticketsClient = TicketService\TicketsApiClient::getInstance($url, $portTickets);
$address = new \TicketService\Generated\Address();


if(isset($_GET['a_id'])){
$_SESSION['a_id']=$_GET['a_id'];
}
if(isset($_GET['sort'])){
	//exit;
	//echo $_SESSION['id'];
	$_GET['a_id']=$_SESSION['a_id'];
	}

$table_name = 'auditorium';
$table = new table($table_name);
$table->addField('Id', 'id', false, '', '');     
//$table->addField('Name', 'name', true, '', ''); 
//$table->addField('Place', 'place', true, '', ''); 
//$table->addJoined('Venue', 'venue_id', true, 'venue', 'name', 'id'); 
$table->addField('Auditorium Name', 'name', true, '', ''); 
$table->addField('Capacity', 'capacity', true, '', ''); 
$table->addField('Map', 'image_url', true, '', ''); 
$table->addField('Auditorium Type', 'type', true, '', ''); 
// which fields to be searchable
$table->searchable = array('id','capacity'); 

// checks for delete/add and displays notifications
if (isset($_GET['delete'])) {
	
	$spaceId = $_GET['delete'];
		$spacedel = $venuesClient->removeSpace($spaceId);
		
		
	$table->delete_entries('id',sanitize($_GET['delete']));
	
	$action_msg = show_notification("The entry you selected was deleted succesfully","success",true);
}

if (isset($_GET['added'])   && isset($_GET['sort']))
	$action_msg = show_notification("The entry was added succesfully","success",true);

if (isset($_GET['saved'])  && (!isset($_GET['p']) && !isset($_GET['sort'])))
	$action_msg = show_notification("The entry was edited succesfully","success",true);

// handle multiple actions (delete and edit)

if (isset($_POST['action_submit'])) {
	
	$checkboxes = array();
	$checkboxes = $_POST['checkbox'];
	$action_type = $_POST['action_type'];

	$action_msg = '';
	
	if (count($checkboxes) == 0) 
		$action_msg = show_notification("You have to select at least one entry to peform action","error",true);
	
	if ($action_type == "noaction" && count($checkboxes) > 0) 
		$action_msg = show_notification("You have to select an action for the applied entries","error",true);
		
	if ($action_type == "delete") {
		$_GET['a_id']=$_SESSION['a_id'];
		$table->delete_entries('id',$checkboxes);
		$action_msg = show_notification("The entries you selected were deleted succesfully","success",true);
	}
	
}

// how many rows to display

if (isset($_GET['rows']))
	$table->howmany_rows = sanitize($_GET['rows']);

include(dirname(__FILE__) . '/html/header.php');
include(dirname(__FILE__) . '/html/menu.php');

?>
		<div id="main-content"> <!-- Main Content Section with everything -->
			
			<!-- Page Head -->
			<a href="<?=$_SERVER['PHP_SELF']?>?id=<?php echo $_GET['a_id'];?>"><h2>Auditoriums</h2></a>
			
			<br>	
			<div style="padding: 4px;width: auto;">
			<?php //$table->search();?>
			<?php //$table->display_records();?>
			</div>			
			<br>
			<br>
			<br>
			<br>
			<?php if ($action_msg) echo $action_msg; ?>
			<br>		
			<div class="clear"></div> <!-- End .clear -->
			
			<div class="content-box"><!-- Start Content Box -->
				
				<div id="dialog" title="Delete this item?" style="display: none;">
					<p>Are you sure you want to delete this entry?</p>
				</div>
				
				<div class="content-box-header">
					
					<h3> Venue Name: <a href="venue.php">
                    <?php 
					
					$formx2 = new table_form('venue', "form");
									$rowx2 = $formx2->get_row("id",$_GET['a_id']);
									//print_r($rowx2);
									$_SESSION['venue_link']="Venue Name: <a href='venue.php'>".$rowx2['name']."</a>";
									$_SESSION['venue_link_aid']=$_GET['a_id'];
									echo $rowx2['name'];
					?>
                    </a>
                    </h3>
					<div class="clear"></div>
					
				</div> <!-- End .content-box-header -->
				
				<div class="content-box-content">
						
						<a href="<?=edit_page()?>?a_id=<?php echo $_GET['a_id']; ?>" class="link_button" style="font-size: 20px;">Add entry</a>
						<br>
						<br>
						<form name="actions" method="POST" action="<?=$_SERVER['PHP_SELF']?>?id=<?php echo $_GET['a_id'];?>">
						<table>
							
							<thead>
								<tr>
								   <th><input class="check-all" type="checkbox" /></th>
								   <?php foreach ($table->columns as $value) { ?>
										<?php if (!strstr($value,'Id')): ?>
									   <th><?=$value?></th>
										<?php endif; ?>
								   <?php } ?>
                                    
								   <th>Actions</th>
								</tr>

								
							</thead>
					 
							<tbody>
								<?php while ($row = $table->fetch()) { 
								$formx = new table_form('auditorium', "form");
									$rowx = $formx->get_row("id",$row['id']);
						//	print_r($rowx['venue_id']);exit;
								if ($rowx['venue_id']==$_GET['a_id']){
									
								?>
								
									
								<tr>
									<td><input type="checkbox" name="checkbox[]" value="<?=$row['id']?>"></td>
									<?php  foreach ($row as $key => $value) { ?>
											<?php if ($key != 'id'): ?>
										  <td>
												<?php
												if($key=='image_url'){ 
												if($value==''){
													echo '<img src="'.$base_url.'/images/no_image.png" width="70">';
													}
												else{
												echo '<a href="'.$base_url.'/map_images/'.$value.'" >';
												echo '<img src="'.$base_url.'/map_images/'.$value.'" width="70">';
												echo '</a>';
												}
												//echo html_entity_decode(stripslashes($value));
												}
												else{
												 echo html_entity_decode(stripslashes($value));
												}
												  ?><?php //echo short_text($value); ?>
										  </td>
										<?php endif; ?>
									<?php } ?>
									
									<td>
										&nbsp;
										<a href="auditorium_section.php?a_id=<?php echo $table->current_id; ?>" title="Sections"><img src="<?=$base_url; ?>/images/icons/section.png" alt="Sections" /></a>
										 <a href="<?php echo edit_url(urlencode($table->current_id)); ?>&a_id=<?php echo $_GET['a_id']?>" title="Edit"><img src="<?=$base_url; ?>/images/icons/pencil.png" alt="Edit" /></a>
										
										<a class="confirm_link" id="delete_link" href="<?=$_SERVER['PHP_SELF']?>?delete=<?php echo urlencode($table->current_id);?>&a_id=<?php echo $_GET['a_id']?>" title="Delete"><img src="<?php echo $base_url; ?>/images/icons/cross.png" alt="Delete" /></a>
									
									</td>
								</tr>
																
								<?php } }?>
							</tbody>
							
								<tfoot>
									<tr>
										<td colspan="6">
											<div class="bulk-actions align-left">
												<select name="action_type">
													<option value="noaction">Choose an action...</option>
													<option value="delete">Delete</option>
												</select>
												<input type="submit" class="button" value="Apply to selected" name="action_submit"></input>
											</div>
											</form>
											
											<div class="pagination">
												<?php $table->pagination(); ?>
											</div> <!-- End .pagination -->
											<div class="clear"></div>
										</td>
									</tr>
								</tfoot>
							
						</table>
	
				</div> <!-- End .content-box-content -->
				
			</div> <!-- End .content-box -->

			
<?php include("html/footer.php"); ?>