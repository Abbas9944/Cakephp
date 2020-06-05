<style type="text/css">
    .left{
        float: left;
    }
    input[type=radio]{
        margin-top: -10px !important;
    }
</style>
<!-- Modal For Authorised Person Check -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Authorised Person ?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" id="auth_user_value" name="auth_user">
                    <option value="">--Select Authorised User--</option>
                    <option value="Dr. Suresh Sisodiya">Dr. Suresh Sisodiya</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-toggle="modal" data-target="#exampleModalCenter1">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" data-toggle="modal" data-target="#exampleModalCenter1">Okay</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal For Reference Person Check -->
<div class="modal fade" id="exampleModalCenter1" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Reference (if any) ?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <select class="form-control" id="dropdown" name="reference_user" required>
                    <option value="">--Select User--</option>
                    <?php foreach ($auth_user as $user) { ?>
                        <option value="<?=$user['authorised_user']['id']?>"><?=$user['authorised_user']['name']?> - <?=$user['authorised_user']['mobile']?></option>
                    <?php } ?>
                </select>                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-dark left" data-toggle="modal" data-target="#exampleModalCenter01">Add User</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">Okay</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal For Authorised Person Check -->
<div class="modal fade" id="exampleModalCenter01" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Add Reference Person</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" id="auth_user_add">
                <input type="hidden" name="form_get" id="form_aaja" value="/handler/add_user">
                <div class="modal-body">
                    <div class="form-group">
                    <label for="recipient-name" class="col-form-label">Enter Name</label>
                    <input type="text" class="form-control" id="auth_user_name" name="auth_user_name">
                </div>
                <div class="form-group">
                    <label for="recipient-name" class="col-form-label">Enter Number</label>
                    <input type="text" class="form-control" id="auth_user_num" name="auth_user_num">
                </div> 
                </div>            
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" onclick="add_user()" data-dismiss="modal">Submit</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php 
$fetch_company_name=$this->requestAction(array('controller' => 'Handler', 'action' => 'fetch_company_name'), array());
foreach($fetch_company_name as $company)
{
 $name=$company['company']['company_name'];
}

