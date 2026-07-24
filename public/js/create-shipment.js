/*
=========================================================
CARTENZ ERP
Create Shipment — Sender / Receiver Source Toggle
Sprint 50.4
=========================================================
*/

document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('shipmentCreateForm')) {
        return;
    }

    // =====================================================
    // ADDRESS SOURCE: NEW / HISTORY
    // =====================================================

    function setSectionVisibility(element, visible) {
        if (!element) {
            return;
        }

        element.classList.toggle('d-none', !visible);
    }

    function setFieldsDisabled(container, disabled) {
        if (!container) {
            return;
        }

        container
            .querySelectorAll('input, select, textarea')
            .forEach(function (field) {
                field.disabled = disabled;
            });
    }

    function initializeAddressSource(config) {
        const newRadio = document.getElementById(
            config.newRadioId
        );

        const historyRadio = document.getElementById(
            config.historyRadioId
        );

        const newSection = document.getElementById(
            config.newSectionId
        );

        const historySection = document.getElementById(
            config.historySectionId
        );

        const historySelect = document.getElementById(
            config.historySelectId
        );

        function refresh() {
            const useHistory =
                historyRadio?.checked === true;

            setSectionVisibility(
                newSection,
                !useHistory
            );

            setSectionVisibility(
                historySection,
                useHistory
            );

            /*
             * Field yang tersembunyi dinonaktifkan agar
             * tidak ikut terkirim dan tidak mengganggu
             * validasi HTML required.
             */
            setFieldsDisabled(
                newSection,
                useHistory
            );

            setFieldsDisabled(
                historySection,
                !useHistory
            );

            if (!useHistory && historySelect) {
                historySelect.value = '';
            }
        }

        newRadio?.addEventListener(
            'change',
            refresh
        );

        historyRadio?.addEventListener(
            'change',
            refresh
        );

        refresh();
    }

    initializeAddressSource({
        newRadioId: 'sender_source_new',
        historyRadioId: 'sender_source_history',
        newSectionId: 'senderNewSection',
        historySectionId: 'senderHistorySection',
        historySelectId: 'sender_address_id',
    });

    initializeAddressSource({
        newRadioId: 'receiver_source_new',
        historyRadioId: 'receiver_source_history',
        newSectionId: 'receiverNewSection',
        historySectionId: 'receiverHistorySection',
        historySelectId: 'receiver_address_id',
    });

    // =====================================================
    // DROPDOWN RIWAYAT MENGISI FORM
    // =====================================================

    function fillFieldsFromAddress(
        selectId,
        fieldPrefix
    ) {
        const select =
            document.getElementById(selectId);

        if (!select) {
            return;
        }

        select.addEventListener('change', function () {
            const option =
                this.options[this.selectedIndex];

            if (!option || !this.value) {
                return;
            }

            const mapping = {
                name: option.dataset.contactName,
                company: option.dataset.company,
                phone: option.dataset.phone,
                email: option.dataset.email,
                identity: option.dataset.identity,
                country: option.dataset.countryId,
                province: option.dataset.state,
                city: option.dataset.city,
                postal: option.dataset.postal,
                address: option.dataset.address,
            };

            Object.entries(mapping).forEach(
                function ([suffix, value]) {
                    const field =
                        document.getElementById(
                            fieldPrefix + '_' + suffix
                        );

                    if (field) {
                        field.value = value || '';
                    }
                }
            );

            /*
             * Trigger country change untuk pricing.
             */
            if (fieldPrefix === 'receiver') {
                const countrySelect =
                    document.getElementById(
                        'receiver_country'
                    );

                if (countrySelect) {
                    countrySelect.value =
                        option.dataset.countryId || '';
                    countrySelect.dispatchEvent(
                        new Event('change')
                    );
                }
            }
        });
    }

    fillFieldsFromAddress(
        'sender_address_id',
        'sender'
    );

    fillFieldsFromAddress(
        'receiver_address_id',
        'receiver'
    );

    // =====================================================
    // OWNER SYNC
    // =====================================================

    const customerSelect = document.getElementById('customer_id');
    const ownerIdInput = document.getElementById('shipment_owner_id');

    if (customerSelect && ownerIdInput) {
        customerSelect.addEventListener('change', function () {
            const option = this.options[this.selectedIndex];

            ownerIdInput.value = option?.value || '';
        });

        if (customerSelect.value) {
            ownerIdInput.value = customerSelect.value;
        }
    }

    // =====================================================
    // RECEIVER COUNTRY -> HIDDEN country_id (pricing)
    // =====================================================

    const receiverCountrySelect = document.getElementById(
        'receiver_country'
    );
    const countryIdInput = document.getElementById('country_id');

    if (receiverCountrySelect && countryIdInput) {
        receiverCountrySelect.addEventListener('change', function () {
            countryIdInput.value = this.value;
            countryIdInput.dispatchEvent(new Event('change'));
        });

        if (receiverCountrySelect.value) {
            countryIdInput.value = receiverCountrySelect.value;
        }
    }
});
