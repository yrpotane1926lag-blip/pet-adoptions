const menuOpenButton = document.querySelector("#menu-open-button");
const menuCloseButton = document.querySelector("#menu-close-button");

if (menuOpenButton && menuCloseButton) {
    menuOpenButton.addEventListener("click", () => {
        document.body.classList.toggle("show-mobile-menu");
    });

    menuCloseButton.addEventListener("click", () => menuOpenButton.click());
}

const adoptModal = document.querySelector("#adoptModal");
const detailsModal = document.querySelector("#detailsModal");

function openModal(modal) {
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
}

function closeModal(modal) {
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
}

document.querySelectorAll("[data-open-adopt]").forEach((button) => {
    button.addEventListener("click", () => {
        document.querySelector("#adoptPetId").value = button.dataset.petId;
        document.querySelector("#adoptPetName").textContent = `Applying for ${button.dataset.petName}`;
        openModal(adoptModal);
    });
});

document.querySelectorAll("[data-open-details]").forEach((button) => {
    button.addEventListener("click", () => {
        document.querySelector("#detailsName").textContent = button.dataset.name;
        document.querySelector("#detailsHealth").textContent = button.dataset.health;
        document.querySelector("#detailsBackstory").textContent = button.dataset.backstory;
        document.querySelector("#detailsCondition").textContent = button.dataset.condition;
        document.querySelector("#detailsVaccinated").textContent = button.dataset.vaccinated;
        openModal(detailsModal);
    });
});

document.querySelectorAll("[data-close-modal]").forEach((button) => {
    button.addEventListener("click", () => closeModal(button.closest(".modal")));
});

document.querySelectorAll(".modal").forEach((modal) => {
    modal.addEventListener("click", (event) => {
        if (event.target === modal) {
            closeModal(modal);
        }
    });
});

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        document.querySelectorAll(".modal.is-open").forEach(closeModal);
    }
});
