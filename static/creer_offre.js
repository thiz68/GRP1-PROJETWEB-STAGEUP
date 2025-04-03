document.addEventListener('DOMContentLoaded', function() {
    const selectedSkills = document.getElementById('selected-skills');
    const skillsDropdown = document.querySelector('.skills-dropdown');
    const skillItems = document.querySelectorAll('.skill-item');
    const placeholder = document.querySelector('.selected-skills .placeholder');

    // Fonction pour ouvrir/fermer le dropdown
    selectedSkills.addEventListener('click', function() {
        this.classList.toggle('active');
        skillsDropdown.classList.toggle('show');
    });

    // Fermer le dropdown si clic en dehors
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.skills-selector')) {
            selectedSkills.classList.remove('active');
            skillsDropdown.classList.remove('show');
        }
    });

    // Sélectionner/désélectionner une compétence
    skillItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const skillId = this.dataset.id;
            const skillName = this.dataset.name;
            const checkbox = document.getElementById('skill_' + skillId);

            if (this.classList.contains('selected')) {
                // Désélectionner
                this.classList.remove('selected');
                checkbox.checked = false;

                // Supprimer le tag
                const tag = document.querySelector(`.skill-tag[data-id="${skillId}"]`);
                if (tag) tag.remove();

                // Afficher le placeholder si aucune compétence sélectionnée
                if (document.querySelectorAll('.skill-tag').length === 0) {
                    placeholder.style.display = 'block';
                }
            } else {
                // Sélectionner
                this.classList.add('selected');
                checkbox.checked = true;

                // Cacher le placeholder
                placeholder.style.display = 'none';

                // Créer et ajouter le tag
                const skillTag = document.createElement('div');
                skillTag.className = 'skill-tag';
                skillTag.dataset.id = skillId;
                skillTag.innerHTML = `
                    ${skillName}
                    <span class="remove-skill">&times;</span>
                `;

                // Gestionnaire pour le bouton de suppression
                const removeBtn = skillTag.querySelector('.remove-skill');
                removeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    skillTag.remove();
                    checkbox.checked = false;

                    // Désélectionner dans le dropdown
                    document.querySelector(`.skill-item[data-id="${skillId}"]`).classList.remove('selected');

                    // Afficher le placeholder si aucune compétence sélectionnée
                    if (document.querySelectorAll('.skill-tag').length === 0) {
                        placeholder.style.display = 'block';
                    }
                });

                selectedSkills.insertBefore(skillTag, selectedSkills.lastChild);
            }
        });
    });
});