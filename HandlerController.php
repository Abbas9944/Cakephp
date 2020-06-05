<?php
date_default_timezone_set('asia/kolkata');
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
ini_set('memory_limit', '256M');
set_time_limit(0);
class HandlerController extends AppController
{
	public $helper=array('html', 'form', 'Js');

	public $components = array(
    'Paginator',
    'Session','Cookie','RequestHandler'
 	);
	
	
	//////////////////////////////////////////////////////////////--------------- Authentication  Start------------------------//////////////////////////////////////////////
	 public function beforeFilter() {
       Configure::write('debug',2);
    } 

  //   public function addBar(){
  //   	// echo "hello";die;
		// $data_to_encode = '1012012,BLAHBLAH01234,1234567891011';
		// $barcode=new BarcodeHelper();

		// // Generate Barcode data
		// $barcode->barcode();
		// $barcode->setType('C128');
		// $barcode->setCode($data_to_encode);
		// $barcode->setSize(80,200);

		// // Generate filename           
		// $random = rand(0,1000000);
		// $file = 'img/barcode/code_'.$random.'.png';

		// // Generates image file on server           
		// $barcode->writeBarcodeFile($file);
  //   }


	public function utility_return()
	{
		$this->layout='index_layout';
		$this->loadmodel('utility_entry');
		$this->loadmodel('master_item');
		$this->loadmodel('counter');
		$this->set('counter_fetch', $this->counter->find('all'));
			if($this->request->is('post'))
			{
				$this->loadmodel('utility_entry');
				$this->loadmodel('utility_return');
				if(isset($this->request->data['report_tic_gen']))
				{ 			
					$qry = $this->utility_entry->find('all',array('conditions' => array('flag' => 0, 'ticket_no' => $this->request->data['ticket_no'])));
					//pr($qry);exit; 
					$this->set('ticket_edit_data',$qry);

					$returnData = $this->utility_return->find('all',array('conditions' => array('flag' => 0, 'ticket_no' => $this->request->data['ticket_no'])));
					//pr($qry);exit; 
					$this->set('returnData',$returnData);
				}
				////////////// 
				if(isset($this->request->data['ticket_submit']))
				{
					$count=$this->request->data['count'];
				
					for($i=1;$i<=$count;$i++)
					{
						if(!empty($this->request->data['no_of_person'.$i]))
						{
							$no_of_person[]=$this->request->data['no_of_person'.$i];
							$refund[]=$this->request->data['refund'.$i];
							$master_item_id[]=$this->request->data['master_item_id'.$i];
						}
					}
 					$this->request->data['no_of_person']=@implode(',',$no_of_person);
					$this->request->data['amount']=@implode(',',$refund);
					$this->request->data['master_item_id']=@implode(',',$master_item_id);
 					$this->request->data['login_id']=$this->Session->read('auto_login_id');
					$this->request->data['counter_id']=$this->Session->read('counter_id');  
					$this->request->data['date']=date('Y-m-d');  
					$this->request->data['time']=date('h:i:s'); 
					//pr($this->request->data);exit; 
 					$this->utility_return->save($this->request->data);
 					$last_id = $this->utility_return->getLastInsertId();
 					$this->response->header('location: utility_return');
 					$this->response->header('location: view_utility_return?id='.$last_id.'');
				}
			}
			
		$mycounter=$this->Session->read('counter_id');
		$item_status=$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'2')));
		foreach($all_data as $key => $match)
		{
			$exp_data=@explode(',',$match['master_item']['counter_id']);
			if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
			{
				unset($item_status[$key]);
			}
			if(!in_array($mycounter,$exp_data))
			{
				unset($all_data[$key]);
			}
		}
		$this->set('master_item_fetch',  $all_data);
		$this->set('master_item_return', $item_status);
	}

	public function view_utility_return() 
	{
		$print=$this->request->query('print');
		$this->set('username',$this->Session->read('user_name'));	
		$this->set('counter_id',$this->Session->read('counter_id'));
		$this->set('print',$print);
		$this->layout='ajax_layout';
		$this->loadmodel('utility_return');		
		$this->set('last_data', $this->utility_return->find('all', array('conditions' => array('flag'=>0 , 'id' => $this->request->query('id')))));
	} 

	public function return_report() 
	{
		$this->layout='index_layout';
		$this->loadmodel('counter');
		$this->set('counter_fetch', $this->counter->find('all'));
		
		if($this->request->is('post'))
		{
			
			$this->loadmodel('utility_entry');
			$this->loadmodel('utility_return');
			if(isset($this->request->data['report_issue_item']))
			{ 	 
				$from_date=$this->set('date_from',$this->request->data['from']);
				$to_date=$this->set('date_to',$this->request->data['to']);
				$from=$this->datefordb($this->request->data['from']);
				$to=$this->datefordb($this->request->data['to']);
				$conditions="";
				if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
				{
					$conditions[]=array('date between ? and ?' => array($from, $to));
				}
				if(!empty($this->request->data['counter_id']))
				 
				{
					$conditions[]=array('counter_id' => ''.$this->request->data['counter_id'].'');
				} 
				$utilityEntry = $this->utility_entry->find('all',array('conditions' => $conditions));
				$utilityReturn = $this->utility_return->find('all',array('conditions' => $conditions));
				//pr($utilityReturn);exit;
				$this->set('utilityEntry',$utilityEntry);
				$this->set('utilityReturn',$utilityReturn);
			}
		}
		$this->loadmodel('master_item');
		$item_status=$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'2')));
		$this->set('master_item_fetch', $item_status);
	}
	
	function ftc_month_issue_quanitty($form,$to)
	{
		$this->loadmodel('utility_entry');
		$user_id=$this->Session->read('auto_login_id');
		$counter_id=$this->Session->read('counter_id');
		$conditions=array('date between ? and ?' => array($form, $to) );
		return $this->utility_entry->find('all', array('conditions' => $conditions));
	}

	function ftc_month_return_quanitty($form,$to)
	{
		$this->loadmodel('utility_return');
		$user_id=$this->Session->read('auto_login_id');
		$counter_id=$this->Session->read('counter_id');
		$conditions=array('date between ? and ?' => array($form, $to) );
		return $this->utility_return->find('all', array('conditions' => $conditions));
	}
	public function generateRandomString($length = 30) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function authentication()
	{
	
		$auto_login_id=$this->Session->read('auto_login_id');
		
		if(empty($auto_login_id))
		{
			$this->Session->destroy();
			$this->redirect(array('action' => 'login'));
		}
		return $auto_login_id;
	}
	public function send_sms_page()
	{
		$this->layout='ajax_layout';
		$this->loadmodel('ticket_entries');
		$ticket_mobile=$this->ticket_entries->find('all',array('conditions' => array('date' => date("Y-m-d")),'fields'=>array('mobile')));
		$working_key='A7a76ea72525fc05bbe9963267b48dd96';
		$sms_sender='MARVEL';
		$sms=str_replace(' ', '+', 'Thank you for visiting Marvel Water Park. Rate us on trip advisor and like us on facebook. Visit us again.');
		
		foreach($ticket_mobile as $data)
		{
			$mobile_no=$data['ticket_entries']['mobile'];
			if(!empty($mobile_no))
			{
				//$mobile_no='9680747166';// Dsu Menaria
				//file_get_contents('http://alerts.sinfini.com/api/web2sms.php?workingkey='.$working_key.'&sender='.$sms_sender.'&to='.$mobile_no.'&message='.$sms.'');
				file_get_contents("http://103.39.134.40/api/mt/SendSMS?user=phppoetsit&password=9829041695&senderid=".$sms_sender."&channel=Trans&DCS=0&flashsms=0&number=".$mobile_no."&text=".$sms."&route=7");
				//exit;
			}
		}
		
	}
	public function send_sms()
	{
		$this->layout='ajax_layout';
		$this->loadmodel('ticket_entry');	
		$this->set('fatch_ticket_entry', $this->ticket_entry->find('all'));
		
	}
	//////////////////////////////////////////////////////////////--------------- Authentication  End------------------------//////////////////////////////////////////////
	public function fetchusername()
	{
		echo strtoupper($this->Session->read('user_name'));
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function login() 
	{	
		$this->Session->destroy();
		$this->layout='login_layout';
		$this->loadmodel('counter');
		$this->set('counter_fetch', $this->counter->find('all'));
		if (isset($this->request->data['login_submit']) || isset($this->request->data["login_submit_text"]))
		{
			
				$login_id=htmlentities($this->request->data["login_id"]);
				$password=htmlentities($this->request->data["password"]);
				$counter_id=htmlentities($this->request->data["counter_id"]);
				$md5ed_password = md5($password);
				$this->loadmodel('login');
						
				$conditions=array("login_id" => $login_id, "password" => $md5ed_password, "counter_id" => $counter_id);
				$result = $this->login->find('all',array('conditions'=>$conditions));
				
				$n = sizeof($result);
				if($n==1)
				{
					$auto_login_id=$result[0]['login']['id'];
					$user_name=$result[0]['login']['username'];
					$counter_id=$result[0]['login']['counter_id'];
					$type=$result[0]['login']['type'];
					$this->Session->write('login_id', $login_id);
					$this->Session->write('auto_login_id', $auto_login_id);
					$this->Session->write('user_name', $user_name);
					$this->Session->write('counter_id', $counter_id);
					$this->Session->write('type', $type);
					$this->redirect(array('action' => 'index'));
				}
				else
				{
					$this->loadmodel('login');
					$conditions=array("login_id" => $login_id);
					$result1 = $this->login->find('all',array('conditions'=>$conditions));
					 $n1 = sizeof($result1);
					if($n1>0)
					{ 
						 $this->set('wrong', 'Password or Counter name is Incorrect');
					}
					else
					{
							
						$this->set('wrong', 'Login ID and Password are Incorrect');
					}	
				}
		}
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function index() 
	{	
			$this->layout='index_layout';
			$this->set('myname',$this->Session->read('user_name'));
			$this->set('type',$this->Session->read('type'));
			$this->loadmodel('group_booking');				
			$this->set('tot_group_data',$this->group_booking->find('count',array('conditions' => array('current_date' => date("Y-m-d")))));
			$this->loadmodel('ticket_entries');
			$this->set('paid_amnt',$this->ticket_entries->find('all',array('conditions' => array('date' => date("Y-m-d")),'fields'=>array('grand_amnt'))));
			$this->loadmodel('item_manage');
			$this->set('item_issue',$this->item_manage->find('all',array('conditions' => array('date' => date("Y-m-d"),'item_status' => '1'),'fields'=>array('no_of_item'))));
			$this->loadmodel('utility_return');
			$this->set('item_return',$this->utility_return->find('all',array('conditions' => array('date' => date("Y-m-d"),'flag' => '0'),'fields'=>array('no_of_person'))));
			$this->loadmodel('master_item');
			$this->set('ftch_master_item',$this->master_item->find('all',array('conditions' => array('status' =>1))));
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function counter_menu() 
	{	
		$this->layout='index_layout';
		
		$this->loadmodel('counter');
		if($this->request->is('post'))
		{
			if(isset($this->request->data['counter_reg']))
			{
				$rs=$this->counter->save($this->request->data);
				if($rs)
				{
					$this->set('activity',1); $this->set('class','teal'); $this->set('state','Success !'); $this->set('message','New counter added successfully.');
				}
				else
				{
					$this->set('activity',2);
				}
			}
			if(isset($this->request->data['edit_counter']))
			{
				$this->counter->id=$this->request->data['counter_id'];
				$rs=$this->counter->save($this->request->data);
				if($rs)
				{
					$this->set('activity',1); $this->set('class','tangerine'); $this->set('state','Success !'); $this->set('message','Counter edit successfully.');
				}
				else
				{
					$this->set('activity',2);
				}
			}
			if(isset($this->request->data['delete_counter']))
			{
				$rs=$this->counter->delete(array('id'=>$this->request->data['counter_id']));
				if($rs)
				{
					$this->set('activity',1); $this->set('class','ruby'); $this->set('state','Success !'); $this->set('message','Counter deleted successfully.');
				}
				else
				{
					$this->set('activity',2);
				}
			}
		}
		$this->set('counter_fetch', $this->counter->find('all'));
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function ticket_menu() 
	{
		
		$this->layout='index_layout';
		$this->loadmodel('master_item');
		$this->loadmodel('counter');
		$this->set('counter_fetch', $this->counter->find('all'));
		$this->loadmodel('master_category');
		$this->set('master_caregory_ftc', $this->master_category->find('all',array('conditions' => array('flag'=>'0'))));
		if($this->request->is('post'))
		{
			if(isset($this->request->data['ticket_item_master']))
			{
				// echo "Hello Add Section";die;
				$counter_id=$this->request->data['counter_id'];
				if(is_array($counter_id))
					$this->request->data['counter_id']=@implode(',',$counter_id);
				else
					$this->request->data['counter_id']='';
				// print_r($this->request->data);
				$rs=$this->master_item->save($this->request->data);
				// print_r($rs);die;
				if($rs)
				{	$this->set('right', 'Entry Updated Successfully'); }
			}
			if(isset($this->request->data['edit_ticket_item_master']))
			{
				// echo "Hello Edit Section";
				$counter_id=@$this->request->data['counter_id'];
				if(is_array($counter_id))
				$this->request->data['counter_id']=@implode(',',$counter_id);
				else
				$this->request->data['counter_id']='';
				
				$this->master_item->id=$this->request->data['master_item_id'];
				$this->master_item->save(@$this->request->data);
				//*** RateChanges 
				$mateItem=$this->request->data['master_item_id'];
				$rate=$this->request->data['rate'];
				
				$this->loadmodel('rate_change');
				$master_item_id=$this->request->data['master_item_id'];
				$rate=$this->request->data['rate'];
				$items=$this->rate_change->find('all', array('conditions'=>array('master_item_id' => $master_item_id),'order'=>'id DESC','limit'=>1));

				$old_rate=$items[0]['rate_change']['rate'];
				if($old_rate!=$rate){
					$cur_date=date('Y-m-d');
					$this->request->data['timestamp']=$cur_date;
					$this->rate_change->save(@$this->request->data);
				}
			}
			if(isset($this->request->data['delete_ticket_item_master']))
			{
				echo "Hello Delete Section";die;
				$this->master_item->delete(array('id'=>$this->request->data['master_item_id']));
			}
		}
		$this->set('master_fetch', $this->master_item->find('all',array('conditions' => array('status'=>'1'))));
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function found_menu()
	{
		$this->layout='index_layout';
		$this->loadmodel('missing');
		if($this->request->is('post'))
		{
			if(isset($this->request->data['found_submit']))
			{
			    $current_date=date("Y-m-d");
			 	$lost_date=$this->datefordb($this->request->data['lost_date']);
				$this->request->data['lost_date']=$lost_date;
				$this->request->data['current_date']=$current_date;
				$rs=$this->missing->save($this->request->data);
				if($rs)
				{
					$this->set('activity',1); $this->set('class','toast_success_notify'); $this->set('state','Success !'); $this->set('message','Found Item successfully submited.');
				}
				else{
					$this->set('activity',2);
				}
			}
			if(isset($this->request->data['edit_lost_menu']))
			{
				$op=$this->request->query('mode');   //$op will be set to op_info
				$name=$this->request->data['name'];
				$mobile_no=$this->request->data['mobile_no'];
				$from=$this->datefordb($this->request->data['from']);
				$to=$this->datefordb($this->request->data['to']);
				
				$conditions="";
				if(!empty($name))
				{
						$conditions[]=array('type' => 1 , 'name LIKE' => '%'.$name.'%');
				}
				if(!empty($mobile_no))
				{
						$conditions[]=array('type' => 1 ,'mobile_no LIKE' => '%'.$mobile_no.'%');
				}
				if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
				{
						$conditions[]=array('type' => 1 , 'current_date between ? and ?' => array($from, $to));
				}
				$qry= $this->missing->find('all',array('conditions' => $conditions,'order'=>'id ASC')); 
				$this->set('lost_menu_fetch',$qry);
				$this->set('op_info',$op);
			}
		
		}
	}
	public function update_found_menu()
	{
		$this->layout='index_layout';
		$this->loadmodel('missing');
		$id = $this->request->query('id');
		$this->set('lostmenu_updatedata', $this->missing->find('all', array('conditions' => array('id' => $id))));
		if($this->request->is('post'))
		{
			if(isset($this->request->data['final_update_lost']))
			{
				$current_date=date("Y-m-d");
			 	$lost_date=$this->datefordb($this->request->data['lost_date']);
				$this->request->data['lost_date']=$lost_date;
				$this->request->data['current_date']=$current_date;
				
				$this->missing->id=$this->request->data['my_id'];
				$this->missing->save($this->request->data);
			}
		}
	}
	public function temp()
	{	
		$this->layout='ajax_layout';
		$this->loadmodel('ticket_entry');
		$fetch_ticket_entry=$this->ticket_entry->find('all', array('conditions' => array('updated' =>0, 'date <= ' => '2017-06-30'),'order'=>'ticket_no DESC'));
			$i=0;
			 
			foreach($fetch_ticket_entry as $old_data)
			{ $i++;
				$this->request->data['id']=$old_data['ticket_entry']['id'];
				echo $old_data['ticket_entry']['ticket_no'].'<br />';
 				$master_item=$old_data['ticket_entry']['master_item_id'];
				$no_of_person=$old_data['ticket_entry']['no_of_person'];
				$date=$old_data['ticket_entry']['date'];
				$discount=$old_data['ticket_entry']['discount'];
				
				$explode_item=explode(',',$master_item);
				$explode_no_person=explode(',',$no_of_person);
				$x=0;
				unset($amount);
				$total_amount=0;
				foreach($explode_item as $onebyOne)
				{
					$rate_changedORnot=$this->requestAction(array('controller' => 'Handler', 'action' => 'check_rate_changeORnot',$date,$onebyOne), array());
					$rate=$rate_changedORnot[0]['rate_change']['rate'];
					$one_person=$explode_no_person[$x];
					$amount_mix=$rate*$one_person;
					$amount[]=$amount_mix;
					$total_amount+=$rate*$one_person;
					$x++;
				}
				$tot_amount=$total_amount;
				$grand_amnt=$tot_amount-$discount;
 				 
			 	$this->request->data['master_item_id']=$old_data['ticket_entry']['master_item_id'];
				$this->request->data['no_of_person']=$old_data['ticket_entry']['no_of_person'];
				$this->request->data['amount']=implode(',', $amount);
				$this->request->data['tot_amnt']=$total_amount;
				$this->request->data['discount']=$old_data['ticket_entry']['discount'];
				$this->request->data['grand_amnt']=$grand_amnt;
				
				$this->request->data['security_amnt']=$old_data['ticket_entry']['security_amnt'];
				$this->request->data['paid_amnt']=$grand_amnt;
				$this->request->data['discount_authorise']=$old_data['ticket_entry']['discount_authorise'];
				$this->request->data['locker_no']=$old_data['ticket_entry']['locker_no'];
				$this->request->data['name_person']=$old_data['ticket_entry']['name_person'];
				$this->request->data['mobile']=$old_data['ticket_entry']['mobile'];
				$this->request->data['date']=$old_data['ticket_entry']['date'];
				$this->request->data['time']=$old_data['ticket_entry']['time'];
				$this->request->data['login_id']=$old_data['ticket_entry']['login_id'];
				$this->request->data['counter_id']=$old_data['ticket_entry']['counter_id'];
				$this->request->data['updated']=1;
 				$this->ticket_entry->save($this->request->data);
			}
	}
	public function lost_menu() 
	{
		
     	$this->layout='index_layout';
		$this->loadmodel('missing');
		if($this->request->is('post'))
		{
			if(isset($this->request->data['lost_submit']))
			{
			    $current_date=date("Y-m-d");
			 	$lost_date=$this->datefordb($this->request->data['lost_date']);
				$this->request->data['lost_date']=$lost_date;
				$this->request->data['current_date']=$current_date;
				$rs=$this->missing->save($this->request->data);
				if($rs)
				{
					$this->set('activity',1); $this->set('class','toast_success_notify'); $this->set('state','Success !'); $this->set('message','Lost item successfully submited.');
				}
				else{
					$this->set('activity',2);
				}
			}
			if(isset($this->request->data['edit_lost_menu']))
			{
				$op=$this->request->query('mode');   //$op will be set to op_info
				$name=$this->request->data['name'];
				$mobile_no=$this->request->data['mobile_no'];
				$from=$this->datefordb($this->request->data['from']);
				$to=$this->datefordb($this->request->data['to']);
				
				$conditions="";
				if(!empty($name))
				{
						$conditions[]=array('type' => 0 , 'name LIKE' => '%'.$name.'%');
				}
				if(!empty($mobile_no))
				{
						$conditions[]=array('type' => 0 , 'mobile_no LIKE' => '%'.$mobile_no.'%');
				}
				if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
				{
						$conditions[]=array('type' => 0 , 'current_date between ? and ?' => array($from, $to));
				}
				$qry= $this->missing->find('all',array('conditions' => $conditions,'order'=>'id ASC')); 
				$this->set('lost_menu_fetch',$qry);
				$this->set('op_info',$op);
			}
		}
	}
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function update_lost_menu() {
		$this->layout='index_layout';
		$this->loadmodel('missing');
		$id = $this->request->query('id');
		$this->set('lostmenu_updatedata', $this->missing->find('all', array('conditions' => array('id' => $id))));
		if($this->request->is('post'))
		{
			if(isset($this->request->data['final_update_lost']))
			{
				$current_date=date("Y-m-d");
			 	$lost_date=$this->datefordb($this->request->data['lost_date']);
				$this->request->data['lost_date']=$lost_date;
				$this->request->data['current_date']=$current_date;
				
				$this->missing->id=$this->request->data['my_id'];
				$rs=$this->missing->save($this->request->data);
				$this->response->header('location:lost_menu?mode=edit');
			}
		}
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function view_all() {
		$this->layout='index_layout';
		$this->loadmodel('missing');
		$id = $this->request->query('id');
		$this->set('lostmenu_viewdata', $this->missing->find('all', array('conditions' => array('id' => $id))));
		$this->set('view_what','lost_menu_data');
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public  function summary_report()
	{
			$this->layout='ajax_layout';
			$this->loadmodel('master_item');
			$this->loadmodel('user_right');
			$id = $this->Session->read('auto_login_id');
			$counter = $this->Session->read('counter_id');
			$this->set('username',$this->Session->read('user_name'));	
			$this->set('counter_id',$counter);
			$this->set('get_section',$this->user_right->find('all',array('conditions'=> array('user_id'=>$id),'fields'=>array('module_id'))));
			$this->set('ftc_master_item_ticket', $this->master_item->find('all', array('conditions' => array('auto_id' => 1))));
			$this->set('ftc_master_item_utility', $this->master_item->find('all', array('conditions' => array('auto_id' => 2))));
			$this->set('ftc_master_item', $this->master_item->find('all'));
			$this->set('adult_rate', $this->master_item->find('all', array('conditions' => array('id' => 1),'fields'=>array('rate'))));
			$this->set('children_rate', $this->master_item->find('all', array('conditions' => array('id' => 2),'fields'=>array('rate'))));
			
	}

	public function ticket_generate(){
			$this->layout='index_layout';
			$this->loadmodel('master_item');
			$this->loadmodel('ticket_entry');			
			$this->set('counter_id',$this->Session->read('counter_id'));
			$this->loadmodel('authorised_user');
			$this->set('auth_user', $this->authorised_user->find('all',array('conditions'=>array('delete_status'=>0))));
			$mycounter=$this->Session->read('counter_id');
			
			$fetchlastticket_id=$this->ticket_entry->find('all', array('conditions'=>array('flag' => 0),'order'=>'ticket_no DESC','limit'=>1));
			//pr($fetchlastticket_id);
			if(sizeof($fetchlastticket_id)>0)
			{
			$id=$fetchlastticket_id[0]['ticket_entry']['ticket_no'];
			$fetch_last=$id+1;	
			}
			else
			{
			$fetch_last=1;
			}
			$this->set('tic_id',$fetch_last);
			
			$item_status=$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'1','ticket_type' => 1)));
			foreach($all_data as $key => $match)
			{
				$exp_data=@explode(',',$match['master_item']['counter_id']);
				if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
				{
					unset($item_status[$key]);
				}
				if(!in_array($mycounter,$exp_data))
				{
					unset($all_data[$key]);
				}
			}
			$this->set('master_item_fetch',  $all_data);
			$this->set('master_item_return', $item_status);
			
			
	}
	public function discount_generate() {
			$this->layout='index_layout';
			$this->loadmodel('master_item');
			$this->loadmodel('ticket_entry');
			$this->set('counter_id',$this->Session->read('counter_id'));
			$mycounter=$this->Session->read('counter_id');
			$fetchlastticket_id=$this->ticket_entry->find('all', array('conditions'=>array('flag' => 0),'order'=>'ticket_no DESC','limit'=>1));
			if(sizeof($fetchlastticket_id)>0)
			{
			$id=$fetchlastticket_id[0]['ticket_entry']['ticket_no'];
			$fetch_last=$id+1;	
			}
			else
			{
			$fetch_last=1;
			}
			$this->set('tic_id',$fetch_last);
			
			$item_status=$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'1','ticket_type' => 2)));
			foreach($all_data as $key => $match)
			{
				$exp_data=@explode(',',$match['master_item']['counter_id']);
				if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
				{
					unset($item_status[$key]);
				}
				if(!in_array($mycounter,$exp_data))
				{
					unset($all_data[$key]);
				}
			}
			$this->set('master_item_fetch',  $all_data);
			$this->set('master_item_return', $item_status);
	}
	public function submit_ticket(){
		$this->loadmodel('master_item');
		$this->loadmodel('ticket_entry');
		$this->loadmodel('authorised_user');
		if($this->request->is('post'))
		{
			if(isset($this->request->data['ticket_submit']))
			{
			 $count=$this->request->data['count'];
			
			for($i=1;$i<=$count;$i++)
			{
				if(!empty($this->request->data['no_of_person'.$i]))
				{
					$no_of_person[]=$this->request->data['no_of_person'.$i];
					$amount[]=$this->request->data['amount'.$i];
					$chk_sts=$this->request->data['master_item_id'.$i];
					$master_item_id[]=$this->request->data['master_item_id'.$i];
				}
			}
			
			if(!empty($chk_sts)){
				$master_item=$this->master_item->find('all',array('conditions'=> array('id' => $chk_sts)));
				$auto_id_mst=$master_item[0]['master_item']['auto_id'];
					
				$ticket_entry=$this->ticket_entry->find('all', array('conditions' => array('category_auto_id' => $auto_id_mst),'order'=>'auto_increment DESC','limit'=>1));
			}
			if(empty($auto_id_mst))
			{
				$auto_id_mst=0;
			}
			if(!empty($ticket_entry))
			{
				$auto_increment=$ticket_entry[0]['ticket_entry']['auto_increment'];
				$auto_iddd=$auto_increment+1;
			}
			else
			{
				$auto_iddd=1;	
			}
			if(empty($this->request->data['locker_no'])){
				$this->request->data['locker_no']=0;
			}
			if(empty($this->request->data['name_person'])){
				$this->request->data['name_person']=0;
			}
			if(empty($this->request->data['mobile'])){
				$this->request->data['mobile']=0;
			}
			if(empty($this->request->data['reference_id'])){
				$this->request->data['reference_id']=0;
			}
			$fetchlastticket_id=$this->ticket_entry->find('all', array('conditions'=>array('flag' => 0),'order'=>'ticket_no DESC','limit'=>1));
			if(sizeof($fetchlastticket_id)>0)
			{
				$id=$fetchlastticket_id[0]['ticket_entry']['ticket_no'];
				$fetch_last=$id+1;	
			}
			else
			{
				$fetch_last=1;
			}
			$this->request->data['ticket_no']=$fetch_last;
			$this->request->data['discount']=@$this->request->data['discount_detail'];
			$this->request->data['discount_authorise']=@$this->request->data['discount_authorise'];
			$this->request->data['reference_id']=@$this->request->data['reference_id'];
			$this->request->data['payment_method']=@$this->request->data['payment_method'];
			$this->request->data['date']=date("Y-m-d");
			$this->request->data['time']=date('h:i:s a', time());
			$this->request->data['no_of_person']=@implode(',',$no_of_person);
			$this->request->data['amount']=@implode(',',$amount);
			$this->request->data['master_item_id']=@implode(',',$master_item_id);
			$this->request->data['auto_increment']=$auto_iddd;
			$this->request->data['category_auto_id']=$auto_id_mst;
			$this->request->data['login_id']=$this->Session->read('auto_login_id');
			$this->request->data['counter_id']=$this->Session->read('counter_id');
			if($no_of_person){
				$rs=$this->ticket_entry->save($this->request->data);
				$lastEntry = $this->ticket_entry->getLastInsertId();
				///////////////////// SMS API STARTS FROM HERE //////////////////////////////////////////
				// $this->layout='ajax_layout';
				if(!empty($this->request->data['reference_id'])){
					$ticket_mobile=$this->ticket_entry->find('list',array('conditions' => array(['date' => date("Y-m-d"),'id'=>$lastEntry]),'fields'=>array('reference_id')));
					// print_r($ticket_mobile);die;
					if($ticket_mobile){
						// echo "hello";die;
						$mobile_data = $this->authorised_user->find('all',array('conditions' => array(['id'=>$ticket_mobile])));
						// print_r($mobile_data[0]['authorised_user']);
						$ref_name = $mobile_data[0]['authorised_user']['name'];
						$mobile_no = $mobile_data[0]['authorised_user']['mobile'];

						$working_key='A7a76ea72525fc05bbe9963267b48dd96';
						$sms_sender='MARVEL';
						$sms=str_replace(' ', '+', 'Thank you'. $ref_name .' for choosing Marvel Water Park. Your guests have arrived and will have are utmost care. Rate us on trip advisor and share your views. Visit us again.');

						if(!empty($mobile_no))
						{
							// echo "Hello There";die;
							//$mobile_no='9680747166';// Dsu Menaria
							//file_get_contents('http://alerts.sinfini.com/api/web2sms.php?workingkey='.$working_key.'&sender='.$sms_sender.'&to='.$mobile_no.'&message='.$sms.'');
							file_get_contents("http://103.39.134.40/api/mt/SendSMS?user=phppoetsit&password=9829041695&senderid=".$sms_sender."&channel=Trans&DCS=0&flashsms=0&number=".$mobile_no."&text=".$sms."&route=7");
							// exit;
						}
					}
				}
				/////////////////////////// SMS API ENDS HERE/////////////////////////////////////////////////
				//$this->response->header('location: view_ticket?id='.$this->ticket_entry->getLastInsertId().'');
				$this->redirect(array('action' => 'view_ticket?id='.$this->ticket_entry->getLastInsertId().''));
			}
			else
			{
				$this->set('activity',2);
				$pr=1;
			}			
			$this->redirect(array('action' => 'ticket_generate?mode=tic'));
			}			
		}
	}
	
	public function marvel_form(){
		$this->loadmodel('master_item');
		$this->loadmodel('ticket_entry');
		$fetchlastticket_id=$this->ticket_entry->find('all', array('conditions'=>array('flag' => 0),'order'=>'ticket_no DESC','limit'=>1));
		if(sizeof($fetchlastticket_id)>0)		{
			$id=$fetchlastticket_id[0]['ticket_entry']['ticket_no'];
			$fetch_last=$id+1;
			$incre_id=$fetchlastticket_id[0]['ticket_entry']['auto_increment'];			
			$fetch_last_auto=$incre_id+1;
		}else{
			$fetch_last=1;
			$fetch_last_auto=1;
		}
		$this->set('ticket_no',$fetch_last);
		$this->set('auto_increment',$fetch_last_auto);
		if($this->request->is('post')){
			$count=$this->request->data['count'];
			// print_r($count); 
			// foreach ($count as $value) {
			// 	$i=1;
			if(!empty($this->request->data['name']))
			{
				// echo "hello ".$i;
				$no_of_person=$this->request->data['ncs'];
				$amount=$this->request->data['total'];
				$chk_sts=$this->request->data['count'];
				$master_item_id=$this->request->data['count'];
			}
			
			if(empty($this->request->data['locker_no'])){
				$locker_no=0;
			}
			if(empty($this->request->data['user_name'])){
				$this->request->data['user_name']=0;
			}
			if(empty($this->request->data['mobile_no'])){
				$this->request->data['mobile_no']=0;
			}
			if(empty($this->request->data['email'])){
				$this->request->data['email']=0;
			}
			$num_per = implode(',',$no_of_person);
			$amt = implode(',',$amount);
			$master_id = implode(',',$master_item_id);
			$auto_incre = $this->request->data['auto_incre'];
			$ticket = $this->request->data['ticket_no'];
			$ticket_type = 1;
			$tot_amnt = $this->request->data['main_total'];
			$discount = 0;
			$security_amnt = $this->request->data['security_amount'];
			$grand_tot_amnt = $this->request->data['paid_amount'];
			$pay_mtd = $this->request->data['counter'];
			$person_name = $this->request->data['user_name'];
			$person_mobile = $this->request->data['mobile_no'];
			$person_email = $this->request->data['email'];
			$category_auto_id = 1;
			$counter_id = $this->request->data['counter_id'];
			$date = date('Y-m-d');
			$time = date('h:i:s a', time());
			// print_r($person_email);
			$this->request->data['ticket_no']=$ticket;
			$this->request->data['ticket_type']=$ticket_type;
			$this->request->data['discount']=@$discount;
			$this->request->data['auto_increment']=@$auto_incre;
			// $this->request->data['reference_id']=@$this->request->data['reference_id'];
			$this->request->data['payment_method']=@$pay_mtd;
			$this->request->data['date']=$date;
			$this->request->data['time']=$time;
			$this->request->data['no_of_person']=@$num_per;
			$this->request->data['amount']=@$amt;
			$this->request->data['paid_amnt']=@$grand_tot_amnt;
			$this->request->data['master_item_id']=@$master_id;
			$this->request->data['auto_increment']=$fetch_last_auto;
			$this->request->data['category_auto_id']=$category_auto_id;
			$this->request->data['counter_id']=$counter_id;
			$this->request->data['name_person']=$person_name;
			$this->request->data['mobile']=$person_mobile;
			$this->request->data['locker_no']=$locker_no;
			$this->request->data['tot_amnt']=$tot_amnt;
			$this->request->data['grand_amnt']=$grand_tot_amnt;
			$this->request->data['security_amnt']=$security_amnt;
			// if($num_per>0){
			// 	// echo "hello";
			// 	print_r($num_per);
			// 	die;
			// }
			// else{
			// 	echo "bye";
			// 	die;
			// }
			// die;
			if($num_per>0){
				$this->ticket_entry->save($this->request->data);
				$this->redirect(array('action' => 'marvel_form'));
			}
			else{
				$this->redirect(array('action' => 'marvel_form'));
			}
		}
	}
	
	public function close_view_ticket(){
		$this->layout='';
		
	}
	public function utility_generate() {
			$this->layout='index_layout';
			$this->loadmodel('master_item');
			$this->loadmodel('utility_entry');			
			$this->set('counter_id',$this->Session->read('counter_id'));	
			$mycounter=$this->Session->read('counter_id');
			$fetchlastticket_id=$this->utility_entry->find('all', array('conditions'=>array('flag' => 0),'order'=>'ticket_no DESC','limit'=>1));
			$this->loadmodel('authorised_user');
			$this->set('auth_user', $this->authorised_user->find('all',array('conditions'=>array('delete_status'=>0))));
			$mycounter=$this->Session->read('counter_id');
			//pr($fetchlastticket_id);
			if(sizeof($fetchlastticket_id)>0)
			{
			$id=$fetchlastticket_id[0]['utility_entry']['ticket_no'];
			$fetch_last=$id+1;	
			}
			else
			{
			$fetch_last=1;
			}
			$this->set('tic_id',$fetch_last);
			
			$item_status=$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'2')));
			foreach($all_data as $key => $match)
			{
				$exp_data=@explode(',',$match['master_item']['counter_id']);
				if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
				{
					unset($item_status[$key]);
				}
				if(!in_array($mycounter,$exp_data))
				{
					unset($all_data[$key]);
				}
			}
			$this->set('master_item_fetch',  $all_data);
			$this->set('master_item_return', $item_status);
			
			
	}
	
	public function submit_utility_ticket(){
		//echo "hello";die;
		
		$this->loadmodel('master_item');
		$this->loadmodel('utility_entry');
		if($this->request->is('post'))
			{
				if(isset($this->request->data['ticket_submit']))
				{
				// $locker_num = $this->request->data['locker_no'];
				// print_r($locker_num);die;
				$count=$this->request->data['count'];
				for($i=1;$i<=$count;$i++)
				{
					if(!empty($this->request->data['no_of_person'.$i]))
					{
						$no_of_person[]=$this->request->data['no_of_person'.$i];
						$amount[]=$this->request->data['amount'.$i];
						$chk_sts=$this->request->data['master_item_id'.$i];
						$master_item_id[]=$this->request->data['master_item_id'.$i];
					}
					unset($this->request->data['no_of_person'.$i]);
					unset($this->request->data['amount'.$i]);
					unset($this->request->data['master_item_id'.$i]);
					unset($this->request->data['master_item_id'.$i]);
				}
				if(!empty($chk_sts)){
					
				$master_item=$this->master_item->find('all',array('conditions'=> array('id' => $chk_sts)));
				$auto_id_mst=$master_item[0]['master_item']['auto_id'];
					
				$utility_entry=$this->utility_entry->find('all', array('conditions' => array('category_auto_id' => $auto_id_mst),'order'=>'auto_increment DESC','limit'=>1));
				
				}
				if(empty($auto_id_mst))
				{$auto_id_mst=0;
				}
				if(!empty($utility_entry))
				{
					$auto_increment=$utility_entry[0]['utility_entry']['auto_increment'];
					$auto_iddd=$auto_increment+1;
				}
				else
				{
					$auto_iddd=1;	
				}
				if(!empty($this->request->data['locker_no'])){
					$locker_no = $this->request->data['locker_no'];
					// print_r($locker_no);die;
				}
				if(empty($this->request->data['locker_no'])){
					$locker_no=0;
				}
				if(empty($this->request->data['name_person'])){
					$this->request->data['name_person']=0;
				}
				if(empty($this->request->data['mobile'])){
					$this->request->data['mobile']=0;
				}				
				if(empty($this->request->data['reference_id'])){
					$this->request->data['reference_id']=0;
				}
				$fetchlastticket_id=$this->utility_entry->find('all', array('conditions'=>array('flag' => 0),'order'=>'ticket_no DESC','limit'=>1));
				
				if(sizeof($fetchlastticket_id)>0)
				{
				$id=$fetchlastticket_id[0]['utility_entry']['ticket_no'];
				$fetch_last=$id+1;	
				}
				else
				{
				$fetch_last=1;
				}
				
				$this->request->data['ticket_no']=$fetch_last;
				
				$this->request->data['discount']=@$this->request->data['discount_detail'];
				$discount_authorise=$this->request->data['discount_authorise'];
				if(!empty($discount_authorise)){
					$this->request->data['discount_authorise']=@$this->request->data['discount_authorise'];
				}else{
					$this->request->data['discount_authorise']=0;
				}
				
				$this->request->data['reference_id']=@$this->request->data['reference_id'];                                                   
				$this->request->data['date']=date("Y-m-d");
				$this->request->data['time']=date('h:i:s a', time());
				$no_of_person=@implode(',',$no_of_person);
				$this->request->data['no_of_person']=$no_of_person;
				$this->request->data['amount']=@implode(',',$amount);
				$this->request->data['master_item_id']=@implode(',',$master_item_id);
				$this->request->data['auto_increment']=$auto_iddd;
				$this->request->data['payment_method']=@$this->request->data['payment_method'];
				$this->request->data['category_auto_id']=$auto_id_mst;
				$this->request->data['locker_no']=$locker_no;				
				$this->request->data['login_id']=$this->Session->read('auto_login_id');
				$this->request->data['counter_id']=$this->Session->read('counter_id');
				
				unset($this->request->data['discount_amount']);
				unset($this->request->data['ticket_submit']);
				unset($this->request->data['count']);
				unset($this->request->data['discount_detail']);
				
				if($no_of_person){
				$rs=$this->utility_entry->save($this->request->data);
				$lastEntry = $this->utility_entry->getLastInsertId();
				///////////////////// SMS API STARTS FROM HERE //////////////////////////////////////////
				// $this->layout='ajax_layout';
				// $ticket_mobile=$this->utility_entry->find('list',array('conditions' => array(['date' => date("Y-m-d"),'id'=>$lastEntry]),'fields'=>array('reference_id')));
				// // print_r($ticket_mobile);
				// if($ticket_mobile){
				// 	echo "hello";die;
				// 	$mobile_data = $this->authorised_user->find('all',array('conditions' => array(['id'=>$ticket_mobile])));
				// 	//print_r($mobile_data[0]['authorised_user']);die;
				// 	 $ref_name = $mobile_data[0]['authorised_user']['name'];
				// 	 $mobile_no = $mobile_data[0]['authorised_user']['mobile'];
				// 	// // print_r($mobile_data);exit;
				// 	// print_r($mobile_no);die;
				
				// 	$working_key='A7a76ea72525fc05bbe9963267b48dd96';
				// 	$sms_sender='MARVEL';
				// 	$sms=str_replace(' ', '+', 'We have provided your guests with Costumes and other necessities. Rate us on trip advisor and share your views. Visit us again.');
				// 	if(!empty($mobile_no))
				// 	{
				// 		//$mobile_no='9680747166';// Dsu Menaria
				// 		//file_get_contents('http://alerts.sinfini.com/api/web2sms.php?workingkey='.$working_key.'&sender='.$sms_sender.'&to='.$mobile_no.'&message='.$sms.'');
				// 		file_get_contents("http://103.39.134.40/api/mt/SendSMS?user=phppoetsit&password=9829041695&senderid=".$sms_sender."&channel=Trans&DCS=0&flashsms=0&number=".$mobile_no."&text=".$sms."&route=7");
				// 		//exit;
				// 	}
				// }
				$pr=2;
				}
				
				else
				{
					$this->set('activity',2);
					$pr=1;
				}
				
				
				if($pr==2){	
				foreach($master_item_id as $key => $data){
				$conditions=array('id' => $data,'status' => '1');
				$check_data=$this->master_item->find('count',array('conditions'=>$conditions,'fields'=>array('id')));
				if(($check_data)>0)
				{
					//$new_p[]=@$no_of_person[$key];	
					$check_data=$this->master_item->find('all',array('conditions'=>$conditions,'fields'=>array('id')));	
					$master_idd[]=@$check_data[0]['master_item']['id'];
					
					/*$total_quantity=$this->requestAction(array('controller' => 'Handler', 'action' => 'find_all_quantity_avilable_stock',$check_data[0]['master_item']['id']),array());				
					$totalstock=0;
					foreach($total_quantity as $tot )
					{
						$totalstock+=$tot['item_inward']['quantitiy'];
					}
					$available_quantity[]=$totalstock;
					*/
				}
				}
				
				if(is_array(@$master_idd))
				{
					
				$this->request->data['no_of_item']=$no_of_person;
				$this->request->data['master_item_id']=@implode(',',$master_idd);
				$this->request->data['item_status']=1;
				$this->request->data['counter_id']=$this->Session->read('counter_id');
				$this->loadmodel('item_manage');
				/*
				$available_quantity;
				$x=0;
				foreach($available_quantity as $subtract )
				{	
					$min=$new_p[$x];
					$actual_qty[]=$subtract-$min;
					$x++;
				}
				$this->request->data['total_stock']=@implode(',',$actual_qty);
				*/
				$this->item_manage->save($this->request->data);
				}
				}
					if($pr=='2'){
					$this->utility_entry->getLastInsertId();
					
					$this->response->header('location: view_utility_ticket?id='.$this->utility_entry->getLastInsertId().'');
					}
				
				}
				
				
				if(isset($this->request->data['item_checked_in']))
					{
						$this->loadmodel('item_manage');		
						$count=$this->request->data['count'];
						for($i=1;$i<=$count;$i++)
						{
							if(!empty($this->request->data['no_of_item'.$i]))
							{
								$no_of_item[]=$this->request->data['no_of_item'.$i];
								$master_item_id[]=$this->request->data['master_item_id'.$i];
							}
						}
								if(!empty($no_of_item))
								{
								 $this->request->data['no_of_item']=@implode(',',$no_of_item);	
								}
								else
								{
								 $this->request->data['no_of_item']='0';		
								}
								if(!empty($master_item_id))
								{
								 $this->request->data['master_item_id']=@implode(',',$master_item_id);	
								}
								else
								{
								 $this->request->data['master_item_id']='0';		
								}
								$this->request->data['date']=date("Y-m-d");
								$this->request->data['time']=date('h:i:s a', time());
								$this->request->data['login_id']=$this->Session->read('auto_login_id');
								$this->request->data['counter_id']=$this->Session->read('counter_id');
								$this->request->data['item_status']=0;
								$this->item_manage->save($this->request->data);
					}	
			}
	}
	
	public function item_manage() {
		$this->layout='index_layout';
		$this->loadmodel('master_item');
		$this->loadmodel('ticket_entry');
		
		$this->set('counter_id',$this->Session->read('counter_id'));
		$mycounter=$this->Session->read('counter_id');
		
		$item_status=$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>2)));
			foreach($all_data as $key => $match)
			{
				$exp_data=@explode(',',$match['master_item']['counter_id']);
				if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
				{
					unset($item_status[$key]);
				}
				if(!in_array($mycounter,$exp_data))
				{
					unset($all_data[$key]);
				}
			}
			
		$this->set('master_item_fetch', $item_status);
		if($this->request->is('post'))
			{
				if(isset($this->request->data['item_checked_in']))
					{
						$this->loadmodel('item_manage');		
						$count=$this->request->data['count'];
						for($i=1;$i<=$count;$i++)
						{
							if(!empty($this->request->data['no_of_item'.$i]))
							{
								$no_of_item[]=$this->request->data['no_of_item'.$i];
								$master_item_id[]=$this->request->data['master_item_id'.$i];
							}
						}
								if(!empty($no_of_item))
								{
								 $this->request->data['no_of_item']=@implode(',',$no_of_item);	
								}
								else
								{
								 $this->request->data['no_of_item']='0';		
								}
								if(!empty($master_item_id))
								{
								 $this->request->data['master_item_id']=@implode(',',$master_item_id);	
								}
								else
								{
								 $this->request->data['master_item_id']='0';		
								}
								
								$this->request->data['date']=date("Y-m-d");
								$this->request->data['time']=date('h:i:s a', time());
								$this->request->data['login_id']=$this->Session->read('auto_login_id');
								$this->request->data['item_status']=0;
								$this->request->data['counter_id']=$this->Session->read('counter_id');
								$this->item_manage->save($this->request->data);
					}	
			}
	}