?>                      
                        
                        <table width="100%">
                         <tr>
                            <td style="text-align:center;line-height:23px;" colspan="3">
                            <div class="caption caption-md">
                            <span class="caption-subject font bold">
                            <span style="color:#0872BA;font-size:30px;font-weight:900"><?php echo $name; ?></span>
                            </span>
                            </div>
                            </td>
                            </tr>
                            <tr><td><br /></td></tr> 
                            <tr>
                            <td style="color:#FCB03B;font-size:20px;font-weight:900" align="center">GENERATE TICKET</td>
                            </tr>
                            </table><br />

                    
                            <!-- BEGIN FORM-->
                            <form method="post" class="form-horizontal" id="form1" action="submit_ticket" target="_blank" onSubmit="setTimeout(function(){window.location.reload();},10)">    
                           
                            <div class="form-body">
                             <?php
                            if($_GET['mode']=='tic')
                            { ?>
                            <div class="table-responsive">
                              <table width="100%" border="0">
                            <tr>
                             <td width="33.33%">
                            <div class="caption caption-md">
                            <span style="font-size:16px;"><strong><tt>Ticket No. #<?php echo $tic_id; ?></tt></strong></span>
                            </div>
                            </td>
                            <input type="hidden" name="ticket_no" value="<?php echo $tic_id;?>" />
                            <td  width="33.33%">
                             <div class="caption caption-md">
                            <span style="font-size:16px;"><strong><tt>Date: <?php echo date("d-M-Y"); ?></tt></strong></span>
                            </div>
                            </td>
                            <td  width="33.33%">
                              <div class="caption caption-md">
                            <span  style="font-size:16px;"><strong><tt>Counter:<?php echo $this->requestAction(array('controller' => 'Handler', 'action' => 'fetchcountername',$counter_id), array());?></tt></strong></span>
                            </div>
                            </td>
                            </tr>
                            </table>
                            <table width="100%" class="table table-condensed table-hover" id="qwerty">
                            <thead>
                            <tr>
                            <th width="12%">SL.</th>
                            <th width="22%">Name</th>
                            <th width="22%">Price [INR]</th>
                            <th width="22%">NCS</th>
                            <th width="22%">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=0;
                            foreach($master_item_fetch as $data)
                            {$i++;
                                ?>
                                <tr id="tr<?php echo $i; ?>">
                                <td><?php echo $i; ?></td>
                                <td><?php echo $data['master_item']['name']; ?><input type="hidden" id="item_id<?php echo $i; ?>" value="<?php echo $data['master_item']['id']; ?>" /></td>
                                <td><?php echo $data['master_item']['rate']; ?></td>
                                <td><input focus_id="<?php echo $i; ?>" type="text" class="form-control input-sm check" val="<?php echo $i; ?>" name="no_of_person<?php echo $i; ?>" id="ncs<?php echo $i; ?>" auto_id="<?php echo $i; ?>" onKeyUp="allLetter(this.value,this.id);cal_amnt();"  style="width:150px;" autofocus autocomplete="off"/></td>
                                <td id="total_amnt<?php echo $i; ?>"></td>
                                 <input tabindex="-1" type="hidden"  id="amount<?php echo $i; ?>"   value="<?php echo $data['master_item']['rate']; ?>">
                                 <input tabindex="-1" type="hidden" id="total<?php echo $i; ?>"  name="amount<?php echo $i; ?>"  >
                                 <input tabindex="-1" type="hidden" id="security<?php echo $i; ?>" value="<?php echo $data['master_item']['security']; ?>">
                                 <input tabindex="-1" type="hidden"  name="master_item_id<?php echo $i; ?>" value="<?php echo $data['master_item']['id']; ?>" />
                                </tr>
                                <?php
                            }
                            ?>
                            <tr>
                                <td><?php echo $i+1;?></td>
                                <!-- <td><input   type="text" placeholder="Locker No." name="locker_no" class="form-control input-sm" id="locker_no" onKeyUp="allLetter(this.value,this.id);" style="width:150px;" /></td> -->
                                <td><input placeholder="Name of Person"  type="text" class="form-control input-sm" id="name_person" name="name_person"  style="width:150px;" /></td>
                                <td><input placeholder="Mobile No." type="text" class="form-control input-sm" id="mobile" name="mobile"  style="width:150px;" maxlength="10" onKeyUp="allLetter(this.value,this.id);"/></td>
                                <td></td>
                            </tr>
                            <tr>
                            <th colspan="4" style="text-align:right;">Total:</th>
                            <th id="all_total"></th>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                            <th colspan="4" style="text-align:right;">  
                            
                             <label class="tooltips" data-original-title="Click to enable discount" ><input name="discount" type="checkbox"  onchange="cal_amnt();" data-toggle="modal" data-target="#exampleModalCenter"> <strong>Discount %:</strong></label>
                            
                            
                            </th>
                            <th id="tax_amnt"><input type="text" class="form-control input-sm" readonly="readonly" name="discount_amount" autofocus  autocomplete="off" id="discount" onKeyUp="allLetter(this.value,this.id);cal_amnt();"  style="width:150px;" />
                            </th>
                            </tr>
                            <tr>
                            <th colspan="4" style="text-align:right;">Payment Method:</th>
                            <th id="pay_method">
                                <input type="checkbox" id="pay" name="payment_method" value="cash"><b>CASH</b>
                                <input type="checkbox" id="pay" name="payment_method" value="paytm"><b>PAYTM</b>
                                <input type="checkbox" id="pay" name="payment_method" value="upi"><b>UPI</b>
                            </th>
                            </tr>
                            <tr>
                            <th colspan="4" style="text-align:right;">Grand Total:</th>
                            <th id="grand_tot">&nbsp;</th>
                            </tr>

                            <!-- <tr>
                            <th colspan="4" style="text-align:right;">Security Amount Refundable:</th>
                            <th id="security_amnt">&nbsp;</th>
                            </tr>
                            <tr>
                            <th colspan="4" style="text-align:right;">Amount To Paid:</th>
                            <th id="paid_amnt">&nbsp;</th>
                            </tr> -->
                            <tr>
                            <td colspan="5" style="text-align:center">
                            <button type="submit" name="ticket_submit" class="btn green-haze btn-lg" id="disbalebtn" >Submit Your Ticket <i class="fa fa-check" ></i></button>

                            </td>
                            </tr>
                            </tfoot>
                            </table>
                            </div>
                            <input type="hidden" value="<?php echo $i; ?>" id="count" name="count"/>
                            <input type="hidden" id="all_tot" name="tot_amnt"/>
                         <!--   <input type="hidden" id="all_tax" name="tax"/>  -->
                            <input type="hidden" id="all_grand_tot" name="grand_amnt"/>
                            <input type="hidden" id="all_security" name="security_amnt"/>  
                            <input type="hidden" id="all_net" name="paid_amnt"/>       
                            <input type="hidden" name="discount_authorise" value="" id="authorise" />
                            <input type="hidden" name="discount_detail" id="discount_detail" value=""/>
                            <input type="hidden" name="reference_id" id="reference" value=""/>
                            <input type="hidden" name="ticket_type"  value="1"/> 
                            <?php
                            }
                        ?>
                            </div>
                            
                            </form>
                            </div>
                            </div>
                            </div>
                            
                           
