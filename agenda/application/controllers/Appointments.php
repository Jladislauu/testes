<?php defined('BASEPATH') or exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * Appointments controller.
 *
 * Handles the appointments related operations.
 *
 * Notice: This file used to have the booking page related code which since v1.5 has now moved to the Booking.php
 * controller for improved consistency.
 *
 * @package Controllers
 */
class Appointments extends EA_Controller
{
    public array $allowed_appointment_fields = [
        'id',
        'start_datetime',
        'end_datetime',
        'location',
        'notes',
        'color',
        'status',
        'is_unavailability',
        'id_users_provider',
        'id_users_customer',
        'id_services',
    ];

    public array $optional_appointment_fields = [
        //
    ];

    /**
     * Appointments constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model('appointments_model');
        $this->load->model('roles_model');

        $this->load->library('accounts');
        $this->load->library('timezones');
        $this->load->library('webhooks_client');
    }

    /**
     * Support backwards compatibility for appointment links that still point to this URL.
     *
     * @param string $appointment_hash
     *
     * @deprecated Since 1.5
     */
    public function index(string $appointment_hash = ''): void
    {
        redirect('booking/' . $appointment_hash);
    }

    /**
     * Filter appointments by the provided keyword.
     */
    public function search(): void
    {
        try {
            if (cannot('view', PRIV_APPOINTMENTS)) {
                abort(403, 'Forbidden');
            }

            $keyword = request('keyword', '');

            $order_by = request('order_by', 'update_datetime DESC');

            $limit = request('limit', 1000);

            $offset = (int) request('offset', '0');

            $appointments = $this->appointments_model->search($keyword, $limit, $offset, $order_by);

            json_response($appointments);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Store a new appointment.
     */
    public function store(): void
    {
        try {
            if (cannot('add', PRIV_APPOINTMENTS)) {
                abort(403, 'Forbidden');
            }

            $appointment = json_decode(request('appointment'), true);

            $this->appointments_model->only($appointment, $this->allowed_appointment_fields);

            $this->appointments_model->optional($appointment, $this->optional_appointment_fields);

            $appointment_id = $this->appointments_model->save($appointment);

            $appointment = $this->appointments_model->find($appointment);

            $this->webhooks_client->trigger(WEBHOOK_APPOINTMENT_SAVE, $appointment);

            json_response([
                'success' => true,
                'id' => $appointment_id,
            ]);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Find an appointment.
     */
    public function find(): void
    {
        try {
            if (cannot('view', PRIV_APPOINTMENTS)) {
                abort(403, 'Forbidden');
            }

            $appointment_id = request('appointment_id');

            $appointment = $this->appointments_model->find($appointment_id);

            json_response($appointment);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Update a appointment.
     */
    public function update(): void
    {
        try {
            if (cannot('edit', PRIV_APPOINTMENTS)) {
                abort(403, 'Forbidden');
            }

            $appointment = json_decode(request('appointment'), true);

            $this->appointments_model->only($appointment, $this->allowed_appointment_fields);

            $this->appointments_model->optional($appointment, $this->optional_appointment_fields);

            $appointment_id = $this->appointments_model->save($appointment);

            json_response([
                'success' => true,
                'id' => $appointment_id,
            ]);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

    /**
     * Remove a appointment.
     */
    public function destroy(): void
    {
        try {
            if (cannot('delete', PRIV_APPOINTMENTS)) {
                abort(403, 'Forbidden');
            }

            $appointment_id = request('appointment_id');

            $appointment = $this->appointments_model->find($appointment_id);

            $this->appointments_model->delete($appointment_id);

            $this->webhooks_client->trigger(WEBHOOK_APPOINTMENT_DELETE, $appointment);

            json_response([
                'success' => true,
            ]);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }

public function save_appointment(): void {
        try {
        log_message('debug', 'Iniciando save_appointment');
        log_message('debug', 'Usuário atual: ' . json_encode($this->session->userdata()));
        if (cannot('add', PRIV_APPOINTMENTS)) {
            log_message('error', 'Permissão negada para add em PRIV_APPOINTMENTS');
            abort(403, 'Forbidden');
        }
        $data = json_decode(request('appointment'), true);
        log_message('debug', 'Dados recebidos: ' . json_encode($data));

        try {
            if (cannot('add', PRIV_APPOINTMENTS)) {
                abort(403, 'Forbidden');
            }

            $data = json_decode(request('appointment'), true);

            // Validação básica
            if (empty($data['id_users_customer']) || empty($data['id_services']) || empty($data['id_users_provider']) || 
                empty($data['start_datetime']) || empty($data['end_datetime'])) {
                throw new Exception('Campos obrigatórios ausentes');
            }

            $appointment_data = [
                'id_users_customer' => $data['id_users_customer'],
                'id_services' => $data['id_services'],
                'id_users_provider' => $data['id_users_provider'],
                'start_datetime' => $data['start_datetime'],
                'end_datetime' => $data['end_datetime'],
                'book_datetime' => date('Y-m-d H:i:s')
            ];

            // Aplicar validação de campos permitidos
            $this->appointments_model->only($appointment_data, $this->allowed_appointment_fields);

            // Aplicar campos opcionais
            $this->appointments_model->optional($appointment_data, $this->optional_appointment_fields);

            if (!empty($data['recurrence_type']) && !empty($data['recurrence_interval']) && !empty($data['recurrence_end_date'])) {
                $appointments = $this->appointments_model->generate_recurring_appointments(
                    $appointment_data,
                    $data['recurrence_type'],
                    $data['recurrence_interval'],
                    $data['recurrence_end_date']
                );

                // Disparar webhook para cada agendamento criado
                foreach ($appointments as $appointment) {
                    $this->webhooks_client->trigger(WEBHOOK_APPOINTMENT_SAVE, $appointment);
                }

                $response = [
                    'success' => true,
                    'message' => 'Agendamentos recorrentes criados',
                    'appointments' => $appointments
                ];
            } else {
                $appointment_id = $this->appointments_model->add($appointment_data);
                $appointment = $this->appointments_model->find($appointment_id);
                $this->webhooks_client->trigger(WEBHOOK_APPOINTMENT_SAVE, $appointment);

                $response = [
                    'success' => true,
                    'message' => 'Agendamento único criado',
                    'id' => $appointment_id
                ];
            }

            json_response($response);
        } catch (Throwable $e) {
            json_exception($e);
        }
    }
}