/////////////////////////
public function Issue_item()
	{
		$this->layout='index_layout';
		$this->loadmodel('master_item');
		$this->loadmodel('issue_return');
		$this->loadmodel('master_category');
		
		$this->set('master_caregory_ftc', $this->master_category->find('all',array('conditions' => array('flag'=>'0'))));
		$this->set('master_item_fetch', $this->master_item->find('all',array('conditions' => array('status'=>'1'))));
		if($this->request->is('post'))
			{
				if(isset($this->request->data['issue_add']))
					{
						$inwrad_date=$this->datefordb($this->request->data['date']);
						$this->request->data['date']=$inwrad_date;
						$this->request->data['login_id']=$this->Session->read('auto_login_id');
						$this->request->data['type']=0;
						$rs=$this->issue_return->save($this->request->data);
						if($rs)
						{
							$this->set('activity',1); $this->set('class','toast_success_notify'); $this->set('state','Success !'); $this->set('message','Item issue successfully.');
						}
						else{
							$this->set('activity',2);
						}
						
					}
			}
			if(isset($this->request->data['issue_search']))
				{
						$op=$this->request->query('mode');
						$from_date=$this->set('date_from',$this->request->data['from']);
						$to_date=$this->set('date_to',$this->request->data['to']);
						$from=$this->datefordb($this->request->data['from']);
						$to=$this->datefordb($this->request->data['to']);
						$conditions="";
						if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
						{
						$conditions[]=array('date between ? and ?' => array($from, $to), 'type' => 0);
						}
						else{
							$conditions[]=array('type' => 0);
							}
						$qry = $this->issue_return->find('all',array('conditions' => $conditions));
						$this->set('update_issue_return',$qry);
						$this->set('op_info',$op);
				}
				
				if(isset($this->request->data['edit_issue']))
				{
						$inward_date=$this->datefordb($this->request->data['date']);
						$this->request->data['date']=$inward_date;
						$this->issue_return->id=$this->request->data['my_id'];
						$rs=$this->issue_return->save($this->request->data);
						if($rs)
						{
							$this->set('activity',1); $this->set('class','tost_edit_notify'); $this->set('state','Success !'); $this->set('message','Item issue update successfully.');
						}
						else
						{
							$this->set('activity',2);
						}
				}
				if(isset($this->request->data['delete_item']))
				{
						$rs=$this->issue_return->updateAll(array('id'=>$this->request->data['my_id']));
						//$this->issue_return->updateAll(array('flag'=>1), array('id'=>$this->request->data['my_id']));
						if($rs)
						{
							$this->set('activity',1); $this->set('class','tost_delete_notify'); $this->set('state','Success !'); $this->set('message','Item issue deleted successfully.');
						}
						else
						{
							$this->set('activity',2);
						}

				}
	}
