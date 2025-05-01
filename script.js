// script.js

document.addEventListener('DOMContentLoaded', () => {
    // --- Responsive Navbar Toggle ---
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelector('.nav-links');

    if (navbar && navLinks) {
        // Create a toggle button dynamically
        const toggleButton = document.createElement('button');
        toggleButton.innerHTML = '&#9776;'; // Hamburger icon
        toggleButton.classList.add('nav-toggle');
        // Insert the button into the navbar, typically before the links or after the logo
        // Adjust insertion point based on desired layout
        navbar.insertBefore(toggleButton, navLinks);

        toggleButton.addEventListener('click', () => {
            navLinks.classList.toggle('active'); // Toggle a class to show/hide/animate
        });

        // Add necessary CSS dynamically (or ensure it's in style.css)
        // This ensures the JS works even if CSS isn't fully loaded or is modified
        const style = document.createElement('style');
        style.textContent = `
            .nav-toggle {
                display: none; /* Hidden on larger screens */
                background: none;
                border: none;
                font-size: 1.8em; /* Slightly larger */
                cursor: pointer;
                color: var(--primary-color);
                padding: 5px 10px; /* Add some padding */
                margin-left: auto; /* Push toggle to the right if needed */
            }

            @media (max-width: 768px) {
                .nav-toggle {
                    display: block; /* Show on small screens */
                }
                .navbar {
                    position: relative; /* Needed if nav-links uses absolute positioning */
                    /* flex-wrap: wrap; /* May not be needed if toggle is handled well */
                }
                .nav-links {
                    display: none; /* Hide links by default on mobile */
                    flex-direction: column;
                    width: 100%;
                    text-align: center;
                    background-color: #fff; /* Background for dropdown */
                    position: absolute; /* Position dropdown below navbar */
                    top: 100%; /* Start below the header */
                    left: 0;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Add shadow to dropdown */
                    padding: 0;
                    margin-top: 0; /* Reset margin */
                    max-height: 0; /* Start hidden for transition */
                    overflow: hidden; /* Hide overflow */
                    transition: max-height 0.4s ease-out; /* Smooth transition */
                    z-index: 999; /* Ensure dropdown is above content */
                }
                .nav-links.active {
                    display: flex; /* Show when active */
                    max-height: 500px; /* Allow it to expand (adjust as needed) */
                    padding: 10px 0; /* Padding when open */
                }
                .nav-links li {
                    margin: 12px 0; /* Adjust spacing */
                    margin-left: 0;
                }
                 .nav-links a {
                    padding: 10px 15px; /* Larger touch targets */
                 }
            }
        `;
        document.head.appendChild(style);
    }

    // --- Add other common JS functions here ---
    // Example: Smooth scrolling for anchor links if needed
    // Example: Simple form validation feedback
});