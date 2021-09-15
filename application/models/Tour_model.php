<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tour_model
 *
 * @author SBI CSP
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Tour_model extends CI_Model
{

    public function add_tour($data)
    {
        $this->db->insert('tbl_tour', $data);

        return $this->db->insert_id();
    }

    public function add_tour_person($data)
    {
        $this->db->insert('tbl_tour_person', $data);

        return $this->db->insert_id();
    }

    public function add_tour_entry($data)
    {
        $this->db->insert('tbl_tour_finance', $data);

        return $this->db->insert_id();
    }

    public function get_AllTour($condition, $offset)
    {

        $this->db->select("t.id, t.name, t.description, DATE_FORMAT(added_on, '%d %b %Y')as date, IF(finish = 1, DATE_FORMAT(finish_date, '%d %b %Y'), 'Running')as status,
        CASE
            WHEN finish_date = '0000-00-00 00:00:00' THEN 
                IFNULL((SELECT SUM(amount) FROM tbl_tour_finance WHERE tour_id = t.id),0)
            WHEN finish_date != '0000-00-00 00:00:00' THEN 
                IFNULL((SELECT SUM(amount) FROM tbl_tour_finance WHERE tour_id = t.id AND added_on < finish_date),0)
        END as budget");
        $this->db->from('tbl_tour t');
        $this->db->where($condition, NULL, FALSE);
        $this->db->order_by('id', 'desc');
        $this->db->limit(10, $offset);
        $query = $this->db->get();
        $data = $query->result_array();

        return $data;
    }

    public function get_tourPerson($tour_id)
    {
        $this->db->select("*, 
        ROUND(IFNULL(( SELECT CASE
            WHEN finish_date = '0000-00-00 00:00:00' THEN 
                IFNULL((SELECT SUM(amount) FROM tbl_tour_finance WHERE tour_id = $tour_id),0)
            WHEN finish_date != '0000-00-00 00:00:00' THEN 
                IFNULL((SELECT SUM(amount) FROM tbl_tour_finance WHERE added_on < finish_date AND tour_id = $tour_id),0)
        END FROM tbl_tour WHERE id = $tour_id), 0), 2)as budget,

        ROUND(IFNULL((SELECT CASE
            WHEN finish_date = '0000-00-00 00:00:00' THEN 
                IFNULL((SELECT SUM(amount) FROM tbl_tour_finance WHERE tour_id = $tour_id),0)
            WHEN finish_date != '0000-00-00 00:00:00' THEN 
                IFNULL((SELECT SUM(amount) FROM tbl_tour_finance WHERE added_on < finish_date AND tour_id = $tour_id),0)
        END FROM tbl_tour WHERE id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0), 2) as perPersonBudget,
        
        ROUND((IFNULL((SELECT SUM(amount) FROM tbl_tour_finance WHERE tour_id = $tour_id AND tour_person_id = tp.id),0)  + IFNULL((SELECT SUM(amount) FROM tbl_tour_person_pay WHERE tour_id = $tour_id AND sender = tp.id),0)), 2)as personBudget,

        CASE
            WHEN IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0) = IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0) THEN 
                '0'
            WHEN IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0) > IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0) THEN 
                ROUND(IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0) - IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0),2)
            WHEN IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0) < IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0) THEN 
                ROUND(IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0),2) - ROUND(IFNULL(IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0)+ IFNULL((SELECT SUM(amount) FROM tbl_tour_person_pay ttpp WHERE ttpp.tour_id = $tour_id AND ttpp.sender = tp.id),0),0), 2)
        END as pl,
        
        CASE
            WHEN IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0) = IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0) THEN 
                '0'
            WHEN IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0) > IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0) THEN 
                '1'
            WHEN IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0) < IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0) THEN 
                '2'
        END as status,
        
        CASE
            WHEN IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0) = IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0) THEN 
                '0'
            WHEN IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0) > IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0) THEN 
                ROUND(IFNULL(IFNULL((SELECT SUM(amount) FROM tbl_tour_finance WHERE tour_id = $tour_id AND tour_person_id = tp.id),0) - IFNULL((SELECT SUM(amount) FROM tbl_tour_person_pay tpp WHERE tpp.tour_id = $tour_id AND tpp.receiver = tp.id),0) ,0), 2)
            WHEN IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id AND tf.tour_person_id = tp.id),0) < IFNULL((SELECT SUM(amount) FROM tbl_tour_finance tf WHERE tf.tour_id = $tour_id) / (SELECT COUNT(*) FROM tbl_tour_person WHERE tour_id = $tour_id) ,0) THEN 
                ROUND(IFNULL(IFNULL((SELECT SUM(amount) FROM tbl_tour_finance WHERE tour_id = $tour_id AND tour_person_id = tp.id),0) + IFNULL((SELECT SUM(amount) FROM tbl_tour_person_pay tpp WHERE tpp.tour_id = $tour_id AND tpp.sender = tp.id),0) ,0), 2)
        END as sr");
        $this->db->from('tbl_tour_person tp');
        $this->db->where("tour_id", $tour_id);
        $query = $this->db->get();
        $data = $query->result_array();

        return $data;
    }

    public function getTourPersonEntry($condition)
    {

        $this->db->select("tf.title, tf.description, tf.amount, DATE_FORMAT(date, '%d %b %Y')as date,
        CASE
            WHEN type = 1 THEN 'Cash'
            WHEN type = 2 THEN 'Bank'
        END as type");
        $this->db->from('tbl_tour_finance tf');
        $this->db->where($condition, NULL, FALSE);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get();
        $data = $query->result_array();

        $this->db->select("CONCAT('Receive to ', (SELECT person_name FROM tbl_tour_person WHERE id = tpp.sender))as title, 'Send money when the tour is over' as description, tpp.amount, DATE_FORMAT(added_on, '%d %b %Y')as date, '' as finance_type,
        CASE
            WHEN type = 1 THEN 'Cash'
            WHEN type = 2 THEN 'Bank'
        END as type");
        $this->db->from('tbl_tour_person_pay tpp');
        $this->db->where(array("tour_id" => $condition['tour_id'], "receiver" => $condition['tour_person_id']), NULL, FALSE);
        $this->db->order_by('id', 'desc');
        $ReceiveQuery = $this->db->get();
        $ReceiveData = $ReceiveQuery->result_array();

        $this->db->select("CONCAT('Send to ', (SELECT person_name FROM tbl_tour_person WHERE id = tpp.receiver))as title, '' as description, tpp.amount, DATE_FORMAT(added_on, '%d %b %Y')as date, '' as finance_type,
        CASE
            WHEN type = 1 THEN 'Cash'
            WHEN type = 2 THEN 'Bank'
        END as type");
        $this->db->from('tbl_tour_person_pay tpp');
        $this->db->where(array("tour_id" => $condition['tour_id'], "sender" => $condition['tour_person_id']), NULL, FALSE);
        $this->db->order_by('id', 'desc');
        $SendQuery = $this->db->get();
        $SendData = $SendQuery->result_array();

        return array_merge($data, $ReceiveData, $SendData);
    }

    public function finish_tour($ID, $data)
    {
        $this->db->where('id', $ID); //which row want to upgrade  
        $this->db->update('tbl_tour', $data);
        return $this->db->affected_rows();
    }

    public function PayPersonAmount($data)
    {
        $this->db->insert('tbl_tour_person_pay', $data);

        return $this->db->insert_id();
    }

    public function get_tourPersonName($data) {
        $this->db->select("id, person_name");
        $this->db->from('tbl_tour_person');
        $this->db->where(array("tour_id" => $data), NULL, FALSE);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get();
        $data = $query->result_array();

        return $data;
    }

    public function DeleteTourPerson($personId) {
        $this->db->delete('tbl_tour_person', array('id' => $personId));
        return $this->db->affected_rows();
    }

    public function getAllTourName($UserId) {
        $this->db->select("name");
        $this->db->from('tbl_tour');
        $this->db->where(array("user_id" => $UserId), NULL, FALSE);
        $this->db->order_by('id', 'desc');
        $query = $this->db->get();
        $data = $query->result_array();

        return $data;
    }
}