//////////////
	public function return_item()
	{
		$this->layout='index_layout';
		$this->loadmodel('master_item');
		$this->loadmodel('issue_return');
		$this->loadmodel('master_category');
		
		$this->set('master_caregory_ftc', $this->master_category->find('all',array('conditions' => array('flag'=>'0'))));
		$this->set('master_item_fetch', $this->master_item->find('all',array('conditions' => array('status'=>'1'))));
		if($this->request->is('post'))
			{
				if(isset($this->request->data['return_add']))
					{
						$inwrad_date=$this->datefordb($this->request->data['date']);
						$this->request->data['date']=$inwrad_date;
						$this->request->data['login_id']=$this->Session->read('auto_login_id');
						$this->request->data['type']=1;
						$rs=$this->issue_return->save($this->request->data);
						if($rs)
						{
							$this->set('activity',1); $this->set('class','toast_success_notify'); $this->set('state','Success !'); $this->set('message','Item return successfully.');
						}
						else{
							$this->set('activity',2);
						}
					}
				if(isset($this->request->data['return_search']))
				{
						$op=$this->request->query('mode');
						$from_date=$this->set('date_from',$this->request->data['from']);
						$to_date=$this->set('date_to',$this->request->data['to']);
						$from=$this->datefordb($this->request->data['from']);
						$to=$this->datefordb($this->request->data['to']);
						$conditions="";
						if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
						{
						$conditions[]=array('date between ? and ?' => array($from, $to), 'type' => 1);
						}
						else{
							$conditions[]=array('type' => 1);
							}
						$qry = $this->issue_return->find('all',array('conditions' => $conditions));
						$this->set('update_issue_return',$qry);
						$this->set('op_info',$op);
				}
				if(isset($this->request->data['edit_return']))
				{
						$inward_date=$this->datefordb($this->request->data['date']);
						$this->request->data['date']=$inward_date;
						$this->issue_return->id=$this->request->data['my_id'];
						$rs=$this->issue_return->save($this->request->data);
						if($rs)
						{
							$this->set('activity',1); $this->set('class','tost_edit_notify'); $this->set('state','Success !'); $this->set('message','Item return update successfully.');
						}
						else
						{
							$this->set('activity',2);
						}
				}
				if(isset($this->request->data['delete_item']))
				{
						$rs=$this->issue_return->delete(array('id'=>$this->request->data['my_id']));
						if($rs)
						{
							$this->set('activity',1); $this->set('class','tost_delete_notify'); $this->set('state','Success !'); $this->set('message','Item return deleted successfully.');
						}
						else
						{
							$this->set('activity',2);
						}
				}
			}
		
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function inward_menu() {
	$this->layout='index_layout';
	$this->loadmodel('master_item');
	$this->loadmodel('item_inward');
	$this->set('master_item_fetch', $this->master_item->find('all',array('conditions' => array('status'=>'1'))));
		if($this->request->is('post'))
			{
				if(isset($this->request->data['inward_add']))
					{
						$inwrad_date=$this->datefordb($this->request->data['date']);
						$this->request->data['date']=$inwrad_date;
						$this->request->data['login_id']=$this->Session->read('auto_login_id');
						$rs=$this->item_inward->save($this->request->data);
						if($rs)
						{
						$this->set('activity',1); $this->set('class','toast_success_notify'); $this->set('state','Success !'); $this->set('message','Inward successfully submited.');
						}
						else{
							$this->set('activity',2);
						}
					}
				if(isset($this->request->data['inward_search']))
				{
						$op=$this->request->query('mode');
						$from_date=$this->set('date_from',$this->request->data['from']);
						$to_date=$this->set('date_to',$this->request->data['to']);
						$from=$this->datefordb($this->request->data['from']);
						$to=$this->datefordb($this->request->data['to']);
						$conditions="";
						if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
						{
						$conditions[]=array('date between ? and ?' => array($from, $to), 'type' => 0);
						}
						else{
							$conditions[]=array('type' => 0);
							}
						$qry = $this->item_inward->find('all',array('conditions' => $conditions));
						$this->set('update_inward_data',$qry);
						$this->set('op_info',$op);
				}
				if(isset($this->request->data['edit_inwards']))
				{
						$inward_date=$this->datefordb($this->request->data['date']);
						$this->request->data['date']=$inward_date;
						$this->item_inward->id=$this->request->data['my_id'];
						$rs=$this->item_inward->save($this->request->data);
						if($rs)
						{
							$this->set('activity',1); $this->set('class','tost_edit_notify'); $this->set('state','Success !'); $this->set('message','Inward update successfully.');
						}
						else
						{
							$this->set('activity',2);
						}
				}
				if(isset($this->request->data['delete_item']))
				{
						$rs=$this->item_inward->delete(array('id'=>$this->request->data['my_id']));
						if($rs)
						{
							$this->set('activity',1); $this->set('class','tost_delete_notify'); $this->set('state','Success !'); $this->set('message','Reading deleted successfully.');
						}
						else
						{
							$this->set('activity',2);
						}
				}
			}
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function outward() 
	{
	$this->layout='index_layout';
	$this->loadmodel('master_item');
	$this->loadmodel('item_inward');
	$this->set('master_item_fetch', $this->master_item->find('all',array('conditions' => array('status'=>'1'))));
		if($this->request->is('post'))
			{
				if(isset($this->request->data['outward_add']))
					{
						$inwrad_date=$this->datefordb($this->request->data['date']);
						$this->request->data['date']=$inwrad_date;
						$this->request->data['login_id']=$this->Session->read('auto_login_id');
						$this->request->data['type']=1;
						$rs=$this->item_inward->save($this->request->data);
						if($rs)
						{
							$this->set('activity',1); $this->set('class','toast_success_notify'); $this->set('state','Success !'); $this->set('message','Outward successfully submited.');
						}
						else{
							$this->set('activity',2);
						}
					}
				if(isset($this->request->data['outward_search']))
				{
						$op=$this->request->query('mode');
						$from_date=$this->set('date_from',$this->request->data['from']);
						$to_date=$this->set('date_to',$this->request->data['to']);
						$from=$this->datefordb($this->request->data['from']);
						$to=$this->datefordb($this->request->data['to']);
						$conditions="";
						if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
						{
						$conditions[]=array('date between ? and ?' => array($from, $to), 'type' => 1);
						}
						else{
							$conditions[]=array('type' => 1);
							}
						$qry = $this->item_inward->find('all',array('conditions' => $conditions ));
						$this->set('update_outward_data',$qry);
						$this->set('op_info',$op);
				}
				if(isset($this->request->data['edit_inwards']))
				{
						$inward_date=$this->datefordb($this->request->data['date']);
						$this->request->data['date']=$inward_date;
						$this->item_inward->id=$this->request->data['my_id'];
						$rs=$this->item_inward->save($this->request->data);
						if($rs)
						{
							$this->set('activity',1); $this->set('class','tost_edit_notify'); $this->set('state','Success !'); $this->set('message','Outward update successfully.');
						}
						else
						{
							$this->set('activity',2);
						}
				}
				if(isset($this->request->data['delete_item']))
				{
						$rs=$this->item_inward->delete(array('id'=>$this->request->data['my_id']));
						if($rs)
						{
						$this->set('activity',1); $this->set('class','tost_delete_notify'); $this->set('state','Success !'); $this->set('message','Outward deleted successfully.');
						}
						else
						{
						$this->set('activity',2);
						}
				}
			}
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////////

public function ph_reading() 
	{
	$this->layout='index_layout';
	$this->loadmodel('ph_reading');
	$this->set('master_ph_reading', $this->ph_reading->find('all'));
		if($this->request->is('post'))
			{
				if(isset($this->request->data['reading_add']))
					{
						$date=$this->datefordb($this->request->data['date']);
						$this->request->data['date']=$date;
						$this->request->data['login_id']=$this->Session->read('auto_login_id');
						$this->request->data['time']=date('h:i:s a', time());
						$rs=$this->ph_reading->save($this->request->data);
						if($rs)
						{
							$this->set('activity',1); $this->set('class','toast_success_notify'); $this->set('state','Success !'); $this->set('message','Reading successfully submited.');
						}
						else{
							$this->set('activity',2);
						}
						
					}
					
				if(isset($this->request->data['reading_search']))
				{
						$op=$this->request->query('mode');
						$from_date=$this->set('date_from',$this->request->data['from']);
						$to_date=$this->set('date_to',$this->request->data['to']);
						$from=$this->datefordb($this->request->data['from']);
						$to=$this->datefordb($this->request->data['to']);
						$conditions="";
						if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
						{
						$conditions[]=array('date between ? and ?' => array($from, $to));
						}
						
						$qry = $this->ph_reading->find('all',array('conditions' => $conditions ));
						$this->set('update_ph_reading',$qry);
						$this->set('op_info',$op);
				}
				
				if(isset($this->request->data['edit_reading']))
				{
						$date=$this->datefordb($this->request->data['date']);
						$this->request->data['date']=$date;
						$this->request->data['time']=date('h:i:s a', time());
						$this->ph_reading->id=$this->request->data['my_id'];
						$rs=$this->ph_reading->save($this->request->data);
						if($rs)
						{
							$this->set('activity',1); $this->set('class','tost_edit_notify'); $this->set('state','Success !'); $this->set('message','Reading update successfully.');
						}
						else
						{
							$this->set('activity',2);
						}
				}
				if(isset($this->request->data['delete_item']))
				{
						$rs=$this->ph_reading->delete(array('id'=>$this->request->data['my_id']));
						if($rs)
						{
							$this->set('activity',1); $this->set('class','tost_delete_notify'); $this->set('state','Success !'); $this->set('message','Reading deleted successfully.');
						}
						else
						{
							$this->set('activity',2);
						}
				}
			}
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function view_ticket() 
	{
		$print=$this->request->query('print');
		$this->set('print',$print);
		$this->layout='ajax_layout';
		$this->loadmodel('ticket_entry');		
		$this->set('last_data', $this->ticket_entry->find('all', array('conditions' => array('flag'=>0 , 'id' => $this->request->query('id')))));
	}
	public function view_utility_ticket() 
	{
		$print=$this->request->query('print');
		$this->set('print',$print);
		$this->layout='ajax_layout';
		$this->loadmodel('utility_entry');		
		$this->set('last_data', $this->utility_entry->find('all', array('conditions' => array('flag'=>0 , 'id' => $this->request->query('id')))));
	}
	public function view_ticket_ticket_edit() 
	{
		$print=$this->request->query('print');
		$this->set('print',$print);
		$view=$this->request->query('view'); 
		$this->set('redirect',$view);
		$this->layout='ajax_layout';
		$this->loadmodel('ticket_entry');		
		$this->set('last_data', $this->ticket_entry->find('all', array('conditions' => array('flag'=>0 , 'id' => $this->request->query('id')))));
	}
	public function view_utility_ticket_edit() 
	{
		$print=$this->request->query('print');
		$this->set('print',$print);
		$view=$this->request->query('view'); 
		$this->set('redirect',$view);
		$this->layout='ajax_layout';
		$this->loadmodel('utility_entry');
	
		$this->set('last_data', $this->utility_entry->find('all', array('conditions' => array('flag'=>0 , 'id' => $this->request->query('id')))));
	}
	
 function backup() {
	 $this->layout='index_layout';
	// $schema = $this->Model->schema();
	 //pr($schema);
	
	}
	public function view_discount()
	{
		$this->layout='ajax_layout';
		$auto_id=$this->request->query('auto_id');
		if($auto_id==1)
		{
			$this->loadmodel('ticket_entry');
			$this->set('last_data', $this->ticket_entry->find('all', array('conditions' => array('flag'=>0 , 'id' => $this->request->query('id')))));
		}
		else if($auto_id==2)
		{
			$this->loadmodel('utility_entry');
			$this->set('last_data', $this->utility_entry->find('all', array('conditions' => array('flag'=>0 , 'id' => $this->request->query('id')))));
		}
		
		$this->set('auto_id', $auto_id);
	}
	
	public function view_discount_group()
	{
		$this->layout='ajax_layout';
		$this->loadmodel('group_booking');		
		$this->set('last_data', $this->group_booking->find('all', array('conditions' => array('id' => $this->request->query('id')))));	
	}
	public function view_texi_commission()
	{
		$print=$this->request->query('print');
		$this->set('print',$print);
		$this->layout='ajax_layout';
		$this->loadmodel('taxi_commission');		
		$this->set('last_data', $this->taxi_commission->find('all', array('conditions' => array('id' => $this->request->query('id')))));
	
	}
	public function view_discount_ticket_edit()
	{
		$this->layout='ajax_layout';
		$auto_id=$this->request->query('auto_id');
		if($auto_id==1)
		{
			$this->loadmodel('ticket_entry');
			$this->set('last_data', $this->ticket_entry->find('all', array('conditions' => array('flag'=>0 , 'id' => $this->request->query('id')))));
		}
		else if($auto_id==2)
		{
			$this->loadmodel('utility_entry');
			$this->set('last_data', $this->utility_entry->find('all', array('conditions' => array('flag'=>0 , 'id' => $this->request->query('id')))));
		}
		
		$this->set('auto_id', $auto_id);
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////
public function taxi_commission()
{
		$this->layout='index_layout';
		$this->loadmodel('taxi_commission');
		
		if($this->request->is('post'))
		{
			if(isset($this->request->data['taxi_insert']))
				{
					$this->request->data['login_id']=$this->Session->read('auto_login_id');
					$this->request->data['counter_id']=$this->Session->read('counter_id');
					$this->request->data['date']=date("Y-m-d");
					$this->request->data['time']=date('h:i:s a', time());
					
					$this->taxi_commission->save($this->request->data);
					$id=$this->taxi_commission->getLastInsertId();
					$this->response->header('location: view_texi_commission?id='.$id.'');
				}
				
		}
}
public function group_booking() {
		$this->layout='index_layout';
		$this->loadmodel('master_item');
		$this->set('adult_rate', $this->master_item->find('all', array('conditions' => array('id' => 12),'fields'=>array('rate'))));
		$this->set('children_rate', $this->master_item->find('all', array('conditions' => array('id' => 13),'fields'=>array('rate'))));
		
    	$this->loadmodel('group_booking');
		$check_data=$this->group_booking->find('all',array('fields'=>array('id','bill_no'),'order'=>'id DESC LIMIT 1'));
		$num_rows = $this->group_booking->find('count');
		if($num_rows==0)
		{
		$max_bill_no=$check_data[0]['group_booking']['bill_no'];
		$max_bill_no++;
		$bill_no=$this->fetch_bill_no($max_bill_no);	
		}
		else
		{
		$max_bill_no=$check_data[0]['group_booking']['bill_no'];
		$max_bill_no++;
		$bill_no=$max_bill_no;
		}
		$this->set('bill_no',$bill_no);
		
		if($this->request->is('post'))
		{
			if(isset($this->request->data['group_booking_reg']))
				{
					$this->request->data['bill_no']=$bill_no;
					$this->request->data['current_date']=date("Y-m-d");
					$this->request->data['login_id']=$this->Session->read('auto_login_id');
					$this->request->data['counter_id']=$this->Session->read('counter_id');
					$this->request->data['time']=date('h:i:s a', time());
					if(empty($this->request->data['adult']))
						$this->request->data['adult']='0';
					if(empty($this->request->data['children']))
						$this->request->data['children']='0';
					$this->group_booking->save($this->request->data);
					$this->group_booking->getLastInsertId();
     				$this->response->header('location: view_group?id='.$this->group_booking->getLastInsertId().'');
				}
				if(isset($this->request->data['group_booking_save']))
				{
					$this->request->data['bill_no']=$bill_no;
					$this->request->data['current_date']=date("Y-m-d");
					$this->request->data['login_id']=$this->Session->read('auto_login_id');
					$this->request->data['counter_id']=$this->Session->read('counter_id');
					$this->request->data['time']=date('h:i:s a', time());
					if(empty($this->request->data['adult']))
						$this->request->data['adult']='0';
					if(empty($this->request->data['children']))
						$this->request->data['children']='0';
					$rs=$this->group_booking->save($this->request->data);
					
					if($rs)
					{
					$this->set('activity',1); $this->set('class','toast_success_notify'); $this->set('state','Success !'); $this->set('message','Group successfully submited.');
					}
					else{
						$this->set('activity',2);
					}					
				}
		}
		if(isset($this->request->data['edit_group_booking']))
		{
				$op=$this->request->query('mode');
				$from_date=$this->set('date_from',$this->request->data['from']);
				$to_date=$this->set('date_to',$this->request->data['to']);
				$from=$this->datefordb($this->request->data['from']);
				$to=$this->datefordb($this->request->data['to']);
				$status=$this->request->data['status'];
				
				$conditions="";
				if(!empty($name))
				{
						$conditions=array('name LIKE' => '%'.$name.'%');
				}
				if(!empty($contact_no))
				{
						$conditions=array('contact_no LIKE' => '%'.$contact_no.'%');
				}
				if(!empty($this->request->data['from']) && !empty($this->request->data['to']) && $status!='')
				{
						$conditions=array('status' => $status,'current_date between ? and ?' => array($from, $to));
						
				}
				if(!empty($this->request->data['from']) && !empty($this->request->data['to']) && $status=='')
				{
						$conditions=array('current_date between ? and ?' => array($from, $to));
				}
				
				if(!empty($this->request->data['status']))
				{
						$conditions=array('status' => $status);
				}
				
				$qry = $this->group_booking->find('all',array('conditions' => $conditions));
				$this->set('all_data_group_booking',$qry);
				$this->set('op_info',$op);
		}
		if(isset($this->request->data['delete_group']))
		{
			$this->group_booking->delete(array('id'=>$this->request->data['my_id']));
		}
	}
	public function edit_groupbooking()
	{
		$this->layout='index_layout';
		$this->loadmodel('group_booking');
		$this->loadmodel('master_item');
		$this->set('adult_rate', $this->master_item->find('all', array('conditions' => array('id' => 12),'fields'=>array('rate'))));
		$this->set('children_rate', $this->master_item->find('all', array('conditions' => array('id' => 13),'fields'=>array('rate'))));
		
		$id=$this->request->query('id'); 
		$this->set('update_id',$id);
		$qry = $this->group_booking->find('all',array('conditions' => array('id' => $id)));
		$this->set('all_data_group_booking',$qry);
		if($this->request->is('post'))
		{
				if(isset($this->request->data['group_booking_reg']))
				{
					
					$this->request->data['current_date']=date("Y-m-d");
					$this->request->data['login_id']=$this->Session->read('auto_login_id');
					$this->request->data['counter_id']=$this->Session->read('counter_id');
					$this->request->data['time']=date('h:i:s a', time());
					if(empty($this->request->data['adult']))
						$this->request->data['adult']='0';
					if(empty($this->request->data['children']))
						$this->request->data['children']='0';
						$this->group_booking->id=$this->request->data['update_id'];
					$this->group_booking->save($this->request->data);
					
     				$this->response->header('location: view_group?id='.$this->request->data['update_id'].'');
				}
				if(isset($this->request->data['group_booking_save']))
				{
					
					$this->request->data['current_date']=date("Y-m-d");
					$this->request->data['login_id']=$this->Session->read('auto_login_id');
					$this->request->data['counter_id']=$this->Session->read('counter_id');
					$this->request->data['time']=date('h:i:s a', time());
					if(empty($this->request->data['adult']))
						$this->request->data['adult']='0';
					if(empty($this->request->data['children']))
						$this->request->data['children']='0';
					$this->group_booking->id=$this->request->data['update_id'];
					$this->group_booking->save($this->request->data);
					$this->response->header('location:group_booking?mode=view');
										
				}
		}
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function view_group() {
		$this->layout='ajax_layout';
		$print=$this->request->query('print');
		$this->set('print',$print);
		$this->loadmodel('group_booking');
		$this->loadmodel('master_item');	
		$this->set('group_data', $this->group_booking->find('all', array('conditions' => array('id' => $this->request->query('id')))));
		$this->group_booking->updateAll(array('status'=> 1 ), array('id'=>$this->request->query('id')));
		$this->set('adult_rate', $this->master_item->find('all', array('conditions' => array('id' => 12),'fields'=>array('rate'))));
		$this->set('children_rate', $this->master_item->find('all', array('conditions' => array('id' => 13),'fields'=>array('rate'))));
	}
	
////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function create_login() {
	$this->layout='index_layout';
	$this->loadmodel('counter');
	$this->set('counter_fetch', $this->counter->find('all'));
		if($this->request->is('post'))
		{
				$this->loadmodel('login');
				if(isset($this->request->data['login_reg']))
				{
						$password=md5($this->request->data['password']);
						$this->request->data['password']=$password;
						$result=$this->login->save($this->request->data);
				}
		}
	}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function authorised_user() {
		$this->layout='index_layout';
		$this->loadmodel('authorised_user');
		// $getData = $this->authorised_user->find('all');
		// print_r($getData);die;
		if($this->request->is('post'))
		{
			$name=$this->request->data['name'];
			$mobile=$this->request->data['mobile_num'];
			$getData = $this->authorised_user->find('all',array('conditions' => array('mobile' => $mobile)));
			if(empty($getData)){
			$this->request->data['name']=$name;
			$this->request->data['mobile']=$mobile;
			$this->request->data['delete_status']=0;
			$this->authorised_user->save($this->request->data);
			$this->set('right', 'Authorised User Created Successfully');
		}else{
			$this->set('wrong', 'Mobile Number already exists');
		}
	}
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function add_user(){
		$this->loadmodel('authorised_user');
		$name = $this->request->data['auth_name'];
		$num = $this->request->data['auth_num'];
		$getData = $this->authorised_user->find('all',array('conditions' => array('mobile' => $num)));
		if(empty($getData)){
			$this->request->data['name']=$name;
			$this->request->data['mobile']=$num;
			$this->request->data['delete_status']=0;
			$result=$this->authorised_user->save($this->request->data);
			echo $result['authorised_user']['id'];
			die;
		}
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function profile() {
		$this->layout='index_layout';
		$this->loadmodel('login');
		$this->set('login_id',$this->Session->read('login_id'));
		$this->set('auto_login_id',$this->Session->read('auto_login_id'));
		$this->set('username',$this->Session->read('user_name'));
		$fetch_email_id=$this->login->find('all', array('conditions' => array('id' => $this->Session->read('auto_login_id')),'fields'=>array('email')));
		$email=$fetch_email_id[0]['login']['email'];
		$this->set('email',$email);
		if($this->request->is('post'))
		{
				if(isset($this->request->data['update_password']))
				{
						$new_password=htmlentities($this->request->data['new_password']);
						$confirm_password=htmlentities($this->request->data['confirm_password']);
						if(($new_password==$confirm_password) && (!empty($new_password)))
						{
							$password=md5($new_password);
							$this->loadmodel('login');
							$this->login->updateAll(array('password'=>"'$password'"), array('id'=>$this->Session->read('auto_login_id')));
						}
						else
						{
							$this->set('wrong', 'Enter re-type correct password.');
						}
				}
		}
	}	

////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function user_right() {
		$this->layout='index_layout';
		$this->loadmodel('login');
		$this->set('fetch_login', $this->login->find('all'));		
		if($this->request->is('post'))
		{
			if(isset($this->request->data['right_submit']))
			{
				$this->loadmodel('user_right');
				if(empty($this->request->data['module_id']))
				{$this->request->data['module_id']=0;}
				$this->request->data['module_id']=implode(',', $this->request->data['module_id']);
				
				$conditions=array("user_id" => $this->request->data['user_id']);
				 $fetch_user_right = $this->user_right->find('all',array('conditions'=>$conditions));
				
				$this->user_right->id=$fetch_user_right['0']['user_right']['id'];
				$this->user_right->save($this->request->data);
			}
			
		}

	}

///////////////////////////////////////////////
public function ticket_edit()
{
	$this->layout='index_layout';
	$this->loadmodel('ticket_entry');
	$this->loadmodel('master_item');
	$this->loadmodel('counter');
	$this->set('counter_fetch', $this->counter->find('all'));
	$type=$this->Session->read('type');
	$this->set('role', $type);
		if($this->request->is('post'))
		{
				$this->loadmodel('ticket_entry');
				if(isset($this->request->data['report_tic_gen']))
				{			
														
					$qry = $this->ticket_entry->find('all',array('conditions' => array('ticket_type'=>1 , 'flag' => 0, 'ticket_no' => $this->request->data['ticket_no'])));
							$this->set('ticket_edit_data',$qry); 
				}
				////////////// 
				if(isset($this->request->data['ticket_submit']))
				{
				 $count=$this->request->data['count'];
				
					for($i=1;$i<=$count;$i++)
					{
						if(!empty($this->request->data['no_of_person'.$i]))
						{
							$no_of_person[]=$this->request->data['no_of_person'.$i];
							$amount[]=$this->request->data['amount'.$i];
							$master_item_id[]=$this->request->data['master_item_id'.$i];
						}
					}
				 
				
					if(empty($this->request->data['locker_no'])){
						$this->request->data['locker_no']=0;
					}
				$this->request->data['discount']=@$this->request->data['discount_detail'];
				$this->request->data['discount_authorise']=@$this->request->data['discount_authorise'];
				
				$this->request->data['no_of_person']=@implode(',',$no_of_person);
				$this->request->data['amount']=@implode(',',$amount);
				$this->request->data['master_item_id']=@implode(',',$master_item_id);
				
				//$this->request->data['login_id']=$this->Session->read('auto_login_id');
				//$this->request->data['counter_id']=$this->Session->read('counter_id');
				
				$this->ticket_entry->id=$this->request->data['update_id'];
				 
				$this->ticket_entry->save($this->request->data);
				
				
				
				$this->response->header('location: view_ticket_ticket_edit?id='.$this->request->data['update_id'].'');
				
				}
				
				
				
		}
		//$fetch_data = $this->ticket_entry->find('all',array('fields'=>array('counter_id'), 'conditions' => array('id' => $this->request->data['update_id'])));
		//$mycounter=$fetch_data[0]['ticket_entry']['counter_id'];
$mycounter=$this->Session->read('counter_id');
		$item_status=$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'1')));
			foreach($all_data as $key => $match)
			{
				$exp_data=@explode(',',$match['master_item']['counter_id']);
				if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
				{
					unset($item_status[$key]);
				}
				if(!in_array($mycounter,$exp_data))
				{
					unset($all_data[$key]);
				}
			}
			$this->set('master_item_fetch',  $all_data);
			$this->set('master_item_return', $item_status);
}
public function discount_edit()
{
	$this->layout='index_layout';
	$this->loadmodel('ticket_entry');
	$this->loadmodel('master_item');
	$this->loadmodel('counter');
	$this->set('counter_fetch', $this->counter->find('all'));
	$type=$this->Session->read('type');
	$this->set('role', $type);
		if($this->request->is('post'))
		{
				$this->loadmodel('ticket_entry');
				if(isset($this->request->data['report_tic_gen']))
				{			
														
							$qry = $this->ticket_entry->find('all',array('conditions' => array('ticket_type'=>2 , 'flag' => 0, 'ticket_no' => $this->request->data['ticket_no'])));
							$this->set('ticket_edit_data',$qry); 
				}
				////////////// 
				if(isset($this->request->data['ticket_submit']))
				{
				 $count=$this->request->data['count'];
				
					for($i=1;$i<=$count;$i++)
					{
						if(!empty($this->request->data['no_of_person'.$i]))
						{
							$no_of_person[]=$this->request->data['no_of_person'.$i];
							$amount[]=$this->request->data['amount'.$i];
							$master_item_id[]=$this->request->data['master_item_id'.$i];
						}
					}
				 
				
					if(empty($this->request->data['locker_no'])){
						$this->request->data['locker_no']=0;
					}
				$this->request->data['discount']=@$this->request->data['discount_detail'];
				$this->request->data['discount_authorise']=@$this->request->data['discount_authorise'];
				
				$this->request->data['no_of_person']=@implode(',',$no_of_person);
				$this->request->data['amount']=@implode(',',$amount);
				$this->request->data['master_item_id']=@implode(',',$master_item_id);
				
				//$this->request->data['login_id']=$this->Session->read('auto_login_id');
				//$this->request->data['counter_id']=$this->Session->read('counter_id');
				
				$this->ticket_entry->id=$this->request->data['update_id'];
				 
				$this->ticket_entry->save($this->request->data);
				
				
				
				$this->response->header('location: view_ticket_ticket_edit?id='.$this->request->data['update_id'].'');
				
				}
				
				
				
		}
 		$mycounter=$this->Session->read('counter_id');
		$item_status=$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'1','ticket_type'=>'2')));
			foreach($all_data as $key => $match)
			{
				$exp_data=@explode(',',$match['master_item']['counter_id']);
				if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1 )
				{
					unset($item_status[$key]);
				}
				if(!in_array($mycounter,$exp_data))
				{
					unset($all_data[$key]);
				}
			}
			$this->set('master_item_fetch',  $all_data);
			$this->set('master_item_return', $item_status);
}

