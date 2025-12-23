console.log("JS file is connected");

document.addEventListener('DOMContentLoaded', function () {
    // ✅ Elements
    
    const authModal = document.getElementById('authModal');
    const closeModalBtn = document.getElementById('closeModal');
    const container = document.getElementById('authContainer');
    const signUpBtn = document.getElementById('signUp');
    const signInBtn = document.getElementById('signIn');
    const formBookButton = document.getElementById('formBookButton');
    const loginButton = document.getElementById('loginButton');
    const authContainer = document.getElementById('authContainer');
    const searchInput = document.getElementById('navSearch');
    const suggestionsList = document.getElementById('searchSuggestions');
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");


// ------------------------------
// POPUP FUNCTION (NEW DESIGN - INAYOS ANG LAPAD)
// ------------------------------
function showPopup(message, type = "success") {
    const popup = document.createElement("div");
    
    popup.className = `popup-message ${type}`; 
    
    popup.innerHTML = `
        <div class="popup-icon">${type === "success" ? "✅" : "❌"}</div>
        <div class="popup-content">
            <div class="popup-body" style="font-weight: bold;">${message}</div>
        </div>
    `;

    // --- NEW STYLING FOR CENTERING AND COLORS ---
    popup.style.position = "fixed";
    popup.style.top = "50%";
    popup.style.left = "50%";
    popup.style.transform = "translate(-50%, -50%)"; // Centering
    
    // GAWIN ITONG MAX WIDTH PARA HINDI LUMABAS SA SCREEN SA MOBILE AT MAGING COMPACT
    popup.style.maxWidth = "400px"; // Itatakda ang maximum width sa 400px
    popup.style.width = "90%";      // Para maging responsive
    
    popup.style.padding = "20px";
    popup.style.borderRadius = "8px";
    popup.style.zIndex = "9999";
    popup.style.boxShadow = "0 6px 20px rgba(0,0,0,0.3)";
    popup.style.textAlign = "center";
    popup.style.display = "flex";
    popup.style.alignItems = "center";
    
    // Light Green/Red Theme
    if (type === "success") {
        popup.style.backgroundColor = "#e8f5e9"; // Light Green
        popup.style.border = "1px solid #4CAF50";
        popup.style.color = "#388E3C"; // Dark Green text
    } else {
        popup.style.backgroundColor = "#ffebee"; // Light Red
        popup.style.border = "1px solid #D32F2F";
        popup.style.color = "#C62828"; // Dark Red text
    }

    document.body.appendChild(popup);

    // Auto-remove after 3 seconds
    setTimeout(() => popup.remove(), 7000);
}


    // ✅ Simple navigation search
    const navSearch = document.getElementById('navSearch');
    if (navSearch) {
    navSearch.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
        const query = this.value.toLowerCase().trim();

         const sections = {
        home: '#home',
        routes: '#routes',
        'popular route': '#routes',
        about: '#about',
        'about us': '#about',
        contact: '#contact',
        'how it works': '#how-it-works',
      };

      let found = false;

      // ✅ Check for section navigation
      for (const key in sections) {
        if (query.includes(key)) {
          window.location.href = sections[key];
          found = true;
          break;
        }
      }

      // ✅ Check for login
      if (!found && (query.includes('login') || query.includes('sign in'))) {
        const loginButton = document.getElementById('loginButton');
        if (loginButton) {
          loginButton.click(); // Opens the modal
          found = true;
        }
      }

      // ✅ If nothing matched
      if (!found) {
        alert('No matching section found.');
      }
    }
  });
}


    // ✅ Role Selection
    const roleButtons = document.querySelectorAll('.role-btn');
    const roleTitle = document.getElementById('roleTitle');
    const roleMessage = document.getElementById('roleMessage');
    const roleIcon = document.getElementById('roleIcon');

    const roleIcons = {
        passenger: 'img/passenger.png',
        driver: 'img/driver.png',
        admin: 'img/admin.png'
    };

    const roleMessages = {
        passenger: 'Hello Passenger! Register your account below.',
        driver: 'Hello Driver! Complete your registration.',
        admin: 'Hello Admin! Secure your access by registering below.'
    };

    // ✅ Show / Hide Auth Modal
    function showAuthModal() {
        authModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function hideAuthModal() {
        authModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // ✅ Open modal buttons
    if (formBookButton) formBookButton.addEventListener('click', e => { e.preventDefault(); showAuthModal(); });
    if (loginButton) loginButton.addEventListener('click', e => { e.preventDefault(); showAuthModal(); });

    // ✅ Close modal when clicking outside
    if (authModal) {
        window.addEventListener('click', (e) => {
            if (e.target === authModal) hideAuthModal();
        });
    }

    // ✅ Close modal when X is clicked
    if (closeModalBtn) closeModalBtn.addEventListener('click', hideAuthModal);

    // ✅ Route Tabs Functionality
    const routeTabs = document.querySelectorAll('.route-tab');
    const routeContents = document.querySelectorAll('.routes-content');

    routeTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetRoute = this.getAttribute('data-route');
            
            // Remove active class from all tabs
            routeTabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all route contents
            routeContents.forEach(content => content.classList.remove('active'));
            // Show target route content
            const targetContent = document.getElementById(targetRoute + '-routes');
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });

    // ✅ Overlay Animation for Sign In / Sign Up
    if (signUpBtn && authContainer) {
        signUpBtn.addEventListener('click', () => authContainer.classList.add('right-panel-active'));
    }
    if (signInBtn && authContainer) {
        signInBtn.addEventListener('click', () => authContainer.classList.remove('right-panel-active'));
    }
    
    // ✅ Mobile Switch Buttons
    const mobileSignUp = document.getElementById('mobileSignUp');
    const mobileSignIn = document.getElementById('mobileSignIn');
    
    if (mobileSignUp && authContainer) {
        mobileSignUp.addEventListener('click', () => authContainer.classList.add('right-panel-active'));
    }
    if (mobileSignIn && authContainer) {
        mobileSignIn.addEventListener('click', () => authContainer.classList.remove('right-panel-active'));
    }
    
    // Hamburger Menu Toggle
    const hamburger = document.getElementById('hamburger');
    const nav = document.getElementById('nav');
    
    if (hamburger && nav) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            nav.classList.toggle('active');
        });
        
        // Close menu when clicking nav links
        nav.addEventListener('click', (e) => {
            if (e.target.tagName === 'A') {
                hamburger.classList.remove('active');
                nav.classList.remove('active');
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !nav.contains(e.target)) {
                hamburger.classList.remove('active');
                nav.classList.remove('active');
            }
        });
    }

    // ✅ Role Selection Logic
    let selectedRole = '';
    if (roleButtons.length > 0) {
        roleButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                roleButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedRole = this.getAttribute('data-role');

                if (roleIcon) roleIcon.src = roleIcons[selectedRole];
                if (roleTitle) roleTitle.textContent = `Register as ${selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1)}`;
                if (roleMessage) roleMessage.textContent = roleMessages[selectedRole];
                if (!selectedRole) return alert('Please select a role!');
            });
        });
    }


