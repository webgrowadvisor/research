<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Actionupdate extends CI_Controller
{
    public function __construct()
    {
      parent::__construct();
    //   $this->load->helper('file_upload');
    //   $this->load->helper('excel_data_validate');
      if(!$this->session->has_userdata('user_session'))
        exit();
    }

    public function university()
    {
        $this->form_validation->set_rules('university_name', 'University', 'required|trim|strtoupper');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'university_name' => $this->input->post('university_name')                
            );     
            
            if($this->updations->update('university',array('university_id' => $this->input->post('university_id')),$form_data) && rename(FCPATH."assets/uploads/Universities/".$this->input->post('university_name_tmp'),FCPATH."assets/uploads/Universities/".$this->input->post('university_name')))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'University Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
        }
        echo json_encode($output);
    }

    public function users()
    {
        $this->form_validation->set_rules('name', 'Full Name', 'required|trim');
        $this->form_validation->set_rules('user_role', 'Role', 'required|trim');
        $this->form_validation->set_rules('upassword', 'Password', 'required|trim');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'name' => $this->input->post('name'),
                'user_role' => $this->input->post('user_role'),
                'upassword' => $this->input->post('upassword')
            );      
            
            if($this->updations->update('users',array('uid' => $this->input->post('uid')),$form_data,))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Users Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function orders()
    {
        $this->form_validation->set_rules('modal-university_id', 'University', 'required');
        $this->form_validation->set_rules('modal-session_id', 'session', 'required');
        $this->form_validation->set_rules('modal-worktype', 'Worktype', 'required');
        $this->form_validation->set_rules('modal-order-subjects[]', 'Subjects', 'required');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'university_id' => $this->input->post('modal-university_id'),
                'session_id'    => $this->input->post('modal-session_id'),
                'univ_worktype_id'   => $this->input->post('modal-worktype'),
                'cpusername'      => $this->session->userdata('order_username') ?? '',
                'cppassword'      => $this->session->userdata('order_password') ?? '',
                'subjects'      => implode(",", $this->input->post('modal-order-subjects'))
            );

            if(isset($form_data['university_id'])){
                $university   = $this->db->where('university_id', $form_data['university_id'])->get('university')->row();
            }
            
            if($this->updations->update('orders',array('order_id' => $this->input->post('order_id')),$form_data,))
            {
                if(isset($form_data['university_id']) && isset($form_data['subjects'])){
                    $new_subjects  = explode(',', $form_data['subjects']);
                    $basePath     = FCPATH . "assets/uploads/Universities/" . strtoupper($university->university_name) . "/";

                    $existing_subjects = $this->db->select('subject_name')->where('university_id', $form_data['university_id'])->get('subjects')->result_array();
                    $sub_map = [];

                    foreach ($new_subjects as $subject) {
                        $subject = trim($subject);
                        if ($subject == '') continue;

                        $folderPath = $basePath . $subject;

                        if (!is_dir($folderPath)) {
                            mkdir($folderPath, 0777, true);

                            if (!in_array($subject, $existing_subjects)) {
                                $form_data = array(
                                    'university_id' => $form_data['university_id'],
                                    'subject_name'  => $subject
                                );
                                $sub_id = $this->insertions->insert('subjects',$form_data);
                            }
                        }else{
                            $sub_details = $this->db->where('subject_name', $subject)
                                        ->where('university_id', $form_data['university_id'])
                                        ->get('subjects')->row();
                            $sub_id = $sub_details->subject_id;
                        }

                        $sub_map[] = [
                                    'order_id'   => $this->input->post('order_id'),
                                    'subject_id' => $sub_id,
                                    'subject_name'  => $subject
                        ];
                    }

                    if(!empty($sub_map)){
                        $this->insertions->batch_insert('order_subject_map',$sub_map);
                    }
                }

                $output['title'] = 'Congrats';
                $output['message'] = 'Order Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }

            if($this->session->userdata('order_password')){
                $this->session->unset_userdata('order_username');
                $this->session->unset_userdata('order_password');
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function ordersJSGrid()
    {
        // _print_r($this->input->post());
        // filed_name = update, give_val, order_id
        $this->form_validation->set_rules('value', 'Value', 'required');
        
        $this->form_validation->set_message('required', '%s is required');

        if($this->form_validation->run() == TRUE)
        {   
            
            if($this->input->post('update') == 'paid_amt')
            {
                $order = $this->db->where('order_id', $this->input->post('where'))->get('orders')->row();

                if($this->input->post('value') > $order->order_amt){
                    $output['error'] = true;
                    $output['title'] = 'Error';
                    $output['message'] = 'The Paid Amount cannot be greater than Order Amount';
                    echo json_encode($output);
                    return;
                }

                $updData = $this->db->set($this->input->post('update'),$this->input->post('value'))
                                    ->set('due_amt',"order_amt-{$this->input->post('value')}",false)
                                    ->where('order_id',$this->input->post('where'))
                                    ->update('orders');
            }
            else if($this->input->post('update') == 'order_amt')
            {
                $updData = $this->db->set($this->input->post('update'),$this->input->post('value'))
                                    ->set('order_amt',$this->input->post('value'))
                                    ->set('due_amt', "({$this->input->post('value')} - paid_amt)", false)
                                    ->where('order_id',$this->input->post('where'))
                                    ->update('orders');
            }
            else
                $updData = $this->updations->update('orders',array('order_id' => $this->input->post('where')),array($this->input->post('update') => $this->input->post('value')));

            if($updData)
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Order Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function administrator_users()
    {
        $this->form_validation->set_rules('admin_name', 'Fullname', 'required|trim|min_length[3]|edit_unique_no_condition[admin_users.admin_name.admin_uid]');

        $this->form_validation->set_rules('admin_phone', 'Mobile number', 'required|trim|min_length[10]|max_length[10]|edit_unique_no_condition[admin_users.admin_phone.admin_uid]');

        $this->form_validation->set_rules('admin_email', 'Email', 'required|trim|valid_email|edit_unique_no_condition[admin_users.admin_email.admin_uid]');

        $this->form_validation->set_rules('admin_username', 'Username', 'required|trim|edit_unique_no_condition[admin_users.admin_username.admin_uid]');
        $this->form_validation->set_rules('admin_password', 'Password', 'required|trim');
        $this->form_validation->set_rules('admin_role', 'Role', 'required|trim');



        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'admin_name' => $this->input->post('admin_name'),
                'admin_phone' => $this->input->post('admin_phone'),
                'admin_email' => $this->input->post('admin_email'),
                'admin_username' => $this->input->post('admin_username'),
                'admin_password' => $this->input->post('admin_password'),
                'admin_role' => $this->input->post('admin_role'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],

            );

            $tracking_data = array(
                'activity_type' => "update_admin_user",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            
            if($this->updations_model->updt_admin_user($form_data,$this->input->post('cid')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Sub-Admin Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function master_billingcycles()
    {
        $this->form_validation->set_rules('billingcycle_title', 'Title', 'required|trim|edit_unique_no_condition[master_billing_cycle.billing_cycle_title.billing_cycle_id]');
        $this->form_validation->set_rules('billingcycle_dates', 'Dates', 'required|trim');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'billing_cycle_title' => $this->input->post('billingcycle_title'),
                'billing_cycle_dates' => $this->input->post('billingcycle_dates'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );

            $tracking_data = array(
                'activity_type' => "update_billing_cycle",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            
            if($this->updations_model->updt_master_billingcycle($form_data,$this->input->post('cid')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Billing Cycle Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function master_codcycles()
    {
        $this->form_validation->set_rules('codcycle_title', 'Title', 'required|trim|edit_unique_no_condition[master_cod_cycle.cod_cycle_title.cod_cycle_id]');
        $this->form_validation->set_rules('codcycle_dates', 'Dates', 'required|trim');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'cod_cycle_title' => $this->input->post('codcycle_title'),
                'cod_cycle_dates' => $this->input->post('codcycle_dates'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );

            $tracking_data = array(
                'activity_type' => "update_cod_cycle",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            
            if($this->updations_model->updt_master_codcycle($form_data,$this->input->post('cid')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'COD Cycle Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function master_transitpartners()
    {
        $this->form_validation->set_rules('transitpartner_name', 'Partner Name', 'required|trim|edit_unique_no_condition[master_transit_partners.transitpartner_name.transitpartner_id]');
        $this->form_validation->set_rules('logo_name', 'Logo filename', 'required|trim');
        $this->form_validation->set_rules('transitpartner_description', 'Description', 'trim');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'transitpartner_name' => $this->input->post('transitpartner_name'),
                'transitpartner_logo' => $this->input->post('logo_name'),
                'transitpartner_description' => $this->input->post('transitpartner_description'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );

            $tracking_data = array(
                'activity_type' => "update_transitpartner",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            
            if($this->updations_model->updt_master_transitpartner($form_data,$this->input->post('cid')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Transit Partner Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function master_transitpartners_accounts()
    {
        $this->form_validation->set_rules('account_name', 'Account Name', 'required|trim|update_unique_no_condition[master_transitpartners_accounts.account_name.account_id.cid_acc]');
        $this->form_validation->set_rules('parent_id', 'Parent', 'required');
        $this->form_validation->set_rules('base_weight', 'Account Key', 'required|trim');
        $this->form_validation->set_rules('account_key', 'Account Key', 'trim');
        $this->form_validation->set_rules('account_username', 'Account username', 'trim');
        $this->form_validation->set_rules('account_password', 'Account password', 'trim');
        $this->form_validation->set_rules('account_description', 'Description', 'trim');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'account_name' => $this->input->post('account_name'),
                'parent_id' => $this->input->post('parent_id'),
                'base_weight' => $this->input->post('base_weight'),
                'account_key' => $this->input->post('account_key'),
                'account_username' => $this->input->post('account_username'),
                'account_password' => $this->input->post('account_password'),
                'account_description' => $this->input->post('account_description'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );
            $tracking_data = array(
                'activity_type' => "update_transitpartner_accounts",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );

            if($this->updations_model->updt_master_transitpartner_account($form_data,$this->input->post('cid_acc')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Transit Partner Accounts Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function master_weightslabs()
    {
        $this->form_validation->set_rules('slab_title', 'Slab title', 'required|trim|edit_unique_no_condition[master_weightslab.slab_title.weightslab_id]');
        $this->form_validation->set_rules('base_weight', 'Base weight', 'required|trim|regex_match[/^\d{0,10}(\.\d{0,2})?$/]');
        $this->form_validation->set_rules('additional_weight', 'Additional weight', 'required|trim');


        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'slab_title' => $this->input->post('slab_title'),
                'base_weight' => $this->input->post('base_weight'),
                'additional_weight' => $this->input->post('additional_weight'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );

            $tracking_data = array(
                'activity_type' => "update_weightslab",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            
            if($this->updations_model->updt_master_weightslab($form_data,$this->input->post('cid')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Weight-slab Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function master_pincodes()
    {
        $this->form_validation->set_rules('f_pincode', 'Pincode', 'required|trim|edit_unique_no_condition[tbl_pincodes.pincode.pincode_id]');
        $this->form_validation->set_rules('f_pin_city', 'City', 'required|trim|strtoupper');
        $this->form_validation->set_rules('f_pin_state', 'State', 'required|trim');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'pincode' => $this->input->post('f_pincode'),
                'pin_city' => $this->input->post('f_pin_city'),
                'pin_state' => $this->input->post('f_pin_state'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );

            $tracking_data = array(
                'activity_type' => "update_pincode",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            
            if($this->updations_model->updt_master_pincode($form_data,$this->input->post('cid')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Pincode Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function master_zones()
    {
        $this->form_validation->set_rules('f_source', 'Source city', 'required|trim|strtoupper');
        $this->form_validation->set_rules('f_destination_pin', 'Destination pin', 'required|trim|min_length[6]|max_length[6]');
        $this->form_validation->set_rules('f_zone', 'Zone', 'required|trim|min_length[1]|max_length[1]|regex_match[/^[A-Fa-f]*$/]|strtoupper');

        $this->form_validation->set_message('regex_match', 'The %s must have value betwen A to F.');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'source_city' => $this->input->post('f_source'),
                'destination_pin' => $this->input->post('f_destination_pin'),
                'zone' => $this->input->post('f_zone'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );

            $tracking_data = array(
                'activity_type' => "update_zone",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            
            if($this->updations_model->updt_master_zone($form_data,$this->input->post('cid')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Zone Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function b2b_master_zones()
    {
        $this->form_validation->set_rules('f_source', 'Source city', 'required|trim|strtoupper');
        $this->form_validation->set_rules('f_destination_pin', 'Destination pin', 'required|trim|min_length[6]|max_length[6]');
        $this->form_validation->set_rules('f_source_zone', 'Source Zone', 'required|trim|min_length[2]|max_length[3]|in_list[N1,N2,N3,N4,C1,C2,W1,W2,S1,S2,S3,S4,E1,E2,NE1,NE2]|strtoupper');
        $this->form_validation->set_rules('f_destination_zone', 'Destination Zone', 'required|trim|min_length[2]|max_length[3]|in_list[N1,N2,N3,N4,C1,C2,W1,W2,S1,S2,S3,S4,E1,E2,NE1,NE2]|strtoupper');
        $this->form_validation->set_message('regex_match', 'Must be a valid zone');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'source_city' => $this->input->post('f_source'),
                'destination_pin' => $this->input->post('f_destination_pin'),
                'src_zone'  => $this->input->post('f_source_zone'),
                'dst_zone'  => $this->input->post('f_destination_zone'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );

            $tracking_data = array(
                'activity_type' => "update_b2b_zone",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            
            if($this->updations_model->updt_master_b2b_zone($form_data,$this->input->post('cid')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'B2B Zone Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function update_user()
    {
        $this->form_validation->set_rules('fullname', 'Full Name', 'required|trim');
        $this->form_validation->set_rules('contact', 'Contact', 'required|trim|min_length[10]|max_length[10]');
        $this->form_validation->set_rules('alt_contact', 'Alt. Contact', 'trim');
        $this->form_validation->set_rules('business_name', 'Business Name', 'required|trim');
        $this->form_validation->set_rules('business_type', 'Business Type', 'required|trim');

        $this->form_validation->set_rules('billing_type', 'Billing Type', 'required|trim');
        $this->form_validation->set_rules('liability_amount', 'Liability Amount', 'required|trim');
        $this->form_validation->set_rules('ndd_charges', 'NDD Charges', 'required|trim');
        $this->form_validation->set_rules('insurance_charges', 'Insurance Charges', 'required|trim');
        $this->form_validation->set_rules('capping_amount', 'Capping Amount', 'required|trim');
        $this->form_validation->set_rules('restrict_amount', 'Restriction Amount', 'required|trim');
        $this->form_validation->set_rules('credit_period', 'Credit Period', 'required|trim');
        $this->form_validation->set_rules('token_key', 'API Token', 'required|trim');

        $this->form_validation->set_rules('codgap', 'COD Gap', 'required|trim');
        $this->form_validation->set_rules('billing_cycle_id', 'Billing Cycle', 'required|trim');
        $this->form_validation->set_rules('cod_cycle_id', 'COD Cycle', 'required|trim');
        $this->form_validation->set_rules('cod_fees_amt', 'COD Fees Amount', 'required|trim');
        $this->form_validation->set_rules('cod_fees_per', 'COD Fees %age', 'required|trim');
        $this->form_validation->set_rules('awb_charges', 'AWB Charges', 'trim');
        $this->form_validation->set_rules('fsc_rate', 'FSC %', 'trim');
        $this->form_validation->set_rules('surcharge_3', 'Surcharge Amount', 'trim');
        $this->form_validation->set_rules('surcharge_4', 'Surcharge %age', 'trim');

        $this->form_validation->set_rules('billing_address', 'Billing Address', 'required|trim');
        $this->form_validation->set_rules('billing_state', 'Billing State', 'required|trim');
        $this->form_validation->set_rules('beneficiary_name', 'Beneficiary Name', 'required|trim');
        $this->form_validation->set_rules('account_number', 'Account Number', 'required|trim');
        $this->form_validation->set_rules('ifsc_code', 'IFSC Code', 'required|trim');
        $this->form_validation->set_rules('bank_name', 'Bank Name', 'required|trim');
        $this->form_validation->set_rules('branch_name', 'Branch Name', 'required|trim');

        $this->form_validation->set_rules('kyc_pan', 'PAN', 'required|trim');
        $this->form_validation->set_rules('upload_file[kyc_pan_doc]', 'PAN Card', 'file_required');
        $this->form_validation->set_rules('kyc_gst_reg', 'GST Registration', 'required|trim');
        $this->form_validation->set_rules('kyc_doctype', 'Document Type', 'required|trim');
        $this->form_validation->set_rules('kyc_doc_number', 'KYC Doc Num', 'required|trim');
        $this->form_validation->set_rules('upload_file[kyc_document]', 'KYC Document', 'file_required');
        $this->form_validation->set_rules('tan_number', 'TAN Number', 'trim');

        $this->form_validation->set_rules('sales_poc_id', 'Sales POC', 'required|trim');
        $this->form_validation->set_rules('ops_poc_id', 'Ops POC', 'required|trim');
        $this->form_validation->set_rules('ndr_poc_id', 'NDR POC', 'required|trim');
        $this->form_validation->set_rules('pickup_poc_id', 'Pickup POC', 'required|trim');
        $this->form_validation->set_rules('finance_poc_id', 'Finance POC', 'required|trim');

        if($this->form_validation->run() == TRUE)
        {
            $uid = $this->input->post('uid');
            $form_data_user = array(
                // User details
                'fullname'          => $this->input->post('fullname'),
                'contact'           => $this->input->post('contact'),
                'alt_contact'       => $this->input->post('alt_contact'),
                'business_name'     => $this->input->post('business_name'),
                'business_type'     => $this->input->post('business_type'),
                // Account Setup
                'billing_type'      => $this->input->post('billing_type'),
                'liability_amount'  => $this->input->post('liability_amount'),
                'ndd_charges'       => $this->input->post('ndd_charges'),
                'insurance_charges' => $this->input->post('insurance_charges'),
                'capping_amount'    => $this->input->post('capping_amount'),
                'restrict_amount'   => $this->input->post('restrict_amount'),
                'credit_period'     => $this->input->post('credit_period'),
                'token_key'         => $this->input->post('token_key'),
                'referral_type'     => $this->input->post('referral_type'),
                'referred_by'       => $this->input->post('referred_by'),
                // billing setting
                // 'category_level' => $this->input->post('category_level'),
                'codgap'            => $this->input->post('codgap'),
                'billing_cycle_id'  => $this->input->post('billing_cycle_id'),
                'cod_cycle_id'      => $this->input->post('cod_cycle_id'),
                'cod_fees_amt'      => $this->input->post('cod_fees_amt'),
                'cod_fees_per'      => $this->input->post('cod_fees_per'),
                'awb_charges'       => $this->input->post('awb_charges'),
                'fsc_rate'          => $this->input->post('fsc_rate'),
                'surcharge_3'       => $this->input->post('surcharge_3'),
                'surcharge_4'       => $this->input->post('surcharge_4'),
                // billing details
                'billing_address'   => $this->input->post('billing_address'),
                'billing_state'     => $this->input->post('billing_state'),
                'beneficiary_name'  => $this->input->post('beneficiary_name'),
                'account_number'    => $this->input->post('account_number'),
                'ifsc_code'         => $this->input->post('ifsc_code'),
                'bank_name'         => $this->input->post('bank_name'),
                'branch_name'       => $this->input->post('branch_name'),
                // 'approved_on'       => date('Y-m-d H:i:s'),
                // 'approved_by'       => $this->session->userdata['user_session']['admin_username'],
                'updated_by'        => $this->session->userdata['user_session']['admin_username'],
            );

            $form_data_kyc = array(
                'kyc_pan' => $this->input->post('kyc_pan'),
                'kyc_gst_reg' => $this->input->post('kyc_gst_reg'),
                'kyc_doctype' => $this->input->post('kyc_doctype'),
                'kyc_doc_number' => $this->input->post('kyc_doc_number'),
                'tan_number' => $this->input->post('tan_number'),
                // 'pan_doc' => $this->input->post('kyc_pan_doc'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],
            );

            $form_data_poc = array(
                'sales_poc_id' => $this->input->post('sales_poc_id'),
                'ops_poc_id' => $this->input->post('ops_poc_id'),
                'ndr_poc_id' => $this->input->post('ndr_poc_id'),
                'pickup_poc_id' => $this->input->post('pickup_poc_id'),
                'finance_poc_id' => $this->input->post('finance_poc_id'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],
            );

            $tracking_data = array(
                'activity_type' => "update_user",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );

            $fileupload_updt['agreement_doc_updt'] = file_upload('agreement_doc_updt','agreements',$this->input->post('business_name'));
            $fileupload_updt['cancelled_cheque_updt'] = file_upload('cancelled_cheque_updt','cheques',$this->input->post('business_name'));
            $fileupload_updt['kyc_pan_doc_updt'] = file_upload('kyc_pan_doc_updt','pan',$this->input->post('business_name'));
            $fileupload_updt['kyc_document_updt'] = file_upload('kyc_document_updt','kyc',$this->input->post('business_name'));

            if($fileupload_updt['agreement_doc_updt']['response']=="Success")
            {
                $form_data_user['agreement_doc'] = $fileupload_updt['agreement_doc_updt']['message'];
            }
            if($fileupload_updt['cancelled_cheque_updt']['response']=="Success")
            {
                $form_data_user['cancelled_cheque'] = $fileupload_updt['cancelled_cheque_updt']['message'];
            }
            if($fileupload_updt['kyc_pan_doc_updt']['response']=="Success")
            {
                $form_data_kyc['kyc_pan_doc'] = $fileupload_updt['kyc_pan_doc_updt']['message'];
            }
            if($fileupload_updt['kyc_document_updt']['response']=="Success")
            {
                $form_data_kyc['kyc_document'] = $fileupload_updt['kyc_document_updt']['message'];
            }
            
            if($this->updations_model->user_update($form_data_user,$form_data_kyc,$form_data_poc,$uid) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'User Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            
        }
        echo json_encode($output);
    }

    public function convert_billingtype()
    {
        if(strtoupper($this->session->userdata('user_session')['role_name']) == 'SUPERADMIN' || $this->permissions_model->check_permission('convert_billingtype'))
        {
            $billingtype_data = array(
                'record_id' => $this->input->post()['record_id'],
                'new_status'      => $this->input->post()['new_status'],
                'codadjust'      => !empty($this->input->post()['new_status']=='postpaid')? 'yes':'no'
            );

            $tracking_data = array(
                'activity_type' => "convert_billingtype",
                'log_data'      => json_encode($billingtype_data),
                'admin_id'      => $this->session->userdata['user_session']['admin_username'],
            );

            if($this->updations_model->convert_billingtype($billingtype_data) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['message'] = 'Billing converted successfully.';
                $output['title'] = 'Congrats';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = 'Oops, you dont have access for this.';
        }
        echo json_encode($output);
    }

    public function users_complete_register()
    {
        $this->load->helper('file_upload');
        $this->form_validation->set_rules('fullname', 'Full Name', 'required|trim');
        $this->form_validation->set_rules('email_id', 'Email', 'required|trim');
        $this->form_validation->set_rules('contact', 'Contact', 'required|trim|min_length[10]|max_length[10]');
        $this->form_validation->set_rules('alt_contact', 'Alt. Contact', 'trim');
        $this->form_validation->set_rules('business_name', 'Business Name', 'required|trim');
        $this->form_validation->set_rules('business_type', 'Business Type', 'required|trim');

        $this->form_validation->set_rules('billing_type', 'Billing Type', 'required|trim');
        $this->form_validation->set_rules('liability_amount', 'Liability Amount', 'required|trim');
        $this->form_validation->set_rules('ndd_charges', 'NDD Charges', 'required|trim');
        $this->form_validation->set_rules('insurance_charges', 'Insurance Charges', 'required|trim');
        $this->form_validation->set_rules('capping_amount', 'Capping Amount', 'required|trim');
        $this->form_validation->set_rules('restrict_amount', 'Restriction Amount', 'required|trim');
        $this->form_validation->set_rules('credit_period', 'Credit Period', 'required|trim');
        if (empty($_FILES['agreement_doc']['name']))
            $this->form_validation->set_rules('agreement_doc', 'Agreement Doc', 'file_required');
        $this->form_validation->set_rules('referral_type', 'Referrer Type', 'required|trim');
        $this->form_validation->set_rules('referred_by', 'Reffered By', 'required|trim');

        $this->form_validation->set_rules('express_type[]', 'Express Type', 'required|trim');
        $this->form_validation->set_rules('weight_slab_id[]', 'Weight slab', 'required|trim');

        $this->form_validation->set_rules('category_level', 'Category', 'required|trim');
        $this->form_validation->set_rules('codgap', 'COD Gap', 'required|trim');
        $this->form_validation->set_rules('billing_cycle_id', 'Billing Cycle', 'required|trim');
        $this->form_validation->set_rules('cod_cycle_id', 'COD Cycle', 'required|trim');
        $this->form_validation->set_rules('cod_fees_amt', 'COD Fees Amount', 'required|trim');
        $this->form_validation->set_rules('cod_fees_per', 'COD Fees %age', 'required|trim');
        $this->form_validation->set_rules('awb_charges', 'AWB Charges', 'trim');
        $this->form_validation->set_rules('fsc_rate', 'FSC %', 'trim');
        $this->form_validation->set_rules('surcharge_3', 'Surcharge Amount', 'trim');
        $this->form_validation->set_rules('surcharge_4', 'Surcharge %age', 'trim');

        $this->form_validation->set_rules('billing_address', 'Billing Address', 'required|trim');
        $this->form_validation->set_rules('billing_state', 'Billing State', 'required|trim');
        $this->form_validation->set_rules('upload_file[cancelled_cheque]', 'Cheque', 'file_required');
        $this->form_validation->set_rules('beneficiary_name', 'Beneficiary Name', 'required|trim');
        $this->form_validation->set_rules('account_number', 'Account Number', 'required|trim');
        $this->form_validation->set_rules('ifsc_code', 'IFSC Code', 'required|trim');
        $this->form_validation->set_rules('bank_name', 'Bank Name', 'required|trim');
        $this->form_validation->set_rules('branch_name', 'Branch Name', 'required|trim');

        $this->form_validation->set_rules('kyc_pan', 'PAN', 'required|trim');
        $this->form_validation->set_rules('upload_file[kyc_pan_doc]', 'PAN Card', 'file_required');
        $this->form_validation->set_rules('kyc_gst_reg', 'GST Registration', 'required|trim');
        $this->form_validation->set_rules('kyc_doctype', 'Document Type', 'required|trim');
        $this->form_validation->set_rules('kyc_doc_number', 'KYC Doc Num', 'required|trim');
        $this->form_validation->set_rules('upload_file[kyc_document]', 'KYC Document', 'file_required');
        $this->form_validation->set_rules('tan_number', 'TAN Number', 'trim');

        $this->form_validation->set_rules('sales_poc_id', 'Sales POC', 'required|trim');
        $this->form_validation->set_rules('ops_poc_id', 'Ops POC', 'required|trim');
        $this->form_validation->set_rules('ndr_poc_id', 'NDR POC', 'required|trim');
        $this->form_validation->set_rules('pickup_poc_id', 'Pickup POC', 'required|trim');
        $this->form_validation->set_rules('finance_poc_id', 'Finance POC', 'required|trim');

        if($this->form_validation->run() == TRUE)
        {
            // $passkey = random_string('alpha', 6);
            $uid = $this->input->post('uid');
            $form_data_user = array(
                'fullname'          => $this->input->post('fullname'),
                'email_id'          => $this->input->post('email_id'),
                'contact'           => $this->input->post('contact'),
                'alt_contact'       => $this->input->post('alt_contact'),
                'mobile_verify'     => $this->input->post('mobile_verify'),
                'email_verify'      => '1',
                'password'          => password_hash($this->input->post('passkey'), PASSWORD_BCRYPT),
                'passkey'           => $this->input->post('passkey'),
                'token_key'         => strtoupper(random_string('alnum', 30)),
                'username'          => $this->input->post('email_id'),
                'business_name'     => $this->input->post('business_name'),
                'display_name'      => $this->input->post('business_name'),
                'business_type'     => $this->input->post('business_type'),
                'billing_type'      => $this->input->post('billing_type'),
                'codadjust'         => $this->input->post('codadjust'),
                'liability_amount'  => $this->input->post('liability_amount'),
                'ndd_charges'       => $this->input->post('ndd_charges'),
                'insurance_charges' => $this->input->post('insurance_charges'),
                'capping_amount'    => $this->input->post('capping_amount'),
                'restrict_amount'   => $this->input->post('restrict_amount'),
                'credit_period'     => $this->input->post('credit_period'),
                'agreement_doc'     => $this->input->post('agreement_doc'),
                'referral_type'     => $this->input->post('referral_type'),
                'referred_by'       => $this->input->post('referred_by'),
                'category_level'    => $this->input->post('category_level'),
                'codgap'            => $this->input->post('codgap'),
                'billing_cycle_id'  => $this->input->post('billing_cycle_id'),
                'cod_cycle_id'      => $this->input->post('cod_cycle_id'),
                'cod_fees_amt'      => $this->input->post('cod_fees_amt'),
                'cod_fees_per'      => $this->input->post('cod_fees_per'),
                'awb_charges'       => $this->input->post('awb_charges'),
                'fsc_rate'          => $this->input->post('fsc_rate'),
                'surcharge_3'       => $this->input->post('surcharge_3'),
                'surcharge_4'       => $this->input->post('surcharge_4'),
                'billing_address'   => $this->input->post('billing_address'),
                'billing_state'     => $this->input->post('billing_state'),
                'cancelled_cheque'  => $this->input->post('cancelled_cheque'),
                'beneficiary_name'  => $this->input->post('beneficiary_name'),
                'account_number'    => $this->input->post('account_number'),
                'ifsc_code'         => $this->input->post('ifsc_code'),
                'bank_name'         => $this->input->post('bank_name'),
                'branch_name'       => $this->input->post('branch_name'),
                'kyc_status'        => '1',
                'approved_on'       => date('Y-m-d H:i:s'),
                'approved_by'       => $this->session->userdata['user_session']['admin_username'],
                'account_status'    => '1',
                'updated_by'        => $this->session->userdata['user_session']['admin_username'],
            );

            $form_data_balances = array(
                'main_balance'  => '0',
                'promo_balance' => '0',
                'total_balance' => '0',
                'added_by'      => 'self',
                'updated_by'    => $this->session->userdata['user_session']['admin_username'],
            );

            $alertsdata = array(
                'fullname'      => $form_data_user['fullname'],
                'businessname'  => $form_data_user['business_name'],
                'username'      => $form_data_user['username'],
                'email'         => $form_data_user['email_id'],
                'number'        => $form_data_user['contact'],
                'password'      => $form_data_user['passkey']
            );

            $express_type = $this->input->post("express_type");
            $weightslab_id = $this->input->post("weight_slab_id");

            $cnt_exp    = count($express_type);
            $cnt_wslab  = count($weightslab_id);

            if($cnt_exp > 0 && $cnt_wslab > 0 && $cnt_wslab==$cnt_exp)
            {
                for($i=0; $i<$cnt_exp; $i++)
                {
                    $form_data_wtslab[] = array(
                        'express'       => $express_type[$i],
                        'weightslab_id' => $weightslab_id[$i],
                        'updated_by'    => $this->session->userdata['user_session']['admin_username']
                    );
                }
            }

            $form_data_kyc = array(
                'kyc_pan'           => $this->input->post('kyc_pan'),
                'kyc_gst_reg'       => $this->input->post('kyc_gst_reg'),
                'kyc_doctype'       => $this->input->post('kyc_doctype'),
                'kyc_doc_number'    => $this->input->post('kyc_doc_number'),
                'tan_number'        => $this->input->post('tan_number'),
                'added_by'          => 'self',
                'updated_by'        => $this->session->userdata['user_session']['admin_username'],
            );

            // $form_data_notification = array(
            //     'transitpartner_name' => $this->input->post('transitpartner_name'),
            //     'transitpartner_description' => $this->input->post('transitpartner_description'),
            //     'added_by' => 'self',
            //     'updated_by' => $this->session->userdata['user_session']['admin_username'],
            // );

            $form_data_poc = array(
                'sales_poc_id'      => $this->input->post('sales_poc_id'),
                'ops_poc_id'        => $this->input->post('ops_poc_id'),
                'ndr_poc_id'        => $this->input->post('ndr_poc_id'),
                'pickup_poc_id'     => $this->input->post('pickup_poc_id'),
                'finance_poc_id'    => $this->input->post('finance_poc_id'),
                'added_by'          => 'self',
                'updated_by'        => $this->session->userdata['user_session']['admin_username'],
            );

            $users_temp = array(
                'account_status'    => '0'
            );

            $tracking_data = array(
                'activity_type' => "add_user",
                'log_data'      => json_encode($this->input->post()),
                'admin_id'      => $this->session->userdata['user_session']['admin_username'],
            );            

            $fileupload_res['agreement_doc'] = file_upload('agreement_doc','agreements',$this->input->post('business_name'));
            $fileupload_res['cancelled_cheque'] = file_upload('cancelled_cheque','cheques',$this->input->post('business_name'));
            $fileupload_res['kyc_pan_doc'] = file_upload('kyc_pan_doc','pan',$this->input->post('business_name'));
            $fileupload_res['kyc_document'] = file_upload('kyc_document','kyc',$this->input->post('business_name'));
            
            // $form_data_user['agreement_doc'] = file_upload('agreement_doc','agreements',$this->input->post('business_name'));

            // print_r($fileupload_res['agreement_doc']);

            if($fileupload_res['agreement_doc']['response']=="Success" && $fileupload_res['cancelled_cheque']['response']=="Success" && $fileupload_res['kyc_pan_doc']['response']=="Success" && $fileupload_res['kyc_document']['response']=="Success")
            {
                $form_data_user['agreement_doc'] = $fileupload_res['agreement_doc']['message'];
                $form_data_user['cancelled_cheque'] = $fileupload_res['cancelled_cheque']['message'];
                $form_data_kyc['kyc_pan_doc'] = $fileupload_res['kyc_pan_doc']['message'];
                $form_data_kyc['kyc_document'] = $fileupload_res['kyc_document']['message'];


                if($this->updations_model->ins_user_completeregis($form_data_user,$form_data_wtslab,$form_data_balances,$form_data_kyc,$form_data_poc,$uid, $users_temp) && $this->insertions_model->activity_logs($tracking_data))
                {
                    $output['title'] = 'Congrats';
                    $output['message'] = 'User Registered & Approved Successfully.';
                    $this->sendalerts_model->trigger_alerts('user_complete_registration',$alertsdata);
                }
                else
                {
                    $output['error'] = true;
                    $output['title'] = 'Error';
                    $output['message'] = 'Some Error occurred, Try again.';
                }
            }
            else
            {
                $message="";
                $output['error'] = true;
                $output['title'] = 'Error';

                $message .= $fileupload_res['agreement_doc']['response']=="Error" ? $fileupload_res['agreement_doc']['message'] : "";
                $message .= $fileupload_res['cancelled_cheque']['response']=="Error" ? $fileupload_res['cancelled_cheque']['message']:'';
                $message .= $fileupload_res['kyc_pan_doc']['response']=="Error" ? $fileupload_res['kyc_pan_doc']['message']:'';
                $message .= $fileupload_res['kyc_document']['response']=="Error" ? $fileupload_res['kyc_document']['message']:'';

                $output['message'] = $message;
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function set_permissions()
    {
        if($this->input->post('permission_type') == "role_based_permission")
            $this->form_validation->set_rules('roles_id', 'Role', 'required|trim');
        else if($this->input->post('permission_type') == "custom_based_permission")
            $this->form_validation->set_rules('admin_id', 'User', 'required|trim');

        $this->form_validation->set_rules('modules_id[]', 'Permissions', 'required|trim');
            
        if($this->form_validation->run() == true)
        {
            // $form_data = array(
            //     'roles_id' => $this->input->post('roles_id'),
            //     'modules_id' => $this->input->post('modules_id'),
            //     'updated_by' => $this->session->userdata['user_session']['admin_username'],                
            // );

            $form_data = $this->input->post();
            $form_data['updated_by'] = $this->session->userdata['user_session']['admin_username'];

            $tracking_data = array(
                'activity_type' => "set_role_permission",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );  

            if($this->permissions_model->insert_update($form_data) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Permission granted successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
        }
        echo json_encode($output);
    }

    public function update_warehouse()
    {            
        $this->form_validation->set_rules('updt_addressee', 'Addressee', 'required|regex_match[/^([a-zA-Z0-9.]|\s)+$/]|trim|min_length[3]');
        $this->form_validation->set_rules('updt_full_address', 'Full Address', 'required|trim|min_length[5]');
        $this->form_validation->set_rules('updt_phone', 'Phone', 'required|trim|min_length[10]|max_length[12]|numeric');
        $this->form_validation->set_rules('updt_pincode', 'Pincode', 'required|trim|regex_match[/^(\d{6})$/]');
        $this->form_validation->set_rules('updt_address_city', 'Address City', 'required|trim');
        $this->form_validation->set_rules('updt_address_state', 'Address State', 'required|trim');            
        $this->form_validation->set_rules('updt_address_id', 'Address Id', 'required|trim');            
        $this->form_validation->set_rules('updt_address_title', 'Address Title', 'required|trim');            

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'address_title' => $this->input->post('updt_address_title'),
                'addressee'     => $this->input->post('updt_addressee'),
                'full_address'  => $this->input->post('updt_full_address'),
                'phone'         => $this->input->post('updt_phone'),
                'pincode'       => $this->input->post('updt_pincode'),
                'address_city'  => $this->input->post('updt_address_city'),
                'address_state' => $this->input->post('updt_address_state'),
                'updated_by'    => $this->session->userdata['user_session']['admin_username']
            );

            $tracking_data = array(
                'activity_type' => "update_warehouse_by_admin",
                'log_data'      => json_encode($this->input->post()),
                'admin_id'      => $this->session->userdata['user_session']['admin_username'],
            );

            if($this->updations_model->update_warehouse($form_data,$this->input->post('updt_address_id')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title']    = 'Congrats';
                $output['message']  = 'Address updated successfully.';
            }
            else
            {
                $output['error']    = true;
                $output['title']    = 'Error';
                $output['message']  = 'Some Error occurred, Try again.';
            }
        }
        else
        {
            $output['error']    = true;
            $output['title']    = 'Error';
            $output['message']  = validation_errors();
        }
        echo json_encode($output);
    }

    public function register_warehouse()
    {
        $this->load->model('Warehousemanagement_model','warehouse');
        $return_data = $this->warehouse->Registerwarehouse($this->input->post());
        if($return_data)
        {
            $output['message']      = 'Address Id '.$this->input->post('address_id');
            $output['response_data']  = $return_data;
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = 'Some Error occurred, Try again.';
        }
        echo json_encode($output);
    }

    public function users_modules()
    {
        $this->form_validation->set_rules('module_parent', 'Parent', 'required|trim');
        $this->form_validation->set_rules('module_name', 'Module Name', 'required|trim|edit_unique_no_condition[userpanel_modules.module_name.user_module_id]');
        $this->form_validation->set_rules('module_route', 'Route', 'required|trim|edit_unique_no_condition[userpanel_modules.module_route.user_module_id]');
        $this->form_validation->set_rules('module_description', 'Description', 'trim');

        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'parent_menu' => $this->input->post('module_parent'),
                'module_name' => $this->input->post('module_name'),
                'module_route' => $this->input->post('module_route'),
                'module_description' => $this->input->post('module_description'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );

            $tracking_data = array(
                'activity_type' => "update_users_module",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            
            if($this->updations_model->updt_users_module($form_data,$this->input->post('cid')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Users Module Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    //logic for single update or reject weight requests
    public function single_update_request()
	{
        $result_data = $this->db->select('uwt_id,user_id,waybill_number,request_weight')->where('uwt_id',$this->input->post('id'))->where('request_status','0')->get('users_weight_update')->row();
        if(!empty($result_data))
        {
            $title = 'Congrats';
            $error = false;
            $request_data[] = [
                "waybill_number" => $result_data->waybill_number,
                "billing_weight" => $result_data->request_weight
            ];

            if($this->input->post('process_type') == 'approve'){
                list($success_count, $error_records) = $this->billing_model->update_weight_request($request_data);

                if(!empty($error_records) && count($error_records) > 0){
                    $title = 'Error';
                    $error = true;
                    $user_request_id = '';
                    $trackingData = [
                        'uwt_id' => $result_data->uwt_id,
                        'waybill_number' => $result_data->waybill_number,
                        'request_weight' => $result_data->request_weight,
                        'message'   => $error_records[0]['error']
                    ];
                }else{
                    $user_request_id = $result_data->uwt_id;
                    $trackingData = [
                        'uwt_id' => $result_data->uwt_id,
                        'waybill_number' => $result_data->waybill_number,
                        'request_weight' => $result_data->request_weight,
                        'message'   => "Request Approved Successfully."
                    ];
                }
            }else{
                $user_request_id = $result_data->uwt_id;
                $trackingData = [
                    'uwt_id' => $result_data->uwt_id,
                    'waybill_number' => $result_data->waybill_number,
                    'request_weight' => $result_data->request_weight,
                    'message'   => "Request Rejected Successfully."
                ];
            }
            $tracking_data = array(
                'activity_type' => ($this->input->post('process_type') == 'approve')?'users_weight_update_request':'users_weight_reject_request',
                'log_data' => json_encode($trackingData),//json_encode([$this->input->post(),$request_data]),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );

            if($user_request_id !=''){
                $this->updations_model->update_request($data=['request_status' => ($this->input->post('process_type') == 'approve')?'1':'2'],$where=['uwt_id' => $this->input->post('id')],'users_weight_update');
            }
            if($this->insertions_model->activity_logs($tracking_data))
            {
                $output['error'] = $error;
                $output['title'] = $title;//'Congrats';
                $output['message'] = $trackingData['message'];//($this->input->post('process_type') == 'approve')?'Request Approved Successfully.':'Request Rejected Successfully.';
            }
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = 'Some Error occurred, Try again.';
        }
        echo json_encode($output);

	}

    // logic for bulk checkbox update or reject
    public function bulk_approve_update_request()
	{
        if(!empty($this->input->post('order_id')) && count($this->input->post('order_id'))>0)
        {
			$order_cnt = $order_cnt_err = 0;
            $prepaired_data = $trackingData = [];
            //$update_status = "";
			foreach ($this->input->post('order_id') as $order)
			{ 
                //Getting User Request Weight
                $result_data = $this->db->select('uwt_id,user_id,waybill_number,request_weight')->where('uwt_id',$order)->where('request_status','0')->get('users_weight_update')->row();

				if(!empty($result_data))
				{
					//$order_cnt++;
                    // Preapare Request Data for billing weight
                    $prepaired_data[] = [
                        "waybill_number" => $result_data->waybill_number,
                        "billing_weight" => $result_data->request_weight
                    ];
                    

                    $update_status = ($this->input->post('process_type') == "approve")?'1':'2';

                    $user_request_id = "";
                    if($this->input->post('process_type') == "approve")
                    {
                        list($success_count, $error_records) = $this->billing_model->update_weight_request($prepaired_data);
                        
                        //check if return any error from shipment billing
                        if(!empty($error_records) && count($error_records) > 0){
                            if(!in_array($result_data->waybill_number,array_column($error_records, 'waybill'))){
                                $order_cnt++;
                                $user_request_id = $result_data->uwt_id;
                                $trackingData[] = [
                                    'uwt_id' => $result_data->uwt_id,
                                    'waybill_number' => $result_data->waybill_number,
                                    'request_weight' => $result_data->request_weight,
                                    'message'   => "Success"
                                ];
                            }else{
                                $order_cnt_err++;
                                $user_request_id = "";
                                $trackingData[] = [
                                    'uwt_id' => $result_data->uwt_id,
                                    'waybill_number' => $result_data->waybill_number,
                                    'request_weight' => $result_data->request_weight,
                                    'message'   => $error_records[0]['error']
                                ];
                            }
                        }else{
                            $order_cnt++;
                            $user_request_id = $result_data->uwt_id;
                            $trackingData[] = [
                                'uwt_id' => $result_data->uwt_id,
                                'waybill_number' => $result_data->waybill_number,
                                'request_weight' => $result_data->request_weight,
                                'message'   => "Success"
                            ];
                        }
                    }else{
                        $order_cnt++;
                        $user_request_id = $result_data->uwt_id;
                        $trackingData[] = [
                            'uwt_id' => $result_data->uwt_id,
                            'waybill_number' => $result_data->waybill_number,
                            'request_weight' => $result_data->request_weight,
                            'message'   => "Success"
                        ];
                    }

                    if(!empty($user_request_id)){
                        //$this->updations_model->update_request($data = ["request_status" => $update_status],$where = ['uwt_id' => $result_data->uwt_id],'users_weight_update');
                        $this->updations_model->update_request($data = ["request_status" => $update_status],$where = ['uwt_id' => $user_request_id],'users_weight_update');
                    }

                }
			}

            $trackingData['action'] = $this->input->post('process_type');

			$tracking_data = array(
				'activity_type' => ($this->input->post('process_type') == "approve")?'users_weight_update_request_bulk':'users_weight_reject_request_bulk',
                'log_data' => json_encode($trackingData),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
			);

			if($this->insertions_model->activity_logs($tracking_data))
			{
				$output['title'] = 'Success';
				$output['message'] = $order_cnt. ' Request Update '. $order_cnt_err. ' error ' ;
			}
			else
			{
				$output['error'] = true;
				$output['title'] = 'Error';
				$output['message'] = 'Some Error occurred, Try again.';
			}
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = "Selected data not valid! Please select valid data";
        }

		echo json_encode($output);
	}

    // validate excel bulk request weight update when found any error then showing preview
    public function bulk_request_updates()
    {
        $this->form_validation->set_rules('requestweight_file', 'Excel File', 'file_required|trim');
        $output = [];
        if($this->form_validation->run() == TRUE)
        {
            // $fileupload_res['requestweight'] = excel_upload('requestweight_file','request_weight');

            $uploadFileName = "Requestweight_".date('dMy_His').'-'.random_string('alnum', 25);
            $fileupload_res['requestweight'] = excel_upload('requestweight_file','request_weight',$uploadFileName);
            $file = FCPATH . '/assets/uploads/request_weight/' . $uploadFileName . ".csv";
            $blobFilePath = $uploadFileName . '.csv';
            file_upload_blob('requestweight', $blobFilePath,$_FILES['requestweight_file']['tmp_name']);

            if($fileupload_res['requestweight']['title']=="Success")
            {
                $error_data = $form_data = [];
                $error_preview ='';
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($fileupload_res['requestweight']['message']);
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                $object = $reader->load($fileupload_res['requestweight']['message']);
                foreach($object->getWorksheetIterator() as $worksheet)
                {
                    $highestRow = $worksheet->getHighestDataRow();
                    for($row=2; $row<=$highestRow; $row++)
                    {
                        $request_id      = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                        $user_name       = strtoupper($worksheet->getCellByColumnAndRow(2, $row)->getValue());
                        $waybill_number  = $worksheet->getCellByColumnAndRow(3, $row)->getValue();
                        $request_weight  = $worksheet->getCellByColumnAndRow(4, $row)->getValue();
                        $action  = strtoupper($worksheet->getCellByColumnAndRow(5, $row)->getValue());
                        //Getting User Request Weight
                        $result_data = $this->db->select('uwt_id,user_id,waybill_number,request_weight,request_status')->where('uwt_id',$request_id)->where('waybill_number',$waybill_number)->get('users_weight_update')->row();
                        //echo " check data "."<pre>"; print_r($result_data);
                        if(!empty($result_data)){
                            if($result_data->request_status != 0){
                                $error_data[] = array(
                                    'uwt_id' => $request_id,
                                    'username' => $user_name,
                                    'waybill_number' => $waybill_number,
                                    'billing_weight' => $request_weight,
                                    'request_status' => $action,
                                    'error'          => "<span class='text-danger'><b>This record has been already ". ($result_data->request_status=='2'? 'Rejected ':($result_data->request_status=='1'? 'Approved ':'')) ."at row # ".$row."</b></span>",

                                );
                            }else if($result_data->request_status == 0 && $action == 'A' && (!round((float)$request_weight, 2) || !round((float)$request_weight, 1) || is_numeric($request_weight) != 1) ){
                                $error_data[] = array(
                                    'uwt_id' => $request_id,
                                    'username' => $user_name,
                                    'waybill_number' => $waybill_number,
                                    'billing_weight' => $request_weight,
                                    'request_status' => $action,
                                    'error'          => "<span class='text-danger'><b>Request weight is incorrect format at row # ".$row."</b></span>",

                                );

                            }else if($result_data->request_status == 0 && ($action == 'R' || $action == 'A')){

                                $form_data[] = array(
                                    'uwt_id' => $request_id,
                                    'waybill_number' => $waybill_number,
                                    'billing_weight' => (($action == "A")?$request_weight:$result_data->request_weight),
                                    'request_status' => (($action == "R")?"2":($action == "A"?"1":"0")),
                                    'updated_by'     => $this->session->userdata['user_session']['admin_username']
                                );
                            }else{
                                $error_data[] = array(
                                    'uwt_id' => $request_id,
                                    'username' => $user_name,
                                    'waybill_number' => $waybill_number,
                                    'billing_weight' => $request_weight,
                                    'request_status' => $action,
                                    'error'          => "<span class='text-danger'><b>Only Allowed Status R (Reject) or A (Approve) at row # ".$row."</b></span>",

                                );
                            }

                        }else{
                            $error_data[] = array(
                                'uwt_id' => $request_id,
                                'username' => $user_name,
                                'waybill_number' => $waybill_number,
                                'billing_weight' => $request_weight,
                                'request_status' => $action,
                                'error'          => "<span class='text-danger'><b>This record does not match in our records at row # ".$row."</b></span>",
                            );

                        }

                    }
                }

                if(!empty($error_data))
                {
                    $error_preview .= '<table id="datatable-preview" class="table table-vcenter table-condensed table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">Request Id #</th>
                            <th class="text-center">Username #</th>
                            <th class="text-center">AWB #</th>
                            <th class="text-center">Request Wt #</th>
                            <th class="text-center">Request Status #</th>
                            <th class="text-center">Error #</th>
                        </tr>
                    </thead>
                    <tbody>
                        <h5><b><span class="text-danger">Found: '.count($error_data). ' errors</span>.</b></h5>';

                        foreach ($error_data as $errors)
                        {
                            $error_preview .='<tr>
                                <td class="text-center">'. $errors['uwt_id'].'</td>
                                <td class="text-center">'. $errors['username'].'</td>
                                <td class="text-center">'. $errors['waybill_number'].'</td>
                                <td class="text-center">'. $errors['billing_weight'].'</td>
                                <td class="text-center">'. $errors['request_status'].'</td>
                                <td class="text-center">'. $errors['error'].'</td>
                            </tr>';
                        }

                        $error_preview .='</tbody></table><form method="post" id="form_weightupdateanyway" style="display:none;" onsubmit="return false;">';
                        foreach ($form_data as $data => $data_value)
                        {
                            $error_preview .='<input type="hidden" name="data['.$data.'][waybill_number]" value="'.$data_value['waybill_number'].'" />
                            <input type="hidden" name="data['.$data.'][billing_weight]" value="'.$data_value['billing_weight'].'" />
                            <input type="hidden" name="data['.$data.'][uwt_id]" value="'.$data_value['uwt_id'].'" />
                            <input type="hidden" name="data['.$data.'][updated_by]" value="'.$data_value['updated_by'].'" />
                            <input type="hidden" name="data['.$data.'][request_status]" value="'.$data_value['request_status'].'" />';
                        }
                        $error_preview .='<input type="hidden" name="tracking_data" value="'.$fileupload_res['requestweight']['message'].'"/></form>';
                        $error_preview .='<div class="col-md-12" style="margin-top:15px;">
                        <button type="button" onclick="reupload();" class="btn btn-sm btn-primary" id="reuploadbtn"><i class="fa fa-repeat"></i> Reupload</button>
                        <button type="button" onclick="saveanyway();" class="btn btn-sm btn-success" id="continuebtn"><i class="fa fa-save"></i> Skip Error(s) & Continue</button></div>';

                    echo json_encode(array('message' => $error_preview), JSON_HEX_QUOT | JSON_HEX_TAG);
                }else if(!empty($form_data)){
                    $data['tracking_data'] = $fileupload_res['requestweight']['message'];
                    $data['data'] = $form_data;
                    $this->excelUpdateRequestWeight($data);
                }
                //pathinfo($fileupload_res['requestweight']['message'], PATHINFO_BASENAME).'": '.$e->getMessage();
                @unlink($file);
            }
            else{
                @unlink($file);
                $output = json_encode($fileupload_res['requestweight']);
            }
                
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);

        }
    }

    //update excel request
    public function excelUpdateRequestWeight($form_data = null){
        //$set_data = $this->input->post()?$this->input->post():$form_data;

        $excel_data = $this->input->post()?$this->input->post():$form_data;
        $set_data['tracking_data'] = $excel_data['tracking_data'];
        
        $set_data['data'] = [];
        if(!empty($excel_data['data'])){
            foreach($excel_data['data'] as $key => $value){
                if(!in_array($value['uwt_id'],array_column($set_data['data'], 'uwt_id'))){
                    $set_data['data'][] = $value;
                }
            }
        }
        
        if(!empty($set_data['data'])){

            $error_records = [];

            //Remove rejected data sending from billing weight model
            $billing_record = $set_data['data'];
            foreach($billing_record as $bkey => $b_data){
                if($b_data['request_status'] == 2){
                    unset($billing_record[$bkey]);
                }
            }

            if(count($billing_record)>0){
                list($success_count, $error_records) = $this->billing_model->update_weight_request($billing_record);
            }

            //check if error data exist then remove error data from prepair updation Data
            if(!empty($error_records) && count($error_records) > 0){
                foreach($set_data['data'] as $key => $exact_data){
                    if(in_array($exact_data['waybill_number'],array_column($error_records, 'waybill_number'))){
                        unset($set_data['data'][$key]);
                    }
                }

            }

            // bulk update for approve and reject
            $update_con = $set_data['data'];
            array_walk( $update_con, function(&$a){
                    unset($a['billing_weight']);
                    unset($a['waybill_number']);
                    //unset($a['uwt_id']);
                    unset($a['updated_by']);
            });

            //check if data is greater then zero for approval
            if(count($update_con)>0){
                //$success_count = count($update_con);
                $result = $this->db->update_batch('users_weight_update',$update_con, 'uwt_id');
                $success_count = $result;
            }

            $tracking_data = array(
                'activity_type' => "excel_weight_update_request",
                'log_data' => json_encode($set_data['tracking_data']."<br />\\n\\nUpdated ".$success_count." Records.<br />\\n\\nError Logs".json_encode($error_records)),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );
            // print_r($tracking_data);
            if($this->insertions_model->activity_logs($tracking_data))
            {
                $output['updated'] = $success_count;
                $output['errors'] = $error_records;
                $output['title'] = 'Success';
                $output['action'] = 'RequestWeightUpdate';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = 'No data available for approval or rejection, Try again.';
        }
        echo json_encode($output);
    }

    public function update_user_new()
    {
        // $columvalue = preg_replace('/\s+/', ' ', $this->input->post('columvalue'));
        $postData = $this->input->post();
        $where = $postData['userid'];
        unset($postData['userid']);

        $tracking_data = array(
            'activity_type' => "update_user",
            'log_data'      => json_encode($this->input->post()),
            'admin_id'      => $this->session->userdata['user_session']['admin_username'],
        );
        $kyc_userid = $this->db->select('user_id')->where('user_id',$where)->get('users_kyc')->row_array();


        if(!empty($postData['adhaar_number']) AND empty($kyc_userid))
        {
            $update_status = ($this->db->insert('users_kyc',['adhaar_number' => $postData['adhaar_number'],'adhaar_status' => '1','user_id' => $where,'added_by' => $this->session->userdata['user_session']['admin_username'],'updated_by' => $this->session->userdata['user_session']['admin_username']]) and  $this->updations_model->update('users',['user_id' => $where],['kyc_status' => '1']));  
        }
        else if(!empty($postData['adhaar_number']) AND !empty($kyc_userid))
        {
            $update_status = ($this->updations_model->update('users_kyc',['user_id' => $where],['adhaar_number' => $postData['adhaar_number'],'adhaar_status' => '1']) and  $this->updations_model->update('users',['user_id' => $where],['kyc_status' => '1']));  
        }
        else if(!empty($postData['kyc_doc_number']))
        {
            $update_status = (!empty($postData['kyc_doc_number']) AND !empty($kyc_userid)) ? $this->updations_model->update('users_kyc',['user_id' => $where],['kyc_pan' => substr($postData['kyc_doc_number'],2,10),'kyc_doc_number' => $postData['kyc_doc_number'],'kyc_gst_reg' => 'yes']) &&  $this->updations_model->update('users',['user_id' => $where],['kyc_status' => '1']) : '0';
        }
        else
        {
            $update_status = isset($postData['tan_number']) ? $this->updations_model->update('users_kyc',['user_id' => $where],$postData) : $this->updations_model->update('users',['user_id' => $where],$postData);
        }
        
        if($update_status && $this->insertions_model->activity_logs($tracking_data))
        {
            $output['title'] = 'Congrats';
            $output['message'] = 'User profile Updated Successfully.';
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = 'Some Error occurred, Try again.';
        }
        echo json_encode($output);
    }
    
    public function update_users_reason()
    {
        $this->form_validation->set_rules('account_status_check', 'status', 'required|trim');
        $this->form_validation->set_rules('reasons', 'reasons', 'trim');
        if($this->form_validation->run() == TRUE)
        {
            $where=$this->input->post("tmp_userid");
            $reasons_remark=$this->input->post("reasons");
            $main_user_id=$this->db->select('main_user_id,email_id')->where('tmp_userid',$where)->get('users_temp')->row_array();
            $record_id=$main_user_id['main_user_id'];
            $new_status='2';

            if(!empty($reasons_remark))
            {
                $this->statusupdate_model->status_master_user($record_id,$new_status);
                $model_form_data = array(
                    'account_status' => $this->input->post("account_status_check"),
                    'dispose_remark' => $reasons_remark
                );     
            }
            else
            {
                $model_form_data = array(
                    'account_status' => $this->input->post("account_status_check")
                );
            }

            $reasons_user =array(
                'tmp_userid' => $where,
                'Dispose_update' => $model_form_data,
                'user_id' => $record_id,
                'email_id' => $main_user_id['email_id']
            );
            $tracking_data = array(
                'activity_type' => "reason_update_user",
                'log_data'      => json_encode($reasons_user),
                'admin_id'      => $this->session->userdata['user_session']['admin_username'],
            );
            
            if($this->updations_model->update('users_temp',['tmp_userid' => $where],$model_form_data) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Dispose Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }

        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
        }
        echo json_encode($output);  
    }
    
    public function update_referreds_source()
    {
        // _print_r($this->input->post(),1);    
        $this->form_validation->set_rules('referred_source', 'Referred Source', 'required|trim');
        $this->form_validation->set_rules('referred_by', 'Referred by', 'trim');
        if($this->form_validation->run() == TRUE)
        {
            $where=$this->input->post("referred_userid");
            if ($this->input->post("referred_source") == "Internal") 
            {
                $form_data = array(
                    'referral_type' =>$this->input->post("referred_source"),
                    'referred_by' => $this->input->post("referred_by")

                );
            }
            elseif ($this->input->post("referred_source") == "Affiliate") 
            {
                $form_data = array(
                    'referral_type' =>$this->input->post("referred_source"),
                    'referred_by' =>$this->input->post("referred_by_Affiliate")

                );
            }
            else
            {
                $form_data = array(
                    'referral_type' => !empty($this->input->post("referred_source") !="Others")?$this->input->post("referred_source"):$this->input->post("Others"),
                    'referred_by' => '0'
                );
            }
            
            $tracking_form_data = array(
                'user_id' => $this->input->post("referred_userid"),
                'form_data'      => $form_data,
            );

            $tracking_data = array(
                'activity_type' => "referred_source_update",
                'log_data'      => json_encode($tracking_form_data),
                'admin_id'      => $this->session->userdata['user_session']['admin_username'],
            );
            if($this->updations_model->update('users',['user_id' => $where],$form_data) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Referred Source Update Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
        }
        echo json_encode($output);
        
    }
    
    public function bulk_status_update()
    {
        // $fileupload_res['statusupdate'] = excel_upload('awbs_file','status_update');

        $uploadFileName = "Statusupdate_".date('dMy_His').'-'.random_string('alnum', 25);
        $fileupload_res['statusupdate'] = excel_upload('awbs_file','status_update',$uploadFileName);
        $file = FCPATH . '/assets/uploads/status_update/' . $uploadFileName . ".csv";
        $blobFilePath = $uploadFileName . '.csv';
        file_upload_blob('statusupdate', $blobFilePath, $_FILES['awbs_file']['tmp_name']);

        if($fileupload_res['statusupdate']['title']=="Success")
        {
            list($form_data, $error_data) = read_exceldata_status($fileupload_res['statusupdate']['message']);
            $error_preview ='';
            if(!empty($error_data))
            {
                $error_preview .= '<table id="datatable-preview" class="table table-vcenter table-condensed table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Waybill #</th>
                        <th class="text-center">Status Update</th>
                        <th class="text-center">Error</th>
                    </tr>
                </thead>
                <tbody>
                    <h5><b><span class="text-danger">Found: '.count($error_data). ' errors</span>.</b></h5>';

                    foreach ($error_data as $errors)
                    {
                        $error_preview .='<tr>
                            <td class="text-center">'. $errors['waybill_number'].'</td>
                            <td class="text-center">'. $errors['user_status'].'</td>
                            <td class="text-center">'. $errors['error'].'</td>
                        </tr>';
                    }

                    $error_preview .='</tbody></table><form method="post" id="form_statusupdateanyway" style="display:none;" onsubmit="return false;">';
                    foreach ($form_data as $data => $data_value)
                    {
                        $error_preview .='<input type="hidden" name="data['.$data.'][waybill_number]" value="'.$data_value['waybill_number'].'" />
                        <input type="hidden" name="data['.$data.'][user_status]" value="'.$data_value['user_status'].'" />';
                    }
                    $error_preview .='<input type="hidden" name="tracking_data" value="'.$fileupload_res['statusupdate']['message'].'"/></form>';
                    $error_preview .='<div class="col-md-12" style="margin-top:15px;">
                    <button type="button" onclick="reupload();" class="btn btn-sm btn-primary" id="reupload"><i class="fa fa-repeat"></i> Reupload</button>
                    <button type="button" onclick="saveanyway();" class="btn btn-sm btn-success" id="continue"><i class="fa fa-save"></i> Skip Error(s) & Continue</button></div>';

                echo json_encode(array('message' => $error_preview), JSON_HEX_QUOT | JSON_HEX_TAG);
            }
            else
            {   if (!empty($form_data))
                {
                    $tracking_data = array(
                        'activity_type' => "Bulk Status Update",
                        'log_data'      => json_encode($form_data),
                        'admin_id'      => $this->session->userdata['user_session']['admin_username'],
                    );
                    if($this->db->update_batch('shipments', $form_data, 'waybill_number') && $this->insertions_model->activity_logs($tracking_data))
                    {
                        $output['title'] = 'Congrats';
                        $output['message'] = 'Status Update Successfully.';
                    }
                    else
                    {
                        $output['error'] = true;
                        $output['title'] = 'Error';
                        $output['message'] = 'Waybill number and status already updated. Try again';
                    }
                    @unlink($file);
                    // echo json_encode($output);
                } 
                else
                {
                    $output['error'] = true;
                    $output['title'] = 'Error';
                    $output['message'] = 'No request processed due to invalid data.'; 
                }
                @unlink($file);
                echo json_encode($output);   
            }
        }
        else{
            @unlink($file);
            echo json_encode($fileupload_res['statusupdate']);
        }
    }

    /* For Update weight excluding errors */
    public function status_update()
    {
        if(!empty($this->input->post('data')))
        { 
            $form_data=$this->input->post('data');
            $tracking_data = array(
                'activity_type' => "bulk_status_update",
                'log_data'      => json_encode($form_data),
                'admin_id'      => $this->session->userdata['user_session']['admin_username'],
            );
            if($this->db->update_batch('shipments', $form_data, 'waybill_number') && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Status Update Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Waybill number and status already updated. Try again';
            }
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = 'No request processed due to invalid data.'; 
        }
        echo json_encode($output);  
    }
    public function master_ticketcategory()
    {
                                                                                            // edit_unique[administrator_roles.role_name.admin_role_id.role_status]
        $this->form_validation->set_rules('category_name', 'Ticket Category', 'required|trim|edit_unique[users_ticket_category.ticket_category.ticket_category_id.ticket_category_status]');
        $this->form_validation->set_rules('estimated_time', 'Estimated Time', 'required|numeric');
        if($this->form_validation->run() == TRUE)
        {
            
            #ticket_category_id, ticket_category, ticket_category_status, added_on, added_by, updated_on, updated_by
            $form_data = array(
                'estimated_tat'  =>  $this->input->post('estimated_time'),
                'parent_category' => $this->input->post('parent_category'),
                'ticket_category' => $this->input->post('category_name'),
                // 'added_by' => $this->session->userdata['user_session']['admin_username'],  
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );
            $tracking_data = array(
                'activity_type' => "update_master_ticket_category",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );

            if($this->updations_model->update_master_ticketcategory($form_data,$this->input->post('cid')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Ticket Category Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function master_ticketcategory_template()
    {
        $this->form_validation->set_rules('cid_template', 'id', 'required|trim|update_unique_no_condition[users_ticket_category.ticket_category.ticket_category_id.cid_template]');
        $this->form_validation->set_rules('template_title', 'Template title', 'required|trim');
        $this->form_validation->set_rules('template_text', 'Template text', 'required|trim');
        $this->form_validation->set_rules('category', 'template ticket category', 'required|trim|integer');
        if($this->form_validation->run() == TRUE)
        {
            $form_data = array(
                'ticket_category_id'=>$this->input->post('category'),
                'replytemplate_title'=>$this->input->post('template_title'),
                'replytemplate_message' => $this->input->post('template_text'),
                'updated_by' => $this->session->userdata['user_session']['admin_username'],               
            );

            $tracking_data = array(
                'activity_type' => "update_master_ticketcategory_template",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            //updt_master_ticketcategory_template
            if($this->updations_model->update_master_ticketcategory_template($form_data,$this->input->post('cid_template')) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Ticket template Updated Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo _json($output);exit;
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo _json($output);exit;
        }
    }

    public function re_push_order()
    {
        if(strtoupper($this->session->userdata('user_session')['role_name']) == 'SUPERADMIN' || $this->permissions_model->check_permission('re_push_order'))
        {
            $from_date = date('Y-m-d'). ' 00:00:00';
            $re_push_order = "UPDATE shipments SET user_status = '220', system_status = '101' WHERE user_status = '200' AND system_status = '100' AND waybill_number = '' AND added_on BETWEEN '$from_date' AND ADDDATE(NOW(), interval -30 MINUTE)";

            $this->db->query($re_push_order);
            $update_row_count = $this->db->affected_rows();

            if(!empty($update_row_count) > 0)
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Re-pushed '.$update_row_count.' order(s) successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'No orders eligible for repushing.';
            }
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error-403';
            $output['message'] = 'Permisson Denied.';
        }
        $tracking_data = array(
            'activity_type' => "re_push_order",
            'log_data' => json_encode($output),
            'admin_id' => $this->session->userdata['user_session']['admin_username'],
        );
        $this->insertions_model->activity_logs($tracking_data);
        echo json_encode($output);
    }

    public function update_notifications()
    {
        $this->form_validation->set_rules('update_notification_title', 'Update notification title', 'required|trim');
        $this->form_validation->set_rules('Update_notification_description', 'Update notification description', 'required|trim');
    
        if($this->form_validation->run() == TRUE)
        {
            $fileName ='';
            if(!empty($_FILES['update_notification_image']['name'])){
                $fileType   = $_FILES['update_notification_image']['type'];
                $extension  = pathinfo($_FILES['update_notification_image']['name'], PATHINFO_EXTENSION);
                $fileName   = date('dMy_His').".".$extension;
                // _print_r($fileType,1);
                if($fileType == "image/gif" || $fileType == "image/GIF" || $fileType == "video/mp4" || $fileType == "video/MP4" || $fileType == "image/png" || $fileType == "image/jpg" || $fileType == "image/jpeg" || $fileType == "image/PNG" || $fileType == "image/JPG" || $fileType == "image/JPEG"){
                    
                    file_upload_blob('updateimage',$fileName,$_FILES['update_notification_image']['tmp_name']);
                }
                else{
                    
                    $output['error'] = true;
                    $output['title'] = 'Error';
                    $output['message'] = 'This file type is not allowed.';
                    echo json_encode($output);exit;
                }
            }
            if(!empty($_FILES['update_notification_image']['name'])){
                $form_data = array(
                    'update_title' => $this->input->post('update_notification_title'),
                    'update_description' => $this->input->post('Update_notification_description'),
                    'update_image' => $fileName,
                );
            }else{
                $form_data = array(
                    'update_title' => $this->input->post('update_notification_title'),
                    'update_description' => $this->input->post('Update_notification_description'),
                );
            }

            $tracking_data = array(
                'activity_type' => "Edit_update_notification",
                'log_data' => json_encode($this->input->post()),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );       
            if($this->updations_model->update('tbl_modalupdates',['updates_id' =>$this->input->post('Uid')],$form_data) && $this->insertions_model->activity_logs($tracking_data))
            {
                $output['title'] = 'Congrats';
                $output['message'] = 'Notification Update Successfully.';
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = 'Some Error occurred, Try again.';
            }
            echo json_encode($output);
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
            echo json_encode($output);
        }
    }

    public function weight_request_modify(){
        
        $result_data = $this->getdata_model->get_data_in_object('uwt_id,user_id,waybill_number,request_weight,request_status,weight_images',['uwt_id' => $this->input->post('modifiedweight')],'','','users_weight_update');

        if(!empty($result_data))
        {
            $request_data = $data = [];
            $container = 'weightimages';
            $error = false;
            $title = 'Congrats';
            $where=['uwt_id' => $result_data->uwt_id];
            $is_sku = false;
            // if update all SKU is YES then check all panding request and update all similler SKU
            if( !empty($this->input->post('modify_sku')) && !empty($this->input->post('product_sku')) ){

                $query = "select waybill_number from shipments_products where waybill_number in (SELECT distinct(UWU.waybill_number) FROM `users_weight_update` `UWU` JOIN `shipments_products` `SP` ON `SP`.`waybill_number` = `UWU`.`waybill_number` WHERE BINARY `SP`.`product_sku` = '".$this->input->post('product_sku')."' AND `UWU`.`user_id` = '".$result_data->user_id."' AND `UWU`.`request_status` = '0' AND `UWU`.`waybill_number` != '')  group by waybill_number having count(waybill_number)=1";

                $sku_data = $this->db->query($query)->result();

                // $sku_data = $this->getdata_model->get_data_in_array('distinct(UWU.waybill_number)',['SP.product_sku' => $this->input->post('product_sku'),'UWU.user_id'=>$result_data->user_id,'UWU.request_status'=>'0','UWU.waybill_number !='=>''],['shipments_products SP'=> 'SP.waybill_number = UWU.waybill_number'],'users_weight_update UWU');

                if($sku_data){
                    $is_sku = true;
                    $data['request_status'] =  '1';
                    for($i=0; $i < count($sku_data); $i++){
                        $request_data[$i]['waybill_number'] =  $sku_data[$i]->waybill_number;
                        $request_data[$i]['billing_weight'] = $this->input->post('modify_new_wt') > 0 ? $this->input->post('modify_new_wt') : $result_data->request_weight;
                        $available_waybill[] = $sku_data[$i]->waybill_number;
                    }
                    $wherein_column = 'waybill_number';
                    $wherein_val = $available_waybill;
                    $where = ['request_status' => '0'];
                }
            }

            // if request status is pending
            if($result_data->request_status == '0'){

                $data['request_status'] =  '1';
                if($this->input->post('modify_new_wt') > 0){
                    $update_weight = $this->input->post('modify_new_wt');
                }else{
                    $update_weight = $result_data->request_weight;
                }
                
                $request_data[] = [
                    "waybill_number" => $result_data->waybill_number,
                    "billing_weight" => $update_weight
                ];
            }else{
                //if request weight is rejected or approved
                if(!empty($this->input->post('modify_new_wt') > 0)){
                    $request_data[] = [
                        "waybill_number" => $result_data->waybill_number,
                        "billing_weight" => $this->input->post('modify_new_wt')
                    ];
                    $data['request_status'] =  '1';
                }
            }

            if(isset($_FILES['files']['name'])){
                $user_request_id = $result_data->uwt_id;
                $files = json_decode($result_data->weight_images);
                foreach($_FILES['files']['name'] as $key => $filename){
                    $ext = pathinfo($_FILES['files']['name'][$key], PATHINFO_EXTENSION);
                    if(in_array($ext,['png','jpg','jpeg'])){
                        $name = $this->input->post('modified_wybillNo').'_admin'.time().$key.'.'.$ext;
                        $tempName = $_FILES['files']['tmp_name'][$key];
                        $upload_count[] = file_upload_blob($container,$name,$tempName);
                        $files[] = $name;
                    }
                }

                $allFiles = json_encode($files);

                $data['weight_images'] =  $allFiles;

                $trackingData = [
                    'uwt_id' => $result_data->uwt_id,
                    'waybill_number' => $result_data->waybill_number,
                    'request_weight' => $result_data->request_weight,
                    'modify_weight' => $this->input->post('modify_new_wt'),
                    'message'   => "Images Upload Successfully.",
                ];
                $_POST['weight_images'] = $allFiles;
            };

            if($request_data){
                list($success_count, $error_records) = $this->billing_model->update_weight_request($request_data);
                if(!empty($error_records) && count($error_records) > 0){
                    $title = 'Error';
                    $error = true;
                    $user_request_id = '';
                    $trackingData = [
                        'uwt_id' => $result_data->uwt_id,
                        'waybill_number' => $result_data->waybill_number,
                        'request_weight' => $result_data->request_weight,
                        'modify_weight' => $this->input->post('modify_new_wt'),
                        'message'   => $error_records[0]['error']
                    ];
                }else{
                    $user_request_id = $result_data->uwt_id;
                    $trackingData = [
                        'uwt_id' => $result_data->uwt_id,
                        'waybill_number' => $result_data->waybill_number,
                        'request_weight' => $result_data->request_weight,
                        'modify_weight' => $this->input->post('modify_new_wt'),
                        'message'   => "Request Approved Successfully."
                    ];
                }
            }

            
            $trackingData['post_data'] = $_POST;
            $tracking_data = array(
                'activity_type' => ($this->input->post('modify_new_wt') > 0)?'weight_modify_by_admin':'users_weight_update_request',
                'log_data' => json_encode($trackingData),
                'admin_id' => $this->session->userdata['user_session']['admin_username'],
            );
    
            if(isset($user_request_id) && $user_request_id !='' && $this->insertions_model->activity_logs($tracking_data)){
                if($is_sku){
                    $this->updations_model->update_request_data($data,$where,$wherein_column,$wherein_val,'users_weight_update');
                }else{
                    $this->updations_model->update_request($data,$where,'users_weight_update');
                }
                $output['error'] = $error;
                $output['title'] = $title;
                $output['message'] = $trackingData['message'];
            }else{
                $output['error'] = $error;
                $output['title'] = $title;
                $output['message'] = isset($trackingData['message'])?$trackingData['message']:'Already up to date';
            }
        }
        else
        {
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = 'Some Error occurred, Try again.';
        }
        echo json_encode($output);
    }

    public function pod_upload()
    {
        if($_POST['bulk_pod'] != "bulkPOD")
        {
            $this->form_validation->set_rules('awb_number', 'AWB Number', 'required|trim');
            if($this->form_validation->run() == TRUE)
            {
                $fileName ='';
                if(!empty($_FILES['awb_file']['name']) && $_POST['bulk_pod'] != "bulkPOD")
                {
                    $fileType   = $_FILES['awb_file']['type'];
                    $extension  = pathinfo($_FILES['awb_file']['name'], PATHINFO_EXTENSION);
                    $fileName   = $_POST['awb_number'].".".$extension;
                    // _print_r($extension,1);
                    if($fileType == "image/png" || $fileType == "image/jpg" || $fileType == "image/jpeg" || $fileType == "image/PNG" || $fileType == "image/JPG" || $fileType == "image/JPEG" || $fileType == "application/pdf")
                    {
                        file_upload_blob('pod',$fileName,$_FILES['awb_file']['tmp_name']);
                        $postData = $this->input->post();
                        $postData['awb_file'] = $fileName;

                        $form_data = array(
                            'pod' => $fileName,
                        );
    
                        $tracking_data = array(
                            'activity_type' => "single_pod",
                            'log_data'      => json_encode($postData),
                            'admin_id'      => $this->session->userdata['user_session']['admin_username'],
                        );

                        if($this->updations_model->update('shipments_statuses_logs',['waybill_number' =>$this->input->post('awb_number')],$form_data) && $this->insertions_model->activity_logs($tracking_data))
                        {
                            $output['title']    = 'Congrats';
                            $output['message']  = 'POD Update Successfully.';
                        }
                        else
                        {
                            $output['error']    = true;
                            $output['title']    = 'Error';
                            $output['message']  = 'Some Error occurred, Try again.';
                        }
                        echo json_encode($output);
                    }
                    else{
                        
                        $output['error']    = true;
                        $output['title']    = 'Error';
                        $output['message']  = 'This file type is not allowed.';
                        echo json_encode($output);exit;
                    }
                }
            }
            else
            {
                $output['error']   = true;
                $output['title']   = 'Error';
                $output['message'] = validation_errors();
                echo json_encode($output);
            }
        }
        else
        {
            if(!empty($_POST['bulk_pod']) && !empty($_FILES['pod_file']))
            {
                $fileData   = [];
                $form_data  = [];
                $error_data = [];
                $totalFiles = count($_FILES['pod_file']['name']);
            
                for($i = 0; $i < $totalFiles; $i++)
                {
                    // Extract file details
                    $fileName = $_FILES['pod_file']['name'][$i];
                    $fileTemp = $_FILES['pod_file']['tmp_name'][$i];
                    $fileType = $_FILES['pod_file']['type'][$i];
                    $fileBaseName = pathinfo($fileName, PATHINFO_FILENAME);
            
                    // Populate fileData
                    $fileData['getFile']['awb'][] = $fileBaseName;
                    $fileData['getFile']['pod_file'][] = $fileName;
                    $fileData['getFile']['pod_file_temp'][] = $fileTemp;
                    $fileData['getFile']['pod_file_type'][] = $fileType;
                }
            
                // Iterate over the files for processing
                foreach($fileData['getFile']['pod_file'] as $key => $podFile)
                {
                    $fileType = $fileData['getFile']['pod_file_type'][$key];
                    $waybill  = $fileData['getFile']['awb'][$key];
                    $fileTemp = $fileData['getFile']['pod_file_temp'][$key];
                    // _print_r($podFile,1);

                    // Check allowed file types
                    if(in_array($fileType, ['image/png', 'image/jpg', 'image/jpeg', 'application/pdf']))
                    {
                        $query = $this->db->select('waybill_number')->where('user_status', '226')->where('waybill_number', $waybill)->get('shipments');
                        if($query->num_rows() > 0)
                        {
                            // update pod field for SSL table
                            $updateStatus = $this->updations_model->update('shipments_statuses_logs',['waybill_number' => $waybill],['pod' => $podFile]);
                            // upload file in blob
                            file_upload_blob('pod',$podFile,$fileTemp);

                            // Valid file type; add to form_data
                            $form_data[] = [
                                'awb'    => $waybill,
                                'pod'    => $podFile,
                                'status' => $updateStatus ? 'Updated' : 'Not Updated',
                            ];
                        }
                        else
                        {
                            $error_data[] = [
                                'awb'    => $waybill,
                                'pod'    => $podFile,
                                'status' => 'Invalid AWB number or Shipment is not delivered',
                            ];
                        }
                    }
                    else
                    {
                        // Invalid file type; return error response
                        $error_data[] = [
                            'awb'    => $waybill,
                            'pod'    => $podFile,
                            'status' => 'This file type is not allowed',
                        ];
                        // echo json_encode($output);
                    }
                }
                // Print form data (for debugging purposes)
                // _print_r($form_data, 1);
                // _print_r(count($form_data), 1);
                if(!empty($form_data))
                {
                    $tracking_data = array(
                        'activity_type' => "bulk_pod",
                        'log_data'      => json_encode($form_data),
                        'admin_id'      => $this->session->userdata['user_session']['admin_username'],
                    );

                    if($this->insertions_model->activity_logs($tracking_data))
                    {
                        $output['title']   = 'Congrats';
                        $output['message'] = count($form_data).' POD Update Successfully and error data '.count($error_data);
                    }
                    else
                    {
                        $output['error']   = true;
                        $output['title']   = 'Error';
                        $output['message'] = 'Some Error occurred, Try again.';
                    }
                    echo json_encode($output);
                }
                else
                {
                    $output['error']   = true;
                    $output['title']   = 'Error';
                    $output['message'] = 'error data '.count($error_data);
                    echo json_encode($output);
                }
            }
            else
            {
                $output['error'] = true;
                $output['title'] = 'Error';
                $output['message'] = "bulk_pod file is required";
                echo json_encode($output);
            }
        }
    }


    public function update_university_worktype()
    {
        $this->form_validation->set_rules('worktype_name', 'Worktype Name', 'required|trim');
        $this->form_validation->set_rules('worktype_id', 'Work Type', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'error'   => true,
                'title'   => 'Validation Error',
                'message' => validation_errors()
            ]);
            return;
        }

        $uid            = $this->input->post('univ_worktype_id') ?? '';
        $university_id  = $this->input->post('university_id') ?? '';
        $worktype_name  = $this->input->post('worktype_name') ?? '';
        $university_url = $this->input->post('university_url') ?? '';
        $worktype_id    = $this->input->post('worktype_id') ?? '';

        // fetch university + worktype
        $university   = $this->db->where('university_id', $university_id)->get('university')->row();
        $worktype_row = $this->db->where('worktype_id', $worktype_id)->get('worktype')->row();

        if (!$university || !$worktype_row) {
            echo json_encode([
                'error'   => true,
                'title'   => 'Error',
                'message' => 'Invalid University or Worktype!'
            ]);
            return;
        }

        $folderName = strtoupper($university->university_name);
        $worktype_name_f = strtolower($this->input->post('worktype_name'));
        $starting_folder_name = $uid.'_'.$worktype_name_f.'_'.$worktype_id;
                        // univ_work_id#work_type_name#worktype_id

        $setpath = "{$folderName}/{$starting_folder_name}/documents/";
        $uploadPath = FCPATH . 'assets/uploads/Universities/'. $setpath;

        // $folderName = strtoupper($university->university_name);
        // $setpath    = "{$folderName}/documents/worktype-{$worktype_row->worktype_id}/";
        // $uploadPath = FCPATH . 'assets/uploads/Universities/' . $setpath;

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, TRUE);
        }

        $files = [];
        $allowed_types = 'pdf|doc|docx';

        if ($uid) {
            $oldData = $this->db->where('univ_worktype_id', $uid)->get('university_worktype')->row();

            if ($oldData && ($oldData->worktype_id != $worktype_id || $oldData->worktype_name != $worktype_name)) {
                $oldWorktype = $this->db->select('worktype_name, worktype_id')
                                        ->where('worktype_id', $oldData->worktype_id)
                                        ->get('worktype')
                                        ->row();

                if ($oldWorktype) {
                    
                    $old_worktype_name_f = strtolower($oldData->worktype_name);
                    $old_starting_folder_name = $uid . '_' . $old_worktype_name_f . '_' . $oldWorktype->worktype_id;

                    $oldSetPath = "{$folderName}/{$old_starting_folder_name}/documents/";
                    $oldPath    = FCPATH . 'assets/uploads/Universities/' . $oldSetPath;

                    for ($i = 1; $i <= 5; $i++) {
                        $oldFile = $oldData->{'file_'.$i} ?? null;
                        if ($oldFile && file_exists($oldPath . $oldFile)) {
                            // move file into new folder
                            rename($oldPath . $oldFile, $uploadPath . $oldFile);
                            $files['file_'.$i] = $oldFile;
                        }
                    }
                    
                }
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            if (!empty($_FILES['file_'.$i]['name']) && $_FILES['file_'.$i]['size'] > 0) {
                $config['upload_path']   = $uploadPath;
                $config['allowed_types'] = $allowed_types;
                $config['max_size']      = 51200;
                $config['encrypt_name']  = FALSE;

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('file_'.$i)) {
                    $uploadData = $this->upload->data();

                    // remove old file if exists
                    if ($uid) {
                        $old = $this->db->select('file_'.$i)
                                        ->where('univ_worktype_id', $uid)
                                        ->get('university_worktype')
                                        ->row();
                        if ($old && !empty($old->{'file_'.$i})) {
                            $oldFilePath = $uploadPath . $old->{'file_'.$i};
                            if (file_exists($oldFilePath)) {
                                unlink($oldFilePath);
                            }
                        }
                    }

                    $files['file_'.$i] = $uploadData['file_name'];
                } else {
                    echo json_encode([
                        'error'   => true,
                        'title'   => 'Upload Error',
                        'message' => $this->upload->display_errors()
                    ]);
                    return;
                }
            }
        }

        $data = [
            'university_id'  => $university_id,
            'worktype_name'  => $worktype_name,
            'university_url' => $university_url,
            'worktype_id'    => $worktype_id
        ];

        $data = array_merge($data, $files);

        // update
        $this->db->where('univ_worktype_id', $uid);
        $this->db->update('university_worktype', $data);

        echo json_encode([
            'error'   => false,
            'title'   => 'Success',
            'message' => 'University Worktype Updated Successfully!'
        ]);
    }

    public function orderSubjectMap(){
        $postData = $this->input->post();

        // _print_r($postData);die;
        $this->form_validation->set_rules('writer_id', 'Writer', 'required|trim');
        $this->form_validation->set_rules('order_map_id', 'Order subject', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'error'   => true,
                'title'   => 'Validation Error',
                'message' => validation_errors()
            ]);
            return;
        }
        
        $data['assigned_to'] = $postData['writer_id'];
        $data['status'] = '1';
        $data['assigned_at'] = date('Y-m-d H:i:s');

        $writerRequiredFile = explode(",", $postData['order_map_id']);

        $fileRequired = 3; //Minimum 3 file required

        if(!empty($writerRequiredFile)){
            $fileCount = ceil(count($writerRequiredFile)/DOCUMENT_UPLOAD_CONST);

            if($fileCount>$fileRequired){
                $fileRequired = $fileCount;
            }
            
        }

        $writer_order['files_required'] = $fileRequired;
        $writer_order['orders_data'] = $postData['order_id'];
        $writer_order['assigned_to'] = $postData['writer_id'];
        $writer_order['university_id'] = $postData['university_id'];
        $writer_order['univ_worktype_id'] = $postData['univ_worktype_id'];
        $writer_order['subject_id'] = $postData['subject_id'];
        $writer_order['status'] = '0';
        $writer_order['created_at'] = date('Y-m-d H:i:s');


        $result = $this->db->where_in('os_map_id', explode(",", $postData['order_map_id']));
        $this->db->update('order_assigment', $data);

        $this->insertions->insert('asgmt_content_writer_uploads', $writer_order);

        if(!empty($result)){
            echo json_encode([
            'error'   => false,
            'title'   => 'Success',
            'message' => 'Writer Assigned Successfully!'
            ]);
        }
        else{
            echo json_encode([
                'error'   => true,
                'title'   => 'Something Wrong',
                'message' => 'Oops! Something went wrong!'
            ]);
        }
        
    }


    public function updateWriterOrder(){

        $postData = $this->input->post();
        $query=$this->db->query('SELECT a.writer_suborder_id, u.university_name, s.subject_name, a.orders_data, a.files_required, a.files_uploaded, a.status, a.created_at, a.updated_at FROM asgmt_content_writer_uploads a LEFT JOIN university u ON a.university_id = u.university_id LEFT JOIN subjects s ON a.subject_id = s.subject_id WHERE a.writer_suborder_id='.$postData['writer_order_id'].';');

        if($query->num_rows()>0){

            $result = $query->result();

            $queryResult = $result[0];

            $requiredFileCount = $queryResult->files_required;

            if($requiredFileCount > count($_FILES['subject_file']['name']) || $requiredFileCount < count($_FILES['subject_file']['name'])){
                echo json_encode([
                    'error'   => true,
                    'title'   => 'Error',
                    'message' => $requiredFileCount." files required"
                 ]);

                return;
            }


            $university_worktype = $this->db->where('univ_worktype_id', $this->input->post('univ_worktype_id'))->get('university_worktype')->row();

            $folderName = strtoupper($this->input->post('university_name'));
            $worktype_name_f = strtolower($university_worktype->worktype_name);
            $starting_folder_name = $university_worktype->univ_worktype_id.'_'.$worktype_name_f.'_'.$university_worktype->worktype_id;
                            // univ_work_id#work_type_name#worktype_id

            $setpath = "{$folderName}/{$starting_folder_name}/subject/" .$this->input->post('subject_name')."/";
            $uploadPath = FCPATH . "assets/uploads/Universities/". $setpath;

            // $uploadPath = FCPATH.'assets/uploads/Universities/'.$queryResult->university_name.'/' 
            // . $queryResult->subject_name . '/writer';

            // Create folder if it doesn't exist
            if (!is_dir($uploadPath)) {
               $created =  mkdir($uploadPath, 0777, true); // recursive = true, so all subfolders created
            }

            $config['upload_path']   = $uploadPath;
            $config['allowed_types'] = 'jpg|jpeg|png|pdf|docx|doc';
            $config['max_size']      = 2048;

            // echo $config['upload_path'];die;

            $this->load->library('upload');
            $files = $_FILES;

            $allUploaded = true;

            $fileNameData = [];

            for ($i = 0; $i < $requiredFileCount; $i++) {
                $_FILES['file']['name']     = $files['subject_file']['name'][$i];
                $_FILES['file']['type']     = $files['subject_file']['type'][$i];
                $_FILES['file']['tmp_name'] = $files['subject_file']['tmp_name'][$i];
                $_FILES['file']['error']    = $files['subject_file']['error'][$i];
                $_FILES['file']['size']     = $files['subject_file']['size'][$i];

                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

                // 👉 Build custom file name (example: file_1_timestamp.pdf)
                $customFileName = $queryResult->subject_name.'_'.($i + 1) . '_' . time() . '.' . $ext;

                // 👉 Reinitialize config with custom file name
                $config['file_name'] = $customFileName;

                $this->upload->initialize($config);

                if ($this->upload->do_upload('file')) {
                    $uploadResult = $this->upload->data();
                    $fileNameData[] = "assets/uploads/Universities/". $setpath . $uploadResult['orig_name'];
                } else {
                    $errorResult = $this->upload->display_errors();
                    $allUploaded = false;
                    break;
                }
            }

            

            $updateData['files_uploaded'] = json_encode($fileNameData);
            $updateData['status'] = "1";
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            if($allUploaded){
                $this->updations->update_request($updateData,['writer_suborder_id'=>$postData['writer_order_id']],'asgmt_content_writer_uploads');

                echo json_encode([
                'error'   => false,
                'title'   => 'Success',
                'message' => 'Document uploaded Successfully!'
                ]);
            }

            else{
                echo json_encode([
                'error'   => true,
                'title'   => 'Something Wrong',
                'message' => 'Oops! Something went wrong!'
                ]);
            }
        }
    }


    public function updateJuniorWriterOrder(){

        $postData = $this->input->post();
        $query=$this->db->query('SELECT a.subject_id, a.writer_suborder_id, u.university_name, s.subject_name, a.orders_data, a.files_required, a.files_uploaded, a.status, a.created_at, a.updated_at FROM asgmt_content_writer_uploads a LEFT JOIN university u ON a.university_id = u.university_id LEFT JOIN subjects s ON a.subject_id = s.subject_id WHERE a.writer_suborder_id='.$postData['writer_order_id'].';');

        if($query->num_rows()>0){

            $result = $query->result();

            $queryResult = $result[0];

            $requiredFileCount = $queryResult->files_required * DOCUMENT_UPLOAD_CONST;

            if($requiredFileCount > count($_FILES['subject_file']['name']) || $requiredFileCount < count($_FILES['subject_file']['name'])){
                echo json_encode([
                    'error'   => true,
                    'title'   => 'Error',
                    'message' => $requiredFileCount." files required"
                 ]);

                return;
            }

            $uploadPath = 'assets/uploads/Universities/'.$queryResult->university_name.'/' 
            . $queryResult->subject_name;

            // Create folder if it doesn't exist
            if (!is_dir(FCPATH.$uploadPath)) {
               $created =  mkdir(FCPATH.$uploadPath, 0777, true); // recursive = true, so all subfolders created
            }

            $config['upload_path']   = FCPATH.$uploadPath;
            $config['allowed_types'] = 'jpg|jpeg|png|pdf|docx';
            $config['max_size']      = 2048;

            // echo $config['upload_path'];die;

            $this->load->library('upload');

            $files = $_FILES;

            $allUploaded = true;

            $fileNameData = [];

            $ordersData = explode(',', $queryResult->orders_data);

            for ($i = 0; $i < count($_FILES['subject_file']['name']); $i++) {
                $canSentFile = 0;

                $orderId = 0;

                if(!empty($ordersData[$i])){

                    $orderId = $ordersData[$i];

                    $sql = "SELECT o.order_id, o.order_amt, o.paid_amt, COUNT(oa.os_map_id) AS total_subject, SUM(CASE WHEN oa.status = '3' THEN 1 ELSE 0 END) AS sent_subject FROM orders o LEFT JOIN order_assigment oa ON oa.order_id = o.order_id WHERE o.order_id = ".$orderId."";

                    $query = $this->db->query($sql);

                    $orderResult = $query->result();
                    $orderResult = $orderResult[0];

                    $perSubjectPrice = floor($orderResult->order_amt/$orderResult->total_subject);

                    $subjectCanSent = floor($orderResult->paid_amt/$perSubjectPrice);

                    $canSentFile = $subjectCanSent - $orderResult->sent_subject;

                    if($canSentFile > 0){
                        $uploadPath = $uploadPath.'/sent';
                        if (!is_dir(FCPATH.$uploadPath)) {
                        $created =  mkdir(FCPATH.$uploadPath, 0777, true); 
                        }
                        $config['upload_path'] = FCPATH.$uploadPath;
                    }

                }
                

                $_FILES['file']['name']     = $files['subject_file']['name'][$i];
                $_FILES['file']['type']     = $files['subject_file']['type'][$i];
                $_FILES['file']['tmp_name'] = $files['subject_file']['tmp_name'][$i];
                $_FILES['file']['error']    = $files['subject_file']['error'][$i];
                $_FILES['file']['size']     = $files['subject_file']['size'][$i];

                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

                $customFileName = $queryResult->subject_name.'_'.($i + 1) . '_' . time();

                $customFileName = $queryResult->subject_name."_".md5($customFileName).".".$ext;

                $config['file_name'] = $customFileName;

                $this->upload->initialize($config);

                if ($this->upload->do_upload('file')) {
                    $uploadResult = $this->upload->data();
                    $fileNameData[] = $uploadResult['orig_name'];

                    if($canSentFile > 0 && !empty($orderId)){
                        //Update order status
                        $orderStatus = $canSentFile==1?'2':'1';
                        $this->updations->update_request(['order_status'=>$orderStatus,'updated_on'=>date('Y-m-d H:i:s')],['order_id'=>$orderId],'orders');
                    }

                    $updateAssignmentData['status'] = '2';
                    $updateAssignmentData['file_path'] = $uploadPath.'/'.$customFileName;
                    $updateAssignmentData['fileuploaded_on'] = date('Y-m-d H:i:s');

                    if($canSentFile > 0){
                        $updateAssignmentData['status'] = '3';
                        $updateAssignmentData['filesent_on'] = date('Y-m-d H:i:s');
                    }

                    if(!empty($orderId)){
                        $this->updations->update_request($updateAssignmentData,['order_id'=>$orderId,'subject_id'=>$queryResult->subject_id,'assigned_to IS NOT NULL'=>NULL],'order_assigment');
                    }
                    
                } else {
                    $errorResult = $this->upload->display_errors();
                    $allUploaded = false;
                    break;
                }

            }

            $updateData['jr_writer_files'] = json_encode($fileNameData);
            $updateData['status'] = "2";
            $updateData['updated_at'] = date('Y-m-d H:i:s');

            if($allUploaded){
                
                $checkAllOrder = $this->db->query("SELECT * FROM multipleupload.orders WHERE order_id IN ('.$queryResult->orders_data.') AND order_status!='2'");

                $orderStatus =  $checkAllOrder->result();
                if(empty($orderStatus)){
                    $updateData['status'] = "3";
                }

                $this->updations->update_request($updateData,['writer_suborder_id'=>$postData['writer_order_id']],'asgmt_content_writer_uploads');

                echo json_encode([
                'error'   => false,
                'title'   => 'Success',
                'message' => 'Document uploaded Successfully!'
                ]);
            }

            else{
                echo json_encode([
                'error'   => true,
                'title'   => 'Something Wrong',
                'message' => 'Oops! Something went wrong!'
                ]);
            }
        }
    }




    public function complete_assignment_portal_task()
    {
        // $this->form_validation->set_rules('exam_score_file', 'Screen Shoot', 'required|trim');

        if (!empty($_FILES['exam_score_file']['name'])) {

            $oid = base64_decode($this->input->post('oid'));
			$os_id = base64_decode($this->input->post('os_id'));
			$status = base64_decode($this->input->post('status'));

            $uploadPath = FCPATH . 'assets/uploads/screenshot/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, TRUE);
            }

            $allowed_types = 'png|jpg|jpeg';

            if (!empty($_FILES['exam_score_file']['name']) && $_FILES['exam_score_file']['size'] > 0) {
                $config['upload_path']   = $uploadPath;
                $config['allowed_types'] = $allowed_types;
                $config['max_size']      = 51200;
                $config['encrypt_name']  = TRUE;

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('exam_score_file')) {
                    $uploadData = $this->upload->data();
                    $file_path = 'assets/uploads/screenshot/' . $uploadData['file_name'];

                    $this->updations->update('order_assigment',['os_map_id' => $os_id],
                    ['status' => '2', 'file_path' => $file_path, 'fileuploaded_on' => date('Y-m-d H:i:s') ]);

                    $this->updations->update('orders',['order_id' => $oid],['order_status' => '1']);

                    $output['title'] = 'Congrats';
                    $output['message'] = 'Result added successfully';

                } else {
                    $output['error'] = true;
                    $output['title'] = 'Upload Error';
                    $output['message'] = $this->upload->display_errors();
                }
            }else{
                $output['error'] = true;
                $output['title'] = 'File Upload Error';
                $output['message'] = 'File check';
            }
            
        }else{
            $output['error']   = true;
            $output['title']   = 'Validation Error';
            $output['message'] = 'The Screen Shoot field is required.';
        }
        
        echo json_encode($output);
    }


    public function complete_assignment_portal_section()
    {
        $this->form_validation->set_rules('exam_score', 'Exam Score', 'required|numeric');
        // return $this->input->post();

        if ($this->form_validation->run() == TRUE) {

            $oid     = base64_decode($this->input->post('oid'));
            $os_id   = base64_decode($this->input->post('os_id'));
            $status  = base64_decode($this->input->post('status'));
            $exam_score   = (int) trim($this->input->post('exam_score'));
            $exam_section = $this->input->post('section');
            $exam_section = $exam_section !== null ? trim($exam_section) : '';

            if ($exam_score === 100) {
                $number = (string) 1;
            } elseif ($exam_score >= 91 && $exam_score <= 99) {
                $number = (string) 2;
            } elseif ($exam_score >= 81 && $exam_score <= 90) {
                $number = (string) 3;
            } elseif ($exam_score < 81) {
                $number = (string) 4;
            } else {
                $number = (string) 0;
            }

            // Update depending on section
            if ($exam_section !== '') {

                $row = $this->getdata->assiment_portel_flag_count('asgmt_portal_mcqbank', ['order_id' => $oid, 'section' => $exam_section]);

                if($row > 0){
                    
                    echo json_encode([
                        'error'   => true,
                        'title'   => 'Record Already Updated',
                        'message' => 'This order and section record already updated'
                    ]);
                    return;
                }

                    $result = $this->updations->update_with_rows(
                        'asgmt_portal_mcqbank',
                        ['order_id' => $oid, 'section' => $exam_section],
                        ['flag_color' => $number]
                    );

                    $output['title']   = 'Congrats';
                    // $output['message'] = 'Result added successfully';
                

            } else {

                $row = $this->getdata->assiment_portel_flag_count('asgmt_portal_mcqbank', ['order_id' => $oid]);

                if($row > 0){

                    echo json_encode([
                        'error'   => true,
                        'title'   => 'Record Already Updated',
                        'message' => 'This order record already updated'
                    ]);
                    return;
                }

                    $result = $this->updations->update_with_rows(
                        'asgmt_portal_mcqbank',
                        ['order_id' => $oid],
                        ['flag_color' => $number]
                    );

                    $output['title']   = 'Full Exam Update';
                    // $output['message'] = 'Full Exam Score Update';
            }

            if ($result > 0) {
                $output['message'] = 'Update successful';
            } else {
                $output['message'] = 'No rows updated – check order_id/section';
            }

            if ($this->session->userdata('section')) {
                $this->session->set_userdata('old_section', $this->session->userdata('section'));
                $this->session->unset_userdata('section');
            }

            $output['error'] = false;

        } else {
            $output['error']   = true;
            $output['title']   = 'Validation Error';
            $output['message'] = validation_errors();
        }

        echo json_encode($output);
    }


    public function assignment_portel_order_assign()
    {
        $postData = $this->input->post();

        $this->form_validation->set_rules('writer_id', 'Writer', 'required|trim');
        $this->form_validation->set_rules('order_map_id', 'Order subject', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'error'   => true,
                'title'   => 'Validation Error',
                'message' => validation_errors()
            ]);
            return;
        }

        $writerRequiredFile = explode(",", $postData['order_map_id']);

        $order_amt = (int)$postData['order_amt']; 
        $paid_amt  = (int)$postData['paid_amt'];
        $writer_id = $postData['writer_id'];

        $perOrderAmt = $order_amt / count($writerRequiredFile);
        $assignCount = floor($paid_amt / $perOrderAmt);

        $this->db->where_in('os_map_id', $writerRequiredFile);
        $this->db->update('order_assigment', [
            'status'      => '0',
            'assigned_to' => NULL
        ]);

        if ($assignCount > 0) {
            $assignOrders = array_slice($writerRequiredFile, 0, $assignCount);

            $this->db->where_in('os_map_id', $assignOrders);
            $result = $this->db->update('order_assigment', [
                'status'      => '1',
                'assigned_to' => $writer_id,
                'assigned_at' => date('Y-m-d H:i:s')
            ]);
        }else{
            $result = null;
        }

        if(!empty($result)){
            echo json_encode([
            'error'   => false,
            'title'   => 'Success',
            'message' => 'Writer Assigned Successfully!'
            ]);
        }
        else{
            echo json_encode([
                'error'   => true,
                'title'   => 'Something Wrong',
                'message' => 'Oops! Something went wrong!'
            ]);
        }
        
    }


    public function complete_project_content()
    {

        if (!empty($_FILES['content_file']['name'])) {

            $university_worktype = $this->db->where('univ_worktype_id', $this->input->post('univ_worktype_id'))->get('university_worktype')->row();

            $folderName = strtoupper($this->input->post('university_name'));
            $worktype_name_f = strtolower($university_worktype->worktype_name);
            $starting_folder_name = $university_worktype->univ_worktype_id.'_'.$worktype_name_f.'_'.$university_worktype->worktype_id;
                            // univ_work_id#work_type_name#worktype_id

            $setpath = "{$folderName}/{$starting_folder_name}/subject/" .$this->input->post('subject_name')."/";
            $uploadPath = FCPATH . "assets/uploads/Universities/". $setpath;  

            // $uploadPath = FCPATH . 'assets/uploads/complete_project_content/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, TRUE);
            }

            $allowed_types = 'pdf|doc|docx';

            if (!empty($_FILES['content_file']['name']) && $_FILES['content_file']['size'] > 0) {
                $config['upload_path']   = $uploadPath;
                $config['allowed_types'] = $allowed_types;
                $config['max_size']      = 51200;
                // $config['encrypt_name']  = TRUE;
                
                $originalName = pathinfo($_FILES['content_file']['name'], PATHINFO_FILENAME);
                $extension    = pathinfo($_FILES['content_file']['name'], PATHINFO_EXTENSION);
                $encrypted    = md5($originalName . time()); // unique hash
                $config['file_name'] = $this->input->post('order_id') . '_' . $encrypted . '.' . $extension;

                $this->load->library('upload', $config);

                if ($this->upload->do_upload('content_file')) {
                    $uploadData = $this->upload->data();
                    $file_path = 'assets/uploads/Universities/' .$setpath. $uploadData['file_name'];

                    $this->updations->update('order_assigment',['os_map_id' => $this->input->post('os_map_id')],
                    ['status' => '2', 'file_path' => $file_path, 'fileuploaded_on' => date('Y-m-d H:i:s') ]);

                    
                    $count = $this->getdata->check_project_content_pending_files($this->input->post('order_id'));

                    if ($count == 0) {
                        $this->updations->update('orders',['order_id' => $this->input->post('order_id')],['order_status' => '1']);
                    }

                    $output['title'] = 'Congrats';
                    $output['message'] = 'Result added successfully';

                } else {
                    $output['error'] = true;
                    $output['title'] = 'Upload Error';
                    $output['message'] = $this->upload->display_errors();
                }
            }else{
                $output['error'] = true;
                $output['title'] = 'File Upload Error';
                $output['message'] = 'File check';
            }
            
        }else{
            $output['error']   = true;
            $output['title']   = 'Validation Error';
            $output['message'] = 'The Screen Shoot field is required.';
        }
        
        echo json_encode($output);
    }



    public function project_content_order_assign()
    {
        $postData = $this->input->post();

        $this->form_validation->set_rules('writer_id', 'Writer', 'required|trim');
        $this->form_validation->set_rules('order_map_id', 'Order subject', 'required');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'error'   => true,
                'title'   => 'Validation Error',
                'message' => validation_errors()
            ]);
            return;
        }

        $writerRequiredFile = explode(",", $postData['order_map_id']);

        $order_amt = (int)$postData['order_amt']; 
        $paid_amt  = (int)$postData['paid_amt'];
        $writer_id = $postData['writer_id'];

        // $perOrderAmt = $order_amt / count($writerRequiredFile);

        // $assignCount = floor($paid_amt / $perOrderAmt);

        // $this->db->where_in('os_map_id', $writerRequiredFile);
        // $this->db->update('order_assigment', [
        //     'status'      => '0',
        //     'assigned_to' => NULL
        // ]);

        // if ($assignCount > 0) {
            // $assignOrders = array_slice($writerRequiredFile, 0, $assignCount);

            $this->db->where_in('os_map_id', $writerRequiredFile);
            $result = $this->db->update('order_assigment', [
                'status'      => '1',
                'assigned_to' => $writer_id,
                'assigned_at' => date('Y-m-d H:i:s')
            ]);
        // }else{
        //     echo json_encode([
        //         'error'   => true,
        //         'title'   => 'Error',
        //         'message' => 'Writer is not assigned due to payment!'
        //     ]);
        //     return;
        // }

        if(!empty($result)){
            echo json_encode([
            'error'   => false,
            'title'   => 'Success',
            'message' => 'Writer Assigned Successfully!'
            ]);
        }
        else{
            echo json_encode([
                'error'   => true,
                'title'   => 'Something Wrong',
                'message' => 'Oops! Something went wrong!'
            ]);
        }
        
    }

    public function punch_order_username_set() 
    {
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if($this->form_validation->run() == TRUE)
        {
            $this->session->set_userdata('order_username', $this->input->post('username'));
            $this->session->set_userdata('order_password', $this->input->post('password'));

            $output['title'] = 'User Set';
            $output['message'] = 'User Set Process Next';

        }else{
            $output['error'] = true;
            $output['title'] = 'Error';
            $output['message'] = validation_errors();
        }

        echo json_encode($output);

    }


}
?>
