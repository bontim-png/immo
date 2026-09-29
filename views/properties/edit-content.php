<div class="property-form-page">
    <form method="POST" action="<?= route('properties.update', ['id' => $property['id']]) ?>" class="property-form" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <input type="hidden" name="_method" value="PUT">
        
        <!-- Header -->
        <div class="form-header">
            <h2><?= trans('edit_property') ?>: <?= sanitize($property['title']) ?></h2>
            <p><?= trans('edit_property_help') ?></p>
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
                        value="<?= sanitize($property['reference_code'] ?? $_SESSION['old_input']['reference_code'] ?? '') ?>"
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
                        value="<?= sanitize($property['title'] ?? $_SESSION['old_input']['title'] ?? '') ?>"
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
                        <?= !$isAdmin ? 'disabled' : '' ?>
                    >
                        <option value=""><?= trans('select_office') ?></option>
                        <?php foreach ($offices as $office): ?>
                            <option value="<?= $office['id'] ?>" 
                                <?= ($property['office_id'] ?? $_SESSION['old_input']['office_id'] ?? '') == $office['id'] ? 'selected' : '' ?>
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
                        <?php foreach ($agentsByOffice[$property['office_id']] as $agent): ?>
                            <option value="<?= $agent['id'] ?>" 
                                <?= ($property['agent_id'] ?? $_SESSION['old_input']['agent_id'] ?? '') == $agent['id'] ? 'selected' : '' ?>
                            >
                                <?= sanitize($agent['first_name'] . ' ' . $agent['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
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
                        <option value="house" <?= ($property['property_type'] ?? $_SESSION['old_input']['property_type'] ?? '') === 'house' ? 'selected' : '' ?>>
                            <?= trans('house') ?>
                        </option>
                        <option value="apartment" <?= ($property['property_type'] ?? $_SESSION['old_input']['property_type'] ?? '') === 'apartment' ? 'selected' : '' ?>>
                            <?= trans('apartment') ?>
                        </option>
                        <option value="villa" <?= ($property['property_type'] ?? $_SESSION['old_input']['property_type'] ?? '') === 'villa' ? 'selected' : '' ?>>
                            <?= trans('villa') ?>
                        </option>
                        <option value="land" <?= ($property['property_type'] ?? $_SESSION['old_input']['property_type'] ?? '') === 'land' ? 'selected' : '' ?>>
                            <?= trans('land') ?>
                        </option>
                        <option value="commercial" <?= ($property['property_type'] ?? $_SESSION['old_input']['property_type'] ?? '') === 'commercial' ? 'selected' : '' ?>>
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
                        <option value="sale" <?= ($property['transaction_type'] ?? $_SESSION['old_input']['transaction_type'] ?? '') === 'sale' ? 'selected' : '' ?>>
                            <?= trans('sale') ?>
                        </option>
                        <option value="rent" <?= ($property['transaction_type'] ?? $_SESSION['old_input']['transaction_type'] ?? '') === 'rent' ? 'selected' : '' ?>>
                            <?= trans('rent') ?>
                        </option>
                    </select>
                </div>

                <!-- Listing Status -->
                <div class="form-group">
                    <label for="listing_status" class="form-label"><?= trans('status') ?></label>
                    <select name="listing_status" id="listing_status" class="form-select">
                        <option value="draft" <?= ($property['listing_status'] ?? $_SESSION['old_input']['listing_status'] ?? '') === 'draft' ? 'selected' : '' ?>>
                            <?= trans('draft') ?>
                        </option>
                        <option value="published" <?= ($property['listing_status'] ?? $_SESSION['old_input']['listing_status'] ?? '') === 'published' ? 'selected' : '' ?>>
                            <?= trans('published') ?>
                        </option>
                        <option value="under_offer" <?= ($property['listing_status'] ?? $_SESSION['old_input']['listing_status'] ?? '') === 'under_offer' ? 'selected' : '' ?>>
                            <?= trans('under_offer') ?>
                        </option>
                        <option value="sold" <?= ($property['listing_status'] ?? $_SESSION['old_input']['listing_status'] ?? '') === 'sold' ? 'selected' : '' ?>>
                            <?= trans('sold') ?>
                        </option>
                        <option value="rented" <?= ($property['listing_status'] ?? $_SESSION['old_input']['listing_status'] ?? '') === 'rented' ? 'selected' : '' ?>>
                            <?= trans('rented') ?>
                        </option>
                        <option value="archived" <?= ($property['listing_status'] ?? $_SESSION['old_input']['listing_status'] ?? '') === 'archived' ? 'selected' : '' ?>>
                            <?= trans('archived') ?>
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
                        value="<?= sanitize($property['address_line1'] ?? $_SESSION['old_input']['address_line1'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="postal_code" class="form-label"><?= trans('postal_code') ?></label>
                    <input 
                        type="text" 
                        id="postal_code" 
                        name="postal_code" 
                        class="form-input"
                        value="<?= sanitize($property['postal_code'] ?? $_SESSION['old_input']['postal_code'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="city" class="form-label"><?= trans('city') ?></label>
                    <input 
                        type="text" 
                        id="city" 
                        name="city" 
                        class="form-input"
                        value="<?= sanitize($property['city'] ?? $_SESSION['old_input']['city'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="country_code" class="form-label"><?= trans('country') ?></label>
                    <select name="country_code" id="country_code" class="form-select">
                        <option value="FR" <?= ($property['country_code'] ?? $_SESSION['old_input']['country_code'] ?? '') === 'FR' ? 'selected' : '' ?>>France</option>
                        <option value="BE" <?= ($property['country_code'] ?? $_SESSION['old_input']['country_code'] ?? '') === 'BE' ? 'selected' : '' ?>>Belgium</option>
                        <option value="NL" <?= ($property['country_code'] ?? $_SESSION['old_input']['country_code'] ?? '') === 'NL' ? 'selected' : '' ?>>Netherlands</option>
                        <option value="LU" <?= ($property['country_code'] ?? $_SESSION['old_input']['country_code'] ?? '') === 'LU' ? 'selected' : '' ?>>Luxembourg</option>
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
                            value="<?= sanitize($property['price'] ?? $_SESSION['old_input']['price'] ?? '') ?>"
                            step="0.01"
                            min="0"
                        >
                        <select name="currency" class="form-select" style="width: 80px;">
                            <option value="EUR" <?= ($property['currency'] ?? $_SESSION['old_input']['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>€</option>
                            <option value="USD" <?= ($property['currency'] ?? $_SESSION['old_input']['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>$</option>
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
                        value="<?= sanitize($property['living_area_m2'] ?? $_SESSION['old_input']['living_area_m2'] ?? '') ?>"
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
                        value="<?= sanitize($property['land_area_m2'] ?? $_SESSION['old_input']['land_area_m2'] ?? '') ?>"
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
                        value="<?= sanitize($property['bedrooms'] ?? $_SESSION['old_input']['bedrooms'] ?? '') ?>"
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
                        value="<?= sanitize($property['bathrooms'] ?? $_SESSION['old_input']['bathrooms'] ?? '') ?>"
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
            ><?= sanitize($property['description'] ?? $_SESSION['old_input']['description'] ?? '') ?></textarea>
        </div>

        <!-- Photos Section -->
        <div class="form-section">
            <h3 class="section-title"><?= trans('photos') ?></h3>
            <p class="section-help"><?= trans('photos_help') ?></p>
            
            <!-- Photo Upload Area -->
            <div class="photo-upload-area" id="photo-upload-area">
                <input 
                    type="file" 
                    id="photo-upload" 
                    name="photos[]"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    style="display: none;"
                    onchange="handlePhotoUpload(event)"
                >
                <div class="upload-placeholder" onclick="document.getElementById('photo-upload').click()">
                    <span class="upload-icon">&#128247;</span>
                    <span class="upload-text"><?= trans('drop_photos') ?></span>
                    <span class="upload-hint"><?= trans('drop_photos_help') ?></span>
                </div>
                
                <!-- Photo Previews -->
                <div class="photo-previews" id="photo-previews">
                    <?php foreach ($property['photos'] as $photo): ?>
                        <div class="photo-item" data-photo-id="<?= $photo['id'] ?>">
                            <div class="photo-image">
                                <img src="<?= asset('uploads/properties/thumbnails/' . basename($photo['file_path'])) ?>" alt="<?= sanitize($photo['original_name']) ?>">
                                <?php if ($photo['is_hero']): ?>
                                    <span class="photo-hero-badge"><?= trans('hero') ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="photo-actions">
                                <button type="button" class="photo-action set-hero" 
                                    onclick="setHero(<?= $photo['id'] ?>, <?= $property['id'] ?>)"
                                    title="<?= trans('set_hero') ?>"
                                >
                                    &#9733;
                                </button>
                                <button type="button" class="photo-action delete-photo" 
                                    onclick="deletePhoto(<?= $photo['id'] ?>, <?= $property['id'] ?>)"
                                    title="<?= trans('delete') ?>"
                                >
                                    &#128465;
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
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
    
    // Handle photo upload
    function handlePhotoUpload(event) {
        const files = event.target.files;
        if (!files.length) return;
        
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('photos[]', files[i]);
        }
        
        // Show loading
        document.getElementById('photo-upload-area').classList.add('loading');
        
        // Upload photos
        fetch('<?= route('properties.update', ['id' => $property['id']]) ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh photo list
                location.reload();
            } else {
                alert(data.error || '<?= trans('photos.upload_failed') ?>');
            }
        })
        .catch(error => {
            alert('<?= trans('photos.upload_error') ?>');
        })
        .finally(() => {
            document.getElementById('photo-upload-area').classList.remove('loading');
            event.target.value = '';
        });
    }
    
    // Set photo as hero
    function setHero(photoId, propertyId) {
        if (!confirm('<?= trans('confirm_set_hero') ?>')) return;
        
        fetch('/properties/' + propertyId + '/photos/' + photoId + '/hero', {
            method: 'PUT',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || '<?= trans('photos.hero_failed') ?>');
            }
        })
        .catch(error => {
            alert('<?= trans('photos.hero_failed') ?>');
        });
    }
    
    // Delete photo
    function deletePhoto(photoId, propertyId) {
        if (!confirm('<?= trans('confirm_delete_photo') ?>')) return;
        
        fetch('/properties/' + propertyId + '/photos/' + photoId, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || '<?= trans('photos.delete_failed') ?>');
            }
        })
        .catch(error => {
            alert('<?= trans('photos.delete_failed') ?>');
        });
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
