const BASE_PATH = "/Biyahe/Php/"; // <--- TIYAKIN NA ITO ANG PATH!

// --- Function to handle registration ---
function handleRegistration(event) {
    event.preventDefault(); 
    
    const form = event.target;
    const formId = form.id;
    let endpoint = '';
    
    // I-locate ang message area sa form
    const messageArea = form.querySelector('.form-message-area') || document.createElement('div');
    if (!form.querySelector('.form-message-area')) {
        messageArea.className = 'form-message-area';
        form.prepend(messageArea); // I-dagdag sa taas ng form
    }
    
    // Determine which registration process to call
    if (formId === 'adminRegistrationForm') {
        endpoint = BASE_PATH + 'register_admin_process.php';
    } else if (formId === 'driverRegistrationForm') {
        endpoint = BASE_PATH + 'register_driver_process.php'; 
    } else {
        messageArea.textContent = "Error: Unknown form ID.";
        messageArea.style.color = 'red';
        return;
    }
    
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    messageArea.textContent = ""; // Clear previous messages
    if (submitButton) {
        submitButton.textContent = "Processing...";
        submitButton.disabled = true;
    }

    fetch(endpoint, {
        method: 'POST',
        body: formData,
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log("PHP Response:", data);
        
        // Ipakita ang message base sa success status mula sa PHP
        if (data.success) {
            messageArea.textContent = data.message;
            messageArea.style.color = 'green';
            // Optional: I-reset ang form fields
            form.reset();
        } else {
            // Ipakita ang error message na galing sa PHP (SQL error, etc.)
            messageArea.textContent = `Registration Failed: ${data.message}`;
            messageArea.style.color = 'red';
        }
    })
    .catch(error => {
        // Network or fetch errors
        messageArea.textContent = `A network error occurred: ${error.message}`;
        messageArea.style.color = 'red';
        console.error("Fetch Error:", error);
    })
    .finally(() => {
        if (submitButton) {
            submitButton.textContent = "Register"; 
            submitButton.disabled = false;
        }
    });
}

// --- Attach the event listeners to the forms ---
window.onload = function() {
    const adminForm = document.getElementById('adminRegistrationForm');
    if (adminForm) {
        adminForm.addEventListener('submit', handleRegistration);
    }

    const driverForm = document.getElementById('driverRegistrationForm');
    if (driverForm) {
        driverForm.addEventListener('submit', handleRegistration);
    }
}