public function utility_ticket_edit()
{
	$this->layout='index_layout';
	$this->loadmodel('utility_entry');
	$this->loadmodel('master_item');
		$this->loadmodel('counter');
		$this->set('counter_fetch', $this->counter->find('all'));
		if($this->request->is('post'))
		{
				$this->loadmodel('utility_entry');
				if(isset($this->request->data['report_tic_gen']))
				{ 			
														
					$qry = $this->utility_entry->find('all',array('conditions' => array('flag' => 0, 'ticket_no' => $this->request->data['ticket_no'])));
					$this->set('ticket_edit_data',$qry);
				}
				////////////// 
				if(isset($this->request->data['ticket_submit']))
				{
					$count=$this->request->data['count'];
				
					for($i=1;$i<=$count;$i++)
					{
						if(!empty($this->request->data['no_of_person'.$i]))
						{
							$no_of_person[]=$this->request->data['no_of_person'.$i];
							$amount[]=$this->request->data['amount'.$i];
							$master_item_id[]=$this->request->data['master_item_id'.$i];
						}
					}
				 
				
					if(empty($this->request->data['locker_no'])){
						$this->request->data['locker_no']=0;
					}
				$this->request->data['discount']=@$this->request->data['discount_detail'];
				$this->request->data['discount_authorise']=@$this->request->data['discount_authorise'];
				
				$this->request->data['no_of_person']=@implode(',',$no_of_person);
				$this->request->data['amount']=@implode(',',$amount);
				$this->request->data['master_item_id']=@implode(',',$master_item_id);
				
				//$this->request->data['login_id']=$this->Session->read('auto_login_id');
				//$this->request->data['counter_id']=$this->Session->read('counter_id');
				
				 $this->utility_entry->id=$this->request->data['update_id'];
				 
				$this->utility_entry->save($this->request->data);
				
					
				foreach($master_item_id as $key => $data){
				$conditions=array('id' => $data,'status' => '1');
				$check_data=$this->master_item->find('count',array('conditions'=>$conditions,'fields'=>array('id')));
				if(($check_data)>0)
				{
					$new_p[]=@$no_of_person[$key];	
					$check_data=$this->master_item->find('all',array('conditions'=>$conditions,'fields'=>array('id')));	
					$master_idd[]=@$check_data[0]['master_item']['id'];
				}
				}
				if(is_array(@$master_idd))
				{
				$this->request->data['no_of_item']=@implode(',',$new_p);
				$this->request->data['master_item_id']=@implode(',',$master_idd);
				$this->request->data['item_status']=1;
				$this->request->data['counter_id']=$this->Session->read('counter_id');
				$this->loadmodel('item_manage');
				$this->item_manage->save($this->request->data);
				}
		
				//$this->utility_entry->getLastInsertId();
				
				$this->response->header('location: view_utility_ticket_edit?id='.$this->request->data['update_id'].'');
				
				}
				
				
		}
		
		$mycounter=$this->Session->read('counter_id');
		$item_status=$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'2')));
			foreach($all_data as $key => $match)
			{
				$exp_data=@explode(',',$match['master_item']['counter_id']);
				if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
				{
					unset($item_status[$key]);
				}
				if(!in_array($mycounter,$exp_data))
				{
					unset($all_data[$key]);
				}
			}
			$this->set('master_item_fetch',  $all_data);
			$this->set('master_item_return', $item_status);
}
/////////////////////////////
	public function taxi_commission_report()
	{
		$this->layout='index_layout';
		$this->loadmodel('taxi_commission');
		$this->loadmodel('counter');
		$this->set('counter_fetch', $this->counter->find('all'));
		if($this->request->is('post'))
		{
				
				if(isset($this->request->data['report_tic_gen']))
				{ 			
							$from_date=$this->set('date_from',$this->request->data['from']);
							$to_date=$this->set('date_to',$this->request->data['to']);
							$from=$this->datefordb($this->request->data['from']);
							$to=$this->datefordb($this->request->data['to']);
							$counter_id=$this->request->data['counter_id'];
							$conditions="";
							if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
							{
							$conditions[]=array('date between ? and ?' => array($from, $to));
							}
							if(!empty($counter_id))
							{
							$conditions[]=array('counter_id' => ''.$counter_id.'');
							}
							$qry = $this->taxi_commission->find('all',array('conditions' => $conditions));
							$this->set('ticket_data',$qry);
				}		
		}
	}