<script src="<?php echo $this->webroot; ?>assets/global/plugins/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">


$( document ).ready(function() { 
    
    $( ".checked_out" ).keyup(function() {
        var id=eval($(this).attr("focus_id"));
        var tr_length=$('#ch_out > tbody  > tr').length;
         var i=0;
            $('#ch_out > tbody  > tr').each(function() {
            i++;
            if(tr_length !=i)
            {
                    if($("#ncs"+id).val()>0)
                    {
                        if($("#sec"+id).val().length>0)
                        {
                            $("#place"+id).html('<i class="fa fa-inr"></i>&nbsp;'+Math.round($("#ncs"+id).val()*$("#sec"+id).val()));
                        }
                    }
                    else
                    {
                        $("#place"+id).html(0);
                    }
            }
            });
    });

});
</script>
<script type="text/javascript">
function allLetter(inputtxt,id)  
{  
//var numbers = /^[-+]?[0-9]+$/;
var numbers =  /^[0-9]*\.?[0-9]*$/;  
if(inputtxt.match(numbers))  
{  

}  
else  
{  
document.getElementById(id).value=""; 
return false;  
}  
}           
</script>
<script>
function cal_amnt()
{
    
var count=0;
var rate=0;
var person=0;
var total_amnt=0;
var security=0;
var total_security=0;
var all_security=0;
var all_total=0;
var grand_total=0;
var paid_amnt=0;
var discount_amnt=0;
count=eval(document.getElementById("count").value); 
for(var k=1;k<=count;k++)
{
    if($('#amount'+k).val().length == 0)
    {
        rate=0; 

    }
    else
    {
        rate=$('#amount'+k).val();
    }
    if($('#ncs'+k).val().length == 0)
    {
        person=0;
    }
    else
    {
        person=$('#ncs'+k).val();
    }
    if($('#security'+k).val().length == 0)
    {
        security=0;
    }
    else
    {
        security=$('#security'+k).val();
    }
    if($('#discount').val().length == 0)
    {
        discount=0;
    }
    else if($('#discount').val().length == 3 && $('#discount').val() >99)
    {
        discount=$('#discount').val();
        security=0;
    }
    else
    {
        discount=$('#discount').val();
    }
    
    total_amnt=rate*person
    // if(isNaN(security)){
    //  total_security=person*0;
    // }
    // else{
    //  total_security=person*security;
    // }

    if(discount > 100){
        discount=100;
        all_security+=0;
        //alert(security);
        all_total+=total_amnt;
        grand_total=0   ;
            ek_total = 0;
        discount_amnt=Math.round(ek_total*discount/100);
        discount_amnt1=Math.round(all_total*discount/100);
        // alert(discount_amnt1);
        console.log('Discount: '+discount);
        console.log('Discount Amount : '+discount_amnt1);
    }
    else{
        // total_security=person*security;
        total_security=person*security;
        all_security+=total_security;
        //alert(security);
        all_total+=total_amnt;
        grand_total=all_total;

        discount_amnt=Math.round(all_total*discount/100);
        discount_amnt1=Math.round(all_total*discount/100);

    }   
    
    $('#discount_detail').val(discount_amnt1);
    $('#total_amnt'+k).text(Math.round(total_amnt));
    $('#all_total').text(Math.round(all_total));
    $('#grand_tot').text(Math.round(grand_total-discount_amnt));
    $('#security_amnt').text(Math.round(all_security));
    $('#paid_amnt').html('<i class="fa fa-inr"></i>&nbsp;'+Math.round((grand_total+all_security)-discount_amnt));
    $('#total'+k).val(Math.round(total_amnt));
    $('#all_tot').val(Math.round(all_total));
    $('#all_grand_tot').val(Math.round(grand_total-discount_amnt));
    $('#all_net').val(Math.round((grand_total+all_security)-discount_amnt));
                        
    }
}




