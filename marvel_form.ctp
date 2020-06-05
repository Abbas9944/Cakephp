

<html>
	<head>
		<link rel="stylesheet" href="<?php echo $this->webroot; ?>assets/global/plugins/bootstrap/css/bootstrap.min.css">
		<style>
            label.error{
            	background: white !important;
            	color: red !important;
            	width: 100%;
            }
            .error{

            	background: white !important;
            	color: black !important;
            }
            #header{
            	display: none;
            }
            #footer{
            	display: none;
            }
            .datepicker-dropdown{
            	width: 17.4%;
            }
            #minus,#plus,#children_minus,#children_plus{
            	width: 20%;
            }
		</style>
	</head>
	<body style="background-color: #fafbfc;background-repeat: no-repeat;">
		<div class="container" style="background: #fff;margin-top: 0;">
			<center><img src="http://marvelwaterpark.in/wp-content/uploads/2019/04/marvel-logo.png" /></center>
			<h3><center>Generate Ticket</center></h3>
			<form method="post" id="myform" action="marvel_form">
				<div class="row">
					<label style="display:none; margin-left: 155px;">
					  Ticket No :
					<input style="border:none;" class="border" type="text" name="ticket_no" value="<?=$ticket_no?>" readonly>
					<input style="border:none;" type="text" name="auto_incre" value="<?=$auto_increment?>" readonly>
					</label>
					<label style="display:none;">
					  Counter :
							<input style="border : none;" class="border" type="text" name="counter_id" value="22" readonly>
							<input style="border : none;" class="border" type="text" name="counter" value="online" readonly>
					</label>
				</div>
				
				<table class="table table-bordered table-responsive" border="0" align="center" id="main_table">
					<tr class="border">
						<td class="border">Name :
							<input type="text"  id="name" name="user_name" class="form-control" tabindex="1" />
						</td>
						<td class="border">Email :
							<input type="text" id="email" name="email" class="form-control" tabindex="3" />
						</td>
						<td class="border">Mobile :
							<input type="text" id="number" onkeypress="ValidateNumberOnly()" name="mobile_no" class="form-control" tabindex="3"/>
						</td>
						<td class="border" colspan="2" style="display: none;">Visit Date :
							<input type="text" name="date" class="form-control datepicker" value="2020-03-05" />
					    </td>
					</tr>				
				</table>
				
				<table class="table table-bordered table-responsive" border="0" align="center" id="main_table">
					<tr>
						<th>SL</th>
						<th>Name</th>
						<th>Price[INR]</th>
						<th style="width: 20%; text-align: center;">No of Person</th>
						<th>Total</th>
					</tr>

					<tr>
						<td>1</td>
						<td>Adult
							<input type="hidden" name="name[]" id="names" class="border">
							<input type="hidden" name="count[]" id="count" value="1">
						</td>
						<td>400
							<input type="hidden" name="price[]" id="price" class="border">
						</td>
						<td style="text-align:center">
						    <input type='button' id='minus' value='-' data-val='-1' />
                    	    <input type="text" class="" name="ncs[]" id="ncs" value="0" readonly style="width: 20%;">
                            <input type='button' id='plus' value='+' data-val='1'/>						    
						  </td>
						<td><input type="text" name="total[]" id="total1" value="0" style="width: 50%;" readonly class="border"></td>
					</tr>

					<tr>
						<td>2</td>
						<td>Children
							<input type="hidden" name="name[]" id="children_name" class="border">
							<input type="hidden" name="count[]" id="count" value="2">
						</td>
						<td>250
							<input type="hidden" name="price[]" id="children_price"class="border">
						</td>
						<td style="text-align: center">

						   <input type='button' id='children_minus' value='-' data-val='-1' />
                    	        <input type="text" name="ncs[]" id="children_ncs" value="0" readonly style="width: 20%;" class="">
                    	   <input type='button' id='children_plus' value='+' data-val='1'/>							    
						    
						    
						</td>
						<td><input type="text" readonly name="total[]" value="0" style="width: 50%;" id="total2" class="border"></td>
					</tr>

					<tr style="display:none;">
						<td colspan="5" style="text-align: right;" >Total:
							<input type="text" readonly name="main_total" id="main_total"  value="0" class="border">
						</td>
					</tr>

					<tr style="display:none;">
						<td colspan="5" style="text-align: right;">Grand Total:
							<input type="text" readonly name="main_total" id="grand_total"  value="0" class="border">
						</td>
					</tr>

					<tr style="display:none;">
						<td colspan="5" style="text-align: right;" value="0" id="security_amount">Security Amount Refundable: 
							<input type="text" readonly name="security_amount" id="security_amount" value="0" class="border">
						</td>
					</tr>

					<tr>
						<td colspan="4" style="text-align: right;">Amount To Paid:</td>
						<td style="text-align: left;" >
							<input type="text" readonly name="paid_amount" style="width: 50%;" id="paid_amount" value="0" class="border">
						</td>
					</tr>

					<tr class="border">
					    <td colspan="3"> 
					            <strong>Note :</strong> 
					                <label style="font-size: 14px;color: red;">Please enter valid email address as your ticket will be send via email!</label>
					            <label style="font-size: 14px;color: red;margin-left: 48px;">Ticket is non returnable !</label>
					   </td>
						<td colspan="2"><center><button type="submit" style="float: right;" name="generate" class="btn btn-success">Submit Your Ticket</button></center></td>
					</tr>
				</table>
			</form>	
		</div>
	</body>
	<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-migrate.min.js" type="text/javascript"></script>
