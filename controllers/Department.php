<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Department extends CI_Controller {

    function __construct() {
        parent::__construct();
        
        $this->load->library('Ion_auth');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('department_model');      
        $language = $this->db->get('settings')->row()->language;
        $this->lang->load('system_syntax', $language);
        $this->load->library('upload');
        $this->load->model('ion_auth_model');
        $this->load->model('settings_model');
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        if (!$this->ion_auth->in_group('admin')) {
            redirect('home/permission');
        }
    }

    public function index() {
        $data['departments'] = $this->department_model->getDepartment();
        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();
            

        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('department/dept_home', $data); 
        $this->load->view('home/admin_foot', $data);       
        $this->load->view('foot');
    }

    public function addNew() {
        $name = $this->input->post('name');
        $description = $this->input->post('description');

            $data = array(
                'dept_name' => $name,
                'description' => $description
            );
                $this->department_model->insertDepartment($data);
                $this->session->set_flashdata('success', 'Department Added Successfully');
            // Loading View
            redirect('department');
        }

    function updatedepartment_info() {
        $id = $this->input->post('id');
        $name = $this->input->post('name');
        $description = $this->input->post('description');
        $data = array(
            'dept_name' => $name,
            'description' => $description
        );
        $this->department_model->updateDepartment($id, $data);
        $this->session->set_flashdata('success', 'Department Updated Successfully');
        redirect('department');
    }

    function getDepartment() {
        $data['departments'] = $this->department_model->getDepartment();
        $this->load->view('department/department', $data);
    }

    function editDepartmentByJason() {
        $id = $this->input->get('id');
        $data['department'] = $this->department_model->getDepartmentById($id);
        echo json_encode($data);
    }

    function delete() {
        $id = $this->input->get('id');
        $this->department_model->delete($id);
        $this->session->set_flashdata('warning', 'Deleted Success');
        redirect('department');
    }

}

/* End of file department.php */
/* Location: ./application/modules/department/controllers/department.php */