////////////////////////////////
	public function user_profile()
	{
		$this->layout='index_layout';
		$this->loadmodel('login');
		$this->set('login_fetch', $this->login->find('all'));
		if($this->request->is('post'))
		{
				if(isset($this->request->data['update_password']))
				{
						$new_password=htmlentities($this->request->data['new_password']);
						$confirm_password=htmlentities($this->request->data['confirm_password']);
						if(($new_password==$confirm_password) && (!empty($new_password)))
						{
							$password=md5($new_password);
							$this->login->updateAll(array('password'=>"'$password'"), array('id'=>$this->request->data['user']));
							$this->set('success', 'Password successfully update.');
						}
						else
						{
							$this->set('wrong', 'Enter re-type correct password.');
						}
				}
		}
	}
	public function report_ticket() 
	{
		$this->layout='index_layout';
		$this->loadmodel('counter');
		$this->set('counter_fetch', $this->counter->find('all'));
		if($this->request->is('post'))
		{
				$this->loadmodel('ticket_entry');
				$this->loadmodel('utility_entry');
				$this->loadmodel('item_manage');
				if(isset($this->request->data['report_tic_gen']))
				{ 			
							$from_date=$this->set('date_from',$this->request->data['from']);
							$to_date=$this->set('date_to',$this->request->data['to']);
							$from=$this->datefordb($this->request->data['from']);
							$to=$this->datefordb($this->request->data['to']);
							$counter_id=$this->request->data['counter_id'];
							$auto_id=$this->request->data['auto_id'];
							 $name_person=$this->request->data['name'];
							$conditions="";
							if(!empty($this->request->data['from']) && !empty($this->request->data['to']) && !empty($name_person))
							{
							$conditions[]=array('flag' => 0,'name_person LIKE' => '%'.$name_person.'%','date between ? and ?' => array($from, $to));
							}
							if(empty($this->request->data['from']) && empty($counter_id) && !empty($name_person))
							{
							$conditions[]=array('flag' => 0,'name_person LIKE' => '%'.$name_person.'%');
							}
							if(!empty($this->request->data['from']) && !empty($this->request->data['to']))
							{
							$conditions[]=array('flag' => 0,'date between ? and ?' => array($from, $to));
							}
							if(!empty($counter_id) && !empty($name_person))
							{
							$conditions[]=array('counter_id' => ''.$counter_id.'', 'name_person LIKE' => '%'.$name_person.'%','flag' => 0);
							}
							if(!empty($counter_id))
							{
							$conditions[]=array('counter_id' => ''.$counter_id.'', 'flag' => 0);
							}
							
							if($auto_id==1)
							{
								$qry = $this->ticket_entry->find('all',array('conditions' => $conditions));
							}
							else if($auto_id==2)
							{
								$qry = $this->utility_entry->find('all',array('conditions' => $conditions));
							}
							$this->set('ticket_data',$qry);
							$this->set('auto_id',$auto_id);
							$this->loadmodel('master_item');
							$this->set('ftch_master_items',$this->master_item->find('all',array('conditions'=> array('status'=>1))));
							
				}		
				if(isset($this->request->data['delete_ticket']))
				{
					//$this->ticket_entry->updateAll(array('id'=>$this->request->data['my_id']));
					$this->ticket_entry->updateAll(array('flag'=> 1), array('id'=>$this->request->data('my_id')));
				}
		}	
	}

