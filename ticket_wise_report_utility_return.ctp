<style media="print">
  .ptint_time_hide
  {
    display:none;
  }
</style>
<div class="portlet box green-meadow">
  <div class="portlet-title">
    <div class="caption">
    <i class="fa fa-ticket"></i> Utility Return Reports
    </div>
  </div>
  <div class="portlet-body form">
<!-- BEGIN FORM-->
    <form  class="form-horizontal" role="form" method="post">    
      <div class="form-body">
        <div class="form-group">
          <label class="control-label col-md-3">Utility From</label>
          <div class="col-md-4">
            <div class="input-group input-large date-picker input-daterange" data-date-format="dd-mm-yyyy">
              <input class="form-control " name="from"  placeholder="Utility From" type="text">
              <span class="input-group-addon" > To </span>
              <input class="form-control " name="to"  placeholder="Utility To" type="text">
            </div>
          </div>
          <div class="col-md-4">
            <button type="submit" name="report_ticket_gen" class="btn red "><i class="icon-bar-chart"></i> Generate</button>
          </div>
        </div>
      </div>
    </form>
    <?php
			if(!empty($from)){?>
        <hr/>
        <?php if(!empty($from)){ ?>
        <div class="note note-info">
        <p> 
      	Ticket Report From <?php echo @$from; ?> To  <?php  echo @$to; ?>
        
        </p>
        </div>
        <?php } ?>   
		<div id="prin_div">
			<table class="table table-striped table-condensed table-bordered table-hover responsive display">
				<thead>
					<tr>
						<th colspan="6" id="center" style="text-align: center !important;">UTILITY ENTRIES</th>
					</tr>
					<tr>
						<th>TICKET NO.</th>
						<th>Male Costume</th>
						<th>Female Costume</th>
						<th>Locker</th>
						<th>Towel</th>
						<th>Group Locker</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php foreach($fetch_data_tiket_item as $row)
							{
							$id=$row['utility_entry']['id'];
				            $ticket_no=$row['utility_entry']['ticket_no'];
				            $name_person=$row['utility_entry']['name_person'];
				            $master_item_id=$row['utility_entry']['master_item_id'];
				            $no_of_person=$row['utility_entry']['no_of_person'];
							$no_explode=explode(',',$no_of_person);
							$date=$row['utility_entry']['date'];
							$date_str=strtotime($date);						
							$current_date=date("Y-m-d");
							$current_date_str=strtotime($current_date);						
						?>
						<tr>
	       		<td># <?php echo $ticket_no; ?> </td>
	        <?php
						foreach($fetch_master_item as $category_data)
						{
							$id_item=$category_data['master_item']['id'];
							$rate=$category_data['master_item']['rate'];
							$ticket_part=explode(',',$master_item_id);
							$total_pax=0;
							if(in_array($id_item, $ticket_part))
							{
								$key = array_search($id_item, $ticket_part);
								 $total_pax+=$no_explode[$key];
							}
							if($id_item>2){
								?>
	              <td><input readonly class="form-control input-xsmall auto" field="no_of_person" item_id="<?php echo $id_item;?>" item_rate="<?php echo $rate;?>" record_id="<?php echo $id;?>"  name="from" value="<?php echo $total_pax; ?>">
	              <?php } } }?>
						</td>
						</tr>		
				</tbody>
			</table>
		</div>
		<br>
		<div id="prin_div">
			<br>
			<br>
			<table class="table table-striped table-condensed table-bordered table-hover responsive display">
				<thead>
					<tr>
						<th colspan="6" id="center" style="text-align: center !important;">UTILITY RETURNS</th>
					</tr>
					<tr>
						<th>Ticket No.</th>
						<th>Male Costume</th>
						<th>Female Costume</th>
						<th>Locker</th>
						<th>Towel</th>
						<th>Group Locker</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<?php foreach($fetch_data_tiket_item1 as $row)
							{
							$id=$row['utility_return']['id'];
	            $ticket_no=$row['utility_return']['ticket_no'];
	            $master_item_id=$row['utility_return']['master_item_id'];
	            $no_of_person=$row['utility_return']['no_of_person'];
							$no_explode=explode(',',$no_of_person);
							$date=$row['utility_return']['date'];
							$date_str=strtotime($date);						
							$current_date=date("Y-m-d");
							$current_date_str=strtotime($current_date);						
						?>
						<tr>
	       		<td># <?php echo $ticket_no; ?> </td>
	        <?php
						foreach($fetch_master_item as $category_data)
						{
							$id_item=$category_data['master_item']['id'];
							$rate=$category_data['master_item']['rate'];
							$ticket_part=explode(',',$master_item_id);
							$total_pax=0;
							if(in_array($id_item, $ticket_part))
							{
								$key = array_search($id_item, $ticket_part);
								 $total_pax+=$no_explode[$key];
							}
							if($id_item>2){
								?>
	              <td><input readonly class="form-control input-xsmall auto" field="no_of_person" item_id="<?php echo $id_item;?>" item_rate="<?php echo $rate;?>" record_id="<?php echo $id;?>"  name="from" value="<?php echo $total_pax; ?>">
	              <?php } } }?>
						</td>
						</tr>		
				</tbody>
			</table>
		</div>
	<?php } ?>
	<br>
	<br>
	</div> 
</div>
<script type="text/javascript">
	$(document).ready(function($) {
  	$('table.display').DataTable();
	});
</script>