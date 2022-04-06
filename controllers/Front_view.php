<?php 

	/*
	 * 
	 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Front_view extends CI_Controller {
	
	function __construct() {
    	parent::__construct();
        $this->load->model('doctor_model');
        $this->load->model('settings_model');
        $this->load->model('department_model');
        $this->load->library('Ion_auth');
        $this->load->library('session');
        $this->load->model('ion_auth_model');
        $this->load->model('settings_model');

	}

	function index() {
        $data['get_dr_this'] = $this->doctor_model->getDoctorThis();
        $data['site_set'] = $this->settings_model->getSettings();
        $data['get_dept'] = $this->department_model->getDepartment();

        $this->load->view('head', $data);
        $this->load->view('front_view/front_header', $data);
        $this->load->view('front_view/front_home', $data);
        $this->load->view('front_view/front_footer', $data);
        $this->load->view('foot');  
	}

    function log_in() {        

        if ($this->ion_auth->logged_in()) {
            if ($this->ion_auth->in_group(array('Doctor'))) {
                redirect('doctor_admin');
            }else {
                redirect('home');
            }
        }   

        $data['site_set'] = $this->settings_model->getSettings();

        $this->load->view('head', $data);
        $this->load->view('front_view/front_header', $data);
        $this->load->view('front_view/login_page', $data);
        $this->load->view('front_view/front_footer', $data);
        $this->load->view('foot');  
    }

	function doctor_list() {		
        $data['site_set'] = $this->settings_model->getSettings();
        $data['get_doctor'] = $this->doctor_model->getDoctorThis();
        $data['get_dept'] = $this->department_model->getDepartment();
        $this->load->view('front_view/head', $data); 
        $this->load->view('front_view/doctors_list', $data);   
        $this->load->view('front_view/footer', $data); 
	} 

    function doctor_profile() {
        $id = $this->input->get('id');
        $data['site_set'] = $this->settings_model->getSettings();
        $data['dr_info'] = $this->doctor_model->getDoctorById($id);
        $data['dr_time'] = $this->doctor_model->getDoctorTime($id);
        $data['dr_spcl'] = $this->doctor_model->getDoctorOthInfoS($id);

        $this->load->view('head', $data);
        $this->load->view('front_view/front_header', $data);
        $this->load->view('front_view/doctors_detail', $data);
        $this->load->view('front_view/front_footer', $data);
        $this->load->view('foot');  
    }

    function addAppoinmentFromOut() {
        $dr_auto_unic_ids       =   $this->input->post('dr_auto_unic_ids');
        $appoinment_dates_temp  =   $this->input->post('appoinment_dates');
        $appoinment_dates       =   date('Y-m-d', strtotime($appoinment_dates_temp));
        $patients_full_names    =   $this->input->post('patients_full_names');
        $patients_age           =   $this->input->post('patients_age');
        $patients_mobile_num    =   $this->input->post('patients_mobile_num');
        $entry_times            =   time();
        $rand_num_8_digit       =   rand(10, 10000000);

        $arrayData = array(
                'appoinment_dates'                  => $appoinment_dates, 
                'patients_full_names'               => $patients_full_names, 
                'patients_age'                      => $patients_age, 
                'patients_mobile_num'               => $patients_mobile_num, 
                'appoinment_timestamp_s'            => $entry_times, 
                'appoinment_dr_auto_uniq_iidi'      => $dr_auto_unic_ids, 
                'appoinment_submit_geo_location'    => '',  
                'rand_num_8_digit'                  => $rand_num_8_digit, 
            );
        $ticket_random_number = $this->doctor_model->insert_online_appoinment($arrayData);
        $this->session->set_flashdata('ticket_num', $rand_num_8_digit);
        redirect('front_view/doctor_profile?id='.$dr_auto_unic_ids);
    }

    function getDr_list_by_search() {
        $search_opt    =   $this->input->get('search_opt');
        $data = $this->doctor_model->getDr_list_by_search($search_opt);
        echo json_encode($data);
    }





















	}
