<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Doctor_admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Ion_auth');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('patient_model');
        $this->load->library('upload');
        // $this->load->library('pdf');
        $language = $this->db->get('settings')->row()->language;
        $this->lang->load('system_syntax', $language);
        $this->load->model('doctor_model');
        $this->load->model('settings_model');
        $this->load->model('ion_auth_model');
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }

        // dump helper
        $this->load->helper('dd_helper');
    }

    /**
     *   Doctor Admin Controller
     *   Load doctor dashboard page
     */
    public function index()
    {
        $NowDate = date('Y-m-d', time());
        $data['doctors'] = $this->doctor_model->getDoctor();
        $data['admit_parients'] = $this->doctor_model->getAdmitPatients();
        $data['today_app'] = $this->doctor_model->getTodayApp($NowDate);
        $data['patients'] = $this->patient_model->getPatient();
        $data['settings'] = $this->settings_model->getSettings();
        $data['beds'] = $this->patient_model->getbed();



        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('doctor_admin/doctor_header', $data);
        // $this->load->view('doctor_admin/doctor_admit_patients', $data);
        $this->load->view('doctor_admin/doctor_dashboard', $data);
        
        $this->load->view('doctor_admin/doctor_footer', $data);
        $this->load->view('foot');
    }

    // doctor appointments view

    public function appointments()
    {
        $id =  $this->ion_auth->user()->row()->doctor_auto_uniq_iidds;
        $NowDate = date('Y-m-d', time());
//        $data['doctors'] = $this->doctor_model->getDoctor();
//        $data['admit_parients'] = $this->doctor_model->getAdmitPatients();
        $data['today_app'] = $this->doctor_model->getTodayApp($NowDate, $id);
//        $data['patients'] = $this->patient_model->getPatient();
//        $data['settings'] = $this->settings_model->getSettings();
//        $data['beds'] = $this->patient_model->getbed();
        
//        dd($data);


        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('doctor_admin/doctor_header', $data);
        $this->load->view('doctor_admin/doctor_appoinment', $data);
        $this->load->view('doctor_admin/doctor_footer', $data);
        $this->load->view('foot');
    }

    // load patient list
    public function patients()
    {
        $NowDate = date('Y-m-d', time());
        $data['doctors'] = $this->doctor_model->getDoctor();
        $data['admit_parients'] = $this->doctor_model->getAdmitPatients();
        $data['today_app'] = $this->doctor_model->getTodayApp($NowDate);
        $data['patients'] = $this->patient_model->getPatient();
        $data['settings'] = $this->settings_model->getSettings();
        $data['beds'] = $this->patient_model->getbed();



        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('doctor_admin/doctor_header', $data);
        $this->load->view('doctor_admin/doctor_dashboard', $data);
        $this->load->view('doctor_admin/doctor_footer', $data);       
        $this->load->view('doctor_admin/doctor_admit_patients', $data);
        
        
        $this->load->view('doctor_admin/doctor_footer', $data);
        $this->load->view('foot');
    }

 


    // load doctor profile_settings
    public function profile_settings()
    {
        $id =  $this->ion_auth->user()->row()->doctor_auto_uniq_iidds;
        $NowDate = date('Y-m-d', time());
        // $data['doctors'] = $this->doctor_model->getDoctor();
        // $data['admit_parients'] = $this->doctor_model->getAdmitPatients();
        // $data['today_app'] = $this->doctor_model->getTodayApp($NowDate);
        // $data['patients'] = $this->patient_model->getPatient();
        // $data['settings'] = $this->settings_model->getSettings();
        // $data['beds'] = $this->patient_model->getbed();

        $data['profile_data'] = $this->doctor_model->getDoctorById($id);

//         dd($data);

        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('doctor_admin/doctor_header', $data);
        $this->load->view('doctor_admin/doctor_profile_setting', $data);
        
        
        $this->load->view('doctor_admin/doctor_footer', $data);
        $this->load->view('foot');
    }
}
