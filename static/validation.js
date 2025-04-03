document.addEventListener("DOMContentLoaded", function () {
    // Fonctions de validation
    function validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function validateName(value) {
        const regex = /^[A-Za-zÀ-ÿ\s'-]+$/;
        return regex.test(value);
    }

    function validatePhone(value) {
        const regex = /^[+]?[(]?[0-9]{1,4}[)]?[-\s./0-9]*$/;
        return regex.test(value);
    }

    function validatePassword(value) {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        return regex.test(value);
    }

    function validateDescription(value) {
        return value.trim().length >= 10;
    }

    function validatePositiveNumber(value) {
        return !isNaN(value) && parseFloat(value) >= 0;
    }

    function validateDate(value) {
        return !isNaN(Date.parse(value));
    }

    function showError(input, message) {
        input.classList.add("invalid");
        let oldError = input.parentElement.querySelector(".error-message");
        if (oldError) oldError.remove();

        const div = document.createElement("div");
        div.className = "error-message";
        div.style.color = "red";
        div.style.fontSize = "0.8em";
        div.textContent = message;
        input.parentElement.appendChild(div);
    }

    function clearError(input) {
        input.classList.remove("invalid");
        const oldError = input.parentElement.querySelector(".error-message");
        if (oldError) oldError.remove();
    }

    function setInputFilters(fields) {
        if (fields.nom) {
            fields.nom.addEventListener("input", () => {
                fields.nom.value = fields.nom.value.replace(/[^A-Za-zÀ-ÿ\s'-]/g, '');
            });
        }
        if (fields.prenom) {
            fields.prenom.addEventListener("input", () => {
                fields.prenom.value = fields.prenom.value.replace(/[^A-Za-zÀ-ÿ\s'-]/g, '');
            });
        }
        if (fields.tel) {
            fields.tel.addEventListener("input", () => {
                fields.tel.value = fields.tel.value.replace(/[A-Za-z]/g, '');
            });
        }
    }

    // Fonction principale à appeler dans chaque page HTML
    window.initFormValidation = function ({ selector, fields }) {
        const form = document.querySelector(selector);
        if (!form) return;

        setInputFilters(fields);

        form.addEventListener("submit", function (e) {
            let isValid = true;

            // Nom
            if (fields.nom && (fields.nom.value.trim() === "" || !validateName(fields.nom.value))) {
                showError(fields.nom, "Nom invalide (lettres uniquement).");
                isValid = false;
            } else if (fields.nom) {
                clearError(fields.nom);
            }

            // Prénom
            if (fields.prenom && (fields.prenom.value.trim() === "" || !validateName(fields.prenom.value))) {
                showError(fields.prenom, "Prénom invalide (lettres uniquement).");
                isValid = false;
            } else if (fields.prenom) {
                clearError(fields.prenom);
            }

            // Email
            if (fields.email && !validateEmail(fields.email.value)) {
                showError(fields.email, "Email invalide.");
                isValid = false;
            } else if (fields.email) {
                clearError(fields.email);
            }

            // Téléphone
            if (fields.tel && !validatePhone(fields.tel.value)) {
                showError(fields.tel, "Numéro de téléphone invalide.");
                isValid = false;
            } else if (fields.tel) {
                clearError(fields.tel);
            }

            // Description
            if (fields.description && !validateDescription(fields.description.value)) {
                showError(fields.description, "Description trop courte (min 10 caractères).");
                isValid = false;
            } else if (fields.description) {
                clearError(fields.description);
            }

            // Mot de passe
            if (fields.password && !validatePassword(fields.password.value)) {
                showError(fields.password, "Mot de passe trop faible (8+ caractères, 1 maj, 1 min, 1 chiffre).");
                isValid = false;
            } else if (fields.password) {
                clearError(fields.password);
            }

            // Titre
            if (fields.titre && fields.titre.value.trim() === "") {
                showError(fields.titre, "Le titre est requis.");
                isValid = false;
            } else if (fields.titre) {
                clearError(fields.titre);
            }

            // Salaire
            if (fields.salaire && !validatePositiveNumber(fields.salaire.value)) {
                showError(fields.salaire, "Rémunération invalide.");
                isValid = false;
            } else if (fields.salaire) {
                clearError(fields.salaire);
            }

            // Dates
            if (fields.date_debut && !validateDate(fields.date_debut.value)) {
                showError(fields.date_debut, "Date de début invalide.");
                isValid = false;
            } else if (fields.date_debut) {
                clearError(fields.date_debut);
            }

            if (fields.date_fin && !validateDate(fields.date_fin.value)) {
                showError(fields.date_fin, "Date de fin invalide.");
                isValid = false;
            } else if (fields.date_fin) {
                clearError(fields.date_fin);
            }

            if (!isValid) e.preventDefault();
        });
    };
});
