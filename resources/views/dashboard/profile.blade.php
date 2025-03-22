<style>
    .profile-image-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto 30px;
    }

    .profile-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .image-upload-btn {
        position: absolute;
        bottom: 0;
        right: 0;
        background-color: #0d6efd;
        color: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .image-upload-btn:hover {
        background-color: #0b5ed7;
    }

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

<form id="profileForm" action="{{url('update/profile')}}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Profile</h2>
        <button class="btn btn-primary" type="submit" form="profileForm">
            <i class="bi bi-save me-2"></i> Save Changes
        </button>
    </div>

    <!-- Profile Photo Section -->
    <div class="form-section text-center">
        <div class="profile-image-container">

            @if(Auth::user()->image && file_exists(public_path('uploads/users/'.Auth::user()->image)))
                <img src="{{url('uploads/users')}}/{{Auth::user()->image}}" class="profile-image" alt="Profile Image">
            @else
                <img src="{{url('assets')}}/images/authors/author.png" class="profile-image" alt="Profile Image">
            @endif

            <label for="profileImage" class="image-upload-btn">
                <i class="bi bi-camera"></i>
                <input type="file" id="profileImage" name="image" class="d-none" accept="image/*">
            </label>
        </div>
        <p class="text-muted small">Click the camera icon to upload a new profile photo</p>
    </div>

    <!-- Personal Information Section -->
    <div class="form-section">
        <h5><i class="bi bi-person-badge me-2"></i>Personal Information</h5>

        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" id="name" value="{{Auth::user()->name}}" placeholder="Full Name" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" id="email" value="{{Auth::user()->email}}" placeholder="example@email.com">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <label for="address" class="form-label">Address</label>
                <input type="text" name="address" class="form-control" id="address" value="{{Auth::user()->address}}" placeholder="Address">
            </div>
            <div class="col-md-6">
                <label for="birthDate" class="form-label">Date of Birth</label>
                <input type="date" name="dob" class="form-control" id="birthDate" value="{{Auth::user()->dob}}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <label for="gender" class="form-label">Gender</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">Select One</option>
                    <option value="male" @if(Auth::user()->gender == 'male') selected @endif>Male</option>
                    <option value="female" @if(Auth::user()->gender == 'female') selected @endif>Female</option>
                    <option value="others" @if(Auth::user()->gender == 'others') selected @endif>Others</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-2"></i>Save Changes
        </button>
    </div>
</form>


