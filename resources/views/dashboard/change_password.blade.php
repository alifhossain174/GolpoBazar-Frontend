<style>
    .form-section {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        padding: 30px;
        margin-bottom: 20px;
    }

    .form-section h5 {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
</style>

<form id="profileForm" action="{{url('update/password')}}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Change Password</h2>
    </div>

    <div class="form-section">
        <h5><i class="bi bi-person-badge me-2"></i>Change Password</h5>
        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" id="current_password" placeholder="********" required>
            </div>
            <div class="col-md-6">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" id="new_password" placeholder="********" required>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-2"></i>Save Changes
        </button>
    </div>
</form>


