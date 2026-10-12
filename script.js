const menuOpenButton = document.querySelector("#menu-open-button");
const menuCloseButton = document.querySelector("#menu-close-button");

menuOpenButton.addEventListener("click", () => {

    document.body.classList.toggle("show-mobile-menu");
});

// close menu
menuCloseButton.addEventListener("click",() => menuOpenButton.click());

// login/Signup
const showPopupBtn = document.querySelector(".login-btn");
const hidePopupBtn = document.querySelector(".form-popup .close-btn");
const formPopup = document.querySelector(".form-popup");
const loginSignupLink = document.querySelectorAll(".form-box .bottom-link a");
// show popup
showPopupBtn.addEventListener("click", () => {
    document.body.classList.toggle("show-popup");
});
// hide popup
hidePopupBtn.addEventListener("click",() => showPopupBtn.click());
loginSignupLink.forEach(link => {
    link.addEventListener("click", (e) => {
        e.preventDefault();
        formPopup.classList[link.id === "signup-link" ? 'add' : 'remove']("show-signup");
    });
});