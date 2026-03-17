// Initialize localStorage with sample data
function initializeApp() {
    if (!localStorage.getItem('students')) {
        localStorage.setItem('students', JSON.stringify([
            {
                id: '2024001',
                firstName: 'John',
                lastName: 'Doe',
                middleName: 'A',
                course: 'BSIT',
                courseLevel: 1,
                email: 'john@student.com',
                password: 'password123',
                address: '123 Main St',
                phone: '09123456789',
                enrollmentDate: '2024-01-15',
                status: 'active'
            }
        ]));
    }
}

// Call on page load
document.addEventListener('DOMContentLoaded', initializeApp);

// LOGIN FUNCTIONALITY
function handleLogin(e) {
    e.preventDefault();
    
    const idNumber = document.getElementById('id-number').value;
    const password = document.getElementById('password').value;
    
    if (!idNumber || !password) {
        alert('Please enter both ID number and password');
        return;
    }
    
    const students = JSON.parse(localStorage.getItem('students')) || [];
    const student = students.find(s => s.id === idNumber && s.password === password);
    
    if (student) {
        localStorage.setItem('currentStudent', JSON.stringify(student));
        alert('Login successful! Redirecting...');
        window.location.href = 'dashboard.html';
    } else {
        alert('Invalid ID number or password');
    }
}

// REGISTRATION FUNCTIONALITY
function handleRegister(e) {
    e.preventDefault();
    
    const idNumber = document.getElementById('reg-id').value;
    const lastName = document.getElementById('reg-lastname').value;
    const firstName = document.getElementById('reg-firstname').value;
    const middleName = document.getElementById('reg-middlename').value;
    const course = document.getElementById('reg-course').value;
    const courseLevel = document.getElementById('reg-course-level').value;
    const password = document.getElementById('reg-password').value;
    const confirmPassword = document.getElementById('reg-confirm-password').value;
    const email = document.getElementById('reg-email').value;
    const address = document.getElementById('reg-address').value;
    
    // Validation
    if (!idNumber || !lastName || !firstName || !email || !password) {
        alert('Please fill in all required fields');
        return;
    }
    
    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    if (password.length < 6) {
        alert('Password must be at least 6 characters');
        return;
    }
    
    const students = JSON.parse(localStorage.getItem('students')) || [];
    
    // Check if ID already exists
    if (students.find(s => s.id === idNumber)) {
        alert('ID number already registered');
        return;
    }
    
    // Check if email already exists
    if (students.find(s => s.email === email)) {
        alert('Email already registered');
        return;
    }
    
    // Create new student
    const newStudent = {
        id: idNumber,
        firstName: firstName,
        lastName: lastName,
        middleName: middleName,
        course: course,
        courseLevel: courseLevel,
        email: email,
        password: password,
        address: address,
        phone: '',
        enrollmentDate: new Date().toISOString().split('T')[0],
        status: 'active'
    };
    
    students.push(newStudent);
    localStorage.setItem('students', JSON.stringify(students));
    
    alert('Registration successful! You can now login with your ID number.');
    window.location.href = 'login.html';
}

// DASHBOARD - Load student info
function loadStudentDashboard() {
    const currentStudent = JSON.parse(localStorage.getItem('currentStudent'));
    
    if (!currentStudent) {
        window.location.href = 'login.html';
        return;
    }
    
    const fullName = currentStudent.firstName + ' ' + (currentStudent.middleName ? currentStudent.middleName + ' ' : '') + currentStudent.lastName;
    
    document.getElementById('student-name').textContent = fullName;
    document.getElementById('student-id').textContent = currentStudent.id;
    document.getElementById('student-email').textContent = currentStudent.email;
    document.getElementById('student-course').textContent = currentStudent.course;
    document.getElementById('student-level').textContent = currentStudent.courseLevel;
    document.getElementById('student-address').textContent = currentStudent.address || 'Not set';
    document.getElementById('student-phone').textContent = currentStudent.phone || 'Not set';
    document.getElementById('student-status').textContent = currentStudent.status;
}

// PROFILE EDITING FUNCTIONALITY
function loadProfileEditForm() {
    const currentStudent = JSON.parse(localStorage.getItem('currentStudent'));
    
    if (!currentStudent) {
        window.location.href = 'login.html';
        return;
    }
    
    // Populate form with current data
    document.getElementById('edit-firstname').value = currentStudent.firstName;
    document.getElementById('edit-lastname').value = currentStudent.lastName;
    document.getElementById('edit-middlename').value = currentStudent.middleName;
    document.getElementById('edit-email').value = currentStudent.email;
    document.getElementById('edit-course').value = currentStudent.course;
    document.getElementById('edit-course-level').value = currentStudent.courseLevel;
    document.getElementById('edit-address').value = currentStudent.address;
    document.getElementById('edit-phone').value = currentStudent.phone;
    
    document.getElementById('student-id-display').textContent = currentStudent.id;
}

function handleProfileUpdate(e) {
    e.preventDefault();
    
    const currentStudent = JSON.parse(localStorage.getItem('currentStudent'));
    
    const firstName = document.getElementById('edit-firstname').value;
    const lastName = document.getElementById('edit-lastname').value;
    const middleName = document.getElementById('edit-middlename').value;
    const email = document.getElementById('edit-email').value;
    const course = document.getElementById('edit-course').value;
    const courseLevel = document.getElementById('edit-course-level').value;
    const address = document.getElementById('edit-address').value;
    const phone = document.getElementById('edit-phone').value;
    
    if (!firstName || !lastName || !email) {
        alert('Please fill in required fields');
        return;
    }
    
    // Update current student object
    const updatedStudent = {
        ...currentStudent,
        firstName: firstName,
        lastName: lastName,
        middleName: middleName,
        email: email,
        course: course,
        courseLevel: courseLevel,
        address: address,
        phone: phone
    };
    
    // Update in localStorage
    const students = JSON.parse(localStorage.getItem('students')) || [];
    const index = students.findIndex(s => s.id === currentStudent.id);
    
    if (index !== -1) {
        students[index] = updatedStudent;
        localStorage.setItem('students', JSON.stringify(students));
        localStorage.setItem('currentStudent', JSON.stringify(updatedStudent));
        alert('Profile updated successfully!');
        window.location.href = 'dashboard.html';
    }
}

// LOGOUT FUNCTIONALITY
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.removeItem('currentStudent');
        window.location.href = 'login.html';
    }
}

// Check if user is logged in
function checkLoginStatus() {
    const currentStudent = localStorage.getItem('currentStudent');
    if (!currentStudent) {
        window.location.href = 'login.html';
    }
}
