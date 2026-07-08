<h2>📍 Add Address</h2>
<hr>

<form method="post" action="<?= base_url('customer/addresses/store') ?>">
    <?= csrf_field() ?>

    <div style="max-width: 500px; margin-bottom: 15px;">
        <label for="address_label">Label</label><br>
        <input type="text" id="address_label" name="address_label" required 
               value="<?= old('address_label') ?>" style="width: 100%; padding: 6px; margin-top: 4px;">
        <small class="text-muted">Contoh: Rumah, Kantor, Gudang</small>
    </div>

    <div style="max-width: 500px; margin-bottom: 15px;">
        <label for="contact_name">Receiver Name</label><br>
        <input type="text" id="contact_name" name="contact_name" required 
               value="<?= old('contact_name') ?>" style="width: 100%; padding: 6px; margin-top: 4px;">
    </div>

    <div style="max-width: 500px; margin-bottom: 15px;">
        <label for="company_name">Company Name</label><br>
        <input type="text" id="company_name" name="company_name" 
               value="<?= old('company_name') ?>" style="width: 100%; padding: 6px; margin-top: 4px;">
    </div>

    <div style="max-width: 500px; margin-bottom: 15px;">
        <label for="phone">Phone</label><br>
        <input type="text" id="phone" name="phone" required 
               value="<?= old('phone') ?>" style="width: 100%; padding: 6px; margin-top: 4px;">
    </div>

    <div style="max-width: 500px; margin-bottom: 15px;">
        <label for="email">Email</label><br>
        <input type="email" id="email" name="email" 
               value="<?= old('email') ?>" style="width: 100%; padding: 6px; margin-top: 4px;">
    </div>

    <div style="max-width: 500px; margin-bottom: 20px;">
        <label for="address">Full Address</label><br>
        <textarea id="address" name="address" rows="4" required 
                  style="width: 100%; padding: 6px; margin-top: 4px;"><?= old('address') ?></textarea>
    </div>

    <button type="submit" style="padding: 8px 16px; cursor: pointer;">
        💾 Save Address
    </button>
</form>