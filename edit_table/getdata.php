<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Actiongetdata extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Your own constructor code
        $this->load->model('getdata_model','getdata');

    }

    public function university()
    {
        $result=$this->getdata->get_data_in_object('university',array('university_id' => $this->input->post('university_id')));
        echo $result['university_id']."#".$result['university_name'];
    }

    public function users()
    {
        $result=$this->getdata->get_data_in_object('users',array('uid' => $this->input->post('uid')));
        echo $result['uid']."#".$result['name']."#".$result['username']."#".$result['user_role']."#".$result['upassword'];
    }

    public function orders()
    {
        echo json_encode($this->getdata->get_orders());
    }

    public function getUniversity() // For JSGrid
    {
        $data = $this->getdata->get_data_in_result($this->input->post('table'), []);
        $i=0;
        foreach($data as $univData)
        {
            $output[] = array(
                'Id' => (int) $univData->university_id,
                'Name' => $univData->university_name
            );
        }
        echo json_encode($output);
    }

    public function getJsGridData(){
        $output = array();
        $output['sessionArr'] = $output['subjectArr'] = $output['worktypeArr'] = [];
        

        //Fetching Sessions
        $sessions = $this->db->select('session_id,session_name')->get_where('sessions',array('university_id' => $this->input->post('university_id'),'session_status'=>'1'))->result();

        if(!empty($sessions))
        {
            foreach($sessions as $session)
                array_push($output['sessionArr'],array('session_id'=>$session->session_id,'session_name'=>$session->session_name));
        }
        else
            array_push($output['sessionArr'],array('session_id'=>'','session_name'=>'No Sessions'));


        //Fetching Subjects
        // $Univdata = $this->db->get_where('university',array('university_id' => $this->input->post('university_id')))->row_array();
        // $output['subjectArr'] = $Univdata['subjects']; 
        $Univdata = $this->db->select('subject_name')->get_where('subjects', ['university_id' => $this->input->post('university_id')])->result_array();
        $subjectArr = array_column($Univdata, 'subject_name');
        $output['subjectArr'] = implode(',',$subjectArr);


        //Fetching worktypes from university_worktype table
        $worktypes = $this->db->where_in('university_id', $this->input->post('university_id'))
        ->where('worktype_status', '1')->get('university_worktype')->result();
        if(!empty($worktypes))
        {
            foreach($worktypes as $worktype)
                array_push($output['worktypeArr'],array('univ_worktype_id'=>$worktype->univ_worktype_id,'worktype_name'=>$worktype->worktype_name));
        }
        else
            array_push($output['worktypeArr'],array('univ_worktype_id'=>'','worktype_name'=>'No worktypes'));

        echo json_encode($output, JSON_FORCE_OBJECT);
    }


    public function getCustomerData()
    {
        $output = [];
        $where = $this->input->post();
        if($this->input->post('event') == 'keyup')
        {
            unset($where['table']);
            unset($where['event']);
            $data = $this->getdata->get_orderData($this->input->post('table'),$where);
            // print_r($this->db->last_query());
            // _print_r($data);

            $cdatastr = '';
            if(!empty($data))
            {
                foreach($data as $cdata)
                    $cdatastr .= $cdata['cmobile'].',';
            }
            $output = $cdatastr;
        }
        else if($this->input->post('event') == 'blur')
        {
            unset($where['table']);
            unset($where['event']);
            $data = $this->getdata->get_CustomerData($this->input->post('table'),$where);
            // print_r($this->db->last_query());
            if(!empty($data)){
                $output = $data['cname']."#".$data['cemail'];
            }else{
                $output = "#";
            }
        }
        echo json_encode($output, JSON_FORCE_OBJECT);
    }


    public function administrator_users()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_admin_user($this->input->post('id'));
        echo $result['admin_uid']."#".$result['admin_name']."#".$result['admin_phone']."#".$result['admin_email']."#".$result['admin_username']."#".$result['admin_password']."#".$result['admin_role'];
    }


    public function master_billingcycles()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_master_billingcycle($this->input->post('id'));
        echo $result['billing_cycle_id']."#".$result['billing_cycle_title']."#".$result['billing_cycle_dates'];
    }


    public function master_codcycles()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_master_codcycle($this->input->post('id'));
        echo $result['cod_cycle_id']."#".$result['cod_cycle_title']."#".$result['cod_cycle_dates'];
    }


    public function get_billingdates()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_billingdates($this->input->post('id'));
        echo $result['billing_cycle_dates'];
    }

    public function get_coddates()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_coddates($this->input->post('id'));
        echo $result['cod_cycle_dates'];
    }


    public function master_transitpartners()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_master_transitpartner($this->input->post('id'));
        echo $result['transitpartner_id']."#".$result['transitpartner_name']."#".$result['transitpartner_logo']."#".$result['transitpartner_description'];
    }

    public function master_transitpartners_accounts()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_master_transitpartner_account($this->input->post('id'));
        echo $result['account_id']."#".$result['parent_id']."#".$result['account_name']."#".$result['account_description']."#".$result['account_key']."#".$result['account_username']."#".$result['account_password']."#".$result['base_weight'];
    }

    
    public function master_weightslabs()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_master_weightslab($this->input->post('id'));
        echo $result['weightslab_id']."#".$result['slab_title']."#".$result['base_weight']."#".$result['additional_weight'];
    }

    public function master_pincodes()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_master_pincode($this->input->post('id'));
        echo $result['pincode_id']."#".$result['pincode']."#".$result['pin_city']."#".$result['pin_state'];
    }

    public function master_zones()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_master_zone($this->input->post('id'));
        echo $result['zone_id']."#".$result['source_city']."#".$result['destination_pin']."#".$result['zone'];
    }

    public function master_b2b_zones()
    {
        $result =   $this->getdata_model->get_master_b2b_zone($this->input->post('id'));
        return _json($result);
    }

    public function get_pocdetails()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_pocdetails($this->input->post('id'));
        echo $result['admin_email']."#".$result['admin_phone'];
    }

    public function get_invoicedata()
    {
        $result=$this->getdata_model->get_invoicedata($this->input->post('id'));
        echo json_encode($result);
    }

    public function get_coddata()
    {
        echo json_encode($this->getdata_model->get_coddata($this->input->post()));
    }
    
    public function generate_apikey()
    {
        $form_data = array(
            'token_key'    => strtoupper(random_string('alnum', 30))
        );

        $tracking_data = array(
            'activity_type' => "update_user_apitoken",
            'log_data' => json_encode(array($this->input->post(),$form_data)),
            'admin_id' => $this->session->userdata['user_session']['admin_username'],
        );
        
        if($this->updations_model->update('users',['user_id' => $this->input->post('userid')],$form_data) && $this->insertions_model->activity_logs($tracking_data))
        {
            $output['token_key'] =$form_data['token_key'];
            $output['title'] = 'Congrats';
            $output['message'] = 'User API Token Updated Successfully.';
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['token_key'] ='';
            $output['message'] = 'Some Error occurred, Try again.';
        }
        echo json_encode($output);
    }

    public function get_permission()
    {
		$keys = array_keys($this->input->post());
		$where[$keys[0]] = $this->input->post($keys[0]);
		$table = $this->input->post('table');
		$result = $this->permissions_model->getPermission($where,$table);
		echo json_encode($result);
	}

    public function get_address_response()
    {
        $result = $this->db->select('api_response')->where('user_address_id',$this->input->post('address_id'))->get('users_address')->row();
        echo str_replace('..!','<br/>',$result->api_response);
    }

    public function get_addressdetails()
    {
        $result = $this->db->where('user_address_id',$this->input->post('address_id'))->get('users_address')->row();
        echo $result->address_title."@".$result->addressee."@".$result->full_address."@".$result->phone."@".$result->pincode."@".$result->address_city."@".$result->address_state;
    }
    
    public function pincodelookup()
    {
        $result=$this->getdata_model->pincodelookup($this->input->post('pincode'));
        if(!empty($result))
            echo $result['pin_city']."#".$result['pin_state'];
    }

    public function users_modules()
    {
        //$record_id=$this->input->post('id');
        $result=$this->getdata_model->get_users_module($this->input->post('id'));
        echo $result['user_module_id']."#".$result['parent_menu']."#".$result['module_name']."#".$result['module_route']."#".$result['module_description'];
    }

    public function master_ticket_category()
    {
        $result = $this->getdata_model->get_master_ticketCategory($this->input->post('id'));
        return _json($result);
    }

    public function user_ticket_assigned_rules()
    {
        $result = $this->getdata_model->get_users_ticketsrules($this->input->post('id'));
        return _json($result);
    }

    public function users_tickets_replytemplates()
    {
        $result = $this->getdata_model->get_users_tickets_replytemplates($this->input->post('id'),$this->input->post('category_id'));
        
    }

    public function get_all_ticket_data()
    {      
        $result = $this->getdata_model->get_all_tickets();
        $count =  $this->getdata_model->get_all_tickets(true);
        $config = array();
        $config["base_url"] = "javascript:void(0)";
        $config["total_rows"] = $count->totalCount ?? 0;
        $config["per_page"] = 100;
        $config['uri_segment'] = 3;
        

        $config["full_tag_open"] = '<ul class="pagination">';
        $config["full_tag_close"] = '</ul>';

        $config["next_tag_open"] = '<li>';
        $config["next_tag_close"] = '</li>';
        $config["prev_tag_open"] = '<li>';
        $config["prev_tag_close"] = '</li>';

        $config["num_tag_open"] = '<li>';
        $config["num_tag_close"] = '</li>';

        $config["first_tag_open"] = '<li>';
        $config["first_tag_close"] = '</li>';
        $config["last_tag_open"] = '<li>';
        $config["last_tag_close"] = '</li>';

        $config["cur_tag_open"] = '<li class="active"><a href="javascript:void(0)">';
        $config["cur_tag_close"] = '</a></li>';

        $this->pagination->initialize($config);

        $result['active_page'] = $this->input->post('offset',0);

        $result["links"] = $this->pagination->create_links();
        $result['totalCount']=$count->totalCount ?? 0;
        return _json($result);
    }
    public function get_ticket_details()
    {   
        $container = 'userticket';   
        $result = $this->getdata_model->get_ticket_details();
        if(count($result['data']) > 0){
            foreach($result['data'] as $key => $val){
                if(!empty($val->attachment)){
                    $result['data'][$key]->blob_attachment = get_blob_file($container,$val->attachment);
                }
            }
        }
        return _json($result);
    }
    public function get_template()
    {      
        $result=$this->getdata_model->get_template();
        return _json($result);
    }
    public function get_subcategory()
    {   
        $action = $this->input->get('action','');
        $action = empty($action)?'not_deleted':$action;
        $result=$this->getdata_model->get_sub_categories($this->input->post('id'),$action);
        return _json($result);
    }

    /** get default unique transit partner name with value */
    public function get_transit_partner(){
        $data = explode(',',$this->input->post('data'));
        if(isset($data[0]) && !empty($data[0])){
            $result = $this->db->select('group_concat(assigned_account) as assigned_account')
                                ->where('priority_status','1')
                                ->where('weightslab_id',$data[0])
                                ->get('default_courierpriority')->row();

            // find unique courier assigned id
            $unique_assigned_id = array_unique(explode(',',$result->assigned_account));

            $courier_result = $this->db->select('account_id,account_name')
                                ->where_in('account_id',$unique_assigned_id)
                                ->where('account_status','1')
                                ->get('master_transitpartners_accounts')
                                ->result();

            if(isset($result) && isset($courier_result)){
                $response_data = [
                    'error'=>false,
                    'title' => 'Congrats',
                    'message' => 'Default data get successfully',
                    'data'     => $courier_result
                ];
            }else{
                $response_data = [
                    'error'=>true,
                    'title' => 'Error',
                    'message' => 'Please set default courier priority'
                ];
            }
        }else{
            $response_data = [
                'error'=>true,
                'title' => 'Error',
                'message' => 'Something went wrong!'
            ];
        }
        echo json_encode($response_data);
    }

    /** get default ratechart data if already set */
    public function get_default_ratechart_data(){
        $slab_data = explode(',',$this->input->post('slab_data'));
        $account_id = $this->input->post('courier_data');
        if(isset($slab_data) && isset($account_id)){
            $result = $this->db->where('weightslab_id',$slab_data[0])
                        ->where('express',$slab_data[1])
                        ->where('account_id',$account_id)
                        ->where('rate_status','1')
                        ->get('default_ratechart');
            if(!empty($result->result())){
                $response_data = [
                    'error'=>false,
                    'title' => 'Congrats',
                    'message' => 'Default data get successfully',
                    'data'    => $result->result()
                ];
            }else{
                $response_data = [
                    'error'=>true,
                    'title' => 'Error',
                    'message' => 'Please set default courier rate chart'
                ];
            }
        }else{
            $response_data = [
                'error'=>true,
                'title' => 'Error',
                'message' => 'Something went wrong!'
            ];
        }
        echo json_encode($response_data);

    }

    /** get users unique transit partner name with value */
    public function get_user_transit_partner(){
        $data = explode(',',$this->input->post('data'));
        $user_id = $this->input->post('user_id');
        $zone = $this->input->post('zone');
        if(isset($data[0]) && !empty($data[0]) && !empty($user_id)){
            $result = $this->db->select('group_concat(assigned_accounts) as assigned_account')
                                ->where('priority_status','1')
                                ->where('slab_id',$data[0])
                                ->where('user_id',$user_id)
                                ->where_in('zone',$zone)
                                ->get('users_courier_priority')->row();

            // find unique courier assigned id
            $unique_assigned_id = array_unique(explode(',',$result->assigned_account));

            $courier_result = $this->db->select('account_id,account_name')
                                ->where_in('account_id',$unique_assigned_id)
                                ->where('account_status','1')
                                ->get('master_transitpartners_accounts')
                                ->result();

            if(isset($result) && isset($courier_result)){
                $response_data = [
                    'error'=>false,
                    'title' => 'Congrats',
                    'message' => 'User data get successfully',
                    'data'     => $courier_result
                ];
            }else{
                $response_data = [
                    'error'=>true,
                    'title' => 'Error',
                    'message' => 'Please set user courier priority'
                ];
            }
        }else{
            $response_data = [
                'error'=>true,
                'title' => 'Error',
                'message' => 'Something went wrong!'
            ];
        }
        echo json_encode($response_data);
    }

    /** get users ratechart data if already set */
    public function get_user_ratechart_data(){
        $slab_data = explode(',',$this->input->post('slab_data'));
        $account_id = $this->input->post('courier_data');
        $user_id = $this->input->post('user_id');
        if(isset($slab_data) && isset($account_id) && isset($user_id)){
            $result = $this->db->where('userslab_id',$slab_data[0])
                        ->where('user_id',$user_id)
                        ->where('express',$slab_data[1])
                        ->where('account_id',$account_id)
                        ->where('rate_status','1')
                        ->get('users_rates_accountwise');
            if(!empty($result->result())){
                $response_data = [
                    'error'=>false,
                    'title' => 'Congrats',
                    'message' => 'User rate get successfully',
                    'data'    => $result->result()
                ];
            }else{
                $response_data = [
                    'error'=>true,
                    'title' => 'Error',
                    'message' => 'Please set user courier rate chart'
                ];
            }
        }else{
            $response_data = [
                'error'=>true,
                'title' => 'Error',
                'message' => 'Something went wrong!'
            ];
        }
        echo json_encode($response_data);
    }
    
    /** get default master ratechart data if already set */
    public function get_master_ratechart_data(){
        $account_id = $this->input->post('courier_data');
        if(isset($account_id)){
            $result = $this->db->where('account_id',$account_id)
                        ->where('rate_status','1')
                        ->get('default_rates_accountwise');
            if(!empty($result->result())){
                $response_data = [
                    'error'=>false,
                    'title' => 'Congrats',
                    'message' => 'Default master data get successfully',
                    'data'    => $result->result()
                ];
            }else{
                $response_data = [
                    'error'=>true,
                    'title' => 'Error',
                    'message' => 'Please set default master courier rate chart'
                ];
            }
        }else{
            $response_data = [
                'error'=>true,
                'title' => 'Error',
                'message' => 'Something went wrong!'
            ];
        }
        echo json_encode($response_data);
    }    

    public function get_datacod_TRN()
    {
        $result=$this->getdata_model->get_datacod_TRN($this->input->post());
        if (isset($result)) {
            $output = [
               'cod_amount' => $result['cod_amount'],
               'total_adjustment' => $result['cod_amount']-($result['total_remitted']+$result['total_adjusted'])
           ];
       }
       else
       {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = 'Given Transaction Id is not valid.';
       }
        echo json_encode($output);
    }
    
    public function get_remit_adjust()
    {
        $output = '';
        $result=$this->getdata_model->get_remit_adjust($this->input->post());
        $output .= '<table class="table table-bordered table-striped table-vcenter">
            <thead>
                <tr>
                    <td class="text-center">Amount</td>';
        $output .= '<td class="text-center">'.(($this->input->post('data_type') == 'remitted')?'UTR':'Invoice no').'</td>';
        $output .='<td class="text-center">Date</td>
                </tr>
            </thead>
            <tbody>';
        foreach($result as $data_SCT){ 
        $output .='<tr>
                    <td class="text-center">'.number_format($data_SCT->action_amount,2).'</td>
                    <td class="text-center">'.substr($data_SCT->action_against,0,30).'<br>'.substr($data_SCT->action_against,30).'</td>
                    <td class="text-center">'.$data_SCT->action_date.'</td>
                </tr>';
            }
        $output .='</tbody></table>';
            echo $output;
    }

    public function getdata_update_notification()
    {
        $result=$this->getdata_model->table_data_in_object('tbl_modalupdates',['updates_id'=>$this->input->post('updates_id')]);

        $dataArr = [
            'status'=>true,
            'updated_id' => $result->updates_id,
            'update_title' => $result->update_title,
            'update_description' => $result->update_description,
        ];
        echo json_encode($dataArr);exit;

    }

    public function get_data(){
        $query = 'select x.*,sb.given_weight,sb.billing_weight,max(`SP`.`product_sku`) as product_sku,`U`.`username`,COUNT(SP.product_name) AS product_count,(SELECT DISTINCT GROUP_CONCAT(CONCAT(`SP`.product_sku))) AS all_product_sku from(select `uwu`.`user_id`,`uwu`.`uwt_id`,`uwu`.`waybill_number`,`uwu`.`request_weight`,`uwu`.`request_status`,max(sb.billing_id) as billing_id from users_weight_update uwu inner join shipments_billing sb on uwu.waybill_number=sb.waybill_number where  `uwu`.`uwt_id` = "'.$this->input->post('weight_id').'" group by `sb`.`waybill_number`)x inner join shipments_billing sb on sb.billing_id=x.billing_id inner JOIN `shipments_products` `SP` ON `x`.`waybill_number` = `SP`.`waybill_number` AND `x`.`user_id` = `SP`.`user_id` JOIN `users` `U` ON `x`.`user_id` = `U`.`user_id` group by SP.waybill_number';
        $query_result = $this->db->query($query)->row();
        echo json_encode($query_result);

    }
    /**
     * Below function returns json data list of shipment weight update requests  
     */
    public function weight_update_list()
    {      
        $result = $this->getdata_model->weight_update();
        $count =  $this->getdata_model->weight_update(true);
        $config = array();
        $config["base_url"] = "javascript:void(0)";
        $config["total_rows"] = $count->totalCount ?? 0;
        $config["per_page"] = 100;
        $config['uri_segment'] = 3;
        

        $config["full_tag_open"] = '<ul class="pagination">';
        $config["full_tag_close"] = '</ul>';

        $config["next_tag_open"] = '<li>';
        $config["next_tag_close"] = '</li>';
        $config["prev_tag_open"] = '<li>';
        $config["prev_tag_close"] = '</li>';

        $config["num_tag_open"] = '<li>';
        $config["num_tag_close"] = '</li>';

        $config["first_tag_open"] = '<li>';
        $config["first_tag_close"] = '</li>';
        $config["last_tag_open"] = '<li>';
        $config["last_tag_close"] = '</li>';

        $config["cur_tag_open"] = '<li class="active"><a href="javascript:void(0)">';
        $config["cur_tag_close"] = '</a></li>';

        $this->pagination->initialize($config);

        $result['active_page'] = $this->input->post('offset',0);

        $result["links"] = $this->pagination->create_links();
        $result['totalCount']=$count->totalCount ?? 0;
        return _json($result);
    }


    public function university_worktype()
    {
        $id = $this->input->post('uid');

        $this->db->trans_start();
        $row = $this->db
                ->select('uw.*, u.university_name') 
                ->from('university_worktype uw')
                ->join('university u', 'u.university_id = uw.university_id', 'left')
                ->where('uw.univ_worktype_id', $id)
                ->get()
                ->row();
        $this->db->trans_complete();

        if ($row) {

            $folderName = strtoupper($row->university_name);
            $worktype_name_f = strtolower($row->worktype_name);
            $starting_folder_name = $id.'_'.$worktype_name_f.'_'.$row->worktype_id;
                            // univ_work_id#work_type_name#worktype_id

            $path = "{$folderName}/{$starting_folder_name}/documents/";
            $setpath = 'assets/uploads/Universities/'. $path;

            // $folderName = strtoupper($row->university_name);
            // $setpath    = "assets/uploads/Universities/{$folderName}/documents/worktype-{$row->worktype_id}/";

            for ($i = 1; $i <= 5; $i++) {
                $fileKey = "file_{$i}";
                if (!empty($row->$fileKey)) {
                    $row->$fileKey = $setpath . $row->$fileKey;
                }
            }
        }
        echo json_encode($row);
    }

    public function orderSubject (){
        $postData = $this->input->post();
        $query = $this->db->query('SELECT os.os_map_id,os.subject_name,os.assigned_to,os.assigned_at,os.status,u.name AS assigned_user_name FROM order_assigment os LEFT JOIN users u ON os.assigned_to=u.uid WHERE os.order_id='.$postData['order_id'].'');

        $result = $query->result();

        echo json_encode($result);
    }




    public function getMcqData()
    {
        $output = [];
        $where = $this->input->post();
        if($this->input->post('event') == 'keyup')
        {
            unset($where['table']);
            unset($where['event']);
            $data = $this->getdata->get_mcqData($this->input->post('table'),$where);
            
            if(!empty($data)) {
                $output = $data;
            } else {
                $output = [];
            }
        }
        else if($this->input->post('event') == 'blur')
        {
            unset($where['table']);
            unset($where['event']);
            $data = $this->getdata->get_mcqData($this->input->post('table'),$where);
            if(!empty($data)){
                foreach ($data as $row) {
                    $output = $row['option_a']."#".$row['option_b']."#".$row['option_c']."#".$row['option_d']."#".$row['correct_option'];
                }
            }else{
                $output = "# # # # #";
            }
        }
        echo json_encode($output, JSON_FORCE_OBJECT);
    }

    public function all_order_section_get()
    {
        $where = base64_decode($this->input->post('order_id'));
        
        $data = $this->getdata->get_order_section('asgmt_portal_mcqbank',$where);
            
        if(!empty($data)) {
                $output = $data;
        } else {
                $output = [];
        }
        echo json_encode($output);
    }

     
    public function order_punch_worktype_check()
    {
        $id = $this->input->post('univ_worktype_id');
        $row = $this->db->where('univ_worktype_id', $id)->get('university_worktype')->row();

        if ($row) {
            echo json_encode([
                'status'      => 'ok',
                'worktype_id' => $row->worktype_id
            ]);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }

}
?>
