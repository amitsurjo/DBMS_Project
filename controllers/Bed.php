<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Bed extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('Ion_auth');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('bed_model');
        $this->load->library('upload');
        $language = $this->db->get('settings')->row()->language;
        $this->lang->load('system_syntax', $language);
        $this->load->model('settings_model');
        $this->load->model('ion_auth_model');
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        if (!$this->ion_auth->in_group(array('admin', 'Accountant'))) {
            redirect('home/permission');
        }
    }

    public function index(){        
        $data['beds'] = $this->bed_model->getbed();

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('bed/bed_list', $data); 
        $this->load->view('home/admin_foot', $data);       
        $this->load->view('foot');
    }

    public function addbed(){        
        $cat        = $this->input->post('cat_name');
        $bedno      = $this->input->post('bedno');
        $floor      = $this->input->post('floor');
        $des        = $this->input->post('description');
        $price      = $this->input->post('price');
        $bed_cat_i  = $this->input->post('bed_cat_i');
        $data = array(
            'category'      => $cat,
            'b_num'         => $bedno,
            'floor'         => $floor,
            'description'   => $des,
            'price'         => $price, 
            'bed_cat_i'     => $bed_cat_i
        );

        $this->bed_model->insertbed($data);
            $this->session->set_flashdata('success', 'New Bed Added');
        redirect('bed');
    }

    function editBedByJason() {
        $id = $this->input->get('id');
        $data['bed_info'] = $this->bed_model->getBedByid($id);
        echo json_encode($data);
    }

    function updatebed() {  

        $bed_id     = $this->input->post('id');

        $cat        = $this->input->post('cat_name');
        $bedno      = $this->input->post('bedno');
        $floor      = $this->input->post('floor');
        $des        = $this->input->post('description');
        $price      = $this->input->post('price');
        $bed_cat_i  = $this->input->post('bed_cat_i');
        $data = array(
            'category'      => $cat,
            'b_num'         => $bedno,
            'floor'         => $floor,
            'description'   => $des,
            'price'         => $price, 
            'bed_cat_i'     => $bed_cat_i
        );

        $this->bed_model->updatebed($bed_id, $data);
            $this->session->set_flashdata('success', 'Bed Update');
        redirect('bed');
    }

    function delete() {
        $id = $this->input->get('id');
        $this->bed_model->delete_bed($id);
            $this->session->set_flashdata('warning', 'bed Deleted');
        redirect('bed');
    }

}