// REGISTER FORM SUBMISSION HANDLER
const registerFormElement = document.getElementById("registerForm");

if (registerFormElement) {
    registerFormElement.addEventListener("submit", async function(e) {
        e.preventDefault();
        
        // I-check sa console kung gumagana ang button. Dapat lumabas ito!
        console.log("Register button clicked. Starting AJAX call..."); 

        const formData = new FormData(this);
        
        try {
            // Using unified file-based backend
            const response = await fetch("Php/register_hybrid.php", {
                method: "POST",
                body: formData
            });

            // 🛑 CHECK #1: Network Status Error (e.g., 404 Not Found)
            if (!response.ok) {
                const errorText = await response.text();
                showPopup(`Network Error (${response.status}): Failed to connect to PHP.`, "error");
                console.error("Fetch failed with status:", response.status, "Response:", errorText);
                return;
            }

            // 🛑 CHECK #2: JSON Parsing Error
            const data = await response.json();

            if (data.success) {
                console.log("Registration successful! Redirecting to:", data.redirect);
                
                showPopup("Registration successful! Welcome.", "success");

                setTimeout(() => {
                    window.location.href = data.redirect; 
                }, 1500);
            
            } else {
                showPopup(data.message, "error");
                console.warn("Server validation failed:", data.message);
            }

        } catch (error) {
            // Catching error if response.json() fails (PHP echo'd something other than JSON)
            showPopup("System Error: Check console for PHP warnings.", "error");
            console.error("CRITICAL JS CATCH ERROR:", error);
        }
    });
} else {
    // Kung hindi gumagana, ito ang lalabas sa console!
    console.error("CRITICAL ERROR: 'registerForm' element not found! Check HTML ID.");
}


//LOGIN FORM SUBMISSION HANDLER
document.getElementById("loginForm").addEventListener("submit", async function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  const response = await fetch('Php/login_hybrid.php', 
    { method: "POST", body: formData });
  const data = await response.json();

  if (data.success) {
    // Close the modal
    hideAuthModal();
    
    // Show success message
    showPopup(data.message, "success");

    // Redirect immediately
    setTimeout(() => {
        window.location.href = data.redirect; 
    }, 1000);
    
  } else {
    showPopup(data.message, "error");
  }
});
});

// About Modal Functions
function openLogoModal() {
    document.getElementById('logoModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeLogoModal() {
    document.getElementById('logoModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openPosterModal() {
    document.getElementById('posterModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closePosterModal() {
    document.getElementById('posterModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openVideoModal() {
    document.getElementById('videoModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeVideoModal() {
    document.getElementById('videoModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    const video = document.querySelector('#videoModal video');
    if (video) video.pause();
}

