<div class="portlet box green-meadow">
	<div class="portlet-title">
		<div class="caption">
			<i class="fa fa-user"></i> Create an Reference User
		</div>
	</div>
	<?php 
		if(!empty($wrong)) { ?>
			<div class="note note-danger">
    		    <p><?php echo $wrong; ?></p>
            </div>
    <?php } ?>
	<?php 
		if(!empty($right)) { ?>
			<div class="note note-success">
    		    <p><?php echo $right; ?></p>
            </div>
    <?php } ?>
	<div class="portlet-body form">
		<!-- BEGIN FORM-->
		<form  class="form-horizontal"  id="myform"  role="form" method="post">
			<div class="form-body">
				<div class="form-group">
					<label class="control-label col-md-3 ">Name</label>
					<div class="col-md-4">
						<div class="input-icon right">
							<i class="fa"></i>
							<input class="form-control " name="name" type="text">
						</div>
						<span class="help-block">Provide your name </span>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-md-3">Mobile No.</label>
					<div class="col-md-4">
						<div class="input-icon right">
							<i class="fa"></i>
							<input class="form-control " name="mobile_num" type="text" id="number">
						</div>
						<span class="help-block">Provide your Mobile Number</span>
					</div>
				</div>
			</div>
			<div class="form-actions">
				<div class="row">
					<div class="col-md-offset-3 col-md-9">
						<button type="submit" name="login_reg" class="btn red "><i class="fa fa-check"></i> Submit</button>
						<button type="button" class="btn default ">Cancel</button>
					</div>
				</div>
			</div>						
		</form>

	</div>
<!-- END FORM-->
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('.note').slideUp(5000);
	});
	$(document).ready(function() {
        $('#number').keypress(function (event) {
            return isNumber(event, this)
        });
        $("#number").attr('maxlength','10');
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
</script>