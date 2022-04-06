<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Doctor extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('Ion_auth');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('doctor_model');
        $this->load->library('Upload');
        $this->load->model('department_model');
        $language = $this->db->get('settings')->row()->language;
        $this->lang->load('system_syntax', $language);
        $this->load->model('ion_auth_model');
        $this->load->model('settings_model');
        $this->load->model('pathology_model');

        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        if (!$this->ion_auth->in_group(array('admin'))) {
            redirect('home/permission');
        }

        // load dd helper
        $this->load->helper('dd_helper');
    }

    public function index()
    {
        $data['doctors'] = $this->doctor_model->getDoctor();
        $data['departments'] = $this->department_model->getDepartment();
        $data['dr_id'] = $this->doctor_model->get_doctor_dr_id();

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId);
        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('doctor/dr_list', $data);
        $this->load->view('home/admin_foot', $data);
        $this->load->view('foot');
    }

    // Add New Doctor
    public function addNew()
    {
        $name       = $this->input->post('name');
        $dr_id      = $this->input->post('dr_id');
        $chamber    = $this->input->post('chamber');
        $phone      = $this->input->post('phone');
        $gender     = $this->input->post('gender');
        $department = $this->input->post('department');
        $activity   = $this->input->post('activity');
        $dr_profile = $this->input->post('profile');

        $data = array(
                    'dr_name'    => $name,
                    'dr_id'      => $dr_id,
                    'chamber'    => $chamber,
                    'phone'      => $phone,
                    'gender'     => $gender,
                    'profile'    => $dr_profile,
                    'department' => $department,
                    'stus'       => $activity
                );

        $this->doctor_model->insertDoctor($data);
        $this->session->set_flashdata('success', 'New Doctor Added');
        redirect('doctor');
    }

    //Doctor Information Update
    public function update()
    {
        $name           = $this->input->post('name');
        $dr_id          = $this->input->post('dr_id');
        $chamber        = $this->input->post('chamber');
        $phone          = $this->input->post('phone');
        $gender         = $this->input->post('gender');
        $department     = $this->input->post('department');
        $activity       = $this->input->post('activity');
        $dr_profile     = $this->input->post('profile');
        $dr_main_id     = $this->input->post('dr_main_id');

        $data = array(
                    'dr_name'    => $name,
                    'chamber'    => $chamber,
                    'phone'      => $phone,
                    'gender'     => $gender,
                    'profile'    => $dr_profile,
                    'department' => $department,
                    'stus'       => $activity
                );

        $this->doctor_model->updateDoctor($dr_main_id, $data);
        $this->session->set_flashdata('success', 'Doctor Info Updated');
        redirect('doctor');
    }

    // Doctor Profile Pic Upload
    public function update_pic()
    {
        $dr_main_id = $this->input->post('dr_main_id');
        $file_name = $_FILES['img_url']['name'];
        $file_name_pieces = explode('_', $file_name);
        $new_file_name = '';
        $count = 1;
        foreach ($file_name_pieces as $piece) {
            if ($count !== 1) {
                $piece = ucfirst($piece);
            }
            $new_file_name .= $piece;
            $count++;
        }
        $config = array(
            'file_name'         => $new_file_name,
            'upload_path'       => "./uploads/doctor/",
            'allowed_types'     => "*", // All Kinds of file Accept
            'overwrite'         => false,
            'max_size'          => "20480000", // Can be set to particular file size , here it is 200 MB(2048 Kb)
            'max_height'        => "1768",
            'max_width'         => "2024"
        );
        $this->load->library('Upload', $config);
        $this->upload->initialize($config);
        if ($this->upload->do_upload('img_url')) {
            $path = $this->upload->data();
            // Upload in Folder
            $img_url = "uploads/doctor/" . $config['file_name'];
            $data = array(
                'img_url' => $img_url
                );
            $this->doctor_model->update_dr_pic($dr_main_id, $data);
            $this->session->set_flashdata('success', 'Doctor Picture Added');
            redirect('doctor');
        } else {
            $this->session->set_flashdata('error', 'Please Check Picture Name');
            redirect('doctor');
        }
    }

    public function Drfee()
    {
        $data['doctorfee']  = $this->doctor_model->drfee();
        $data['doctors']    = $this->doctor_model->getDoctorforDrfee();

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId);
        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('doctor/dr_fee', $data);
        $this->load->view('home/admin_foot', $data);
        $this->load->view('foot');
    }

    //Doctor Ticket/Appoinment Fee Add
    public function addfee()
    {
        $dr_id          = $this->input->post('dr_id');
        $dr_firsttime   = $this->input->post('dr_firsttime');
        $dr_sectime     = $this->input->post('dr_sectime');
        $hospital_first = $this->input->post('hospital_first');
        $hospital_sec   = $this->input->post('hospital_sec');
        $data = array(
                'dr_a_idid_auto'    => $dr_id,
                'dr_firsttime'      => $dr_firsttime,
                'dr_sectime'        => $dr_sectime,
                'hospital_first'    => $hospital_first,
                'hospital_sec'      => $hospital_sec
            );
        $this->doctor_model->insertDoctorfee($data);
        $this->session->set_flashdata('success', 'Doctor Fee Added');
        redirect('doctor/drfee');
    }

    //Get Doctor By json
    public function editDoctorByJason()
    {
        $id = $this->input->get('id');
        $data['doctor'] = $this->doctor_model->getDoctorById($id);
        echo json_encode($data);
    }

    public function delete()
    {
        $id = $this->input->get('id');
        $this->doctor_model->delete_doctor($id);
        $this->session->set_flashdata('warning', 'Data Deleted ');
        redirect('doctor');
    }

    public function editDoctorFeeByJason()
    {
        $id = $this->input->get('id');
        $data['dr_fee'] = $this->doctor_model->getDoctorfeeById($id);
        echo json_encode($data);
    }

    public function update_fee()
    {
        $dr_id          = $this->input->post('dr_id');
        $dr_firsttime   = $this->input->post('dr_firsttime');
        $dr_sectime     = $this->input->post('dr_sectime');
        $hospital_first = $this->input->post('hospital_first');
        $hospital_sec   = $this->input->post('hospital_sec');
        $dr_fee_id      = $this->input->post('dr_fee_id');
        $data = array(
                    'dr_a_idid_auto'    => $dr_id,
                    'dr_firsttime'      => $dr_firsttime,
                    'dr_sectime'        => $dr_sectime,
                    'hospital_first'    => $hospital_first,
                    'hospital_sec'      => $hospital_sec
                );
        $this->doctor_model->updateDoctorfee($dr_fee_id, $data);
        $this->session->set_flashdata('success', 'Update Successfully');
        redirect('doctor/drfee');
    }

    public function deletedr_fee()
    {
        $id = $this->input->get('id');
        $this->doctor_model->delete_fee($id);
        $this->session->set_flashdata('warning', 'Deleted');
        redirect('doctor/drfee');
    }

    //Doctor Speciality View Method for Front View
    public function dr_spclty()
    {
        $data['doctors'] = $this->doctor_model->getDoctorforDrfee();

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId);
        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('doctor/dr_spciality', $data);
        $this->load->view('home/admin_foot', $data);
        $this->load->view('foot');
    }

    public function setDoctorSpeciality()
    {
        $drIdd = $this->input->post('drIddII');
        $drAutoIdd = $this->input->post('dr_at_idd');
        $dr_sp_text = $this->input->post('dr_sp_txt');

        $data = array(
            'dr_id_m'         => $drIdd,
            'dr_a_id_auto'    => $drAutoIdd,
            'dr_special'      => $dr_sp_text
            );
        $this->doctor_model->setDoctorSpeciality($data);
    }

    public function getDoctorAllSpeciality()
    {
        $dr_a_id = $this->input->get('dr_a_iidd');
        $data = $this->doctor_model->getDrAllSpeciality($dr_a_id);
        echo json_encode($data);
    }

    public function update_Dr_Speciality()
    {
        $drSpcl = $this->input->post('drSpcl');
        $iniq_id = $this->input->post('iniq_id');

        $data_dr_speciality = array(
                                'dr_special' => $drSpcl,
                            );

        $this->doctor_model->updateSpclty($data_dr_speciality, $iniq_id);
    }

    public function delete_Dr_Speciality()
    {
        $iniq_id = $this->input->post('iniq_id');
        $this->doctor_model->deleteSpclty($iniq_id);
    }

    // Doctor Chamber Time
    public function dr_chamber()
    {
        $data['doctors'] = $this->doctor_model->getDoctorforDrfee();

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId);
        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('doctor/doctor_chamber', $data);
        $this->load->view('home/admin_foot', $data);
        $this->load->view('foot');
    }

    public function setDoctorTimable()
    {
        $dr_a_Idd       = $this->input->post('dr_at_idd');
        $drIddII        = $this->input->post('drIddII');
        $daysSelect     = $this->input->post('daysSelect');
        $timeStarts     = $this->input->post('timeStarts');
        $timeEnds       = $this->input->post('timeEnds');

        $all_data = array(
                    'dr_auto_iidd_a' => $dr_a_Idd,
                    'dr_iiddd_man' => $drIddII,
                    'day' => $daysSelect,
                    'timestart' => $timeStarts,
                    'timeend' => $timeEnds
                );
        $this->doctor_model->setDoctorTimeable($all_data);
    }

    public function getDoctorTimeable()
    {
        $dr_a_idd = $this->input->get('dr_a_idd');
        $data = $this->doctor_model->getDoctorTimeable($dr_a_idd);
        echo json_encode($data);
    }

    public function update_Dr_Timabless()
    {
        $drDays = $this->input->post('drDays');
        $drTimeStart = $this->input->post('drTimeStart');
        $drTimeEnd = $this->input->post('drTimeEnd');
        $iniq_id = $this->input->post('iniq_id');

        $f_data = array(
                    'day' => $drDays,
                    'timestart' => $drTimeStart,
                    'timeend' => $drTimeEnd
                );
        $this->doctor_model->updateDoctorTime_able($f_data, $iniq_id);
    }

    public function delete_Dr_Time_able()
    {
        $iniq_id = $this->input->post('iniq_id');
        $this->doctor_model->deleteDRTime_able($iniq_id);
    }


    /**
    *    Doctors commission
    *   view commission form
    */
    public function commission()
    {
        // get doctors information
        $data['doc_info'] = $this->doctor_model->getDoctorThis();
        // get pathology department 
        $data['test_dept'] = $this->pathology_model->get_testDept();
        $data['commission_info'] = $this->doctor_model->get_reffer_commission();

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId);
        $data['site_set'] = $this->settings_model->getSettings();

        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('doctor/dr_commision', $data);
        $this->load->view('home/admin_foot', $data);
        $this->load->view('foot');
    }

    /**
     *    save doctor commission
     */
    
    public function save_commission()
    {
        $dr_a_idddd = $this->input->post('doctor_idd');
        $dept_idddd = $this->input->post('department_idd');
        $com_number = $this->input->post('comm_number');
        $comm_types = $this->input->post('com_type');

        $data_array = array(
                        'doctor__primary_auto_id'   => $dr_a_idddd, 
                        'dept_primary_auto_id'      => $dept_idddd, 
                        'commission'                => $com_number, 
                        'radio_self_ref'            => $comm_types, 
                     );
        $this->doctor_model->set_reffer_commission($data_array);
        $this->session->set_flashdata('success', 'Update Successfully');
        redirect('doctor/commission');
    }


    
}
