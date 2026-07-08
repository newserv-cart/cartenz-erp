<h2>📍 My Addresses</h2>
<hr>

<!-- Tombol Tambah Alamat -->
<a href="<?= base_url('customer/addresses/create') ?>" class="btn btn-primary mb-3">
    ➕ Add Address
</a>

<hr>

<?php if (empty($addresses)): ?>
    <p class="text-muted">Belum ada alamat tersimpan</p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Label</th>
                <th>Receiver</th>
                <th>Phone</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($addresses as $address): ?>
                <tr>
                    <td><?= esc($address['address_label'] ?? '-') ?></td>
                    <td><?= esc($address['contact_name'] ?? '-') ?></td>
                    <td><?= esc($address['phone'] ?? '-') ?></td>
                    <td>
                        <a href="<?= base_url('customer/addresses/default/' . $address['id']) ?>">⭐ Default</a> |
                        <a href="<?= base_url('customer/addresses/edit/' . $address['id']) ?>">✏ Edit</a> |
                        <a href="<?= base_url('customer/addresses/delete/' . $address['id']) ?>"
                           onclick="return confirm('Hapus alamat ini?')">🗑 Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>