$(document).keydown(function(e) {
    switch(e.which) {
        case 37: // left
        break;
        
        case 38: // up
        var $focused=$(':focus');
        var f_id=$focused.attr("focus_id");
        f_id--;
        $("#ncs"+f_id).focus();
        break;

        case 39: // right
       
        break;

        case 40: // down
        var $focused=$(':focus');

        var f_id=$focused.attr("focus_id");
        f_id++;
        $("#ncs"+f_id).focus();
        break;

        default: return; // exit this handler for other keys
    }
    e.preventDefault(); // prevent the default action (scroll / move caret)
});
</script>
<script>
    $(document).ready(function() {
        $('#auth_user_num').keypress(function (event) {
            return isNumber(event, this)
        });
        $("#auth_user_num").attr('maxlength','10');
        $('#pay').attr('required',true);
    });
    // THE SCRIPT THAT CHECKS IF THE KEY PRESSED IS A NUMERIC OR DECIMAL VALUE.
    function isNumber(evt, element) {

        var charCode = (evt.which) ? evt.which : event.keyCode

        if (
            (charCode != 45 || $(element).val().indexOf('-') != -1) &&      // “-” CHECK MINUS, AND ONLY ONE.
            (charCode != 46 || $(element).val().indexOf('.') != -1) &&      // “.” CHECK DOT, AND ONLY ONE.
            (charCode < 48 || charCode > 57))
            return false;

        return true;
    }
function myFunction() 
{
    value = +$('input[name=discount]').is(':checked');
    if(value==0)
    {
      $('input[name=discount_amount]').prop('readOnly', true);
      $('input[id=discount]').val('0');
       $('input[id=authorise]').val('');
    }
    else
    {
        // var person = prompt("Name of Authorised Person ?","Dr. Suresh Sisodiya");
        
        //   if((person!=null)&&(person!='')){ 
        //   $('input[name=discount_amount]').prop('readOnly', false);
        //   $('input[id=authorise]').val(person);
        //   }
        //   else {
        //    $('input[id=authorise]').val(''); 
        //   }
        var reference = prompt("Refernce (if any) ?");
          while((reference!=null) && (reference!='')){
            var ref_number = prompt("Enter Mobile Number");
            if((ref_number!='')&&(ref_number!=null)){
                $('input[id=ref_name]').val(reference);
                $('input[id=ref_num]').val(ref_number);
                break;
            }
            else{
                alert("Please Enter Mobile Number !!");                    
            }
        }
    }   
}
function add_user(){
    var name = $('#auth_user_name').val();
    // alert(name);
    var num = $('#auth_user_num').val();
    // alert(num);
    let url = $('#form_aaja').val();
    // alert(url);
    $.ajax({
        type: 'POST',
        url: url,
        data: {auth_name:name ,auth_num:num},
        success: function(data){
            // alert(data+name+num);
            $('#dropdown').append('<option value='+data+'>'+ name +' - '+ num+'</option>');
        },
        error: function(data){
            // alert('nahi hua');
        }
    }); 
}
$(document).ready(function($) {
    $('#auth_user_value').on('change',function(){
        let auth_value = $(this).val();
        if(auth_value!='null' && auth_value!=''){
            $('input[name=discount_amount]').prop('readOnly', false);
            $('input[id=authorise]').val(auth_value);
        }
        else{
            $('input[name=discount_amount]').prop('readOnly', true);
        }
        
    });
    $('#dropdown').on('change',function(){
        let ref_value = $(this).val();
        // alert(ref_value);
        $('input[id=reference]').val(ref_value);
    });
    $('#discount').on('keydown keyup change', function(){
            let value = $(this).val();
            if(value > 100 ){
                $(this).val('100');
            }
        });
    $('input[type=checkbox]').on('click',function(){
        // alert($(this).val());
        if($(this).val() == 'cash'){
            var open = prompt("Amount Given By Person ?");
            let tot_val = $('#all_net').val();
            // alert(tot_val);
            if(open == tot_val)
                alert('No amount to be given back');
            else if(open>tot_val){
                let give_back = Math.round(open-tot_val);
                alert("Please Give back "+ give_back +" Rupees");
            }
            else{
                alert("Please Pay Full Amount");
                $(this).attr('checked',false);
            }
        }
    });
});
</script>