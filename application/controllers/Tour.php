<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Tour extends REST_Controller
{

    private $auth;

    public function __construct()
    {
        parent::__construct();

        $this->auth = new stdClass();
        $this->load->helper('form');
        // load database
        $this->load->database();

        $this->load->model(array('tour_model' => 'tour'));
    }

    function addTour_post()
    {
        $data['user_id'] = $this->input->post('user_id');
        $data['name'] = $this->input->post('name');
        $data['description'] = $this->input->post('description');

        $insert_id = $this->tour->add_tour($data);

        if ($insert_id > 0) {
            $this->response([
                'message' => 'Your tour has been created in our system successfully.',
                'success'  => true,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'Your tour failed to be created in our system, please try again later.',
                'success'  => false,
            ], REST_Controller::HTTP_OK);
        }
    }

    function addTourPerson_post()
    {
        $data['tour_id'] = $this->input->post('tour_id');
        $data['person_name'] = $this->input->post('person_name');

        $insert_id = $this->tour->add_tour_person($data);

        if ($insert_id > 0) {
            $this->response([
                'message' => 'Person has been added.',
                'success'  => true,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'Person has been failed added, please try again later.',
                'success'  => false,
            ], REST_Controller::HTTP_OK);
        }
    }

    function addTourEntry_post()
    {
        $data['tour_id'] = $this->input->post('tour_id');
        $data['tour_person_id'] = $this->input->post('tour_person_id');
        $data['title'] = $this->input->post('title');
        $data['description'] = $this->input->post('description');
        $data['amount'] = $this->input->post('amount');
        $data['type'] = $this->input->post('type');
        $data['date'] = date('Y-m-d', strtotime($this->input->post('date')));

        $insert_id = $this->tour->add_tour_entry($data);

        if ($insert_id > 0) {
            $this->response([
                'message' => 'Tour entry has been added.',
                'success'  => true,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'Tour entry has been failed added, please try again later.',
                'success'  => false,
            ], REST_Controller::HTTP_OK);
        }
    }

    function getTour_post()
    {

        $user_id = $this->input->post('user_id');
        $tour = $this->input->post('tour');
        $PageIndex = $this->input->post('PageIndex');
        $formDate = $this->input->post('formDate');
        $toDate = $this->input->post('toDate');
        $isFilter = $this->input->post('isFilter');
        $WHERE = "user_id = $user_id ";

        $PageIndex = $PageIndex * 10;

        if (!empty($tour)) {
            $WHERE .= "AND name like '$tour%'";
        }

        if (!empty($formDate) && !empty($toDate)) {
            $FromDate = date('Y-m-d', strtotime($formDate));
            $ToDate = date('Y-m-d', strtotime($toDate));
            $WHERE .= "AND (added_on >= '$FromDate' and added_on <= '$ToDate')";
        }

        $data = $this->tour->get_AllTour($WHERE, $PageIndex);

        $this->response([
            'data' => $data,
            'success'  => true,
            'isFilter'  => $isFilter
        ], REST_Controller::HTTP_OK);
    }

    function getTourPerson_get()
    {
        $tour_id = $this->input->get('tour_id');

        $data = $this->tour->get_tourPerson($tour_id);

        foreach ($data as $key => $value) {
            $data[$key]['entry'] = $this->tour->getTourPersonEntry(array("tour_id" => $tour_id, "tour_person_id" => $value['id']));
            usort($data[$key]['entry'], function ($a, $b) {
                return $b['date'] <=> $a['date'];
            });
        }

        $this->response([
            'data' => $data,
            'success'  => true
        ], REST_Controller::HTTP_OK);
    }

    function getTourPersonEntry_get()
    {

        $tour_id = $this->input->get('tour_id');
        $tour_person_id = $this->input->get('tour_person_id');

        $data = $this->tour->getTourPersonEntry(array("tour_id" => $tour_id, "tour_person_id" => $tour_person_id));

        $this->response([
            'data' => $data,
            'success'  => true
        ], REST_Controller::HTTP_OK);
    }

    function finishTour_post()
    {
        $tour_id = $this->input->post('tour_id');

        $insert_id = $this->tour->finish_tour($tour_id, array('finish' => 1, 'finish_date' => date('Y-m-d H:i:s')));

        if ($insert_id) {
            $this->response([
                'message' => 'Congratulations your tour is over.',
                'success'  => true,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'your tour can not be over, please try again later.',
                'success'  => false,
            ], REST_Controller::HTTP_OK);
        }
    }

    function PayPersonAmount_post()
    {
        $data['tour_id'] = $this->input->post('tour_id');
        $data['sender'] = $this->input->post('sender');
        $data['receiver'] = $this->input->post('receiver');
        $data['amount'] = $this->input->post('amount');
        $data['type'] = $this->input->post('type');

        $insert_id = $this->tour->PayPersonAmount($data);

        if ($insert_id > 0) {
            $this->response([
                'message' => 'Your tour has been created in our system successfully.',
                'success'  => true,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'Your payment failed, please try again later',
                'success'  => false,
            ], REST_Controller::HTTP_OK);
        }
    }

    function getTourPersonName_get() {
        $tour_id = $this->input->get('tour_id');

        $data = $this->tour->get_tourPersonName($tour_id);

        $this->response([
            'data' => $data,
            'success'  => true
        ], REST_Controller::HTTP_OK);
    }

    function deleteTourPerson_post() {
        $tour_person_id = $this->input->post('tour_person_id');

        $status = $this->tour->DeleteTourPerson($tour_person_id);

        if ($status) {
            $this->response([
                'message' => 'Your tour person has been removed successfully.',
                'success'  => true,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'Sorry, we can\'t remove the tour person, please try again later',
                'success'  => false,
            ], REST_Controller::HTTP_OK);
        }
    }

    function getAllTour_get() {
        $user_id = $this->input->get('user_id');

        $data = $this->tour->getAllTourName($user_id);

        $TourName = array();
        foreach ($data as $key => $value) {
            $TourName[] = $value['name'];
        }

        $this->response([
            'data' => $TourName,
            'success'  => true
        ], REST_Controller::HTTP_OK);
    }
}
