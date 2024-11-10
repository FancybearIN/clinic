const wrap = document.querySelector('.wrap');
const loginLink = document.querySelector('.login-link');
const registerLink = document.querySelector('.register-link');
const btnPopup = document.querySelector('.btnLogin-popup');
const iconClose = document.querySelector('.icon-close');

registerLink.addEventListener('click', ()=> {
    wrap.classList.add('active');
});

loginLink.addEventListener('click', ()=> {
    wrap.classList.remove('active');
});

btnPopup.addEventListener('click', ()=> {
    wrap.classList.add('active-popup');
});
iconClose.addEventListener('click', ()=> {
    wrap.classList.remove('active-popup');
});

{/* <script> */}
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize dropdowns
        const dropdowns = document.querySelectorAll('.dropdown-toggle');
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('click', function(event) {
                event.preventDefault();
                const dropdownMenu = this.nextElementSibling;
                dropdownMenu.classList.toggle('show');
            });
        });
    });
{/* </script> */}