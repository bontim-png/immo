<div class="property-form-page">
    <form method="POST" action="<?= route('properties.store') ?>" class="property-form" enctype="multipart/form-data">
        <?= csrf_input() ?>
        
        <!-- Header -->
        <div class="form-header">
            <h2><?= trans('create_property') ?></h2>
            <p><?= trans('create_property_help') ?></p>
        </div>

        <!-- Property Details Section -->
        <div class="form-section">
            <h3 class="section-title"><?= trans('property_details') ?></h3>
            <p class="section-help"><?= trans('property_details_help') ?></p>
            
            <div class="form-grid">
                <!-- Reference -->
                <div class="form-group">
                    <label for="reference_code" class="form-label">
                        <?= trans('reference') ?> *
                    </label>
                    <input 
                        type="text" 
                        id="reference_code" 
                        name="reference_code" 
                        class="form-input <?= isset($errors['reference_code']) ? 'has-error' : '' ?>"
                        value="<?= sanitize($_SESSION['old_input']['reference_code'] ?? '') ?>"
                        required
                        autofocus
                    >
                    <?php if (isset($errors['reference_code'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['reference_code'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Title -->
                <div class="form-group">
                    <label for="title" class="form-label">
                        <?= trans('title') ?> *
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        class="form-input <?= isset($errors['title']) ? 'has-error' : '' ?>"
                        value="<?= sanitize($_SESSION['old_input']['title'] ?? '') ?>"
                        required
                    >
                    <?php if (isset($errors['title'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['title'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Office -->
                <div class="form-group">
                    <label for="office_id" class="form-label">
                        <?= trans('office') ?> *
                    </label>
                    <select 
                        name="office_id" 
                        id="office_id" 
                        class="form-select <?= isset($errors['office_id']) ? 'has-error' : '' ?>"
                        required
                        onchange="updateAgentOptions(this.value)"
                    >
                        <option value=""><?= trans('select_office') ?></option>
                        <?php foreach ($offices as $office): ?>
                            <option value="<?= $office['id'] ?>" 
                                <?= ($_SESSION['old_input']['office_id'] ?? '') == $office['id'] ? 'selected' : '' ?>
                            >
                                <?= sanitize($office['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['office_id'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['office_id'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Agent -->
                <div class="form-group">
                    <label for="agent_id" class="form-label">
                        <?= trans('agent') ?> *
                    </label>
                    <select 
                        name="agent_id" 
                        id="agent_id" 
                        class="form-select <?= isset($errors['agent_id']) ? 'has-error' : '' ?>"
                        required
                    >
                        <option value=""><?= trans('select_agent') ?></option>
                        <?php if (!empty($agentsByOffice[$_SESSION['old_input']['office_id'] ?? ''])): ?>
                            <?php foreach ($agentsByOffice[$_SESSION['old_input']['office_id'] ?? ''] as $agent): ?>
                                <option value="<?= $agent['id'] ?>" 
                                    <?= ($_SESSION['old_input']['agent_id'] ?? '') == $agent['id'] ? 'selected' : '' ?>
                                >
                                    <?= sanitize($agent['first_name'] . ' ' . $agent['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (isset($errors['agent_id'])): ?>
                        <div class="form-error">
                            <?php foreach ($errors['agent_id'] as $error): ?>
                                <span><?= sanitize($error) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Property Type -->
                <div class="form-group">
                    <label for="property_type" class="form-label"><?= trans('property_type') ?> *</label>
                    <select 
                        name="property_type" 
                        id="property_type" 
                        class="form-select"
                        required
                    >
                        <option value="house" <?= ($_SESSION['old_input']['property_type'] ?? '') === 'house' ? 'selected' : '' ?>>
                            <?= trans('house') ?>
                        </option>
                        <option value="apartment" <?= ($_SESSION['old_input']['property_type'] ?? '') === 'apartment' ? 'selected' : '' ?>>
                            <?= trans('apartment') ?>
                        </option>
                        <option value="villa" <?= ($_SESSION['old_input']['property_type'] ?? '') === 'villa' ? 'selected' : '' ?>>
                            <?= trans('villa') ?>
                        </option>
                        <option value="land" <?= ($_SESSION['old_input']['property_type'] ?? '') === 'land' ? 'selected' : '' ?>>
                            <?= trans('land') ?>
                        </option>
                        <option value="commercial" <?= ($_SESSION['old_input']['property_type'] ?? '') === 'commercial' ? 'selected' : '' ?>>
                            <?= trans('commercial') ?>
                        </option>
                    </select>
                </div>

                <!-- Transaction Type -->
                <div class="form-group">
                    <label for="transaction_type" class="form-label"><?= trans('transaction') ?> *</label>
                    <select 
                        name="transaction_type" 
                        id="transaction_type" 
                        class="form-select"
                        required
                    >
                        <option value="sale" <?= ($_SESSION['old_input']['transaction_type'] ?? '') === 'sale' ? 'selected' : '' ?>>
                            <?= trans('sale') ?>
                        </option>
                        <option value="rent" <?= ($_SESSION['old_input']['transaction_type'] ?? '') === 'rent' ? 'selected' : '' ?>>
                            <?= trans('rent') ?>
                        </option>
                    </select>
                </div>

                <!-- Listing Status -->
                <div class="form-group">
                    <label for="listing_status" class="form-label"><?= trans('status') ?></label>
                    <select name="listing_status" id="listing_status" class="form-select">
                        <option value="draft" <?= ($_SESSION['old_input']['listing_status'] ?? '') === 'draft' ? 'selected' : '' ?>>
                            <?= trans('draft') ?>
                        </option>
                        <option value="published" <?= ($_SESSION['old_input']['listing_status'] ?? '') === 'published' ? 'selected' : '' ?>>
                            <?= trans('published') ?>
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Location Section -->
        <div class="form-section">
            <h3 class="section-title"><?= trans('location') ?></h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="address_line1" class="form-label"><?= trans('address') ?></label>
                    <input 
                        type="text" 
                        id="address_line1" 
                        name="address_line1" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['address_line1'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="postal_code" class="form-label"><?= trans('postal_code') ?></label>
                    <input 
                        type="text" 
                        id="postal_code" 
                        name="postal_code" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['postal_code'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="city" class="form-label"><?= trans('city') ?></label>
                    <input 
                        type="text" 
                        id="city" 
                        name="city" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['city'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="country_code" class="form-label"><?= trans('country') ?></label>
                    <select name="country_code" id="country_code" class="form-select">
                        <option value="FR" <?= ($_SESSION['old_input']['country_code'] ?? '') === 'FR' ? 'selected' : '' ?>>France</option>
                        <option value="BE" <?= ($_SESSION['old_input']['country_code'] ?? '') === 'BE' ? 'selected' : '' ?>>Belgium</option>
                        <option value="NL" <?= ($_SESSION['old_input']['country_code'] ?? '') === 'NL' ? 'selected' : '' ?>>Netherlands</option>
                        <option value="LU" <?= ($_SESSION['old_input']['country_code'] ?? '') === 'LU' ? 'selected' : '' ?>>Luxembourg</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Details Section -->
        <div class="form-section">
            <h3 class="section-title"><?= trans('property_details') ?></h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="price" class="form-label"><?= trans('price') ?></label>
                    <div class="input-group">
                        <input 
                            type="number" 
                            id="price" 
                            name="price" 
                            class="form-input"
                            value="<?= sanitize($_SESSION['old_input']['price'] ?? '') ?>"
                            step="0.01"
                            min="0"
                        >
                        <select name="currency" class="form-select" style="width: 80px;">
                            <option value="EUR" <?= ($_SESSION['old_input']['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>€</option>
                            <option value="USD" <?= ($_SESSION['old_input']['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>$</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="living_area_m2" class="form-label"><?= trans('living_area') ?></label>
                    <input 
                        type="number" 
                        id="living_area_m2" 
                        name="living_area_m2" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['living_area_m2'] ?? '') ?>"
                        step="0.01"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label for="land_area_m2" class="form-label"><?= trans('land_area') ?></label>
                    <input 
                        type="number" 
                        id="land_area_m2" 
                        name="land_area_m2" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['land_area_m2'] ?? '') ?>"
                        step="0.01"
                        min="0"
                    >
                </div>

                <div class="form-group">
                    <label for="bedrooms" class="form-label"><?= trans('bedrooms') ?></label>
                    <input 
                        type="number" 
                        id="bedrooms" 
                        name="bedrooms" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['bedrooms'] ?? '') ?>"
                        min="0"
                        max="50"
                    >
                </div>

                <div class="form-group">
                    <label for="bathrooms" class="form-label"><?= trans('bathrooms') ?></label>
                    <input 
                        type="number" 
                        id="bathrooms" 
                        name="bathrooms" 
                        class="form-input"
                        value="<?= sanitize($_SESSION['old_input']['bathrooms'] ?? '') ?>"
                        min="0"
                        max="50"
                    >
                </div>
            </div>
        </div>

        <!-- Description Section -->
        <div class="form-section">
            <h3 class="section-title"><?= trans('description') ?></h3>
            <textarea 
                name="description" 
                class="form-textarea"
                rows="5"
                placeholder="<?= trans('property_description_placeholder') ?>"
            ><?= sanitize($_SESSION['old_input']['description'] ?? '') ?></textarea>
        </div>

        <!-- Submit -->
        <div class="form-actions">
            <a href="<?= route('properties.index') ?>" class="btn btn-secondary">
                <?= trans('cancel') ?>
            </a>
            <button type="submit" class="btn btn-primary">
                <?= trans('save_property') ?>
            </button>
        </div>
    </form>
</div>

<script>
    // Update agent options based on selected office
    function updateAgentOptions(officeId) {
        if (!officeId) {
            document.getElementById('agent_id').innerHTML = '<option value=""><?= trans('select_agent') ?></option>';
            return;
        }
        
        // Get agents for this office
        const agents = <?= json_encode($agentsByOffice) ?>;
        const agentSelect = document.getElementById('agent_id');
        
        let options = '<option value=""><?= trans('select_agent') ?></option>';
        if (agents[officeId]) {
            agents[officeId].forEach(agent => {
                options += `<option value="${agent.id}">${agent.first_name} ${agent.last_name}</option>`;
            });
        }
        
        agentSelect.innerHTML = options;
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const officeId = document.getElementById('office_id').value;
        if (officeId) {
            updateAgentOptions(officeId);
        }
    });
</script>

<?php unset($_SESSION['old_input'], $_SESSION['errors']); ?>
