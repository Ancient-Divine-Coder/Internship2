<?php
require_once __DIR__ . '/functions.php';
$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKVBQF4odlXh5iqT9vBj0v2R6p0hX+3PTx1bXzQe5KZz0eW3D1t54eXj9TRJ5S5nYbY8vVg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="style.css" />
</head>

<body class="bg-light">
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="card shadow-sm overflow-hidden registration-card">
                    <div class="row g-0">
                        <div class="col-lg-5 bg-primary text-white left-panel d-flex flex-column justify-content-between p-5">
                            <div>
                                <span class="badge bg-white text-primary mb-3">Student Registration</span>
                                <h1 class="h3 mb-3">Register for Campus Orientation</h1>
                                <p class="text-white-75">Complete the form below to reserve your seat. Upload a profile photo, select your course, choose your gender, and tell us where you're coming from.</p>
                            </div>
                            <div class="mt-4">
                                <p class="small text-white-75 mb-1">Registration Checklist</p>
                                <ul class="list-unstyled text-white-75 lh-lg mb-0">
                                    <li><i class="fa-solid fa-user-check me-2"></i>Full name without numbers</li>
                                    <li><i class="fa-solid fa-venus-mars me-2"></i>Gender selection required</li>
                                    <li><i class="fa-solid fa-book-open me-2"></i>Course dropdown and address</li>
                                    <li><i class="fa-solid fa-image me-2"></i>Profile photo preview</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-7 p-4 p-lg-5">
                            <div class="d-flex align-items-start justify-content-between mb-4">
                                <div>
                                    <span class="badge bg-info text-dark">Step 1</span>
                                    <h2 class="mb-1">Quick student form</h2>
                                    <p class="text-muted mb-0">Fill these details to complete your registration.</p>
                                </div>
                                <div class="text-primary fs-2"><i class="fa-solid fa-graduation-cap"></i></div>
                            </div>

                            <div class="row row-cols-1 row-cols-sm-2 g-2 mb-4">
                                <div class="col">
                                    <div class="feature-pill"><i class="fa-solid fa-shield-halved me-2"></i>Secure submission</div>
                                </div>
                                <div class="col">
                                    <div class="feature-pill"><i class="fa-solid fa-clock me-2"></i>Fast confirmation</div>
                                </div>
                                <div class="col">
                                    <div class="feature-pill"><i class="fa-solid fa-user-graduate me-2"></i>Track-based courses</div>
                                </div>
                                <div class="col">
                                    <div class="feature-pill"><i class="fa-solid fa-image me-2"></i>Photo preview</div>
                                </div>
                            </div>

                            <div id="errorBox" class="alert alert-danger d-none" role="alert" aria-live="assertive"></div>

                            <form id="registrationForm" action="confirm.php" method="post" enctype="multipart/form-data" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= safe($token) ?>">
                                <div class="row gy-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="fullName">Full Name</label>
                                        <input type="text" id="fullName" name="full_name" class="form-control" placeholder="Jane Doe" required minlength="3" />
                                        <div class="invalid-feedback">Enter a name with at least 3 letters and no numbers.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="email">Email Address</label>
                                        <input type="email" id="email" name="email" class="form-control" placeholder="jane@example.com" required />
                                        <div class="invalid-feedback">Enter a valid email address.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="mobile">Mobile Number</label>
                                        <input type="tel" id="mobile" name="mobile" class="form-control" placeholder="+91 98765 43210" required />
                                        <div class="invalid-feedback">Enter a valid mobile number (7-20 digits).</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label d-block">Gender</label>
                                        <div class="btn-group w-100" role="group" aria-label="Gender selection">
                                            <input type="radio" class="btn-check" name="gender" id="genderMale" value="Male" autocomplete="off" required />
                                            <label class="btn btn-outline-secondary" for="genderMale">Male</label>

                                            <input type="radio" class="btn-check" name="gender" id="genderFemale" value="Female" autocomplete="off" required />
                                            <label class="btn btn-outline-secondary" for="genderFemale">Female</label>

                                            <input type="radio" class="btn-check" name="gender" id="genderOther" value="Other" autocomplete="off" required />
                                            <label class="btn btn-outline-secondary" for="genderOther">Other</label>
                                        </div>
                                        <div class="form-text text-danger d-none" id="genderError">Please select a gender.</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label" for="course">Course</label>
                                        <select class="form-select" id="course" name="course" required>
                                            <option value="">Choose a course</option>
                                            <option value="B.Tech CSE">B.Tech CSE</option>
                                            <option value="B.Sc IT">B.Sc IT</option>
                                            <option value="BBA">BBA</option>
                                            <option value="Class 12 Science">Class 12 Science</option>
                                            <option value="Diploma Engineering">Diploma Engineering</option>
                                        </select>
                                        <div class="invalid-feedback">Please choose a course.</div>
                                    </div>

                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <label class="form-label" for="address">Address</label>
                                            <span class="form-text" id="addressCount">0 / 12 min</span>
                                        </div>
                                        <textarea class="form-control" id="address" name="address" rows="4" minlength="12" placeholder="Street, city, state, postal code" required></textarea>
                                        <div class="invalid-feedback">Address must be at least 12 characters long.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="photo">Profile Photo</label>
                                        <div class="photo-dropzone" id="photoDropzone">
                                            <input class="form-control" type="file" id="photo" name="photo" accept="image/png, image/jpeg, image/webp" />
                                            <p class="mb-0 small text-muted mt-2"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Drag a photo here or click to browse</p>
                                        </div>
                                        <div class="form-text">JPEG, PNG, or WEBP, up to 3MB.</div>
                                        <div class="invalid-feedback" id="photoError">Please upload a JPEG, PNG, or WEBP under 3MB.</div>
                                    </div>

                                    <div class="col-md-6 d-flex align-items-end">
                                        <div class="photo-preview" id="photoPreview">
                                            <img src="https://via.placeholder.com/120?text=Photo" alt="Profile preview" />
                                            <button type="button" class="btn btn-sm btn-outline-danger d-none" id="removePhoto" title="Remove photo">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="consent" name="consent" required />
                                            <label class="form-check-label" for="consent">I confirm my details are correct and I am available for the orientation.</label>
                                            <div class="invalid-feedback">Please confirm your details before submitting.</div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                                            <i class="fa-solid fa-paper-plane me-2"></i>Submit Registration
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="script.js"></script>
</body>

</html>