<!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<script src="<?php echo $this->webroot; ?>assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="<?php echo $this->webroot; ?>assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script src="<?php echo $this->webroot; ?>assets/admin/pages/scripts/form-validation.js"></script>
 <script src="<?php echo $this->webroot; ?>assets/admin/pages/scripts/ui-general.js" type="text/javascript"></script>
 <script type="text/javascript">
	$.widget.bridge('uibutton', $.ui.button);
	$("#myform").validate({
		  rules: {
		    paid_amount: {
		    	required: true,
		    	minlength: 3
		    },
		    user_name: "required",
		    date: "required",
			mobile_no:{
			    required:true,
				minlength:10,
				maxlength:10,
				number: true
			},
		    email: {
		      required: true,
		      email: true
		    }
		  },
		  messages: {
		    date: "Please specify date",
		    mobile_no: "Please specify your mobile number",
		    user_name: "Please specify your name",
		    paid_amount: "Add members",
		    email: {
		      required: "We need your email address to contact you",
		      email: "Your email address must be in the format of name@gmail.com"
		    }
		}
	});

	</script>
	<script>
	function ValidateNumberOnly()
	{
		if ((event.keyCode < 48 || event.keyCode > 57)) 
		{
		   event.returnValue = false;
		}
	}
	
    $(document).ready(function() {
       $('#plus,#minus').on('click',handlePlusMinus); 
       $('.datepicker').datepicker();   
    });
     
    function handlePlusMinus(event){
    	var val = parseInt($('#ncs').val()) + parseInt($(this).data("val"));
    	if(val >= 0)
    	{   
         	$('#ncs').val(val);	 
    	}
        else{
            alert('Invalid No of customer');
        }
    }	
	
	

    $(document).ready(function() {
       $('#children_plus,#children_minus').on('click',handlePlusMinus_children);     
    });
     
    function handlePlusMinus_children(event){
    	var val = parseInt($('#children_ncs').val()) + parseInt($(this).data("val"));
    	if(val >= 0)
    	{   
         	$('#children_ncs').val(val);	 
    	}
        else{
            alert('Invalid No of customer');
        }
    }	
	
	
	$(document).ready(function() {
		$('#plus').on('click',function(){
		    $('#names').val('Adult');
			$('#price').val('400');
		    var no_people=$('#ncs').val();
			var a=$(this).closest('tr').find('td:nth-child(3) #price').val();
			var total1= no_people * a;
			$(this).closest('tr').find('td:nth-child(5) input').val(total1);
			calculate_total()   
		})
		
		
		$('#minus').on('click',function(){
		    $('#names').val('Adult');
			$('#price').val('400');
		    var no_people=$('#ncs').val();
			var a=$(this).closest('tr').find('td:nth-child(3) #price').val();
			var total1= no_people * a;
			$(this).closest('tr').find('td:nth-child(5) input').val(total1);
			calculate_total()   
		})		
		
		

		$('#children_plus').on('click',function(){ 
			$('#children_name').val('Children');
			$('#children_price').val('250');
			var no_people=$('#children_ncs').val();
			var a=$(this).closest('tr').find('td:nth-child(3) #children_price').val();
			var total2= no_people * a;
			$(this).closest('tr').find('td:nth-child(5) input').val(total2);
			calculate_total()
		})


		$('#children_minus').on('click',function(){
			$('#children_name').val('Children');
			$('#children_price').val('250');
			var no_people=$('#children_ncs').val();
			var a=$(this).closest('tr').find('td:nth-child(3) #children_price').val();
			var total2= no_people * a;
			$(this).closest('tr').find('td:nth-child(5) input').val(total2);
			calculate_total()
		})


		function calculate_total(){
			//var total=0;
			var total1=$('#total1').val();
			//alert(total1);
			var total2=$('#total2').val();
			//alert(total2)
			var amount=(+total1) + (+total2);

			$('#main_total').val(amount);
			$('#grand_total').val(amount);
			$('#paid_amount').val(amount);
			//alert(amount);
		}
	});
	</script>	
</html>