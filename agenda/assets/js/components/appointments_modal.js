/* ----------------------------------------------------------------------------
 * Easy!Appointments - Online Appointment Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://easyappointments.org
 * @since       v1.5.0
 * ---------------------------------------------------------------------------- */

/**
 * Appointments modal component.
 *
 * This module implements the appointments modal functionality, including recurrence handling.
 */
App.Components.AppointmentsModal = (function () {
    const $appointmentsModal = $('#appointments-modal');
    const $startDatetime = $('#start-datetime');
    const $endDatetime = $('#end-datetime');
    const $filterExistingCustomers = $('#filter-existing-customers');
    const $customerId = $('#customer-id');
    const $firstName = $('#first-name');
    const $lastName = $('#last-name');
    const $email = $('#email');
    const $phoneNumber = $('#phone-number');
    const $address = $('#address');
    const $city = $('#city');
    const $zipCode = $('#zip-code');
    const $language = $('#language');
    const $timezone = $('#timezone');
    const $customerNotes = $('#customer-notes');
    const $selectCustomer = $('#select-customer');
    const $saveAppointment = $('#save-appointment');
    const $appointmentId = $('#appointment-id');
    const $appointmentLocation = $('#appointment-location');
    const $appointmentStatus = $('#appointment-status');
    const $appointmentColor = $('#appointment-color');
    const $appointmentNotes = $('#appointment-notes');
    const $reloadAppointments = $('#reload-appointments');
    const $selectFilterItem = $('#select-filter-item');
    const $selectService = $('#select-service');
    const $selectProvider = $('#select-provider');
    const $insertAppointment = $('#insert-appointment');
    const $existingCustomersList = $('#existing-customers-list');
    const $newCustomer = $('#new-customer');
    const $customField1 = $('#custom-field-1');
    const $customField2 = $('#custom-field-2');
    const $customField3 = $('#custom-field-3');
    const $customField4 = $('#custom-field-4');
    const $customField5 = $('#custom-field-5');

    const moment = window.moment;

    /**
     * Update the displayed timezone.
     */
    function updateTimezone() {
        const providerId = $selectProvider.val();
        const provider = vars('available_providers').find(
            (availableProvider) => Number(availableProvider.id) === Number(providerId)
        );
        if (provider && provider.timezone) {
            $('.provider-timezone').text(vars('timezones')[provider.timezone]);
        }
    }

    /**
     * Validate the manage appointment dialog data.
     */
    function validateAppointmentForm() {
        $appointmentsModal.find('.is-invalid').removeClass('is-invalid');
        $appointmentsModal.find('.modal-message').addClass('d-none');

        try {
            // Validate required fields
            const requiredFields = ['#select-service', '#select-provider', '#start-datetime', '#end-datetime'];
            if (vars('require_customer_id')) {
                requiredFields.push('#customer-id');
            }
            let missingRequiredField = false;
            requiredFields.forEach(selector => {
                const $field = $appointmentsModal.find(selector);
                if (!$field.val()) {
                    $field.addClass('is-invalid');
                    missingRequiredField = true;
                }
            });
            if (missingRequiredField) {
                throw new Error(lang('fields_are_required'));
            }

            // Validate email
            if ($email.val() && !App.Utils.Validation.email($email.val())) {
                $email.addClass('is-invalid');
                throw new Error(lang('invalid_email'));
            }

            // Validate date range
            const startDateTime = App.Utils.UI.getDateTimePickerValue($startDatetime);
            const endDateTime = App.Utils.UI.getDateTimePickerValue($endDatetime);
            if (startDateTime >= endDateTime) {
                $startDatetime.addClass('is-invalid');
                $endDatetime.addClass('is-invalid');
                throw new Error(lang('start_date_before_end_error'));
            }

            // Validate recurrence fields
            if ($appointmentsModal.find('#is_recurring').is(':checked')) {
                const $frequency = $appointmentsModal.find('select[name="recurrence_frequency"]');
                const $interval = $appointmentsModal.find('input[name="recurrence_interval"]');
                const $endType = $appointmentsModal.find('input[name="recurrence_end_type"]:checked');
                const $endCount = $appointmentsModal.find('input[name="recurrence_end_count"]');
                const $endDate = $appointmentsModal.find('input[name="recurrence_end_date"]');

                if (!$frequency.val()) {
                    $frequency.addClass('is-invalid');
                    throw new Error(lang('recurrence_frequency_required'));
                }
                if (!$interval.val() || parseInt($interval.val()) < 1) {
                    $interval.addClass('is-invalid');
                    throw new Error(lang('recurrence_interval_invalid'));
                }
                if (!$endType.val()) {
                    $endType.addClass('is-invalid');
                    throw new Error(lang('recurrence_end_type_required'));
                }
                if ($endType.val() === 'count' && (!$endCount.val() || parseInt($endCount.val()) < 1)) {
                    $endCount.addClass('is-invalid');
                    throw new Error(lang('recurrence_end_count_invalid'));
                }
                if ($endType.val() === 'date' && !$endDate.val()) {
                    $endDate.addClass('is-invalid');
                    throw new Error(lang('recurrence_end_date_required'));
                }
                if ($endType.val() === 'date' && new Date($endDate.val()) < startDateTime) {
                    $endDate.addClass('is-invalid');
                    throw new Error(lang('recurrence_end_date_before_start'));
                }
            }

            return true;
        } catch (error) {
            $appointmentsModal
                .find('.modal-message')
                .addClass('alert-danger')
                .text(error.message)
                .removeClass('d-none');
            return false;
        }
    }

    /**
     * Add the component event listeners.
     */
    function addEventListeners() {
        $selectProvider.on('change', updateTimezone);

        $saveAppointment.on('click', () => {
            if (!validateAppointmentForm()) {
                return;
            }

            const startDateTimeObject = App.Utils.UI.getDateTimePickerValue($startDatetime);
            const startDatetime = moment(startDateTimeObject).format('YYYY-MM-DD HH:mm:ss');
            const endDateTimeObject = App.Utils.UI.getDateTimePickerValue($endDatetime);
            const endDatetime = moment(endDateTimeObject).format('YYYY-MM-DD HH:mm:ss');

            const appointment = {
                id_services: $selectService.val(),
                id_users_provider: $selectProvider.val(),
                start_datetime: startDatetime,
                end_datetime: endDatetime,
                location: $appointmentLocation.val(),
                color: App.Components.ColorSelection.getColor($appointmentColor),
                status: $appointmentStatus.val(),
                notes: $appointmentNotes.val(),
                is_unavailability: Number(false),
                is_recurring: $appointmentsModal.find('#is_recurring').is(':checked') ? 'on' : '',
                recurrence_frequency: $appointmentsModal.find('#is_recurring').is(':checked') ? $appointmentsModal.find('select[name="recurrence_frequency"]').val() : '',
                recurrence_interval: $appointmentsModal.find('#is_recurring').is(':checked') ? $appointmentsModal.find('input[name="recurrence_interval"]').val() : '',
                recurrence_end_type: $appointmentsModal.find('#is_recurring').is(':checked') ? $appointmentsModal.find('input[name="recurrence_end_type"]:checked').val() : '',
                recurrence_end_count: $appointmentsModal.find('#is_recurring').is(':checked') && $appointmentsModal.find('input[name="recurrence_end_type"]:checked').val() === 'count' ? $appointmentsModal.find('input[name="recurrence_end_count"]').val() : '',
                recurrence_end_date: $appointmentsModal.find('#is_recurring').is(':checked') && $appointmentsModal.find('input[name="recurrence_end_type"]:checked').val() === 'date' ? $appointmentsModal.find('input[name="recurrence_end_date"]').val() : ''
            };

            if ($appointmentId.val() !== '') {
                appointment.id = $appointmentId.val();
            }

            const customer = {
                first_name: $firstName.val(),
                last_name: $lastName.val(),
                email: $email.val(),
                phone_number: $phoneNumber.val(),
                address: $address.val(),
                city: $city.val(),
                zip_code: $zipCode.val(),
                language: $language.val(),
                timezone: $timezone.val(),
                notes: $customerNotes.val(),
                custom_field_1: $customField1.val(),
                custom_field_2: $customField2.val(),
                custom_field_3: $customField3.val(),
                custom_field_4: $customField4.val(),
                custom_field_5: $customField5.val()
            };

            if ($customerId.val() !== '') {
                customer.id = $customerId.val();
                appointment.id_users_customer = customer.id;
            }

            console.log('Appointment Data:', appointment);

            App.Http.Calendar.saveAppointment(appointment, customer, function() {
                App.Layouts.Backend.displayNotification(lang('appointment_saved'));
                $appointmentsModal.find('.alert').addClass('d-none');
                $appointmentsModal.modal('hide');
                $reloadAppointments.trigger('click');
            }, function() {
                $appointmentsModal.find('.modal-message').text(lang('service_communication_error'));
                $appointmentsModal.find('.modal-message').addClass('alert-danger').removeClass('d-none');
                $appointmentsModal.find('.modal-body').scrollTop(0);
            });
        });

        $insertAppointment.on('click', () => {
            $('.popover').remove();
            App.Components.AppointmentsModal.resetModal();

            if ($selectFilterItem.find('option:selected').attr('type') === 'provider') {
                const providerId = $('#select-filter-item').val();
                const providers = vars('available_providers').filter(
                    (provider) => Number(provider.id) === Number(providerId)
                );
                if (providers.length) {
                    $selectService.val(providers[0].services[0]).trigger('change');
                    $selectProvider.val(providerId);
                }
            } else if ($selectFilterItem.find('option:selected').attr('type') === 'service') {
                $selectService.find('option[value="' + $selectFilterItem.val() + '"]').prop('selected', true);
            } else {
                $selectService.find('option:first').prop('selected', true).trigger('change');
            }

            $selectProvider.trigger('change');

            const serviceId = $selectService.val();
            const service = vars('available_services').find(
                (availableService) => Number(availableService.id) === Number(serviceId)
            );
            const duration = service ? service.duration : 60;

            const startMoment = moment();
            const currentMin = parseInt(startMoment.format('mm'));
            if (currentMin > 0 && currentMin < 15) {
                startMoment.set({minutes: 15});
            } else if (currentMin > 15 && currentMin < 30) {
                startMoment.set({minutes: 30});
            } else if (currentMin > 30 && currentMin < 45) {
                startMoment.set({minutes: 45});
            } else {
                startMoment.add(1, 'hour').set({minutes: 0});
            }

            App.Utils.UI.setDateTimePickerValue($startDatetime, startMoment.toDate());
            App.Utils.UI.setDateTimePickerValue($endDatetime, startMoment.add(duration, 'minutes').toDate());

            $appointmentsModal.find('.modal-header h3').text(lang('new_appointment_title'));
            $appointmentsModal.modal('show');
        });

        $selectCustomer.on('click', (event) => {
            if (!$existingCustomersList.is(':visible')) {
                $(event.target).find('span').text(lang('hide'));
                $existingCustomersList.empty();
                $existingCustomersList.slideDown('slow');
                $filterExistingCustomers.fadeIn('slow').val('');
                vars('customers').forEach((customer) => {
                    $('<div/>', {
                        'data-id': customer.id,
                        'text': (customer.first_name || '[No First Name]') + ' ' + (customer.last_name || '[No Last Name]')
                    }).appendTo($existingCustomersList);
                });
            } else {
                $existingCustomersList.slideUp('slow');
                $filterExistingCustomers.fadeOut('slow');
                $(event.target).find('span').text(lang('select'));
            }
        });

        $appointmentsModal.on('click', '#existing-customers-list div', (event) => {
            const customerId = $(event.target).attr('data-id');
            const customer = vars('customers').find((customer) => Number(customer.id) === Number(customerId));
            if (customer) {
                $customerId.val(customer.id);
                $firstName.val(customer.first_name);
                $lastName.val(customer.last_name);
                $email.val(customer.email);
                $phoneNumber.val(customer.phone_number);
                $address.val(customer.address);
                $city.val(customer.city);
                $zipCode.val(customer.zip_code);
                $language.val(customer.language);
                $timezone.val(customer.timezone);
                $customerNotes.val(customer.notes);
                $customField1.val(customer.custom_field_1);
                $customField2.val(customer.custom_field_2);
                $customField3.val(customer.custom_field_3);
                $customField4.val(customer.custom_field_4);
                $customField5.val(customer.custom_field_5);
            }
            $selectCustomer.trigger('click');
        });

        let filterExistingCustomersTimeout = null;
        $filterExistingCustomers.on('keyup', (event) => {
            if (filterExistingCustomersTimeout) {
                clearTimeout(filterExistingCustomersTimeout);
            }
            const keyword = $(event.target).val().toLowerCase();
            filterExistingCustomersTimeout = setTimeout(() => {
                $('#loading').css('visibility', 'hidden');
                App.Http.Customers.search(keyword, 50)
                    .done((response) => {
                        $existingCustomersList.empty();
                        response.forEach((customer) => {
                            $('<div/>', {
                                'data-id': customer.id,
                                'text': (customer.first_name || '[No First Name]') + ' ' + (customer.last_name || '[No Last Name]')
                            }).appendTo($existingCustomersList);
                            if (!vars('customers').some((existingCustomer) => Number(existingCustomer.id) === Number(customer.id))) {
                                vars('customers').push(customer);
                            }
                        });
                    })
                    .fail(() => {
                        $existingCustomersList.empty();
                        vars('customers').forEach((customer) => {
                            if (
                                customer.first_name.toLowerCase().indexOf(keyword) !== -1 ||
                                customer.last_name.toLowerCase().indexOf(keyword) !== -1 ||
                                customer.email.toLowerCase().indexOf(keyword) !== -1 ||
                                customer.phone_number.toLowerCase().indexOf(keyword) !== -1 ||
                                customer.address.toLowerCase().indexOf(keyword) !== -1 ||
                                customer.city.toLowerCase().indexOf(keyword) !== -1 ||
                                customer.zip_code.toLowerCase().indexOf(keyword) !== -1 ||
                                customer.notes.toLowerCase().indexOf(keyword) !== -1
                            ) {
                                $('<div/>', {
                                    'data-id': customer.id,
                                    'text': (customer.first_name || '[No First Name]') + ' ' + (customer.last_name || '[No Last Name]')
                                }).appendTo($existingCustomersList);
                            }
                        });
                    })
                    .always(() => {
                        $('#loading').css('visibility', '');
                    });
            }, 1000);
        });

        $selectService.on('change', () => {
            const serviceId = $selectService.val();
            const providerId = $selectProvider.val();
            $selectProvider.empty();
            const service = vars('available_services').find(
                (availableService) => Number(availableService.id) === Number(serviceId)
            );
            if (service?.color) {
                App.Components.ColorSelection.setColor($appointmentColor, service.color);
            }
            const duration = service ? service.duration : 60;
            const startDateTimeObject = App.Utils.UI.getDateTimePickerValue($startDatetime);
            const endDateTimeObject = new Date(startDateTimeObject.getTime() + duration * 60000);
            App.Utils.UI.setDateTimePickerValue($endDatetime, endDateTimeObject);
            vars('available_providers').forEach((provider) => {
                provider.services.forEach((providerServiceId) => {
                    if (
                        vars('role_slug') === App.Layouts.Backend.DB_SLUG_PROVIDER &&
                        Number(provider.id) !== vars('user_id')
                    ) {
                        return;
                    }
                    if (
                        vars('role_slug') === App.Layouts.Backend.DB_SLUG_SECRETARY &&
                        vars('secretary_providers').indexOf(Number(provider.id)) === -1
                    ) {
                        return;
                    }
                    if (Number(providerServiceId) === Number(serviceId)) {
                        $selectProvider.append(new Option(provider.first_name + ' ' + provider.last_name, provider.id));
                    }
                });
                if ($selectProvider.find(`option[value="${providerId}"]`).length) {
                    $selectProvider.val(providerId);
                }
            });
        });

        $selectProvider.on('change', updateTimezone);

        $newCustomer.on('click', () => {
            $customerId.val('');
            $firstName.val('');
            $lastName.val('');
            $email.val('');
            $phoneNumber.val('');
            $address.val('');
            $city.val('');
            $zipCode.val('');
            $language.val(vars('default_language'));
            $timezone.val(vars('default_timezone'));
            $customerNotes.val('');
            $customField1.val('');
            $customField2.val('');
            $customField3.val('');
            $customField4.val('');
            $customField5.val('');
        });
    }

    /**
     * Reset Appointment Dialog
     */
    function resetModal() {
        $appointmentsModal.find('input, textarea').val('');
        $appointmentsModal.find('.modal-message').addClass('d-none');
        const defaultStatusValue = $appointmentStatus.find('option:first').val();
        $appointmentStatus.val(defaultStatusValue);
        $language.val(vars('default_language'));
        $timezone.val(vars('default_timezone'));
        $appointmentColor.find('.color-selection-option:first').trigger('click');
        $selectService.val($selectService.eq(0).attr('value'));
        $selectProvider.empty();
        vars('available_providers').forEach((provider) => {
            const serviceId = $selectService.val();
            const canProvideService = provider.services.some(
                (providerServiceId) => Number(providerServiceId) === Number(serviceId)
            );
            if (canProvideService) {
                $selectProvider.append(new Option(provider.first_name + ' ' + provider.last_name, provider.id));
            }
        });
        $existingCustomersList.slideUp('slow');
        $filterExistingCustomers.fadeOut('slow');
        $selectCustomer.find('span').text(lang('select'));
        const serviceId = $selectService.val();
        const service = vars('available_services').find(
            (availableService) => Number(availableService.id) === Number(serviceId)
        );
        const duration = service ? service.duration : 60;
        const startMoment = moment();
        const currentMin = parseInt(startMoment.format('mm'));
        if (currentMin > 0 && currentMin < 15) {
            startMoment.set({minutes: 15});
        } else if (currentMin > 15 && currentMin < 30) {
            startMoment.set({minutes: 30});
        } else if (currentMin > 30 && currentMin < 45) {
            startMoment.set({minutes: 45});
        } else {
            startMoment.add(1, 'hour').set({minutes: 0});
        }
        App.Utils.UI.setDateTimePickerValue($startDatetime, startMoment.toDate());
        App.Utils.UI.setDateTimePickerValue($endDatetime, startMoment.add(duration, 'minutes').toDate());
        // Reset recurrence fields
        $appointmentsModal.find('#is_recurring').prop('checked', false);
        $appointmentsModal.find('#recurrence-options').hide();
        $appointmentsModal.find('select[name="recurrence_frequency"]').val('daily');
        $appointmentsModal.find('input[name="recurrence_interval"]').val('1');
        $appointmentsModal.find('input[name="recurrence_end_type"][value="count"]').prop('checked', true);
        $appointmentsModal.find('input[name="recurrence_end_count"]').val('1');
        $appointmentsModal.find('input[name="recurrence_end_date"]').val('');
    }

    /**
     * Initialize the module.
     */
    function initialize() {
        addEventListeners();
    }

    document.addEventListener('DOMContentLoaded', initialize);

    return {
        resetModal,
        validateAppointmentForm,
    };
})();