<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recurrences_model extends CI_Model {
    public function create_recurrence($data) {
        $this->db->insert('appointment_recurrences', $data);
        return $this->db->insert_id();
    }

    public function get_recurrence($id) {
        return $this->db->get_where('appointment_recurrences', ['id' => $id])->row_array();
    }

    public function update_recurrence($id, $data) {
        $this->db->where('id', $id)->update('appointment_recurrences', $data);
        return $this->db->affected_rows();
    }

    public function delete_recurrence($id) {
        $this->db->where('id', $id)->delete('appointment_recurrences');
        $this->db->where('recurrence_id', $id)->delete('ea_appointments');
        $this->db->where('recurrence_id', $id)->delete('appointment_recurrence_exceptions');
    }

    public function add_exception($recurrence_id, $occurrence_date, $exception_type, $edited_appointment_id = null) {
    $data = [
        'recurrence_id' => $recurrence_id,
        'occurrence_date' => $occurrence_date,
        'exception_type' => $exception_type,
        'edited_appointment_id' => $edited_appointment_id
    ];
    $this->db->insert('appointment_recurrence_exceptions', $data);
}
}