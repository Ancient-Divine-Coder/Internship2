const form = document.getElementById('registrationForm');
const errorBox = document.getElementById('errorBox');
const photoInput = document.getElementById('photo');
const photoPreview = document.getElementById('photoPreview');
const photoDropzone = document.getElementById('photoDropzone');
const removePhotoBtn = document.getElementById('removePhoto');
const addressField = document.getElementById('address');
const addressCount = document.getElementById('addressCount');
const genderError = document.getElementById('genderError');
const photoError = document.getElementById('photoError');
const submitBtn = document.getElementById('submitBtn');

const previewImage = photoPreview.querySelector('img');
const DEFAULT_PHOTO = 'https://via.placeholder.com/120?text=Photo';
const MAX_PHOTO_BYTES = 3 * 1024 * 1024;
const ALLOWED_PHOTO_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

/* ---------- error summary box ---------- */

function displayErrors(messages) {
  if (messages.length === 0) {
    errorBox.classList.add('d-none');
    errorBox.innerHTML = '';
    return;
  }

  errorBox.classList.remove('d-none');
  errorBox.innerHTML = '<ul class="mb-0">' + messages.map(msg => '<li>' + msg + '</li>').join('') + '</ul>';
  errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

/* ---------- per-field validation ---------- */

function setFieldValidity(field, isValid) {
  if (!field) return;
  field.classList.toggle('is-invalid', !isValid);
  field.classList.toggle('is-valid', isValid);
}

function validateFullName() {
  const value = form.full_name.value.trim();
  const isValid = value.length >= 3 && !/\d/.test(value);
  setFieldValidity(form.full_name, isValid);
  return isValid;
}

function validateEmail() {
  const value = form.email.value.trim();
  const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  setFieldValidity(form.email, isValid);
  return isValid;
}

function validateMobile() {
  const value = form.mobile.value.trim();
  const isValid = /^[0-9+\s()-]{7,20}$/.test(value);
  setFieldValidity(form.mobile, isValid);
  return isValid;
}

function validateGender() {
  const checked = form.querySelector('input[name="gender"]:checked');
  genderError.classList.toggle('d-none', !!checked);
  return !!checked;
}

function validateCourse() {
  const isValid = form.course.value !== '';
  setFieldValidity(form.course, isValid);
  return isValid;
}

function validateAddress() {
  const value = addressField.value.trim();
  const isValid = value.length >= 12;
  setFieldValidity(addressField, isValid);
  addressCount.textContent = `${value.length} / 12 min`;
  addressCount.classList.toggle('text-danger', !isValid);
  addressCount.classList.toggle('text-success', isValid);
  return isValid;
}

function validateConsent() {
  const isValid = form.consent.checked;
  setFieldValidity(form.consent, isValid);
  return isValid;
}

function validatePhotoFile(file) {
  if (!file) {
    photoError.classList.add('d-none');
    photoInput.classList.remove('is-invalid');
    return true;
  }
  const isValidType = ALLOWED_PHOTO_TYPES.includes(file.type);
  const isValidSize = file.size <= MAX_PHOTO_BYTES;
  const isValid = isValidType && isValidSize;

  photoInput.classList.toggle('is-invalid', !isValid);
  photoError.classList.toggle('d-none', isValid);
  if (!isValid) {
    photoError.textContent = !isValidType
      ? 'Please upload a JPEG, PNG, or WEBP image.'
      : 'Profile photo must be smaller than 3MB.';
  }
  return isValid;
}

/* ---------- photo preview + drag & drop ---------- */

function updatePhotoPreview(file) {
  if (!file) {
    previewImage.src = DEFAULT_PHOTO;
    removePhotoBtn.classList.add('d-none');
    return;
  }

  const reader = new FileReader();
  reader.onload = () => {
    previewImage.src = reader.result;
    removePhotoBtn.classList.remove('d-none');
  };
  reader.readAsDataURL(file);
}

function setPhotoFile(file) {
  if (file && !validatePhotoFile(file)) {
    // Reject invalid files: don't attach them to the input.
    photoInput.value = '';
    updatePhotoPreview(null);
    return;
  }

  if (file) {
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    photoInput.files = dataTransfer.files;
  }

  updatePhotoPreview(file);
}

photoInput.addEventListener('change', (event) => {
  const file = event.target.files[0];
  setPhotoFile(file || null);
});

removePhotoBtn.addEventListener('click', () => {
  photoInput.value = '';
  validatePhotoFile(null);
  updatePhotoPreview(null);
});

['dragenter', 'dragover'].forEach(eventName => {
  photoDropzone.addEventListener(eventName, (event) => {
    event.preventDefault();
    photoDropzone.classList.add('dragover');
  });
});

['dragleave', 'drop'].forEach(eventName => {
  photoDropzone.addEventListener(eventName, (event) => {
    event.preventDefault();
    photoDropzone.classList.remove('dragover');
  });
});

photoDropzone.addEventListener('drop', (event) => {
  const file = event.dataTransfer.files[0];
  if (file) {
    setPhotoFile(file);
  }
});

/* ---------- live validation wiring ---------- */

form.full_name.addEventListener('input', validateFullName);
form.email.addEventListener('input', validateEmail);
form.mobile.addEventListener('input', validateMobile);
form.course.addEventListener('change', validateCourse);
addressField.addEventListener('input', validateAddress);
form.consent.addEventListener('change', validateConsent);
form.querySelectorAll('input[name="gender"]').forEach(radio => {
  radio.addEventListener('change', validateGender);
});

/* ---------- submit ---------- */

form.addEventListener('submit', (event) => {
  const errors = [];

  if (!validateFullName()) errors.push('Full name must be at least 3 characters and contain no numbers.');
  if (!validateEmail()) errors.push('Please enter a valid email address.');
  if (!validateMobile()) errors.push('Please enter a valid mobile number.');
  if (!validateGender()) errors.push('Gender selection is required.');
  if (!validateCourse()) errors.push('Please select a course.');
  if (!validateAddress()) errors.push('Address must be at least 12 characters long.');
  if (!validatePhotoFile(photoInput.files[0])) errors.push('Please fix the profile photo before submitting.');
  if (!validateConsent()) errors.push('Please confirm that your details are correct.');

  if (errors.length > 0) {
    event.preventDefault();
    displayErrors(errors);
    return;
  }

  displayErrors([]);
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
});