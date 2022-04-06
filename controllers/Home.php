<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Home extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('Ion_auth');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('ion_auth_model');
        $this->load->library('upload');
        $language = $this->db->get('settings')->row()->language;
        $this->lang->load('system_syntax', $language);
        $this->load->model('settings_model');
        $this->load->model('home_model');
        
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        } 

        if ($this->ion_auth->in_group(array('Doctor'))) {
            redirect('doctor_admin');
        }
    }

    function index() {
        $data['total_doctors'] = $this->home_model->getTotalActiveDoctor(); 
        $data['total_admit_patient'] = $this->home_model->getTotalAddmitedPatient(); 
        $data['ptn_unreport'] = $this->home_model->getTotalPathologyUnreport(); 
        $data['total_employees'] = $this->home_model->getTotalActiveEmployee(); 

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('home/admin_dashboard', $data); 
        $this->load->view('home/admin_foot', $data);       
        $this->load->view('foot');
    }









    function permission() {
        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $this->load->view('home/permission', $data);
    }

}