////////////////////////////////////////////////////////////////
function month_sels_report()
{
		$this->layout='index_layout';
		$this->loadmodel('ticket_entry');
		$this->loadmodel('master_item');
		$this->loadmodel('group_booking');
		if($this->request->is('post'))
		{
			if(isset($this->request->data['generate']))
			{
				 $month=$this->request->data['month'];
				 $first_date='01-'.$month;
				$this->set('MOnth_name',date("M", strtotime($first_date)));
				$this->set('month_yrs',date("M-Y", strtotime($first_date)));
				$first_date=$this->datefordb($first_date);
				$last_date=date("Y-m-t", strtotime($first_date));
				
				$this->set('first_date', $first_date);
				$this->set('last_date', $last_date);
				
				$this->set('fetch_master_items',$this->master_item->find('all',array('conditions'=> array('status'=>1))));
				

			}
		}
		
}
/////////
public function month_report()
	{
		$this->layout='index_layout';
		$this->loadmodel('ticket_entry');
		$this->loadmodel('master_item');
		$this->loadmodel('group_booking');
		$this->set('fetch_group_booking',$this->group_booking->find('all'));
	 
		$this->set('adult_rate', $this->master_item->find('all', array('conditions' => array('id' => 1),'fields'=>array('rate'))));
		$this->set('children_rate', $this->master_item->find('all', array('conditions' => array('id' => 2),'fields'=>array('rate'))));
		
		if($this->request->is('post'))
		{
			if(isset($this->request->data['generate']))
			{
				$month=$this->request->data['month'];
				if(!empty($month)){
				$first_date='01-'.$month;
				 
					$first_date=$this->datefordb($first_date);
					$last_date=date("Y-m-t", strtotime($first_date));
					$from=date('d-m-Y',strtotime($first_date));
					$to=date('d-m-Y',strtotime($last_date));
					$from_date=$this->set('from',$from);
					$to_date=$this->set('to',$to);
					$this->set('ftch_master_items',$this->master_item->find('all',array('conditions'=> array('status'=>1))));

				}
				else
				{
					 $first_date=$this->datefordb($this->request->data['from']);
					 $last_date=$this->datefordb($this->request->data['to']);
					 $from_date=$this->set('from',$this->request->data['from']);
					 $to_date=$this->set('to',$this->request->data['to']);
					$this->set('ftch_master_items',$this->master_item->find('all',array('conditions'=> array('status'=>1))));
				}
				$this->set('first_date', $first_date);
				$this->set('last_date', $last_date);
				
				$this->set('fetch_master_items',$this->master_item->find('all'));
				

			}
		}
	}
	////////
public function ticket_wise_report_utility()
	{
		$this->layout='index_layout';
		$this->loadmodel('utility_entry');
		$this->loadmodel('master_item');
		$type=$this->Session->read('type');
		$this->set('role', $type);
		$mycounter=$this->Session->read('counter_id');
		if($this->request->is('post'))
		{	
			if(isset($this->request->data['report_ticket_gen']))
			{
				
				
				 	 $first_date=$this->datefordb($this->request->data['from']);
				 	 $last_date=$this->datefordb($this->request->data['to']); 
					 $from_date=$this->set('from',$this->request->data['from']);
					 $to_date=$this->set('to',$this->request->data['to']);
					 
					$this->set('fetch_data_tiket_item', $this->utility_entry->find('all',array('conditions'=> array('date between ? and ?' => array($first_date, $last_date)))));
				 	$this->set('fetch_master_item', $this->master_item->find('all',array('conditions'=>array('auto_id'=>'2'))));
					
					$item_status=$all_data=$this->master_item->find('all');
					
						foreach($all_data as $key => $match)
						{
							$exp_data=@explode(',',$match['master_item']['counter_id']);
							if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
							{
								unset($item_status[$key]);
							}
							if(!in_array($mycounter,$exp_data))
							{
								unset($all_data[$key]);
							}
						}
						$this->set('master_item_fetch',  $all_data);
			}
			
		}
			
	}
	///////////////////
	public function ticket_wise_report_utility_return(){
		// $this->layout='index_layout';
		// $this->loadmodel('utility_entry');
		// $this->loadmodel('utility_return');
		// $type=$this->Session->read('type');
		// $this->set('role', $type);
		// $mycounter=$this->Session->read('counter_id');
		// if($this->request->is('post'))
		// {	
		// 	if(isset($this->request->data['report_ticket_gen']))
		// 	{			
		// 		$first_date=$this->datefordb($this->request->data['from']);
		// 		$last_date=$this->datefordb($this->request->data['to']);
		// 		$from_date=$this->set('from',$this->request->data['from']);
		// 		$to_date=$this->set('to',$this->request->data['to']);
		// 		// $datas = $this->utility_entry->query("select utility_entries.ticket_no,utility_returns.date, utility_entries.master_item_id, utility_returns.master_item_id
		// 		//  	from utility_entries INNER JOIN utility_returns ON utility_entries.ticket_no = utility_returns.ticket_no
		// 		//  	WHERE utility_entries.date >= '$first_date' and utility_entries.date <= '$last_date'");
		// 		$data_entries = $this->utility_entry->find('all',array('conditions'=> array('date between ? and ?' => array($first_date, $last_date))));
		// 		$data_return = $this->utility_return->find('all',array('conditions'=> array('date between ? and ?' => array($first_date, $last_date))));
		// 		// echo "<pre>";
		// 		// print_r($data_entries);
		// 		foreach($data_entries as $key => $match)
		// 		{
		// 			$exp_data[]=@explode(',',$match['utility_entry']['no_of_person']);
		// 		}
		// 		foreach($data_return as $key1 => $match1)
		// 		{
		// 			$exp_data1[]=@explode(',',$match1['utility_return']['no_of_person']);
		// 		}
		// 		// print_r($exp_data);
		// 		// echo "</pre>";die;
		// 		$this->set('entry_data',$exp_data);
		// 		$this->set('return_data',$exp_data1);
		// 		$this->set('check_entry',$data_entries);
		// 		$this->set('check_return',$data_return);

		// 	}
			
		// }
		$this->layout='index_layout';
		$this->loadmodel('utility_entry');
		$this->loadmodel('utility_return');
		$this->loadmodel('master_item');
		$type=$this->Session->read('type');
		$this->set('role', $type);
		$mycounter=$this->Session->read('counter_id');
		if($this->request->is('post'))
		{	
			if(isset($this->request->data['report_ticket_gen']))
			{				
				$first_date=$this->datefordb($this->request->data['from']);
				$last_date=$this->datefordb($this->request->data['to']); 
				$from_date=$this->set('from',$this->request->data['from']);
				$to_date=$this->set('to',$this->request->data['to']);

				$this->set('fetch_data_tiket_item', $this->utility_entry->find('all',array('conditions'=> array('date between ? and ?' => array($first_date, $last_date)))));
				$this->set('fetch_data_tiket_item1', $this->utility_return->find('all',array('conditions'=> array('date between ? and ?' => array($first_date, $last_date)))));
				$this->set('fetch_master_item', $this->master_item->find('all',array('conditions'=>array('auto_id'=>'2'))));
			}
			
		}
	}
	//////////////////

	//////////
	public function discount_wise_report()
	{
		$this->layout='index_layout';
		$this->loadmodel('ticket_entry');
		$this->loadmodel('master_item');
		$type=$this->Session->read('type');
		$this->set('role', $type);
		$mycounter=$this->Session->read('counter_id');
		if($this->request->is('post'))
		{	
			if(isset($this->request->data['report_ticket_gen']))
			{
				 	 $first_date=$this->datefordb($this->request->data['from']);
				 	 $last_date=$this->datefordb($this->request->data['to']); 
					 $from_date=$this->set('from',$this->request->data['from']);
					 $to_date=$this->set('to',$this->request->data['to']);
					 
					$this->set('fetch_data_tiket_item', $this->ticket_entry->find('all',array('conditions'=> array('ticket_type' => 2 , 'date between ? and ?' => array($first_date, $last_date)))));
				 	$this->set('fetch_master_item', $this->master_item->find('all',array('conditions'=>array('auto_id'=>'1','ticket_type' => 2))));
					$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'1','ticket_type' => 2)));
						foreach($all_data as $key => $match)
						{
							$exp_data=@explode(',',$match['master_item']['counter_id']);
							if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
							{
								unset($item_status[$key]);
							}
							if(!in_array($mycounter,$exp_data))
							{
								unset($all_data[$key]);
							}
						}
						$this->set('master_item_fetch',  $all_data);
			}
			if(isset($this->request->data['ticket_submit']))
			{
				
				 
				 $count=$this->request->data['count'];
				
					for($i=1;$i<=$count;$i++)
					{
						if(!empty($this->request->data['no_of_person'.$i]))
						{
							$no_of_person[]=$this->request->data['no_of_person'.$i];
							$amount[]=$this->request->data['amount'.$i];
							$master_item_id[]=$this->request->data['master_item_id'.$i];
						}
					}
				 
				
					if(empty($this->request->data['locker_no'])){
						$this->request->data['locker_no']=0;
					}
				$this->request->data['discount']=@$this->request->data['discount_detail'];
				$this->request->data['discount_authorise']=@$this->request->data['discount_authorise'];
 				$this->request->data['no_of_person']=@implode(',',$no_of_person);
				$this->request->data['amount']=@implode(',',$amount);
				$this->request->data['master_item_id']=@implode(',',$master_item_id);
 				$this->request->data['ticket_no']=@$this->request->data['real_ticket'];
				
				$this->ticket_entry->id=$this->request->data['update_id'];
 				$this->ticket_entry->save($this->request->data);
 					
				foreach($master_item_id as $key => $data){
				$conditions=array('id' => $data,'status' => '1');
				$check_data=$this->master_item->find('count',array('conditions'=>$conditions,'fields'=>array('id')));
				if(($check_data)>0)
				{
					$new_p[]=@$no_of_person[$key];	
					$check_data=$this->master_item->find('all',array('conditions'=>$conditions,'fields'=>array('id')));	
					$master_idd[]=@$check_data[0]['master_item']['id'];
				}
				}
				if(is_array(@$master_idd))
				{
					$this->request->data['no_of_item']=@implode(',',$new_p);
					$this->request->data['master_item_id']=@implode(',',$master_idd);
					$this->request->data['item_status']=1;
					$this->request->data['counter_id']=$this->Session->read('counter_id');
					$this->loadmodel('item_manage');
					$this->item_manage->save($this->request->data);
				}
		//<--------------------------------------------------------------------->
				
				$first_date=$this->datefordb($this->request->data['from']);
				$last_date=$this->datefordb($this->request->data['to']); 
				$from_date=$this->set('from',$this->request->data['from']);
				$to_date=$this->set('to',$this->request->data['to']);
					 
					$this->set('fetch_data_tiket_item', $this->ticket_entry->find('all',array('conditions'=> array('discount !=' =>0 ,'date between ? and ?' => array($first_date, $last_date)))));
					 
				 	$this->set('fetch_master_item', $this->master_item->find('all',array('conditions'=>array('auto_id'=>'1','ticket_type' => 2))));
					$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'1','ticket_type' => 2)));
						foreach($all_data as $key => $match)
						{
							$exp_data=@explode(',',$match['master_item']['counter_id']);
							if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
							{
								unset($item_status[$key]);
							}
							if(!in_array($mycounter,$exp_data))
							{
								unset($all_data[$key]);
							}
						}
						$this->set('master_item_fetch',  $all_data);	
				
			}
		}
	}
	public function ticket_wise_report()
	{
		$this->layout='index_layout';
		$this->loadmodel('ticket_entry');
		$this->loadmodel('master_item');
		$type=$this->Session->read('type');
		$this->set('role', $type);
		$mycounter=$this->Session->read('counter_id');
		if($this->request->is('post'))
		{	
			if(isset($this->request->data['report_ticket_gen']))
			{
				 	 $first_date=$this->datefordb($this->request->data['from']);
				 	 $last_date=$this->datefordb($this->request->data['to']); 
					 $from_date=$this->set('from',$this->request->data['from']);
					 $to_date=$this->set('to',$this->request->data['to']);
					$this->set('fetch_data_tiket_item', $this->ticket_entry->find('all',array('conditions'=> array('ticket_type' =>1 ,'date between ? and ?' => array($first_date, $last_date)))));
					// print_r($this->master_item->find('all',array('conditions'=>array('auto_id'=>1,'ticket_type' =>1)))); exit;
				 	$this->set('fetch_master_item', $this->master_item->find('all',array('conditions'=>array('auto_id'=>1,'ticket_type' =>1))));
					
					$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>1,'ticket_type' =>1)));
					
						foreach($all_data as $key => $match)
						{
							$exp_data=@explode(',',$match['master_item']['counter_id']);
							if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
							{
								unset($item_status[$key]);
							}
							if(!in_array($mycounter,$exp_data))
							{
								unset($all_data[$key]);
							}
						}
						$this->set('master_item_fetch',  $all_data);
			}
			if(isset($this->request->data['ticket_submit']))
			{
				
				 
				 $count=$this->request->data['count'];
				
					for($i=1;$i<=$count;$i++)
					{
						if(!empty($this->request->data['no_of_person'.$i]))
						{
							$no_of_person[]=$this->request->data['no_of_person'.$i];
							$amount[]=$this->request->data['amount'.$i];
							$master_item_id[]=$this->request->data['master_item_id'.$i];
						}
					}
				 
				
					if(empty($this->request->data['locker_no'])){
						$this->request->data['locker_no']=0;
					}
				$this->request->data['discount']=@$this->request->data['discount_detail'];
				$this->request->data['discount_authorise']=@$this->request->data['discount_authorise'];
 				$this->request->data['no_of_person']=@implode(',',$no_of_person);
				$this->request->data['amount']=@implode(',',$amount);
				$this->request->data['master_item_id']=@implode(',',$master_item_id);
 				$this->request->data['ticket_no']=@$this->request->data['real_ticket'];
				
				$this->ticket_entry->id=$this->request->data['update_id'];
 				$this->ticket_entry->save($this->request->data);
 					
				foreach($master_item_id as $key => $data){
				$conditions=array('id' => $data,'status' => '1');
				$check_data=$this->master_item->find('count',array('conditions'=>$conditions,'fields'=>array('id')));
				if(($check_data)>0)
				{
					$new_p[]=@$no_of_person[$key];	
					$check_data=$this->master_item->find('all',array('conditions'=>$conditions,'fields'=>array('id')));	
					$master_idd[]=@$check_data[0]['master_item']['id'];
				}
				}
				if(is_array(@$master_idd))
				{
					$this->request->data['no_of_item']=@implode(',',$new_p);
					$this->request->data['master_item_id']=@implode(',',$master_idd);
					$this->request->data['item_status']=1;
					$this->request->data['counter_id']=$this->Session->read('counter_id');
					$this->loadmodel('item_manage');
					$this->item_manage->save($this->request->data);
				}
		//<--------------------------------------------------------------------->
				
				$first_date=$this->datefordb($this->request->data['from']);
				 	 $last_date=$this->datefordb($this->request->data['to']); 
					 $from_date=$this->set('from',$this->request->data['from']);
					 $to_date=$this->set('to',$this->request->data['to']);
					 
					$this->set('fetch_data_tiket_item', $this->ticket_entry->find('all',array('conditions'=> array('discount'=>0 , 'date between ? and ?' => array($first_date, $last_date)))));
				 	$this->set('fetch_master_item', $this->master_item->find('all'));
					$all_data=$this->master_item->find('all',array('conditions'=>array('auto_id'=>'1','ticket_type' => 1)));
					
						foreach($all_data as $key => $match)
						{
							$exp_data=@explode(',',$match['master_item']['counter_id']);
							if(!in_array($mycounter,$exp_data) || $match['master_item']['status'] !=1)
							{
								unset($item_status[$key]);
							}
							if(!in_array($mycounter,$exp_data))
							{
								unset($all_data[$key]);
							}
						}
						$this->set('master_item_fetch',  $all_data);	
				
			}
		}
			
	}

	//////////////////
	public function group_report()
	{
		$this->layout='index_layout';
		$this->loadmodel('group_booking');
		if($this->request->is('post'))
		{	
			if(isset($this->request->data['group_search']))
			{
					$from_date=$this->set('date_from',$this->request->data['from']);
					$to_date=$this->set('date_to',$this->request->data['to']);
					$this->loadmodel('master_item');
		$this->set('adult_rate', $this->master_item->find('all', array('conditions' => array('id' => 1),'fields'=>array('rate'))));
		$this->set('children_rate', $this->master_item->find('all', array('conditions' => array('id' => 2),'fields'=>array('rate'))));
			}
		}
		
	}
	//////////////////
	public function daily_summary_report()
	{
		$this->layout='index_layout';
		$this->loadmodel('ticket_entry');
		if($this->request->is('post'))
		{	
			if(isset($this->request->data['daily_search']))
			{
					$from_date=$this->set('date_from',$this->request->data['from']);
					$to_date=$this->set('date_to',$this->request->data['to']);
					$from=$this->datefordb($this->request->data['from']);
					$to=$this->datefordb($this->request->data['to']);
					/*$conditions="";
					if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
					{
					$conditions[]=array('date between ? and ?' => array($from, $to));
					}
					
					$qry = $this->ticket_entry->find('all',array('conditions' => $conditions));
					$this->set('fatch_ticket_entry',$qry);
					*/
			}
		}
	}
