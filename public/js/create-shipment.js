/*
=========================================================
CARTENZ ERP
Create Shipment — Sender / Receiver Source Toggle
Sprint 50.6 — Owner-scoped address history live search
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

        const addressIdInput = document.getElementById(
            config.addressIdInputId
        );

        const searchInput = document.getElementById(
            config.searchInputId
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

            setFieldsDisabled(
                newSection,
                useHistory
            );

            setFieldsDisabled(
                historySection,
                !useHistory
            );

            if (!useHistory) {
                if (addressIdInput) {
                    addressIdInput.value = '';
                }

                if (searchInput) {
                    searchInput.value = '';
                }
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
        addressIdInputId: 'sender_address_id',
        searchInputId: 'sender_history_search',
    });

    initializeAddressSource({
        newRadioId: 'receiver_source_new',
        historyRadioId: 'receiver_source_history',
        newSectionId: 'receiverNewSection',
        historySectionId: 'receiverHistorySection',
        addressIdInputId: 'receiver_address_id',
        searchInputId: 'receiver_history_search',
    });

    // =====================================================
    // OWNER-SCOPED ADDRESS HISTORY LIVE SEARCH
    // =====================================================

    function debounce(callback, delay) {
        delay = delay ?? 300;

        let timer = null;

        return function (...args) {
            window.clearTimeout(timer);

            timer = window.setTimeout(
                function () {
                    callback.apply(this, args);
                },
                delay
            );
        };
    }

    function setValueByPossibleIds(ids, value) {
        for (const id of ids) {
            const field = document.getElementById(id);

            if (!field) {
                continue;
            }

            field.value = value ?? '';

            field.dispatchEvent(
                new Event('change', {
                    bubbles: true,
                })
            );

            return;
        }
    }

    function fillAddressFields(prefix, address) {
        setValueByPossibleIds(
            [prefix + '_name'],
            address.contact_name
        );

        setValueByPossibleIds(
            [prefix + '_company'],
            address.company_name
        );

        setValueByPossibleIds(
            [prefix + '_phone'],
            address.phone
        );

        setValueByPossibleIds(
            [prefix + '_email'],
            address.email
        );

        setValueByPossibleIds(
            [prefix + '_identity'],
            address.identity_number
        );

        setValueByPossibleIds(
            [
                prefix + '_country',
                prefix + '_country_id',
            ],
            address.country_id
        );

        setValueByPossibleIds(
            [
                prefix + '_province',
                prefix + '_state',
            ],
            address.state
        );

        setValueByPossibleIds(
            [prefix + '_city'],
            address.city
        );

        setValueByPossibleIds(
            [prefix + '_postal'],
            address.postal_code
        );

        setValueByPossibleIds(
            [prefix + '_address'],
            address.address
        );
    }

    function setupAddressHistorySearch(config) {
        const searchInput = document.getElementById(
            config.searchInputId
        );

        const resultsContainer = document.getElementById(
            config.resultsContainerId
        );

        const selectedContainer = document.getElementById(
            config.selectedContainerId
        );

        const addressIdInput = document.getElementById(
            config.addressIdInputId
        );

        if (
            !searchInput
            || !resultsContainer
            || !addressIdInput
        ) {
            return;
        }

        let abortController = null;

        function hideResults() {
            resultsContainer.classList.add('d-none');
            resultsContainer.innerHTML = '';
        }

        function showMessage(message) {
            resultsContainer.innerHTML = '';

            const item = document.createElement('div');

            item.className =
                'list-group-item text-muted';

            item.textContent = message;

            resultsContainer.appendChild(item);
            resultsContainer.classList.remove('d-none');
        }

        function renderResults(addresses) {
            resultsContainer.innerHTML = '';

            if (
                !Array.isArray(addresses)
                || addresses.length === 0
            ) {
                showMessage('Riwayat tidak ditemukan.');
                return;
            }

            addresses.forEach(function (address) {
                const button =
                    document.createElement('button');

                button.type = 'button';

                button.className =
                    'list-group-item ' +
                    'list-group-item-action';

                const title =
                    document.createElement('div');

                title.className = 'fw-semibold';

                title.textContent =
                    address.contact_name ||
                    address.company_name ||
                    '-';

                const description =
                    document.createElement('small');

                description.className =
                    'text-muted d-block';

                const details = [
                    address.company_name,
                    address.phone,
                    address.city,
                    address.postal_code,
                ].filter(Boolean);

                description.textContent =
                    details.join(' · ');

                button.appendChild(title);
                button.appendChild(description);

                button.addEventListener(
                    'click',
                    function () {
                        addressIdInput.value =
                            String(address.id || '');

                        searchInput.value =
                            address.label ||
                            address.contact_name ||
                            '';

                        fillAddressFields(
                            config.prefix,
                            address
                        );

                        if (selectedContainer) {
                            selectedContainer.innerHTML = '';

                            const selectedTitle =
                                document.createElement('strong');

                            selectedTitle.textContent =
                                address.contact_name || '-';

                            const selectedDetails =
                                document.createElement('div');

                            selectedDetails.className =
                                'small text-muted mt-1';

                            selectedDetails.textContent = [
                                address.company_name,
                                address.phone,
                                address.address,
                                address.city,
                                address.postal_code,
                                address.country_name,
                            ]
                                .filter(Boolean)
                                .join(' · ');

                            selectedContainer.appendChild(
                                selectedTitle
                            );

                            selectedContainer.appendChild(
                                selectedDetails
                            );

                            selectedContainer.classList.remove(
                                'd-none'
                            );
                        }

                        hideResults();

                        console.log(
                            config.prefix.toUpperCase() +
                            ' HISTORY SELECTED:',
                            address
                        );
                    }
                );

                resultsContainer.appendChild(button);
            });

            resultsContainer.classList.remove('d-none');
        }

        async function performSearch() {
            const ownerTypeInput = document
                .getElementById('shipment_owner_type');

            const ownerIdInput = document
                .getElementById('shipment_owner_id');

            const ownerType = ownerTypeInput?.value || '';
            const ownerId = ownerIdInput?.value || '';

            if (!ownerType || !ownerId) {
                showMessage(
                    'Pilih account owner terlebih dahulu.'
                );

                return;
            }

            if (!window.addressHistorySearchUrl) {
                showMessage(
                    'URL pencarian riwayat belum tersedia.'
                );

                return;
            }

            if (abortController) {
                abortController.abort();
            }

            abortController = new AbortController();

            const url = new URL(
                window.addressHistorySearchUrl,
                window.location.origin
            );

            url.searchParams.set(
                'q',
                searchInput.value.trim()
            );

            url.searchParams.set(
                'address_type',
                config.addressType
            );

            url.searchParams.set(
                'owner_type',
                ownerType
            );

            url.searchParams.set(
                'owner_id',
                ownerId
            );

            showMessage('Mencari riwayat...');

            try {
                const response = await fetch(
                    url.toString(),
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With':
                                'XMLHttpRequest',
                        },
                        signal: abortController.signal,
                    }
                );

                const rawResponse = await response.text();

                let result = {};

                try {
                    result = rawResponse
                        ? JSON.parse(rawResponse)
                        : {};
                } catch (error) {
                    result = {
                        message: rawResponse,
                    };
                }

                if (!response.ok) {
                    throw new Error(
                        result.message ||
                        'Server error: ' + response.status
                    );
                }

                renderResults(result.data || []);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                console.error(
                    'ADDRESS HISTORY SEARCH FAILED:',
                    error
                );

                showMessage(
                    error.message ||
                    'Riwayat gagal dimuat.'
                );
            }
        }

        const debouncedSearch = debounce(
            performSearch,
            300
        );

        searchInput.addEventListener(
            'input',
            function () {
                addressIdInput.value = '';

                selectedContainer?.classList.add(
                    'd-none'
                );

                debouncedSearch();
            }
        );

        searchInput.addEventListener(
            'focus',
            function () {
                performSearch();
            }
        );

        document.addEventListener(
            'click',
            function (event) {
                if (
                    event.target === searchInput
                    || resultsContainer.contains(
                        event.target
                    )
                ) {
                    return;
                }

                hideResults();
            }
        );
    }

    setupAddressHistorySearch({
        prefix: 'sender',
        addressType: 'SENDER',
        searchInputId: 'sender_history_search',
        resultsContainerId: 'sender_history_results',
        selectedContainerId: 'sender_history_selected',
        addressIdInputId: 'sender_address_id',
    });

    setupAddressHistorySearch({
        prefix: 'receiver',
        addressType: 'RECEIVER',
        searchInputId: 'receiver_history_search',
        resultsContainerId: 'receiver_history_results',
        selectedContainerId: 'receiver_history_selected',
        addressIdInputId: 'receiver_address_id',
    });

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
