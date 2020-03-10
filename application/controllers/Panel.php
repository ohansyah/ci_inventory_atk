<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Panel extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Model_Panel');
        $this->load->model('Model_transaksi');
        $this->load->model('Model_budget');
    }

    public function index()
    {
        $this->getChart('');
    }

    public function getChart($year)
    {
        
        $query = $this->Model_auth->check();
        if ($query->num_rows()) {
            $data['content'] = 'admin/main.php';
            $data['sess'] = $this->session->userdata('user');
            $data['type'] = 'admin';
            
            // validate insert year
            $data['year'] = $year;
            if($data['year'] == ''){
                $data['year'] = 2020;
            }

            // mapping nominal request
            $chart_year = $this->Model_transaksi->CountYear($data['year']);
            $data['Map_chart_year'] = $this->MappingCount($chart_year);

            /// mapping budget
            $budget = $this->Model_budget->getBudget($data['year']);
            $data['Map_budget_year'] = $this->MappingBudget($budget);


            // count data
            $data['c_staff'] = $this->Model_Panel->CountStaff(1);
            $data['c_supplier'] = $this->Model_Panel->CountStaff(3);
            $data['c_barang'] = $this->Model_Panel->Count('barang');
            $data['c_trx'] = $this->Model_Panel->Count('request');

            // count request
            $data['New'] = $this->countStatus('New', $data);
            $data['Send'] = $this->countStatus('Send', $data);
            $data['Reject'] = $this->countStatus('Reject', $data);
            $data['Received'] = $this->countStatus('Received', $data);
            $data['Process'] = $this->countStatus('Process', $data);
            $this->load_page($data);
        } else {
            $this->session->unset_userdata('user');
            redirect('index.php');
        }

    }

    public function staff()
    {
        $this->Model_auth->check();
        $data['content'] = 'staff/main.php';
        $data['sess'] = $this->session->userdata('user');
        $data['type'] = 'staff';

        // count data
        $data['c_barang'] = $this->Model_Panel->Count('barang');
        $data['c_trx'] = $this->Model_Panel->Count('requestStaff');

        // count request
        $data['New'] = $this->countStatus('New', $data);
        $data['Send'] = $this->countStatus('Send', $data);
        $data['Reject'] = $this->countStatus('Reject', $data);
        $data['Received'] = $this->countStatus('Received', $data);
        $data['Process'] = $this->countStatus('Process', $data);
        $this->load_page($data);

    }

    public function supplier()
    {
        $this->Model_auth->check();
        $data['content'] = 'supplier/main.php';
        $data['sess'] = $this->session->userdata('user');
        $data['type'] = 'supplier';

        // count data
        $data['c_barang'] = $this->Model_Panel->Count('barangSupplier');
        $data['c_trx'] = $this->Model_Panel->Count('requestSupplier');

        // count request
        $data['New'] = $this->countStatus('New', $data);
        $data['Send'] = $this->countStatus('Send', $data);
        $data['Reject'] = $this->countStatus('Reject', $data);
        $data['Received'] = $this->countStatus('Received', $data);
        $data['Process'] = $this->countStatus('Process', $data);
        $this->load_page($data);

    }

    public function load_page($data)
    {
        $this->load->view('head.php');
        $this->load->view($data['type'].'/index.php', $data);
        $this->load->view('footer.php');
    }

    // mapping nominal request
    function MappingCount($chart_year){
        $Map_chart_year = array();
        for($i=1; $i<13; $i++){
            $swap = null;
            foreach($chart_year->result() as $row){
                if($i == $row->bulan){
                    $swap = $row->total;
                }    
            }
            array_push($Map_chart_year, $swap);
        }
        
        return $Map_chart_year;
    }
    

     /// mapping budget
    function MappingBudget($budget){
        $month = array(
            'januari','febuari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'
        );

        $ret = Array();
        for($i=0; $i<12; $i++){
            $swap = null;
            foreach($budget->result() as $row){
                if($month[$i] == $row->month){
                    $swap = $row->budget;
                }    
            }
            array_push($ret, $swap);
        }
        return $ret;
    }

    function countStatus($status, $data){
        try{
            $ret = number_format((float)(($this->Model_Panel->CountStatus($status, $data['sess']) / $data['c_trx']) * 100), 2, '.', '');
        }catch(Exception $e){
            $ret = 0;
        }
        return $ret;
    }
}