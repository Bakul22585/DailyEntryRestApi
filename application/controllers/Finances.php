<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries//PHPMailer/class.phpmailer.php';
require APPPATH . '/libraries//PHPMailer/class.smtp.php';

class Finances extends REST_Controller
{

    private $auth;

    public function __construct()
    {
        parent::__construct();

        $this->auth = new stdClass();
        $this->load->helper('form');
        // load database
        $this->load->database();

        $this->load->model(array('finances_model' => 'finances', 'user_model' => 'user'));
    }

    function add_post()
    {
        $data['user_id'] = $this->input->post('user_id');
        $data['to_user'] = $this->input->post('to_user');
        $data['description'] = $this->input->post('description');
        $data['amount'] = $this->input->post('amount');
        $data['type'] = $this->input->post('type');
        $data['finance_type'] = $this->input->post('finance_type');
        $data['date'] = date('Y-m-d', strtotime($this->input->post('date')));
        $data['cheque_number'] = $this->input->post('cheque_number');
        $data['cheque_date'] = $this->input->post('cheque_date');
        $data['is_complate'] = $this->input->post('is_complate');
        $image = $this->input->post('image');

        if ($data['is_complate'] == 1) {
            $data['complate_date']  = date('Y-m-d');
        }

        if (!empty($data['cheque_date'])) {
            $data['cheque_date'] = date('Y-m-d', strtotime($this->input->post('cheque_date')));
        }

        if (!empty($image)) {
            $data['img'] = $image;
        }

        $ToUserData = $this->user->get_touser(array("user_id" => $data['user_id'], "name" => $data['to_user']));
        if (count($ToUserData) > 0) {
            $data['to_user'] = $ToUserData[0]['id'];
        } else {
            $id = $this->user->add_touser(array("user_id" => $data['user_id'], "name" => $data['to_user']));
            $data['to_user'] = $id;
        }

        $insert_id = $this->finances->add_finance($data);

        if ($insert_id > 0) {
            $this->response([
                'message' => 'Your data has been stored in our system successfully.',
                'success'  => true,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'Your data failed to be stored in our system, please try again later.',
                'success'  => false,
            ], REST_Controller::HTTP_OK);
        }

    }

    function getFinanceList_post() {

        $user_id = $this->input->post('user_id');
        $user = $this->input->post('user');
        $PageIndex = $this->input->post('PageIndex');
        $formDate = $this->input->post('formDate');
        $toDate = $this->input->post('toDate');
        $isFilter = $this->input->post('isFilter');
        $WHERE = "f.user_id = $user_id ";

        $PageIndex = $PageIndex * 10;

        if (!empty($user)) {
            $WHERE .= "AND (SELECT name FROM tbl_touser WHERE id = f.to_user) like '$user%'";
        }

        if (!empty($formDate) && !empty($toDate)) {
            $FromDate = date('Y-m-d', strtotime($formDate));
            $ToDate = date('Y-m-d', strtotime($toDate));
            $WHERE .= "AND (f.date >= '$FromDate' and f.date <= '$ToDate')";
        }

        $data = $this->finances->get_financesEntry($WHERE, $PageIndex);

        $this->response([
            'data' => $data,
            'success'  => true,
            'isFilter'  => $isFilter
        ], REST_Controller::HTTP_OK);
    }

    function getBarCharData_get() {
        $user_id = $this->input->get('user_id');
        $status = $this->input->get('status');
        $type = '';

        if ($status != '0') {
            if ($status == '1') {
                $type = "AND type IN(1)";
            } else {
                $type = "AND type IN(2,3)";
            }
        }

        $data = $this->finances->get_barchardata($user_id, $type);
        $total = $this->finances->get_totalBalance($user_id, $type);

        $BarCharData = array();
        $BarCharData['OriginalData'] = $data;
        $BarCharData['total'] = $total;
        $BarCharData['credit'] = [];
        $BarCharData['debit'] = [];
        foreach ($data as $key => $value) {
            $value['id'] = $key + 1;
            if ($value['finance_type'] == 1) {
                $BarCharData['credit'][] = $value;
            } else {
                $value['amount'] = '-' . $value['amount'];
                $BarCharData['debit'][] = $value;
            }
        }

        $this->response([
            'data' => $BarCharData,
            'success'  => true
        ],
            REST_Controller::HTTP_OK
        );
    }

    function deleteFinanceEntry_post()
    {
        $finances_id = $this->input->post('finances_id');

        $status = $this->finances->DeleteFinanceEntry($finances_id);

        if ($status) {
            $this->response([
                'message' => 'Your finance entry has been removed successfully.',
                'success'  => true,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'Sorry, we can\'t remove the finance entry, please try again later',
                'success'  => false,
            ], REST_Controller::HTTP_OK);
        }
    }
}
