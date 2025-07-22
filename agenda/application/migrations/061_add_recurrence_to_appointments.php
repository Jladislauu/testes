<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_recurrence_to_appointments extends CI_Migration {

    public function up()
    {
        // Tabela para armazenar as regras de recorrência
        $this->dbforge->add_field([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ],
            'recurrence_type' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
                'comment' => 'daily, weekly, monthly'
            ],
            'separation_count' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 1,
                'comment' => 'e.g., repeats every 2 weeks'
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => TRUE,
            ],
            'max_occurrences' => [
                'type' => 'INT',
                'constraint' => 5,
                'null' => TRUE,
            ],
            'days_of_week' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                'comment' => 'Comma-separated list of days, e.g., mon,tue,wed'
            ],
        ]);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('ea_appointment_recurrences');

        // Adicionar a coluna de chave estrangeira na tabela de agendamentos
        $fields = [
            'id_recurrence' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'null' => TRUE,
                'after' => 'id_services'
            ]
        ];
        $this->dbforge->add_column('ea_appointments', $fields);

        // Adicionar a restrição de chave estrangeira
        $this->db->query('ALTER TABLE `ea_appointments` ADD CONSTRAINT `fk_appointments_recurrence` FOREIGN KEY (`id_recurrence`) REFERENCES `ea_appointment_recurrences`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        // Remover a chave estrangeira
        $this->db->query('ALTER TABLE `ea_appointments` DROP FOREIGN KEY `fk_appointments_recurrence`');

        // Remover a coluna
        $this->dbforge->drop_column('ea_appointments', 'id_recurrence');

        // Remover a tabela de recorrências
        $this->dbforge->drop_table('ea_appointment_recurrences');
    }
}