////////////////////////////////////////////
	public function report_stock() 
	{
		$this->layout='index_layout';
		$this->loadmodel('item_inward');
		if($this->request->is('post'))
		{
				if(isset($this->request->data['report_stock_gen']))
				{ 			
							$from_date=$this->set('date_from',$this->request->data['from']);
							$to_date=$this->set('date_to',$this->request->data['to']);
							$from=$this->datefordb($this->request->data['from']);
							$to=$this->datefordb($this->request->data['to']);
							$conditions="";
							if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
							{
							$conditions[]=array('type' => 0,'date between ? and ?' => array($from, $to));
							}
							if(!empty($counter_id))
							{
							$conditions[]=array('type' => 0,'counter_id' => ''.$counter_id.'');
							}
							$qry = $this->item_inward->find('all',array('conditions' => $conditions));
							$this->set('item_inwards',$qry);
							
							
				}		
		}	
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function report_item_allotted() 
	{
		$this->layout='index_layout';
		$this->loadmodel('counter');
		$this->set('counter_fetch', $this->counter->find('all'));
		
		if($this->request->is('post'))
		{
				$this->loadmodel('item_manage');
				if(isset($this->request->data['report_issue_item']))
				{ 		
					/*$this->loadmodel('item_inward');
					$this->set('ftc_item_inward', $this->item_inward->find('all'));
					*/
					$from_date=$this->set('date_from',$this->request->data['from']);
					$to_date=$this->set('date_to',$this->request->data['to']);
					$from=$this->datefordb($this->request->data['from']);
					$to=$this->datefordb($this->request->data['to']);
					$conditions="";
					if(!empty($this->request->data['from'])&&!empty($this->request->data['to']))
					{
					$conditions[]=array('date between ? and ?' => array($from, $to));
					}
					//$conditions[]=array('item_status'=>'1');
					$qry = $this->item_manage->find('all',array('conditions' => $conditions));
					$this->set('item_manage',$qry);
				}
		}
	}
	
////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	public function my_ajax()
	 {	
		$this->layout='ajax_layout';
		 $identity=$this->request->query("identity");
		 if($this->request->query('edit_lost_table')==1)
		 {		
 				$this->loadmodel('missing');
				$this->request->query["found_by"];
				$auto_ir=$this->request->query["auto_ir"];
				$found_date=$this->datefordb($this->request->query['found_date']);
				$this->request->query['found_date']=$found_date;
				$this->request->query["found_location"];
				$found_comment=$this->request->query["found_comment"];
				$this->request->query["status"]=1;
				$this->missing->id=$this->request->query['id'];
				$this->missing->save($this->request->query);
				$fetch_data=$this->missing->find('all', array('conditions' => array('id' => $this->request->query['id']),'fields'=>array('name','mobile_no','lost_date','description_item')));
				foreach(@$fetch_data as $value)
				{
					$name=$value['missing']['name'];
					$mobile_no=$value['missing']['mobile_no'];
					$lost_date=$value['missing']['lost_date'];
					$description_item=$value['missing']['description_item'];
				} 
				/*
						---------------Or------------without make foreach loop
						$name=$fetch_data[0]['missing']['name'];
						$mobile_no=$fetch_data[0]['missing']['mobile_no'];
						$lost_date=$fetch_data[0]['missing']['lost_date'];
						------------------------------------------------------
				*/
				echo '<td>'.$auto_ir.'</td>
					  <td>'.$fetch_data[0]['missing']['name'].'</td>	
					  <td>'.$mobile_no.'</td>
					  <td>'.$this->dateforview($lost_date).'</td>
					  <td>'.$description_item.'</td>
                      <td><a target="_blank" href="update_lost_menu?id='.$this->request->query['id'].'" role="button" class="btn btn-xs yellow-crusta" ><i class="fa fa-edit"></i> Edit</a> <span class="label label-sm label-info"><i class="fa fa-check"></i> Successfully founded</span></td>';
		 }
		 
		if($this->request->query('user_rights')==1)
		{
			$user_id=$this->request->query('user_id');
			$this->loadmodel('module');
			$fetch_menu = $this->module->find('all',array('conditions' => array('delete_status'=>0)));
			 
			$this->loadmodel('user_right');
			$conditions=array("user_id" => $user_id);
			$fetch_user_right = $this->user_right->find('all',array('conditions'=>$conditions));
			@$user_right1=$fetch_user_right['0']['user_right']['module_id'];
			
			 $user_right=explode(',', $user_right1);
			?><center>
                <table class="table table-striped table-condensed table-bordered table-hover">
                <thead>
                    <tr>
                        <th width="50%">Module</th>
                        <th  width="50%" style="text-align:center;"><input type="checkbox"  name="" id="check_all" /></th>
                    </tr>
                </thead>
                <tbody>
                
                <?php
				$i=1;
			foreach($fetch_menu as $data)
			{
				?>
               <tr>
               <td><?php echo $data['module']['name'];  ?></td>
               <td style="text-align:center"><input type="checkbox" name="module_id[]" class="check" value="<?php echo $data['module']['id']; ?>" <?php if(in_array($data['module']['id'], $user_right)){ echo 'checked'; } ?> /></td>
               </tr>
                <?php
			}
			?>
            
            <tr>
            <td colspan="2" style="text-align:center;">
            <button type="submit" class="btn red " name="right_submit"><i class="fa fa-check"></i> Assign Rights</button>
            </td>
            </tr>
            </tbody>
            </table>
            <?php
		}
		/////////////
		if($this->request->query('check_total_quantity_outward')==1)
		 {		
 				$this->loadmodel('item_inward');
				$q=$this->request->query["q"];
				$q=json_decode($q);
				$item_id=$q[0];
				$quantity=$q[1];
				$this->loadmodel('master_items');
					/*      Inward Item in Item inwards   */
					$fetch_data=$this->item_inward->find('all', array('conditions' => array('type' => 0, 'master_item_id' => $item_id)));
					$total_instock=0;
					if(!empty($fetch_data))
					{foreach($fetch_data as $in_stock){
						$total_quantity_inward=$in_stock['item_inward']['quantitiy'];	
						$total_instock+=$total_quantity_inward;
						}
					}
					if($quantity>$total_instock)
					{
						echo "1";
					}
		 }
		 /////////////
		 if($this->request->query('check_total_quantity_of_return_item')==1)
		 {
			$q=$this->request->query["q"];
			$q=json_decode($q);
			$item_id=$q[1];
			$quantity=$q[0];
			$this->loadmodel('item_manage');
			$fetch_data=$this->item_manage->find('all', array('conditions' => array('date' => date('Y-m-d'))));
			$total_item_issue=0;
			$total_item_return=0;
			
			foreach($fetch_data as $data)
			{
				$item_status=$data['item_manage']['item_status'];
				//////
				if($item_status==1){	
					$master_item_id=$data['item_manage']['master_item_id'];	
					$master_item_id_explode=explode(',', $master_item_id);
					if(in_array($item_id, $master_item_id_explode))
					{
						$key=array_search($item_id, $master_item_id_explode);
						$no_of_item=$data['item_manage']['no_of_item'];	
						$no_of_item_explode=explode(',', $no_of_item);
						$no_of_given_Item=$no_of_item_explode[$key];
						$total_item_issue+=$no_of_given_Item;
						
					}
				}
				////
				if($item_status==0){	
					$master_item=$data['item_manage']['master_item_id'];	
					$master_item_explode=explode(',', $master_item);
					if(in_array($item_id, $master_item_explode))
					{
						$key=array_search($item_id, $master_item_explode);
						$no_of_item1=$data['item_manage']['no_of_item'];	
						$no_of_item_explod=explode(',', $no_of_item1);
						$no_of_return_Item=$no_of_item_explod[$key];
						$total_item_return+=$no_of_return_Item;
						
					}
				}
				//////////
			}
			 $total_avaible_quantity=$total_item_issue-$total_item_return;
			if($quantity>$total_avaible_quantity)
			{
				echo '2';
			}
		 }
		/////////////    
		if($this->request->query('delete_lost_table')==1)
		 {		
		 	$this->loadmodel('missing');
		 	$id=$this->request->query["q"];
			$this->missing->delete(array('id'=>$id));			
		 }
		///////////
		if($this->request->query('check_ticket_available')==1)
		 {		
		 	$this->loadmodel('ticket_entry');
		 	$ticket_no=$this->request->query["q"];
			$fetch_data=$this->ticket_entry->find('all', array('conditions' => array('flag' => 0, 'ticket_no' => $ticket_no)));
			if(!empty($fetch_data))
			{
				echo $data=1;
			}
		exit;	
		 }
		if($this->request->query('check_total_quantity')==1)
		 {		
 				$this->loadmodel('item_inward');
				$q=$this->request->query["q"];
				$q=json_decode($q);
				$ncs=$q[0];
				$item_id=$q[1];
				$this->loadmodel('master_items');
				$fetch_item_type=$this->master_items->find('all', array('conditions' => array( 'id' => $item_id)));
				$status=$fetch_item_type[0]['master_items']['status'];
				if($status==1){
					/*      Inward Item in Item inwards   */
					$fetch_data=$this->item_inward->find('all', array('conditions' => array('type' => 0, 'master_item_id' => $item_id)));
					$total_instock=0;
					if(!empty($fetch_data))
					{foreach($fetch_data as $in_stock){
						$total_quantity_inward=$in_stock['item_inward']['quantitiy'];	
						$total_instock+=$total_quantity_inward;
						}
					}
					/*      Outward Item in Item inwards   */
					$fetch_data_out=$this->item_inward->find('all', array('conditions' => array('type' => 1, 'master_item_id' => $item_id)));
					$total_outstock=0;
					if(!empty($fetch_data_out))
					{ foreach($fetch_data_out as $out_stock){
						$total_quantity_inward=$fetch_data[0]['item_inward']['quantitiy'];	
						$total_outstock+=$total_quantity_inward;
						}
					}
					
					$current_stock=$total_instock-$total_outstock;
					/*             Issue Item in Item manage   */
					$this->loadmodel('item_manage');
					$fetch_item_manage=$this->item_manage->find('all', array('conditions' => array( 'master_item_id LIKE' => '%'.$item_id.'%' ,'item_status' => 1 )));
					$total_issue_quantity=0;
					foreach($fetch_item_manage as $manage_data)
					{
						$master_item_array=$manage_data['item_manage']['master_item_id'];
						$item_explode=explode(',', $master_item_array);
						$no_of_item_array=$manage_data['item_manage']['no_of_item'];
						$no_of_item_array_explode=explode(',', $no_of_item_array);
						
						$key = array_search($item_id, $item_explode);
						$total_issue_quantity+= $no_of_item_array_explode[$key];
					}
					/*             Return Item in Item manage   */
					$fetch_item_manages=$this->item_manage->find('all', array('conditions' => array( 'master_item_id LIKE' => '%'.$item_id.'%' ,'item_status' => 0 )));
					$total_return_quantity=0;
					foreach($fetch_item_manages as $manages_data)
					{
						$master_item_array=$manages_data['item_manage']['master_item_id'];
						$item_explode=explode(',', $master_item_array);
						$no_of_item_array=$manages_data['item_manage']['no_of_item'];
						$no_of_item_array_explode=explode(',', $no_of_item_array);
						
						$key = array_search($item_id, $item_explode);
						$total_return_quantity+= $no_of_item_array_explode[$key];
					}
					
					$total_instock_in_manage=$total_issue_quantity-$total_return_quantity;
					/*---------------      Final  Minimize */
					$totalquantity_instock=$current_stock-$total_instock_in_manage;
					if($ncs>$totalquantity_instock)
					{
						$status_t=2;
					}
					else
					{
						$status_t=1;
					}
					echo $status_t;
				}
				
		 }
	 }


	//////////////////////  Start Php Function  ////////////////////////
	function datefordb($date)
	{$date_new=date("Y-m-d",strtotime($date));return($date_new);}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function dateforview($date)
	{
	$date_no='N/A';	
	$date_new=date("d-m-Y",strtotime($date));
	if($date_new=='01-01-1970')
	return($date_no);
	else
	return($date_new);
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////		
	function user_rights()
	{
		$auto_login_id=$this->Session->read('auto_login_id');
		$this->loadmodel('user_right');
		$conditions=array("user_id" => $auto_login_id);
		return $fetch_user_right = $this->user_right->find('all',array('conditions'=>$conditions));
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	public function menu()
	{
		$this->loadmodel('module');
		$conditions=array("delete_status"=>0);
		return $fetch_menu = $this->module->find('all',array('conditions'=>$conditions,'order'=>'preferance ASC'));
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	public function menu_submenu($main_menu,$data)
	{
		$user_id=$this->Session->read('auto_login_id');
		$counter_id=$this->Session->read('counter_id');
		$this->loadmodel('module');
		//$conditions=array("main_menu" => $main_menu, "sub_menu" => $sub_menu);
		$conditions=array("main_menu" => $main_menu,"delete_status"=>0);
		if(!empty($data))
		{
			return $fetch_menu_submenu = $this->module->find('all',array('conditions'=>$conditions, 'group' => 'sub_menu'));
		}
		else
		{
			return $fetch_menu_submenu = $this->module->find('all',array('conditions'=>$conditions));
		}
		//return $fetch_menu = $this->module->find('all');
		
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	public function submenu($sub_menu)
	{
		$user_id=$this->Session->read('auto_login_id');
		$counter_id=$this->Session->read('counter_id');
		$this->loadmodel('module');
		$conditions=array("sub_menu" => $sub_menu);
		return $fetch_submenu = $this->module->find('all',array('conditions'=>$conditions));
		
	}	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	function fetchmasteritemname($master_item_id)
	{
		$this->loadmodel('master_item');
		$conditions=array('id' => $master_item_id);
		$master_item=$this->master_item->find('all',array('conditions'=>$conditions,'fields'=>array('name')));
		return $master_item[0]['master_item']['name'];
	}
	
	function fetchmastercategory($master_category_id)
	{
		$this->loadmodel('master_category');
		$conditions=array('id' => $master_category_id);
		$master_item=$this->master_category->find('all',array('conditions'=>$conditions,'fields'=>array('category')));
		return $master_item[0]['master_category']['category'];
	}
	
	function fetch_tecket_data_summary($master_item_id,$auto_id)
	{
		$date=date("Y-m-d");
		$conditions=array('flag'=>0 ,'master_item_id LIKE ' => '%'.$master_item_id.'%' , 'date' => $date );
		if($auto_id==1)
		{
			$this->loadmodel('ticket_entry');
			return $last_data = $this->ticket_entry->find('all',array('conditions' => $conditions));
		}
		else if($auto_id==2)
		{
			$this->loadmodel('utility_entry');
			return $last_data = $this->utility_entry->find('all',array('conditions' => $conditions));
		}
		 
	}
	function fetch_tecket_daily_summary_report($date,$auto_id)
	{
		$this->loadmodel('ticket_entry');
		$counter = $this->Session->read('counter_id');
		$conditions=array('flag'=>0 , 'date' => $date , 'counter_id' => $counter

	);
		if($auto_id==1)
		{
			$this->loadmodel('ticket_entry');
			return $last_data = $this->ticket_entry->find('all',array('conditions' => $conditions));
		}
		else if($auto_id==2)
		{
			$this->loadmodel('utility_entry');
			return $last_data = $this->utility_entry->find('all',array('conditions' => $conditions));
			// print_r($last_data);
		}
	}
	/*public function fetch_tecket_aseDESC_summary()
	{
		
		
	}*/
	function fetch_ticket_data_for_report($id,$auto_id)
	{
		$conditions=array('flag' => 0 , 'ticket_no' => $id );
		if($auto_id==1)
		{
			$this->loadmodel('ticket_entry');
			return $last_data = $this->ticket_entry->find('all',array('conditions' => $conditions));
		}
		else if($auto_id==2)
		{
			$this->loadmodel('utility_entry');
			return $last_data = $this->utility_entry->find('all',array('conditions' => $conditions));
		}
	}
	function ftc_taxi_commission($date)
	{
		$this->loadmodel('taxi_commission');
		$user_id=$this->Session->read('auto_login_id');
		$counter_id=$this->Session->read('counter_id');
		$conditions=array('counter_id'=>$counter_id , 'login_id' => $user_id ,'date'=> $date );
		return $this->taxi_commission->find('all', array('conditions' => $conditions));
	}
	function ftc_security_amount($date)
	{
		$this->loadmodel('item_manage');
		$user_id=$this->Session->read('auto_login_id');
		$counter_id=$this->Session->read('counter_id');
		$conditions=array('counter_id'=>$counter_id , 'login_id' => $user_id ,'date'=> $date );
		return $this->item_manage->find('all', array('conditions' => $conditions));
	}
	function ftc_security_amountissue($date)
	{
		$this->loadmodel('utility_entry');
		$user_id=$this->Session->read('auto_login_id');
		$counter_id=$this->Session->read('counter_id');
		$conditions=array('counter_id'=>$counter_id , 'login_id' => $user_id ,'date'=> $date );
		return $this->utility_entry->find('all', array('conditions' => $conditions));
	}
	function ftc_security_amountreturn($date)
	{
		$this->loadmodel('utility_return');
		$user_id=$this->Session->read('auto_login_id');
		$counter_id=$this->Session->read('counter_id');
		$conditions=array('counter_id'=>$counter_id , 'login_id' => $user_id ,'date'=> $date );
		return $this->utility_return->find('all', array('conditions' => $conditions));
	}
	function ftc_security_amount_ticket_report($form,$to)
	{
		$this->loadmodel('item_manage');
		$user_id=$this->Session->read('auto_login_id');
		$counter_id=$this->Session->read('counter_id');
		$conditions=array('date between ? and ?' => array($form, $to) );
		return $this->item_manage->find('all', array('conditions' => $conditions));
	}
	function service_tax($date)
    {
        $this->loadmodel('master_tax');
        $conditions=array('type'=>0, 'date <=' => $date ,'end_date >=' => $date);
        $tax=$this->master_tax->find('all', array('conditions' =>$conditions,'order'=>'date DESC','limit'=>1));
		return $tax[0]['master_tax']['tax'];
    }
	function service_tax_all($date)
    {
        $this->loadmodel('master_tax');
        $conditions=array('type'=>1, 'date <=' => $date  ,'end_date >=' => $date);
       return $tax=$this->master_tax->find('all', array('conditions' =>$conditions));
		  
	}
	
	function ftc_taxi_commission_report($id)
	{
		$this->loadmodel('taxi_commission');
		$conditions=array('ticket_no' => $id );
		return $this->taxi_commission->find('all', array('conditions' => $conditions));
	}
	function ftc_taxi_commission_report_in_month($form,$to)
	{
		$this->loadmodel('taxi_commission');
		$conditions=array('date between ? and ?' => array($form, $to) );
		return $this->taxi_commission->find('all', array('conditions' => $conditions));
	}
	function check_rate_changeORnot($date,$master_item_id)
	{
		$this->loadmodel('rate_change');
		$conditions=array('timestamp <=' => $date,'master_item_id' => $master_item_id );
		
		return $this->rate_change->find('all', array('conditions' => $conditions,'order'=>'id DESC','limit'=>1));
	}
	function check_rate_changeORnot_data($form,$to,$master_item_id)
	{
		$this->loadmodel('rate_change');
		$conditions=array('timestamp between ? and ?' => array($form, $to),'master_item_id' => $master_item_id );
		return $this->rate_change->find('all', array('conditions' => $conditions));
	}
	function fetchticket_entry($first_date,$last_date,$master_item_id,$auto_id)
	{
		if($master_item_id>0)
		{
			$conditions[]=array('flag'=>0 ,'master_item_id LIKE'=> '%'.$master_item_id.'%','date between ? and ?' => array($first_date, $last_date));
		}
		else if(($master_item_id==0) &&($last_date==0)) 
		{
			$counter_id=$this->Session->read('counter_id');
			$conditions[]=array('flag'=>0 ,'counter_id'=>$counter_id,'date between ? and ?' => array($first_date, $first_date));
		}
		else 
		{
			$conditions[]=array('flag'=>0 ,'date between ? and ?' => array($first_date, $last_date));
		}
		if($auto_id==1)
		{
			$this->loadmodel('ticket_entry');
			return $qry = $this->ticket_entry->find('all',array('conditions' => $conditions));
		}
		else if($auto_id==2)
		{
			$this->loadmodel('utility_entry');
			return $qry = $this->utility_entry->find('all',array('conditions' => $conditions));
		}
		
	}
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	function fetchmasterrate($master_item_id)
	{
		$this->loadmodel('master_item');
		$conditions=array('id' => $master_item_id);
		$master_item=$this->master_item->find('all',array('conditions'=>$conditions,'fields'=>array('rate')));
		return $master_item[0]['master_item']['rate'];
	}
	function fetchmasterrate_security($master_item_id)
	{
		$this->loadmodel('master_item');
		$conditions=array('id' => $master_item_id);
		$master_item=$this->master_item->find('all',array('conditions'=>$conditions,'fields'=>array('security')));
		if(!empty($master_item))
		{
			return $master_item[0]['master_item']['security'];
		}
		
	}
	function fetch_company_name()
	{
		$this->loadmodel('company');
		return $company=$this->company->find('all');
	}
	function fetch_group_booking($date_time)
	{
		$this->loadmodel('group_booking');
		$conditions[]=array('date_time LIKE'=> '%'.$date_time.'%');
		return $qry = $this->group_booking->find('all',array('conditions' => $conditions));
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	function fetchcountername($counter_id)
	{
		
		$counter_id_array=explode(',',$counter_id);
		$this->loadmodel('counter');
		foreach($counter_id_array as $data){
			$conditions=array('id' => $data);
			$master_item=$this->counter->find('all',array('conditions'=>$conditions,'fields'=>array('name')));
			$c_name[]=$master_item[0]['counter']['name'];
		} 
			return implode(',',$c_name);
	}
   
   ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	function fetch_bill_no($max_bill_no)
	{
			$len=strlen($max_bill_no);
			$mylen="";
			if($len==1)
			$mylen="000";
			else if($len==2)
			$mylen="00";
			else if($len==3)
			$mylen="0";
			$prefix="GH";
			return($prefix.$mylen.$max_bill_no);
	
	}
	function find_all_quantity_avilable_stock($id)
	{
		$this->loadmodel('item_inward');
		$conditions[]=array('master_item_id' => $id);
		return $qry = $this->item_inward->find('all',array('conditions' => $conditions));
	}
	
	function auto_save_data()
	{  
		$record_id=$this->request->query('record_id'); 
		$field=$this->request->query('field');
		$value=$this->request->query('value_update');
		$item_rate=$this->request->query('item_rate');
 		$item_id=$this->request->query('item_id');
		$table=$this->request->query('table');
 		$this->loadmodel('ticket_entry');
		$this->loadmodel('utility_entry');
		 
		$record_data_ftc=$this->$table->find('all',array('conditions' => array('id' => $record_id)));	
		$master_item_id=$record_data_ftc[0][$table]['master_item_id'];	
		$no_of_person=$record_data_ftc[0][$table]['no_of_person'];	
		$amount=$record_data_ftc[0][$table]['amount'];	
		$tot_amnt=$record_data_ftc[0][$table]['tot_amnt'];	
		$grand_amnt=$record_data_ftc[0][$table]['grand_amnt'];	
		$paid_amnt=$record_data_ftc[0][$table]['paid_amnt'];
		$discount=$record_data_ftc[0][$table]['discount'];		
		// Explode
		$explode_master_item_id=explode(',', $master_item_id);
		$explode_no_of_person=explode(',', $no_of_person);
		$explode_amount=explode(',', $amount);
		
		$update_amount=$item_rate*$value; 
		
			if(in_array($item_id, $explode_master_item_id))
			{
				$key = array_search($item_id, $explode_master_item_id);
				$pax=$explode_no_of_person[$key];
				$amount=$explode_amount[$key];
				
				$remainig_amount=$tot_amnt-$amount;
 				
				$updated_amount=$remainig_amount+$update_amount;	
				$this->request->data['tot_amnt']=$updated_amount;
				
				$grnd_amt=$updated_amount-$discount;
				$this->request->data['grand_amnt']=$grnd_amt;
				$this->request->data['paid_amnt']=$grnd_amt;
				
				$explode_amount[$key]=$update_amount;
				$explode_no_of_person[$key]=$value;
				$amt=implode(',',$explode_amount);
				$no_p=implode(',',$explode_no_of_person);
 			}
			else
			{
				$explode_amount[]=$update_amount;
				$explode_no_of_person[]=$value;
				$explode_master_item_id[]=$item_id;
				
				$updated_amount=$tot_amnt+$update_amount;	
				$this->request->data['tot_amnt']=$updated_amount;
				
				$grnd_amt=$updated_amount-$discount;
				$this->request->data['grand_amnt']=$grnd_amt;
				$this->request->data['paid_amnt']=$grnd_amt;
				
				$amt=implode(',',$explode_amount);
				$no_p=implode(',',$explode_no_of_person);
				$master=implode(',',$explode_master_item_id);
				
				$this->request->data['master_item_id']=$master;	
			}
			
			if($table=='utility_entry'){$x=0;$total_security=0;$security=0;
				foreach($explode_master_item_id as $item_ids){
				$total_quantity_security=$this->requestAction(array('controller' => 'Handler', 'action' => 'fetchmasterrate_security',$item_ids),array());
					$per=$explode_no_of_person[$x];
					$security=$total_quantity_security*$per;
				
				$x++;
				$total_security+=$security;
				}
				$this->request->data['paid_amnt']=$grnd_amt+$total_security;
				$this->request->data['security_amnt']=$total_security;
			}
 			
			$this->request->data['amount']=$amt;
			$this->request->data['no_of_person']=$no_p;
 			$this->request->data['id']=$record_id;
		   $this->$table->save($this->request->data);	
			 
		 
	}
	public function delete_ticket()
	{
		$this->layout='index_layout';
		$this->loadmodel('ticket_entry');
		$this->loadmodel('utility_entry');
		if($this->request->is('post'))
		{
			$this->loadmodel('ticket_entry');
			if(isset($this->request->data['report_tic_gen']))
			{	
				$first_ticket_no=$this->request->data['first_ticket_no'];
				$auto_id=$this->request->data['auto_id'];
				if($auto_id==1){
					$this->ticket_entry->deleteAll(array('ticket_no >='=>$first_ticket_no));
				}
				elseif($auto_id==2)
				{
					$this->utility_entry->deleteAll(array('ticket_no >='=>$first_ticket_no));
				}
				$this->set('activity',1); 
				$this->set('class','ruby'); 
				$this->set('state','Success !'); 
				$this->set('message','Ticket deleted successfully.');
				$this->redirect(array('action' => 'delete_ticket'));
			}
		}
	}
	//////////////////////  End Php Function  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

}
?>
