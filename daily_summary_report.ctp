<div class="portlet box blue-hoki">
                         <div class="portlet-title">
                            <div class="caption">
                            <i class="fa fa-edit"></i> Daily Summary
                            </div>
                            </div>
                            <div class="portlet-body form">
							<!-- BEGIN FORM-->
                                <div class="form-body">
								<form  class="form-horizontal" role="form" method="post">    
                                <div class="form-group">
                                <label class="control-label col-md-3">Date Range</label>
                                <div class="col-md-4">
                                <div class="input-group input-large date-picker input-daterange" data-date-format="dd-mm-yyyy">
                                <input class="form-control " name="from" placeholder="From" type="text">
                                <span class="input-group-addon" style="line-height:1 !important;">
                                to </span>
                                <input class="form-control " name="to" placeholder="To" type="text">
                                </div>
                                </div>
                                <div class="col-md-4">
                                <button type="submit" name="daily_search" class="btn red "><i class="fa fa-search"></i> Search</button>
                                </div>
                                </div>
                                </form>
                                <?php
								if(!empty($date_from) && !empty($date_to))
								{ 
								 $start_date=date("Y-m-d",strtotime($date_from));
								 $end_date=date("Y-m-d",strtotime($date_to));
								?>
                                <span style="text-align:right"><input type="button" class="btn blue" onclick="printDiv('prin_div')"   value="Print" /></br></span></br>

                                <div id="prin_div">
                                <table class="table table-striped table-condensed table-bordered table-hover">
                                <thead>
                                <tr>
                                <th>SL.</th>
                                <th>Date</th>
                                <th>Receipt after Discount</th>
                                <th>Net Cash Record</th>
                                <th>Total Receipts</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
								$result1 = array();
								$currentTime = strtotime($start_date);
									$endTime = strtotime($end_date);
									while ($currentTime <= $endTime) 
									{
										  if (date('N', $currentTime) < 8)
										  {
											$result1[] = date('Y-m-d', $currentTime);
										  }
										  $currentTime = strtotime('+1 day', $currentTime);
									}
									$i=0;
									$grand_total=0;
									foreach($result1 as $value)
									{ $i++; 
										$mont_yr=date("d-M",strtotime($value));
									?>
                                    	<tr>
                                        	<td><?php echo $i;?></td>
                                        	<td><?php echo $mont_yr;?></td>
                                    
									<?php	$ftc_ticket=$this->requestAction(array('controller' => 'Handler', 'action' => 'fetch_tecket_daily_summary_report',$value,1), array());
										$total_amount1=0;
										$total_discount1=0;
										foreach($ftc_ticket as $data)
										{
											$amount=$data['ticket_entry']['tot_amnt'];	
											$total_amount1+=$amount;
											$discount=$data['ticket_entry']['discount'];
											$total_discount1+=$discount;
										} 
										$ftc_ticket=$this->requestAction(array('controller' => 'Handler', 'action' => 'fetch_tecket_daily_summary_report',$value,2), array());
										$total_amount2=0;
										$total_discount2=0;
										foreach($ftc_ticket as $data)
										{
											$amount=$data['utility_entry']['tot_amnt'];	
											$total_amount2+=$amount;
											$discount=$data['utility_entry']['discount'];
											$total_discount2+=$discount;
										} ?>
                                        <?php
                                            $total_amount = $total_amount1+$total_amount2;
                                            $total_discount = $total_discount1+$total_discount2;
                                        ?>
										<td><?php echo $amt=$total_amount-$total_discount;?></td>
                                        <td><?php echo $amt;?></td>
                                       
                                        <td><?php echo $amt;?></td>
                                        </tr>
                                        <?php
										$grand_total+=$amt;
									
									}
						?>
                        <tr><th align="center" colspan="2">TOTAL</th>
                        	<th><?php echo $grand_total; ?></th>
                            <th><?php echo $grand_total; ?></th>
                            
                            <th><?php echo $grand_total; ?></th>
                            </tr>
									 
                        </tbody>
                        </table>
                        </div>
                                <?php
								}
								?>
                                    
                                </div>
                            </div>
                        </div>
<script type="text/javascript">
function printDiv(print_div) { 
     var printContents = document.getElementById(print_div).innerHTML;
     document.body.innerHTML = printContents;
     window.print();
	 location='daily_summary_report';
}
</script>
                             
 