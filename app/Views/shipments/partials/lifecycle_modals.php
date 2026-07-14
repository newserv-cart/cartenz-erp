<!-- =====================================================
     GLOBAL SHIPMENT LIFECYCLE MODALS
     Render exactly once, outside table-responsive containers.
====================================================== -->

<div
    class="modal fade"
    id="cancelShipmentModal"
    tabindex="-1"
    aria-labelledby="cancelShipmentModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form
                method="post"
                action=""
                id="cancelShipmentForm">

                <?= csrf_field() ?>

                <div class="modal-header bg-danger text-white">
                    <h5
                        class="modal-title"
                        id="cancelShipmentModalLabel">
                        Cancel Shipment
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning">
                        Shipment tidak dihapus. Data, tracking,
                        package, pricing, dan history tetap tersimpan.
                    </div>

                    <p>
                        AWB:
                        <strong id="cancelShipmentAwb">-</strong>
                    </p>

                    <label
                        for="cancelShipmentReason"
                        class="form-label">
                        Alasan pembatalan *
                    </label>

                    <textarea
                        id="cancelShipmentReason"
                        name="cancel_reason"
                        class="form-control"
                        rows="4"
                        minlength="10"
                        maxlength="1000"
                        required></textarea>

                    <div class="form-text">
                        Minimal 10 karakter. Alasan akan masuk
                        ke audit trail.
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Kembali
                    </button>

                    <button
                        type="submit"
                        class="btn btn-danger"
                        id="confirmCancelShipment">
                        Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div
    class="modal fade"
    id="deleteShipmentModal"
    tabindex="-1"
    aria-labelledby="deleteShipmentModalLabel"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form
                method="post"
                action=""
                id="deleteShipmentForm">

                <?= csrf_field() ?>

                <div class="modal-header bg-dark text-white">
                    <h5
                        class="modal-title"
                        id="deleteShipmentModalLabel">
                        Delete Shipment Permanently
                    </h5>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger">
                        Tindakan ini menghapus shipment DRAFT beserta
                        package, item, pricing, tracking, dan history.
                        Snapshot audit penghapusan tetap disimpan.
                    </div>

                    <p>
                        AWB:
                        <strong id="deleteShipmentAwb">-</strong>
                    </p>

                    <label
                        for="deleteShipmentReason"
                        class="form-label">
                        Alasan penghapusan *
                    </label>

                    <textarea
                        id="deleteShipmentReason"
                        name="delete_reason"
                        class="form-control mb-3"
                        rows="3"
                        minlength="10"
                        maxlength="1000"
                        required></textarea>

                    <label
                        for="deleteShipmentConfirmAwb"
                        class="form-label">
                        Ketik ulang AWB di atas
                    </label>

                    <input
                        id="deleteShipmentConfirmAwb"
                        type="text"
                        name="confirm_awb"
                        class="form-control"
                        autocomplete="off"
                        required>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Kembali
                    </button>

                    <button
                        type="submit"
                        class="btn btn-dark"
                        id="confirmDeleteShipment">
                        Delete Permanently
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function (window, document) {
    "use strict";

    if (window.CartenzLifecycleModalsInitialized) {
        return;
    }

    window.CartenzLifecycleModalsInitialized = true;

    const cancelModal =
        document.getElementById("cancelShipmentModal");

    const cancelForm =
        document.getElementById("cancelShipmentForm");

    const cancelAwb =
        document.getElementById("cancelShipmentAwb");

    const cancelReason =
        document.getElementById("cancelShipmentReason");

    const cancelSubmit =
        document.getElementById("confirmCancelShipment");

    cancelModal?.addEventListener(
        "show.bs.modal",
        function (event) {
            const trigger = event.relatedTarget;

            if (!(trigger instanceof HTMLElement)) {
                event.preventDefault();
                return;
            }

            cancelForm.action =
                trigger.dataset.actionUrl || "";

            cancelAwb.textContent =
                trigger.dataset.awb || "-";

            cancelReason.value = "";

            cancelSubmit.disabled = false;
            cancelSubmit.textContent =
                "Confirm Cancel";
        }
    );

    cancelModal?.addEventListener(
        "shown.bs.modal",
        function () {
            cancelReason?.focus();
        }
    );

    cancelForm?.addEventListener(
        "submit",
        function () {
            cancelSubmit.disabled = true;
            cancelSubmit.textContent =
                "Cancelling...";
        }
    );

    const deleteModal =
        document.getElementById("deleteShipmentModal");

    const deleteForm =
        document.getElementById("deleteShipmentForm");

    const deleteAwb =
        document.getElementById("deleteShipmentAwb");

    const deleteReason =
        document.getElementById("deleteShipmentReason");

    const deleteConfirmAwb =
        document.getElementById("deleteShipmentConfirmAwb");

    const deleteSubmit =
        document.getElementById("confirmDeleteShipment");

    deleteModal?.addEventListener(
        "show.bs.modal",
        function (event) {
            const trigger = event.relatedTarget;

            if (!(trigger instanceof HTMLElement)) {
                event.preventDefault();
                return;
            }

            deleteForm.action =
                trigger.dataset.actionUrl || "";

            deleteAwb.textContent =
                trigger.dataset.awb || "-";

            deleteReason.value = "";
            deleteConfirmAwb.value = "";

            deleteSubmit.disabled = false;
            deleteSubmit.textContent =
                "Delete Permanently";
        }
    );

    deleteModal?.addEventListener(
        "shown.bs.modal",
        function () {
            deleteReason?.focus();
        }
    );

    deleteForm?.addEventListener(
        "submit",
        function () {
            deleteSubmit.disabled = true;
            deleteSubmit.textContent =
                "Deleting...";
        }
    );

    document.addEventListener(
        "hide.bs.modal",
        function (event) {
            const modal = event.target;
            const activeElement =
                document.activeElement;

            if (
                modal instanceof HTMLElement
                && activeElement instanceof HTMLElement
                && modal.contains(activeElement)
            ) {
                activeElement.blur();
            }
        }
    );
})(window, document);
</script>
