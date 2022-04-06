<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Labrcv extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('Ion_auth');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->model('labrcv_model');
        $this->load->model('doctor_model');
        $this->load->model('usermgnt_model');
        $this->load->library('upload');
        // $this->load->library('Pdf');
        $language = $this->db->get('settings')->row()->language;
        $this->lang->load('system_syntax', $language);
        $this->load->model('ion_auth_model');
        $this->load->model('settings_model');
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login', 'refresh');
        }
        if (!$this->ion_auth->in_group(array('admin', 'Accountant', 'Receptionist', 'Laboratorist'))) {
            redirect('home/permission');
        }
    }

    function index() {
        $data['ptNInfo'] = $this->labrcv_model->getptninfo();

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('labrcv/index', $data); 
        $this->load->view('home/admin_foot', $data);       
        $this->load->view('foot');
    }

    public function path_report()
    {
        $data['ptNInfo'] = $this->labrcv_model->getptninfo();

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $data['all_users'] = $this->usermgnt_model->getuser();
            
        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('labrcv/path_report', $data); 
        $this->load->view('home/admin_foot', $data);       
        $this->load->view('foot');
    }

    function editReceiveTest() {
        $data['ptNInfo'] = $this->labrcv_model->getptninfo();
        $loginId = $this->ion_auth->user()->row()->emp_id;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 

        $this->load->view('home/dashboard', $data); // just the header file
        $this->load->view('labrcv/editRcvTst', $data);
        $this->load->view('home/footer'); // just the header file

    }

    function addnew() {
        $data['doctor'] = $this->labrcv_model->getdoctor();        
        $data['labtest'] = $this->labrcv_model->getlabtest_with_active();

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('labrcv/addnew_test', $data); 
        $this->load->view('home/admin_foot', $data);       
        $this->load->view('foot');
    }

    function testbill() {
        $tstiid = $this->input->get('tstiid');
        $data['tstinfo'] = $this->labrcv_model->gettestforrate($tstiid);
        echo json_encode($data);
    }

    function search_patientByID() {
        $serch_Idi = $this->input->get('id');
        $data = $this->labrcv_model->getPatienbyid($serch_Idi);
        echo json_encode($data);
    }

    function newtest() {    	

        $newlabiidi = 0; // Check last id and inchement    
        $newlabptnid_date_wise = 0;
        $getid = $this->labrcv_model->getlabtstiid();
        $thsyer = date('Y', time());
        $thsmnth = date('m', time()); 
        $thistims       = time();
        $thssdateee     = date('Y-m-d', time());

        if ($thsyer != $getid->thsyear) {
            $newlabiidi = date('y',time()).'00000001';
        }else {
            $newlabiidi = $getid->lab_rgstr_iidd + 1;
        } // Check last id and inchement

        if ($thssdateee != $getid->this_date) {
            $newlabptnid_date_wise = date('ymd', time()).'01';
        }else {
            $newlabptnid_date_wise = $getid->date_wise_ptn_id + 1;
        } // Check last id date wise and inchement


        $emp_id         = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $patnname       = $this->input->post('ptnname');
        $patnage        = $this->input->post('ptnage').' '.$this->input->post('ymd');
        $sex            = $this->input->post('gender');
        $drid           = $this->input->post('dctr_id');
        $ttrate         = $this->input->post('ttlrattte');
        $dscntamnt      = $this->input->post('discnt');
        $ttlrcvtaka     = $this->input->post('ttlrcvamnt');

        $duerfr         = $this->input->post('hsprfrname');
        $ptnmblno       = $this->input->post('ptnmblno');
        $discntrfstxs   = $this->input->post('discntrfstxs');
        $due_ref_mobile = $this->input->post('due_ref_mobile');

        $outdrnam       = $this->input->post('optdrname');
        $outdrdegre     = $this->input->post('optdrdgre');
        
        $dscntamtk = $ttrate - $dscntamnt;
        $dscnt123 = $dscntamtk * 100 / $ttrate;
        $dscntprcntgs = 100 - $dscnt123;

        $ttlduetk = $dscntamtk - $ttlrcvtaka;

        $tstidid        = array($this->input->post('test_iiddd'));
        //$tstyp          = array($this->input->post('testtypss'));
        $tstamntk       = array($this->input->post('testtakk'));


        $data = array(
            'lab_rgstr_iidd'        => $newlabiidi, 
            'date_wise_ptn_id'      => $newlabptnid_date_wise, 
            'labpnname'             => $patnname, 
            'labpn_age'             => $patnage, 
            'lbpdr_id'              => $drid, 
            'outdr_name'            => $outdrnam, 
            'outdr_degree'          => $outdrdegre, 
            'gndr'                  => $sex, 
            'ptnmbl'                => $ptnmblno, 
            'tst_rcv_emp'           => $emp_id, 
            'this_tim'              => $thistims, 
            'this_date'             => $thssdateee, 
            'thsyear'               => $thsyer, 
            'thshmnth'              => $thsmnth,
            'ttl_bill_tk'           => $ttrate, 
            'ttl_dscnt_tk'          => $dscntamnt, 
            'ttl_dscnt_prsnt'       => $dscntprcntgs, 
            'dscnt_name'            => $discntrfstxs, 
            'ttl_due'               => $ttlduetk,  
            'un_paid_amount'        => $ttlduetk, 
            'rcv_tak'               => $ttlrcvtaka, 
            'due_ref_mobile'        => $due_ref_mobile, 
            'duerfrtxt'             => $duerfr
        );
        $last_insert_id = $this->labrcv_model->insert_labrcvptn($data);        


        $f_data = [];
        foreach ($tstamntk as $key => $value) {
            foreach ($value as $key1 => $value1) {
                $f_data[] = [
                    'tstamnttaka'           => $tstamntk[$key][$key1],
                    'tstiiddid'             => $tstidid[$key][$key1],
                    'labptnididid'          => $newlabiidi, 
                    'lab_ptn_auto_idd_ss'   => $last_insert_id, 
                    'thssdate'              => $thssdateee, 
                    'thsstimess'            => $thistims,
                    'user_emp_id'           => $emp_id 
                ];
            }
        }

        $this->labrcv_model->insert_rcvtstinfo($f_data);        

        $tk_data = array(
            'lbpn_iiddd'            => $newlabiidi,
            'lab_ptn_auto_idd_ss'   => $last_insert_id,
            'rcv_amont'             => $ttlrcvtaka,
            'thssstime'             => $thistims,
            'thssdatee'             => $thssdateee,
            'emp_id_user'           => $emp_id 
        );
        $this->labrcv_model->insert_rcvttkdata($tk_data);

        $this->session->set_flashdata('success', 'Test Added'); 

        $link = "<script>window.open('print_memo?labrcvid=$last_insert_id','_blank', 'width=700,height=700,left=260,top=270');window.location.href = 'addnew';</script>";
        echo $link; 

    }


    function print_memo()
    {
        $labrcvidii = $this->input->get('labrcvid');
        $data['patient_info'] = $this->labrcv_model->getLabPatient($labrcvidii);        
        $data['labtest_forprint'] = $this->labrcv_model->gettstByPtnIDD($labrcvidii);        
        $data['department'] = $this->labrcv_model->getLabDepartment();   


        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();
        
        $this->load->view('head', $data);
        $this->load->view('labrcv/main_invoice', $data); 
        $this->load->view('foot');


        // // HTML to PDF
        // $html = $this->output->get_output();
        // $this->dompdf->loadHtml($html);
        // $this->dompdf->setPaper('A4', 'portrait');
        // $this->dompdf->render();        
        // $this->dompdf->stream("Bill.pdf", array("Attachment"=>0));
        // //Output Line
    }

    function getAllTstData() {
        $labPtnIIDDD = $this->input->post('labPtnIIDD');
        $data['labPtn'] = $this->labrcv_model->getLabTstPtnInfo($labPtnIIDDD);
        $data['labPtn'] = $this->labrcv_model->getLabTstPtnInfo($labPtnIIDDD);
        echo json_encode($data);
    } 

    public function editTest()
    {
        $data['doctor'] = $this->labrcv_model->getdoctor();        
        $data['labtest'] = $this->labrcv_model->getlabtest_with_active();

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('labrcv/edit_test_a', $data); 
        $this->load->view('home/admin_foot', $data);       
        $this->load->view('foot');
    }

    public function getPatient_bydate_ptn_id()
    {
        $serch_Idi = $this->input->get('ptn_iddi');
        $data['ptn_details'] = $this->labrcv_model->getPatienbyDateid($serch_Idi);
        echo json_encode($data);
    }

    public function getPatientTest_byP_iiddd()
    {
        $labrcvidii = $this->input->get('ptn_iddi');
        $data = $this->labrcv_model->getLabTestforP($labrcvidii);
        echo json_encode($data);
    }

    public function test_statement_report()
    {
        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $start_date_temp = $this->input->get('st_date');
        $end_date_temp = $this->input->get('last_date');
        $start_date  = date('Y-m-d', strtotime($start_date_temp));
        $end_date  = date('Y-m-d', strtotime($end_date_temp));

        $data['s_date'] = $start_date_temp;
        $data['l_date'] = $end_date_temp;

        $data['labrcv_statement_with_patient'] = $this->labrcv_model->getlabrcv_statement_with_patient($start_date, $end_date);
        $data['labrcv_test_statement'] = $this->labrcv_model->get_labrcv_test_statement($start_date, $end_date);

        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();

        $this->load->view('labrcv/test_statement_report', $data);
    }

    public function due_list_statement_report()
    {
        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $start_date_temp = $this->input->get('st_date');
        $end_date_temp = $this->input->get('last_date');
        $start_date  = date('Y-m-d', strtotime($start_date_temp));
        $end_date  = date('Y-m-d', strtotime($end_date_temp));

        $data['s_date'] = $start_date_temp;
        $data['l_date'] = $end_date_temp;

        $data['labrcv_statement_with_patient'] = $this->labrcv_model->getlabrcv_due_list_statement_with_patient($start_date, $end_date);
        $data['labrcv_test_statement'] = $this->labrcv_model->get_labrcv_test_statement($start_date, $end_date);

        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();

        $this->load->view('labrcv/due_list_statement', $data);

    }

    public function paid_due_amount()
    {
        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();
            
        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('labrcv/paid_due_amount_view', $data); 
        $this->load->view('home/admin_foot', $data);       
        $this->load->view('foot');   
    }

    public function get_due_paid_history()
    {
        $labrcvidii = $this->input->get('ptn_iddi');
        $data = $this->labrcv_model->get_due_paid_history($labrcvidii);
        echo json_encode($data);
    }

    public function entry_paidable_due_amount()
    {
        $patient_primary_idd = $this->input->post('patient_primary_idd');        
        $typed_paid_amount = $this->input->post('typed_paid_amount');        
        $unpaid_amount = $this->input->post('unpaid_amount');

        // Insert Paid Amount
        $insertable_array_data = array(
            'labrcv_patient_primary_iddii' => $patient_primary_idd, 
            'paid_dates' => date('Y-m-d', time()), 
            'paid_amount' => $typed_paid_amount, 
            'unpaid_amount' => $unpaid_amount, 
        );
        $this->labrcv_model->entry_paidable_due_amount($insertable_array_data);

        // Update unpaid amount
        $update_patient_array_data = array(
            'un_paid_amount' => $unpaid_amount,
        );
        $this->labrcv_model->update_lab_patient_info($patient_primary_idd, $update_patient_array_data);

        // insert lab test receive amount
        $insert_test_rcv_array_data = array(
            'lab_ptn_auto_idd_ss'   => $patient_primary_idd,
            'rcv_amont'             => $typed_paid_amount,
            'thssstime'             => time(),
            'thssdatee'             => date('Y-m-d', time()),
            'emp_id_user'           => $this->ion_auth->user()->row()->auto_emp_a_iid
        );
        $this->labrcv_model->insert_test_rcv_amount($insert_test_rcv_array_data);
    }

    public function print_due_paid_memo()
    {
        $due_paid_p_id = $this->input->get('ptn_id'); 

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $data['paid_data'] = $this->labrcv_model->get_due_paid_by_id($due_paid_p_id);

        $this->load->view('head', $data);      
        $this->load->view('labrcv/print_due_paid_memo', $data);
        $this->load->view('foot');  
    }

    public function refferfee()
    {
        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $data['all_doctor'] = $this->doctor_model->getDoctor();
            
        $this->load->view('head', $data);
        $this->load->view('home/admin_head', $data);
        $this->load->view('labrcv/reffer_fee', $data); 
        $this->load->view('home/admin_foot', $data);       
        $this->load->view('foot');  
    }

    public function patho_commission()
    { 
        $temp_start_date = $this->input->get('st_date');
        $temp_end_date = $this->input->get('last_date');

        $start_date = date('Y-m-d', strtotime($temp_start_date));
        $end_date = date('Y-m-d', strtotime($temp_end_date));

        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;

        $doctor_ids = $this->input->get('dr_id');
        $data['dr_info'] = $this->labrcv_model->getdoctor_id($doctor_ids);

        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $data['dr_patho_comission'] = $this->labrcv_model->get_doctor_patho_commission($start_date, $end_date, $doctor_ids);

        $this->load->view('head', $data);      
        $this->load->view('labrcv/patho_comission_view', $data);
        $this->load->view('foot'); 
    }

    public function rado_commission() 
    {
    }

    public function disscount_list_statement_report()
    {
        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $data['paid_data'] = $this->labrcv_model->get_due_paid_by_id($due_paid_p_id);

        $start_date_temp = $this->input->get('st_date');
        $end_date_temp = $this->input->get('last_date');
        $start_date  = date('Y-m-d', strtotime($start_date_temp));
        $end_date  = date('Y-m-d', strtotime($end_date_temp));

        $data['s_date'] = $start_date_temp;
        $data['l_date'] = $end_date_temp;

        $data['labrcv_statement_with_patient'] = $this->labrcv_model->getlabrcv_statement_with_patient($start_date, $end_date);
        $data['labrcv_test_statement'] = $this->labrcv_model->get_labrcv_test_statement($start_date, $end_date);


        $this->load->view('labrcv/disscount_list_statement', $data);
    }

    public function summary_statement_report()
    {
        $this->load->view('labrcv/summary_statement');
    }

    public function user_by_pathology_report()
    {
        $loginId = $this->ion_auth->user()->row()->auto_emp_a_iid;
        $data['user_P'] = $this->settings_model->get_log_user($loginId); 
        $data['site_set'] = $this->settings_model->getSettings();

        $start_date_temp = $this->input->get('st_date');
        $end_date_temp = $this->input->get('last_date');
        $start_date  = date('Y-m-d', strtotime($start_date_temp));
        $end_date  = date('Y-m-d', strtotime($end_date_temp));

        $data['s_date'] = $start_date_temp;
        $data['l_date'] = $end_date_temp;

        $data['labrcv_statement_with_patient'] = $this->labrcv_model->getlabrcv_statement_with_patient($start_date, $end_date);
        $data['labrcv_test_statement'] = $this->labrcv_model->get_labrcv_test_statement($start_date, $end_date);

        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();
        // $data['labtest'] = $this->labrcv_model->getlabtest_with_active();

        
        $this->load->view('labrcv/report_by_user_statement',$data);
    }




// END Bracket
}

