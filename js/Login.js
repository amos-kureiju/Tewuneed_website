   // Firebase configuration
   const firebaseConfig = {
    apiKey: "AIzaSyDz5t6mlBzXq7bjK3PGOGyEBo_WzjsHYME",
    authDomain: "tewuneed-marketplace.firebaseapp.com",
    projectId: "tewuneed-marketplace",
    storageBucket: "tewuneed-marketplace.appspot.com",
    messagingSenderId: "999093621738",
    appId: "1:999093621738:web:87b68aa3a5a5ebca395893",
    measurementId: "G-8WNLD8T7GY"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();
const db = firebase.firestore();

// Login Form Handler
document.getElementById('loginForm').addEventListener('submit', (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const errorElement = document.getElementById('error-message');
    
    // Clear previous errors
    errorElement.textContent = "";
    
    // Sign in user with email and password
    auth.signInWithEmailAndPassword(email, password)
        .then((userCredential) => {
            // Check if email is verified
            if (userCredential.user.emailVerified) {
                // Redirect to home page after successful login
                window.location.href = 'home.html';
            } else {
                // If email not verified
                auth.signOut(); // Sign out the user
                errorElement.textContent = "Please verify your email first. Check your inbox for the verification link.";
            }
        })
        .catch((error) => {
            // Handle errors
            errorElement.textContent = error.message;
        });
});

// Forgot password handler
document.getElementById('forgotPassword').addEventListener('click', (e) => {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const errorElement = document.getElementById('error-message');
    
    if (!email) {
        errorElement.textContent = "Please enter your email address first";
        return;
    }
    
    auth.sendPasswordResetEmail(email)
        .then(() => {
            errorElement.textContent = "";
            alert('Password reset email sent. Please check your inbox.');
        })
        .catch((error) => {
            errorElement.textContent = error.message;
        });
});