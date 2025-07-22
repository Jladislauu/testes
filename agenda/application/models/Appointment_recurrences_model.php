<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Appointment Recurrences model.
 *
 * @package Models
 */
class Appointment_recurrences_model extends EA_Model {

    /**
     * @var array
     */
    protected array $casts = [
        'id' => 'integer',
        'separation_count' => 'integer',
        'max_occurrences' => 'integer'
    ];

    /**
     * Save (insert or update) a recurrence rule.
     *
     * @param array $recurrence_rule Associative array with the recurrence data.
     * @return int Returns the recurrence rule ID.
     * @throws InvalidArgumentException
     */
    public function save(array $recurrence_rule): int
    {
        $this->validate($recurrence_rule);

        if (empty($recurrence_rule['id'])) {
            return $this->insert($recurrence_rule);
        } else {
            return $this->update($recurrence_rule);
        }
    }

    /**
     * Validate the recurrence rule data.
     *
     * @param array $recurrence_rule Associative array with the recurrence data.
     * @throws InvalidArgumentException
     */
    public function validate(array $recurrence_rule): void
    {
        if (empty($recurrence_rule['recurrence_type'])) {
            throw new InvalidArgumentException('Recurrence type is required.');
        }

        if (!in_array($recurrence_rule['recurrence_type'], ['daily', 'weekly', 'monthly'])) {
            throw new InvalidArgumentException('Invalid recurrence type provided.');
        }

        if (isset($recurrence_rule['end_date']) && !empty($recurrence_rule['end_date']) && !validate_date($recurrence_rule['end_date'])) {
            throw new InvalidArgumentException('Invalid end date for recurrence.');
        }
    }

    /**
     * Insert a new recurrence rule into the database.
     *
     * @param array $recurrence_rule Associative array with the recurrence data.
     * @return int Returns the recurrence rule ID.
     * @throws RuntimeException
     */
    protected function insert(array $recurrence_rule): int
    {
        if (!$this->db->insert('appointment_recurrences', $recurrence_rule)) {
            throw new RuntimeException('Could not insert recurrence rule.');
        }
        return $this->db->insert_id();
    }

    /**
     * Update an existing recurrence rule.
     *
     * @param array $recurrence_rule Associative array with the recurrence data.
     * @return int Returns the recurrence rule ID.
     * @throws RuntimeException
     */
    protected function update(array $recurrence_rule): int
    {
        if (!$this->db->update('appointment_recurrences', $recurrence_rule, ['id' => $recurrence_rule['id']])) {
            throw new RuntimeException('Could not update recurrence rule record.');
        }
        return $recurrence_rule['id'];
    }

    /**
     * Get a specific recurrence rule from the database.
     *
     * @param int $recurrence_id The ID of the record to be returned.
     * @return array Returns an array with the recurrence data.
     * @throws InvalidArgumentException
     */
    public function find(int $recurrence_id): array
    {
        $recurrence_rule = $this->db->get_where('appointment_recurrences', ['id' => $recurrence_id])->row_array();

        if (!$recurrence_rule) {
            throw new InvalidArgumentException(
                'The provided recurrence ID was not found in the database: ' . $recurrence_id
            );
        }

        $this->cast($recurrence_rule);

        return $recurrence_rule;
    }

    /**
     * Remove an existing recurrence rule from the database.
     *
     * @param int $recurrence_id Recurrence Rule ID.
     * @throws RuntimeException
     */
    public function delete(int $recurrence_id): void
    {
        // Note: The foreign key constraint on ea_appointments is ON DELETE SET NULL,
        // so related appointments will be unlinked, not deleted.
        $this->db->delete('appointment_recurrences', ['id' => $recurrence_id]);
    }
}