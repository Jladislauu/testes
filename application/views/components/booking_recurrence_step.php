<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="card">
    <div class="card-header">
        <h5><?= lang('recurrence') ?></h5>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label for="recurrence_frequency"><?= lang('recurrence_type') ?></label>
            <select class="form-control" id="recurrence_frequency" name="recurrence_frequency">
                <option value=""><?= lang('none') ?></option>
                <option value="daily"><?= lang('daily') ?></option>
                <option value="weekly"><?= lang('weekly') ?></option>
                <option value="monthly"><?= lang('monthly') ?></option>
                <option value="custom"><?= lang('custom') ?></option>
            </select>
        </div>
        <div id="recurrence_options" style="display: none;">
            <div class="form-group">
                <label for="interval_value"><?= lang('interval') ?></label>
                <input type="number" class="form-control" id="interval_value" name="interval_value" value="1" min="1">
            </div>
            <div class="form-group">
                <label for="days_of_week"><?= lang('days_of_week') ?> (<?= lang('for_weekly') ?>)</label>
                <input type="text" class="form-control" id="days_of_week" name="days_of_week" placeholder="Ex: 1,3,5 (Mon,Wed,Fri)">
            </div>
            <div class="form-group">
                <label for="end_date"><?= lang('end_date') ?></label>
                <input type="date" class="form-control" id="end_date" name="end_date">
            </div>
            <div class="form-group">
                <label for="repeat_count"><?= lang('repeat_count') ?></label>
                <input type="number" class="form-control" id="repeat_count" name="repeat_count" min="1">
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#recurrence_frequency').change(function() {
            $('#recurrence_options').toggle($(this).val() !== '');
        });
    });
</script>